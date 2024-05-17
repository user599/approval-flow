<?php


namespace Js3\ApprovalFlow\Entity\Interceptor;



use Illuminate\Support\Facades\Log;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/14 18:12
 */
class LogInterceptor implements NodeInterceptor
{

    /**
     * @explain: 默认日志拦截器
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/17 8:06
     * @remark:
     */
    public function intercept(ApprovalFlowContext $context)
    {

        $str_approval_flow_name = $context->getApprovalFlowInstance()->name;
        Log::debug("[Approval-flow]执行{$str_approval_flow_name}",['current' => $context->getCurrentNode(),'args' => $context->getArgs()]);
    }
}
