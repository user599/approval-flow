<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceNodeService
{

    /**
     * @var ApprovalFlowInstanceNodeRelatedMemberService
     */
    protected $obj_service_related_member;

    /**
     * @var ApprovalFlowInstanceNodeOperateRecordService
     */
    protected $obj_service_operate_record;

    /**
     * @var ApprovalFlowInstanceNode
     */
    private $obj_model_node;

    /**
     * @param ApprovalFlowInstanceNodeRelatedMemberService $obj_service_related_member
     * @param ApprovalFlowInstanceNode $obj_model_node
     */
    public function __construct(
        ApprovalFlowInstanceNodeRelatedMemberService $obj_service_related_member,
        ApprovalFlowInstanceNodeOperateRecordService $obj_service_operate_record,
        ApprovalFlowInstanceNode                     $obj_model_node
    )
    {
        $this->obj_service_related_member = $obj_service_related_member;
        $this->obj_service_operate_record = $obj_service_operate_record;
        $this->obj_model_node = $obj_model_node;

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
            ->obj_model_node
            ->newQuery()
            ->findOrFail($id);
    }

    /**
     * @explain:回滚节点
     * @param ApprovalFlowInstanceNode $node
     * @return ApprovalFlowInstanceNode
     * @author: wzm
     * @date: 2024/6/7 16:36
     * @remark:
     */
    public function rollbackNode(ApprovalFlowInstanceNode $node)
    {

        $node->relatedMembers()->update([
            "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE,
            "remark" => null,
            "operate_time" => null,
        ]);
        $node->status = ApprovalFlowInstanceNode::STATUS_UN_OPERATE;
        $node->pass_time = null;
        $node->save();
        return $node;
    }

}
