<?php


namespace Js3\ApprovalFlow\Parser\impl;


use Illuminate\Database\Eloquent\Model;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Entity\Node\ApplyNode;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Parser\AbstractNodeParser;

/**
 * @explain: 审批节点解析器
 * @author: wzm
 * @date: 2024/5/20 15:34
 */
class AuditNodeParser extends AbstractNodeParser
{

    protected function newNode()
    {
        return $this->app->make(AuditNode::class);
    }

    /**
     * @explain:
     * @param AuditNode $node
     * @param Model $model
     * @author: wzm
     * @date: 2024/5/27 14:19
     * @remark:
     */
    protected function parseExtra(AbstractNode $node, Model $model)
    {

        return $node->setAuditType($model->audit_type)
            ->setRejectType($model->reject_type)
            ->setOtherOperate($model->other_operate)
            ->setOperateMethod($model->operate_method)
            ->setApprovedWhenSameWithApplicant($model->approved_when_same_with_applicant)
            ->setApprovedWhenSameWithHistory($model->approved_when_same_with_history)
            ->setAuditors($model->relatedMembers)
            ;

    }


}