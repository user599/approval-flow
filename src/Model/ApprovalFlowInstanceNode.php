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

    /**
     * 节点类型
     * 申请节点
     * 分支节点
     * 抄送节点
     * 审批节点
     */
    const NODE_TYPE_APPLY = 1;
    const NODE_TYPE_BRANCH = 2;
    const NODE_TYPE_APPROVE = 3;
    const NODE_TYPE_CARBON_COPY = 4;

    /**
     * 节点状态：0未操作 1通过
     */
    const STATUS_UN_OPERATE = 0;
    const STATUS_PASS = 1;

    /**
     * 审批类型：
     * 或签
     * 会签
     */
    const APPROVE_TYPE_OR = 1;
    const APPROVE_TYPE_UNION = 2;

    /**
     * 驳回类型
     * 驳回即结束
     * 驳回至上一审批
     */
    const REJECT_TYPE_REJECT_TO_END = 1;
    const REJECT_TYPE_REJECT_TO_PRE_APPROVE = 2;

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
        return $this->hasMany(ApprovalFlowInstanceNodeOperateRecord::class,"node_id");
    }


}
