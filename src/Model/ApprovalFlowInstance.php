<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowInstance extends Model
{
    use SoftDeletes;
    protected $table = 'approval_flow_instance';
    protected $guarded = [];

    /**
     * 实例状态：1：未开始，2：进行中，3：已完成，4：撤销
     */
    const STATUS_NOT_START = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FINISH = 3;
    const STATUS_REVOCATION = 4;




    /**
     * @explain:节点信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author: wzm
     * @date: 2024/5/20 9:29
     * @remark:
     */
    public function nodes()
    {
        return $this->hasMany(ApprovalFlowInstanceNode::class,"instance_id");
    }

    public function currentNode() {
        return $this->hasOne(ApprovalFlowNode::class,"current_node_id");
    }
    /**
     * @explain:操作人信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author: wzm
     * @date: 2024/5/20 9:29
     * @remark:
     */
    public function operators() {
        return $this->hasMany(ApprovalFlowInstanceNodeOperator::class,"instance_id");
    }
}
