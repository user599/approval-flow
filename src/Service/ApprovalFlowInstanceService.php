<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;

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
     * @var  ApprovalFlowInstanceNodeOperateRecordService
     */
    protected $obj_service_operate_record;

    /**
     * @var ApprovalFlowInstanceNodeRelatedMemberService
     */
    protected $obj_service_related_member;


    /**
     * @param ApprovalFlowInstance $obj_model_approval_flow_instance
     */
    public function __construct(
        ApprovalFlowInstanceNodeService              $obj_service_instance_node,
        ApprovalFlowInstanceNodeOperateRecordService $obj_service_operate_record,
        ApprovalFlowInstanceNodeRelatedMemberService $obj_service_related_member,
        ApprovalFlowInstance                         $obj_model_instance
    )
    {
        $this->obj_model_instance = $obj_model_instance;
        $this->obj_service_operate_record = $obj_service_operate_record;
        $this->obj_service_related_member = $obj_service_related_member;
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
     * @explain:结束实例
     * @param $instance_id
     * @param $remark
     * @author: wzm
     * @date: 2024/6/3 15:57
     * @remark: 结束并不会将当前节点id置空
     */
    public function endInstance($instance_id, $remark = null)
    {
        return $this->obj_model_instance->newQuery()
            ->where("id", $instance_id)
            ->update([
                "status" => ApprovalFlowInstance::STATUS_END,
                "end_time" => date('Y-m-d H:i:s'),
                "remark" => $remark
            ]);

    }
}
