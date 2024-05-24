<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperateRecord;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceNodeOperateRecordService

{

    /**
     * @var ApprovalFlowInstanceNodeOperateRecord
     */
    private $obj_model_operate_record;

    /**
     * @param ApprovalFlowInstanceNodeOperateRecord $obj_model_operate_record
     */
    public function __construct(ApprovalFlowInstanceNodeOperateRecord $obj_model_operate_record)
    {

        $this->obj_model_operate_record = $obj_model_operate_record;
    }

    /**
     * @explain: 创建一条操作记录
     * @param $node_id
     * @param $instance_id
     * @param $related_member_id
     * @param $status
     * @param $remark
     * @author: wzm
     * @date: 2024/5/24 15:58
     * @remark:
     */
    public function createOperateRecord($node_id, $instance_id, $related_member_id,$status,  $remark = null) {
        return $this->obj_model_operate_record->newQuery()->create([
            "node_id" => $node_id,
            "instance_id" => $instance_id,
            "related_member_id" => $related_member_id,
            "status" => $status,
            "remark" => $remark
        ]);
    }



}
