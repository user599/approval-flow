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

    public function nodes()
    {
        return $this->hasMany(ApprovalFlowNode::class,"instance_id");
    }

    public function records() {
        return $this->hasMany(ApprovalFlowOperateRecord::class,"instance_id");
    }
}