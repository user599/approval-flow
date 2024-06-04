<?php


namespace Js3\ApprovalFlow\Model;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:54
 */
class ApprovalFlowInstance extends AbstractApprovalFlowBaseModel
{

    protected $table = 'approval_flow_instance';
    protected $guarded = [];

    /**
     * 实例状态：1：未开始，2：进行中，3：结束，4：撤销 5:拒绝
     */
    const STATUS_NOT_START = 1;
    const STATUS_RUNNING = 2;
    const STATUS_END = 3;
    const STATUS_WITHDRAW = 4;
    const STATUS_REFUSE = 5;

    /**
     * 是允许撤回
     */
    const ALLOW_WITHDRAW_TRUE = 1;
    const ALLOW_WITHDRAW_FALSE = 0;

    /**
     * 撤回类型 1 未进入流程时 2流程中 3 流程结束时
     */
    const WITHDRAW_TYPE_NOT_IN_PROGRESS = 1;
    const WITHDRAW_TYPE_IN_PROGRESS = 2;
    const WITHDRAW_TYPE_END = 3;

    /**
     * 是否存在审核信息
     */
    const HAS_AUDIT_TRUE  = 1;
    const HAS_AUDIT_FALSE = 0;



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

    /**
     * @explain: 当前节点
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * @author: wzm
     * @date: 2024/6/3 10:47
     * @remark:
     */
    public function currentNode() {
        return $this->belongsTo(ApprovalFlowInstanceNode::class,"current_node_id");
    }

    /**
     * @explain:操作记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author: wzm
     * @date: 2024/5/23 15:30
     * @remark:
     */
    public function operateRecords() {
        return $this->hasMany(ApprovalFlowInstanceNodeOperateRecord::class,"instance_id");
    }
}
