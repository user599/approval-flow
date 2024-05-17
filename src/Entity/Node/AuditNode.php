<?php


namespace Js3\ApprovalFlow\Entity\Node;



use Js3\ApprovalFlow\Entity\ApprovalFlowContext;

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


    }

    protected function canContinueExecute(ApprovalFlowContext $context)
    {
        /**
         * 审批节点只有当前节点的审批数据全部完成后才能继续执行
         */


    }


}
