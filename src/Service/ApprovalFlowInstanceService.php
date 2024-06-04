<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceService
{

    /**
     * @var ApprovalFlowInstance
     */
    protected $obj_model_instance;

    /**
     * @var ApprovalFlowInstanceNodeService
     */
    protected $obj_service_instance_node;

    /**
     * @param ApprovalFlowInstance $obj_model_approval_flow_instance
     */
    public function __construct(
        ApprovalFlowInstanceNodeService $obj_service_instance_node,
        ApprovalFlowInstance            $obj_model_instance
    )
    {
        $this->obj_model_instance = $obj_model_instance;
        $this->obj_service_instance_node = $obj_service_instance_node;
    }

    /**
     * @explain: 基于id获取实例
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws ModelNotFoundException
     * @author: wzm
     * @date: 2024/5/17 14:03
     * @remark:
     */
    public function findById($id)
    {
        return $this
            ->obj_model_instance
            ->newQuery()
            ->findOrFail($id);
    }

    /**
     * @explain: 表单验证规则
     * @return array
     * @author: wzm
     * @date: 2024/6/4 15:02
     * @remark:
     */
    private function validateRule() {
        return [
            "type_id" => "required|integer",
            "id" => "required|integer",
            "allow_withdraw" => [
                "required",
                Rule::in([ApprovalFlowInstance::ALLOW_WITHDRAW_TRUE, ApprovalFlowInstance::ALLOW_WITHDRAW_FALSE])
            ],
            "withdraw_type" => [
                "required_if:allow_withdraw," . ApprovalFlowInstance::ALLOW_WITHDRAW_TRUE,
                Rule::in([ApprovalFlowInstance::WITHDRAW_TYPE_NOT_IN_PROGRESS, ApprovalFlowInstance::WITHDRAW_TYPE_IN_PROGRESS, ApprovalFlowInstance::WITHDRAW_TYPE_END])
            ],
            "node" => "required|array",
            "form_data" => "nullable",
        ];
    }

    /**
     * @explain:保存实例到数据库
     * @param $ary_data
     * @param AuthInfo $auth_info
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|ApprovalFlowInstance|\LaravelIdea\Helper\Js3\ApprovalFlow\Model\_IH_ApprovalFlowInstance_QB
     * @author: wzm
     * @date: 2024/5/20 16:58
     * @remark: 此方法没有事务包裹，调用时自行包裹事务
     */
    public function saveInstance($ary_data, AuthInfo $auth_info)
    {

        $validator = Validator::make($ary_data, $this->validateRule());
        if($validator->fails()) {
            Log::error("[approval-flow]创建审批实例失败。",[$validator->errors()]);
        }
        $ary_insert_data = [
            "config_id" => $ary_data["id"],
            "allow_withdraw" => $ary_data["allow_withdraw"],
            "withdraw_type" => $ary_data["withdraw_type"],
            "creator_id" => $auth_info->getAuthId(),
            "creator_type" => $auth_info->getAuthType(),
            "form_data" => json_encode($ary_data['form_data']),
            "status" => ApprovalFlowInstance::STATUS_NOT_START,
        ];
        $obj_instance = $this->obj_model_instance->newQuery()->create($ary_insert_data);
        $obj_node = $this->obj_service_instance_node->createNode($ary_data["node"], $obj_instance->id);
        //保存一下当前节点id
        $obj_instance->current_node_id = $obj_node->id;
        $obj_instance->save();
        return $obj_instance;
    }

    /**
     * @explain: 审批拒绝
     * @param $node_id
     * @param AuthInfo $operate_auth_info
     * @param $remark
     * @return mixed|void
     * @author: wzm
     * @date: 2024/5/30 14:46
     * @remark:
     */
    public function refuseByNodeId($node_id, AuthInfo $operate_auth_info, $remark = null)
    {
        return approvalFlowTransaction(
            function () use ($node_id, $operate_auth_info, $remark) {
                list($obj_instance, $current_node, $current_related_member) = $this->preHandleRejectInfo($node_id, $operate_auth_info);
                //添加一条当前人员的拒绝记录
                $current_related_member->operateRecords()->create([
                    "instance_id" => $current_related_member->instance_id,
                    "node_id" => $current_related_member->node_id,
                    "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_REJECT,
                    "operate_time" => date('Y-m-d H:i:s'),
                    "remark" => $remark
                ]);

                //是否驳回到上一审核节点
                if ($current_node->reject_type == ApprovalFlowInstanceNode::REJECT_TYPE_REJECT_TO_PRE_APPROVE) {
                    //获取上一级审核节点
                    $node_group_by_parent_id = $obj_instance->nodes->keyBy("parent_id");
                    $pre_node = $current_node;
                    $ary_rollback_node_info = [];
                    while (!empty($pre_node = $node_group_by_parent_id[$pre_node->id])) {
                        $ary_rollback_node_info[] = $pre_node;
                        if ($pre_node->type == ApprovalFlowInstanceNode::NODE_TYPE_APPROVE) {
                            break;
                        }
                    }
                }
                //判断上级审核节点是否存在
                if (!empty($pre_node)) {
                    /**
                     * 则将从该节点开始的所有节点状态为未操作
                     * 将所有相关人设置为未操作
                     */
                    foreach ($ary_rollback_node_info as $node) {
                        $node->relatedMembers()->update([
                            "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE,
                            "operate_time" => null
                        ]);
                        $node->status = ApprovalFlowInstanceNode::STATUS_UN_OPERATE;
                        $node->pass_time = null;
                        $node->remark = null;
                        $node->save();
                    }
                    //设置实例的当前节点
                    $obj_instance->current_node_id = $pre_node->id;
                } else {
                    //若节点为拒绝即结束，或者没有上级审批节点，则直接结束
                    //结束当前实例
                    $this->endInstance($obj_instance->id, $remark);
                }
                $obj_instance->save();
            }
        );
    }

    /**
     * @explain: 预处理拒绝信息
     * @param $node_id      节点id
     * @param AuthInfo $operate_auth_info 当前操作人信息
     * @return array
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/30 15:06
     * @remark:
     */
    private function preHandleRejectInfo($node_id, AuthInfo $operate_auth_info)
    {
        $obj_node = $this->obj_service_instance_node->findById($node_id);
        $obj_instance = $obj_node->instance ?? null;

        $current_node = $obj_instance->currentNode;
        //当前节点、实例必须正常
        if (
            empty($obj_instance)
            || $obj_instance->status != ApprovalFlowInstance::STATUS_RUNNING
            || $current_node->type != ApprovalFlowInstanceNode::NODE_TYPE_APPROVE
        ) {
            throw new ApprovalFlowException("当前审批流数据已更新，请刷新后操作");
        }

        //查找当前操作人
        try {
            /** @var ApprovalFlowInstanceNodeRelatedMember $current_related_member */
            $current_related_member = $current_node->relatedMembers
                ->ofAuth($operate_auth_info)
                ->firstOrFail();
        } catch (\Exception $e) {
            throw new ApprovalFlowException("当前人员无法执行驳回操作");
        }
        $obj_instance->loadMissing(["nodes"]);
        return [
            $obj_instance, $current_node, $current_related_member
        ];


    }

    /**
     * @explain:结束实例
     * @param $instance_id
     * @param $remark
     * @author: wzm
     * @date: 2024/6/3 15:57
     * @remark:
     */
    public function endInstance($instance_id, $remark = null)
    {
        $this->obj_model_instance->newQuery()
            ->where("id", $instance_id)
            ->update([
                "status" => ApprovalFlowInstance::STATUS_END,
                "end_time" => date('Y-m-d H:i:s'),
                "remark" => $remark
            ]);

    }
}
