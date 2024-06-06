<?php
/**
 * @explain:
 * @author: wzm
 * @date: 2024/6/3 9:57
 */


use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;

if (!function_exists('approvalFlowTransaction')) {

    /**
     * @explain: 审批流数据库事务
     * @param $callback
     * @return mixed
     * @throws Throwable
     * @author: wzm
     * @date: 2024/6/3 9:59
     * @remark:
     */
    function approvalFlowTransaction($callback)
    {
        return DB::connection(config("approval-flow.db.connection"))
            ->transaction($callback);
    }
}

if (!function_exists('approvalFlowAssert')) {

    /**
     * @explain:断言-默认抛出 ApprovalFlowException
     * @param bool $condition 断言条件
     * @param string $message      断言提示
     * @param Exception|null $clazz 要自定义抛出的异常类
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/6/3 10:02
     * @remark:
     */
    function approvalFlowAssert($condition, $message,?Exception $clazz = null)
    {
        if ($condition) {
            if (!empty($clazz)) {
                throw new $clazz($message);
            } else {
                throw new ApprovalFlowException($message);
            }
        }
    }
}


if (!function_exists('approvalFlowEnableQueryLog')) {

    /**
     * @explain: 开启打印sql日志
     * @param $callback
     * @return mixed
     * @throws Throwable
     * @author: wzm
     * @date: 2024/6/3 9:59
     * @remark:
     */
    function approvalFlowEnableQueryLog()
    {
        return DB::connection(config("approval-flow.db.connection"))
            ->enableQueryLog();
    }
}

if (!function_exists('approvalFlowGetQueryLog')) {

    /**
     * @explain: 打印sql日志
     * @param $callback
     * @return mixed
     * @throws Throwable
     * @author: wzm
     * @date: 2024/6/3 9:59
     * @remark:
     */
    function approvalFlowGetQueryLog()
    {
        return DB::connection(config("approval-flow.db.connection"))
            ->getQueryLog();
    }
}

