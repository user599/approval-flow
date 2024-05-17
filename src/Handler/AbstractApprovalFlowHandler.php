<?php


namespace Js3\ApprovalFlow\Handler;


use Js3\ApprovalFlow\Entity\AuthInfo;

/**
 * @explain:抄送审批流处理
 * @author: wzm
 * @date: 2024/5/14 14:48
 */
abstract class AbstractApprovalFlowHandler implements ApprovalFlowHandler
{

    protected $approval_flow_slug;
    protected $approvalFlowContext;
    protected $auth_info;

    private $http_client;

    /**
     * @param $approval_flow_slug
     * @param AuthInfo $auth_info
     */
    public function __construct(AuthInfo $auth_info)
    {
        $this->auth_info = $auth_info;
    }


    public function generate($form_data)
    {
        $instance_id = 1;
        return $instance_id;

    }

    public function execute($instance_id, $form_data)
    {
        $context = ApprovalFlowContext::generateContextByInstanceId($instance_id);
        $context->getCurrentNode()->execute($context);

    }

    public function auditPass($snapshot_id, $remark = null, $operate_time = null)
    {

        /** @var ApprovalFlowContext $nodeInfo */
        $context = ApprovalFlowContext::generateContextByInstanceId($snapshot_id);
        $context->getCurrentNode();
        $nodeInfo->execute($context);
        //审核通过
        if ($nodeInfo->isFinished() && $nodeInfo->slug) {
            $this->handleAuditExtraOperate($nodeInfo->slug);
        }
    }

    public function reject($snapshot_id, $remark = null, $operate_time = null)
    {
        // TODO: Implement reject() method.
    }

    public function revocation($instance_id, $remark = null, $operate_time = null)
    {
        // TODO: Implement revocation() method.
    }

    public function getStatus($instance_id)
    {
        // TODO: Implement getStatus() method.
    }


    public function getAuthInfo(): AuthInfo
    {
        return $this->auth_info;
    }

    public function getApprovalFlowSlug(): string
    {
        return $this->approval_flow_slug;
    }


    abstract function handleAuditExtraOperate($audit_extra_operate_slug);

    abstract function handleCarbonCopy($carbon_copy_slug);

}
