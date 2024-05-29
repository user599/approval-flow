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
        $ary_metadata = json_decode($model->metadata,true);
        return $node->setApproveType($ary_metadata['approve_type']??null)
            ->setRejectType($ary_metadata['reject_type']??null)
            ->setOtherOperate($ary_metadata['other_operate']??null)
            ->setOperateMethod($ary_metadata['operate_method']??null)
            ->setApprovedWhenSameWithApplicant($ary_metadata['approved_when_same_with_applicant']??null)
            ->setApprovedWhenSameWithHistory($ary_metadata['approved_when_same_with_history']??null)
            ->setAuditors($model->relatedMembers)
            ;

    }


}
