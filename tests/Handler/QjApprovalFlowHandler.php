<?php


namespace Js3\ApprovalFlow\Test\Handler;


use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\Handler\AbstractApprovalFlowHandler;
use Js3\ApprovalFlow\HttpClient\HttpClient;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/20 9:37
 */
class QjApprovalFlowHandler extends AbstractApprovalFlowHandler
{

    /**
     * @var string 审批流标识
     */
    protected $approval_flow_slug = 'qj';


    /**
     * @inheritDoc
     */
    function handleAuditExtraOperate(AuditNode $node)
    {
        dd(123);
    }

    /**
     * @inheritDoc
     */
    function handleCarbonCopyExtraOperate(CarbonCopyNode $node)
    {
        dd(222);
    }
}