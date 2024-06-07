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


    /**
     * 人员状态
     */
    const STATUS_UN_OPERATE = 0;        //未操作
    const STATUS_PASS = 1;          //通过
    const STATUS_REJECT = 2;        //拒绝
    const STATUS_WITHDRAW = 3;      //撤回

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

    /**
     * @explain: 操作记录
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     * @author: wzm
     * @date: 2024/5/30 14:41
     * @remark:
     */
    public function operateRecords()
    {
        return $this->hasMany(ApprovalFlowInstanceNodeOperateRecord::class, "related_member_id");
    }

    /**
     * @explain: 局部作用域，基于身份信息查询
     * @param $query
     * @param AuthInfo $authInfo
     * @return mixed
     * @author: wzm
     * @date: 2024/5/30 14:41
     * @remark: newQuery()->ofAuth($auth_info)->get()
     */
    public function scopeOfAuth($query, AuthInfo $authInfo)
    {
        return $query->where("member_id", $authInfo->getAuthId())
            ->where("member_type", $authInfo->getAuthType());
    }
}
