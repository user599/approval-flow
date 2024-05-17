<?php


namespace Js3\ApprovalFlow\Entity\Interceptor;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/14 18:01
 */
interface NodeInterceptor
{

    /**
     * @explain: 拦截方法
     * @param ApprovalFlowContext $context
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 8:06
     * @remark:
     */
    public function intercept(ApprovalFlowContext $context);



}
