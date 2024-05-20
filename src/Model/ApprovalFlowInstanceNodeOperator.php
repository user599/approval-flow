<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowInstanceNodeOperator extends Model
{
    use SoftDeletes;

    protected $table = 'approval_flow_instance_node_operator';
    protected $guarded = [];

    const OPERATOR_STATUS_UN_OPERATE = 0;
    const OPERATOR_STATUS_PASS = 1;
    const OPERATOR_STATUS_REFUSE = 2;
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
        return $this->belongsTo(ApprovalFlowInstanceNode::class, "instance_id");
    }
}