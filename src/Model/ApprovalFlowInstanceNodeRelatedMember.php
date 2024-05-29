<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Js3\ApprovalFlow\Entity\AuthInfo;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowInstanceNodeRelatedMember extends AbstractApprovalFlowBaseModel
{

    protected $table = 'approval_flow_instance_node_related_member';
    protected $guarded = [];


    const STATUS_UN_OPERATE = 0;
    const STATUS_PASS = 1;
    const STATUS_REFUSE = 2;
    const STATUS_WITHDRAW = 3;

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

    public function operateRecords() {
        return $this->hasMany(ApprovalFlowInstanceNodeOperateRecord::class,"related_member_id");
    }

    public function scopeOfAuth($query,AuthInfo $authInfo) {
        return $query->where("member_id",$authInfo->getAuthId())
            ->where("member_type",$authInfo->getAuthType());
    }
}
