<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowNode extends Model
{
    protected $table = 'approval_flow_node';
    protected $guarded = [];

    public function instance()
    {
        return $this->belongsTo(ApprovalFlowInstance::class, "instance_id");
    }


}