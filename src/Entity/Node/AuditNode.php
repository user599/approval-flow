<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;
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

    private $audit_type;


    function doExecute(ApprovalFlowContext $context)
    {
        /**
         * 审核节点
         */

    }

    protected function canContinueExecute(ApprovalFlowContext $context)
    {
        //若当前节点审核类型是会签（所有人都同意
        if ($this->audit_type == self::AUDIT_TYPE_UNION) {
            return collect($this->operator ?? [])->filter(function ($item) {
                    return $item->status != ApprovalFlowInstanceNodeOperator::STATUS_PASS;
                })->count() == 0;
        } elseif ($this->audit_type == self::AUDIT_TYPE_OR) {

        } else {
            throw new ApprovalFlowException("审核节点[{$this->name}]的审核类型异常:{$this->audit_type}，请联系管理员");
        }


    }


}
