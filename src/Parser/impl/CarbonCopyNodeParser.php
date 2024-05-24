<?php


namespace Js3\ApprovalFlow\Parser\impl;


use Illuminate\Database\Eloquent\Model;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Entity\Node\ApplyNode;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;
use Js3\ApprovalFlow\Parser\AbstractNodeParser;

/**
 * @explain: 抄送节点解析器
 * @author: wzm
 * @date: 2024/5/20 15:34
 */
class CarbonCopyNodeParser extends AbstractNodeParser
{



    protected function newNode()
    {
        return new CarbonCopyNode();
    }





}