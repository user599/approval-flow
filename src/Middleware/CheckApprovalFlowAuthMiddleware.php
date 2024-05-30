<?php


namespace Js3\ApprovalFlow\Middleware;


use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/30 8:39
 */
class CheckApprovalFlowAuthMiddleware
{

    /**
     * @var Encrypter
     */
    private $encrypter;

    public function __construct(\Js3\ApprovalFlow\Encrypter\Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }


    /**
     * @explain:
     * @param $request
     * @param \Closure $next
     * @return AuthInfo
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/30 8:43
     * @remark:
     */
    public function handle(Request $request, \Closure $next)
    {
        //验证身份信息
        $str_token = $request->header("token");
        if (empty($str_token)) {
            throw new ApprovalFlowException("身份验证失败：未知的身份信息");
        } else {
            $auth_info = $this->encrypter->decrypt($str_token);
            $auth_info = json_decode($auth_info, true);
            if (empty($auth_info["auth_data"]) || empty($auth_info["auth_data"]["id"]) || empty($auth_info["auth_type"])) {
                throw new ApprovalFlowException("身份验证失败：缺少必要参数");
            }
            $auth_info = new AuthInfo($auth_info['auth_data'], $auth_info['auth_type']);
            $request->offsetSet("auth_info", $auth_info);
        }
        return $next($request);
    }

}
