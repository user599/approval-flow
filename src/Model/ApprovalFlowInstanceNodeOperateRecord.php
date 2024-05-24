<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowInstanceNodeOperateRecord extends AbstractApprovalFlowBaseModel
{

    protected $table = 'approval_flow_instance_node_operate_record';
    protected $guarded = [];

    const STATUS_UN_OPERATE = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;

    /**
     * @explain: 所属实例
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author: wzm
     * @date: 2024/5/20 9:30
     * @remark:
     */
    public function instance()
    {
        return $this->belongsTo(ApprovalFlowInstance::class, "instance_id");
    }

    /**
     * @explain:节点信息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author: wzm
     * @date: 2024/5/20 9:30
     * @remark:
     */
    public function node()
    {
        return $this->belongsTo(ApprovalFlowInstanceNode::class, "node_id");
    }

    public function relatedMember()
    {
        return $this->belongsTo(ApprovalFlowInstanceNodeRelatedMember::class, "related_member_id");
    }
}
