<?php


namespace Js3\ApprovalFlow\Exceptions;


/**
 * @explain: 远程调用异常，目前用在请求创建审批流实例时
 * @author: wzm
 * @date: 2024/5/17 12:41
 */
class RemoteCallErrorException extends ApprovalFlowException
{

    protected $response;

    /**
     * @param $response
     */
    public function __construct($message, $response, $code = 500, $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }


}
