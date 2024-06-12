<?php
/**
 * @explain:
 * @author: wzm
 * @date: 2024/6/3 9:57
 */


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;

if (!function_exists('approvalFlowTransaction')) {

    /**
     * @explain: 审批流数据库事务
     * @param $callback
     * @return mixed
     * @throws Throwable
     * @author: wzm
     * @date: 2024/6/3 9:59
     * @remark: 1.拼接数据库链接，
     *          2.包装找不到模型时的异常
     */
    function approvalFlowTransaction($callback)
    {
        return DB::connection(config("approval-flow.db.connection"))
            ->transaction(function () use ($callback) {
                try {
                    return $callback();
                } catch (ModelNotFoundException $exception) {
                    throw new ApprovalFlowException("未知或已删除的数据，请刷新后重试:[{$exception->getModel()}]");
                }
            });
    }
}

if (!function_exists('approvalFlowAssert')) {

    /**
     * @explain:断言-默认抛出 ApprovalFlowException
     * @param bool $condition 断言条件，为 true 则抛出异常
     * @param string $message 断言提示
     * @param Exception|null $clazz 要自定义抛出的异常类
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/6/3 10:02
     * @remark:
     */
    function approvalFlowAssert($condition, $message, ?Exception $clazz = null)
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
     * @return mixed
     * @throws Throwable
     * @author: wzm
     * @date: 2024/6/3 9:59
     * @remark:
     */
    function approvalFlowEnableQueryLog()
    {
        DB::connection(config("approval-flow.db.connection"))
            ->enableQueryLog();
    }
}

if (!function_exists('approvalFlowGetQueryLog')) {

    /**
     * @explain: 打印sql日志
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

