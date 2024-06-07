<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
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
    protected $obj_model_related_member;

    /**
     * @var ApprovalFlowInstanceNodeOperateRecordService
     */
    protected $obj_service_operate_record;

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
     * @explain:基于节点id和用户信息获取实例
     * @param int $obj_node
     * @param AuthInfo $obj_auth_info
     * @return mixed
     * @author: wzm
     * @date: 2024/6/7 11:00
     * @remark:
     */
    public function findByNodeIdAndAuthInfo($int_node_id, $obj_auth_info)
    {
        return $this->obj_model_related_member
            ->newQuery()
            ->ofAuth($obj_auth_info)
            ->where("node_id", $int_node_id)
            ->firstOrFail();
    }


    /**
     * @explain: 基于节点和身份信息执行审核通过
     * @param int $int_node_id
     * @param AuthInfo $auth_info
     * @param int $audit_status {@see ApprovalFlowInstanceNodeRelatedMember::STATUS_xxx}
     * @param string $remark 备注信息
     * @return mixed
     * @throws ApprovalFlowException
     * @throws \Throwable
     * @author: wzm
     * @date: 2024/6/7 11:11
     * @remark:
     */
    public function auditByNodeIdAndAuthInfo($int_node_id, $auth_info, $status, $remark = null)
    {
        return approvalFlowTransaction(function () use ($int_node_id, $auth_info, $status, $remark) {
            try {
                $current_related_member = $this->findByNodeIdAndAuthInfo($int_node_id, $auth_info);
            } catch (\Exception $e) {
                throw new ApprovalFlowException("未知或已删除的相关人员");
            }

            approvalFlowAssert($current_related_member->status != ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE, "该人员已执行操作，请勿重复");
            $current_related_member->status = $status;
            $current_related_member->operate_time = now();
            $current_related_member->remark = $remark;
            $current_related_member->save();
            //创建一条操作记录
            $this->obj_service_operate_record->createOperateRecordByRelatedMember(
                $current_related_member,
                $status,
                $remark
            );
            return true;
        });

    }
}
