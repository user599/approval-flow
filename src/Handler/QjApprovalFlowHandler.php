<?php

namespace Js3\ApprovalFlow\Handler;

class QjApprovalFlowHandler extends AbstractApprovalFlowHandler
{

    protected $approval_flow_slug = 'QJ';

    function handleAuditExtraOperate($audit_extra_operate_slug)
    {

    }

    function handleCarbonCopy($carbon_copy_slug)
    {
        if ($carbon_copy_slug == 1) {

        } elseif($carbon_copy_slug == 2) {

        }

    }


}
