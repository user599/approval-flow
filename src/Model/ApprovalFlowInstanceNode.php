<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowInstanceNode extends AbstractApprovalFlowBaseModel
{
    protected $table = 'approval_flow_instance_node';
    protected $guarded = [];

    const NODE_TYPE_APPLY = 1;
    const NODE_TYPE_CARBON_COPY = 3;
    const NODE_TYPE_AUDIT = 4;
    const NODE_TYPE_END=5;

    /**
     * @explain:所属实例
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author: wzm
     * @date: 2024/5/20 9:29
     * @remark:
     */
    public function instance()
    {
        return $this->belongsTo(ApprovalFlowInstance::class, "instance_id");
    }

    /**
     * @explain: 操作人信息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author: wzm
     * @date: 2024/5/20 9:30
     * @remark:
     */
    public function relatedMembers() {
        return $this->hasMany(ApprovalFlowInstanceNodeRelatedMember::class,"node_id");
    }


    public function operateRecords() {
        return $this->hasMany(ApprovalFlowInstanceOperateRecord::class,"node_id");
    }


}
