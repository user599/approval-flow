<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;
use Js3\ApprovalFlow\Model\ApprovalFlowNodeOperator;

/**
 * @explain: 审核节点
 * @author: wzm
 * @date: 2024/5/14 18:29
 */
class AuditNode extends AbstractNode
{

    const AUDIT_TYPE_UNION = 1;
    const AUDIT_TYPE_OR = 2;

    /**
     * @var int 审核类型：与签 或签
     */
    private $audit_type;

    /**
     * @var int 驳回类型：驳回即结束 驳回到上一审批节点
     */
    private $reject_type;

    /**
     * @var string 审核其他操作
     */
    private $other_operate;

    /**
     * @var int 操作方法：【1=通过；2=驳回；3=确认；4=回复】（多个以,隔开）
     */
    private $operate_method;

    /**
     * @var bool 当前节点审批人与流程发起人为同一人时，自动通过
     */
    private $approved_when_same_with_applicant;

    /**
     * @var bool 历史节点为同一人时，自动通过
     */
    private $approved_when_same_with_history;


    /**
     * @var array<ApprovalFlowInstanceNodeRelatedMember> 审核人
     */
    private $auditors;


    function doExecute(ApprovalFlowContext $context)
    {
        $auth_info = $context->getAuthInfo();
        /**
         * 处理是否自动通过当前操作人
         * 1.历史节点存在当前审批人自动通过
         * 2.申请人即是审批人自动通过
         */
        $will_auto_pass = false;
        if ($this->approved_when_same_with_history) {
            //若当前操作人之前审批过
            $temp_node = $this->pre_node;
            while (!empty($temp_node)) {
                if ($temp_node instanceof AuditNode) {
                    foreach ($temp_node->auditors as $related_member) {
                        if ($auth_info->isSameMember($related_member->member_id, $related_member->member_type)) {
                            $will_auto_pass = true;
                            break;
                        }
                    }
                }
                $temp_node = $temp_node->pre_node;
            }
        }
        if ($this->approved_when_same_with_applicant) {
            if ($auth_info->isSameMember(
                $context->getApprovalFlowInstance()->creator_id,
                $context->getApprovalFlowInstance()->creator_type)
            ) {
                $will_auto_pass = true;
            }
        }

        if ($will_auto_pass) {
            //TODO 自动通过当前人员
            $this->obj_service_af_related_member->passMember($auth_info);
        }

    }

    /**
     * @inheritDoc
     */
    protected function canContinueExecute(ApprovalFlowContext $context)
    {
        //若当前节点审核类型是会签（所有人都同意
        if ($this->audit_type == self::AUDIT_TYPE_UNION) {
            return collect($this->auditors)->filter(function ($item) {
                    return $item->status !== ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS;
                })->count() > 0;
        } elseif ($this->audit_type == self::AUDIT_TYPE_OR) {
            //或签，只要有一个同意即可
            return collect($this->auditors)->filter(function ($item) {
                    return $item->status == ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS;
                })->count() > 0;
        } else {
            throw new ApprovalFlowException("审核节点[{$this->name}]的审核类型异常:{$this->audit_type}，请联系管理员");
        }
    }
    //region getter // setter
    /**
     * @param int $audit_type
     * @return AuditNode
     */
    public function setAuditType(int $audit_type): AuditNode
    {
        $this->audit_type = $audit_type;
        return $this;
    }

    /**
     * @param int $reject_type
     * @return AuditNode
     */
    public function setRejectType(int $reject_type): AuditNode
    {
        $this->reject_type = $reject_type;
        return $this;
    }

    /**
     * @param string $other_operate
     * @return AuditNode
     */
    public function setOtherOperate(string $other_operate): AuditNode
    {
        $this->other_operate = $other_operate;
        return $this;
    }

    /**
     * @param int $operate_method
     * @return AuditNode
     */
    public function setOperateMethod(int $operate_method): AuditNode
    {
        $this->operate_method = $operate_method;
        return $this;
    }

    /**
     * @param bool $approved_when_same_with_applicant
     * @return AuditNode
     */
    public function setApprovedWhenSameWithApplicant(bool $approved_when_same_with_applicant): AuditNode
    {
        $this->approved_when_same_with_applicant = $approved_when_same_with_applicant;
        return $this;
    }

    /**
     * @param bool $approved_when_same_with_history
     * @return AuditNode
     */
    public function setApprovedWhenSameWithHistory(bool $approved_when_same_with_history): AuditNode
    {
        $this->approved_when_same_with_history = $approved_when_same_with_history;
        return $this;
    }

    /**
     * @param ApprovalFlowInstanceNodeRelatedMember[] $auditors
     * @return AuditNode
     */
    public function setAuditors(array $auditors): AuditNode
    {
        $this->auditors = $auditors;
        return $this;
    }




}
