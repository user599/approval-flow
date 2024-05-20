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