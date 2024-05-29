<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceNodeRelatedMemberService
{

    /**
     * @var ApprovalFlowInstanceNodeRelatedMember
     */
    private $obj_model_related_member;

    private $obj_service_operate_record;

    /**
     * @param ApprovalFlowInstanceNodeRelatedMember $obj_model_related_member
     */
    public function __construct(
        ApprovalFlowInstanceNodeRelatedMember        $obj_model_related_member,
        ApprovalFlowInstanceNodeOperateRecordService $obj_service_operate_record
    )
    {

        $this->obj_model_related_member = $obj_model_related_member;
        $this->obj_service_operate_record = $obj_service_operate_record;
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
            ->obj_model_related_member
            ->newQuery()
            ->findOrFail($id);
    }

    /**
     * @explain: 创建相关人
     * @param array $operator_data
     * @param $instance_id
     * @param $node_id
     * @author: wzm
     * @date: 2024/5/24 14:10
     * @remark:
     */
    public function createRelatedMember(array $operator_data, $instance_id, $node_id)
    {
        foreach ($operator_data as $operator_data) {
            $ary_insert_operator_data = [
                "node_id" => $node_id,
                "instance_id" => $instance_id,
                "member_id" => $operator_data["member_id"],
                "member_type" => $operator_data["member_type"],
                "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE
            ];
            $this->obj_model_related_member->newQuery()->create($ary_insert_operator_data);
        }
    }

    /**
     * @explain:通过相关人
     * @param $related_members
     * @author: wzm
     * @date: 2024/5/24 15:47
     * @remark:
     */
    public function passMemberByNodeId($node_id, $remark = null)
    {
        return $this->obj_model_related_member->newQuery()
            ->where("node_id", $node_id)
            ->update([
                "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS,
                "operate_time" => date('Y-m-d H:i:s'),
                "remark" => $remark,
            ]);

    }


    public function passMember(ApprovalFlowInstanceNodeRelatedMember $related_member, $remark = null)
    {
        switch ($related_member->status) {
            case ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE:
                DB::transaction(function () use ($related_member,$remark) {
                    $related_member->update([
                        "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS,
                        "operate_time" => date('Y-m-d H:i:s'),
                        "remark" => $remark
                    ]);
                    $this->obj_service_operate_record->createOperateRecord(
                        $related_member->node_id,
                        $related_member->instance_id,
                        $related_member->id,
                        ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS,
                        $remark
                    );
                    return $related_member;
                });
                break;
            case ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS:
                //已通过的不再操作
                break;
            case ApprovalFlowInstanceNodeRelatedMember::STATUS_REFUSE:
                //拒绝与撤销的都不应该继续操作
            case ApprovalFlowInstanceNodeRelatedMember::STATUS_WITHDRAW:
                throw new ApprovalFlowException("已完成操作，请勿重复");
                break;
            default:
                throw new ApprovalFlowException("未知的人员状态:{$related_member->status}");
        }


    }


}
