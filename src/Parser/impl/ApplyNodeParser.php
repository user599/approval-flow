<?php


namespace Js3\ApprovalFlow\Parser\impl;


use Illuminate\Database\Eloquent\Model;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Entity\Node\ApplyNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Parser\AbstractNodeParser;

/**
 * @explain: 申请节点解析器
 * @author: wzm
 * @date: 2024/5/20 15:34
 */
class ApplyNodeParser extends AbstractNodeParser
{

    protected function newNode()
    {
        return new ApplyNode();
    }

    protected function parseExtra(AbstractNode $node, Model $model)
    {
        $node->setApplicant($model->relatedMembers->first()??null);
    }


}