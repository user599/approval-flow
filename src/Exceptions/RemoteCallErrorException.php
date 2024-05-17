<?php


namespace Js3\ApprovalFlow\Exceptions;


/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 12:41
 */
class RemoteCallErrorException extends ApprovalFlowException
{

    protected $response;

    /**
     * @param $response
     */
    public function __construct($message,$response,$code = 500,$previous = null)
    {
        parent::__construct($message,$code,$previous);
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