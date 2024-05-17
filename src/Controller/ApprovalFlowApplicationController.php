<?php


namespace Js3\ApprovalFlow\Controller;


use Doctrine\Inflector\GenericLanguageInflectorFactory;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Js3\ApprovalFlow\Encrypter\Encrypter;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Generators\RelateApplicationFactory;
use Js3\ApprovalFlow\Generators\RelateApplicationGenerator;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 15:26
 */
class ApprovalFlowApplicationController extends Controller
{

    /**
     * @var Application
     */
    private $application;

    /**
     * @var AuthInfo $auth_info
     */
    private $auth_info;
    public function __construct(Application $application,Request $request,Encrypter $encrypter)
    {
        $this->application = $application;
        //验证身份信息
        try {
            $auth_data
                = $encrypter->decrypt($request->header("token", ""));
            if (empty($auth_data) ) {
                //调试用
                if (!$request->exists("debug")) {
                    throw new ApprovalFlowException("未知的身份信息");
                } else {
                    $this->auth_info = new AuthInfo(["id"=>15],AuthInfo::AUTH_TYPE_FRONT);
                }
            } else {
                $this->auth_info = new AuthInfo($auth_data['auth_data'],$auth_data['auth_type']);
            }
        } catch (\Exception $e) {
            throw new ApprovalFlowException("验证身份信息失败",500,$e);
        }

    }

    public function getApplicationInfo($slug)
    {
        /** @var RelateApplicationGenerator $generator */
        $generator = $this->application->make(RelateApplicationFactory::chooseGenerator($slug));
        return $generator->options();

    }

    public function getApplicationChildren($slug,Application $application)
    {
        /** @var RelateApplicationGenerator $generator */
        $generator = $this->application->make(RelateApplicationFactory::chooseGenerator($slug));
        return $generator->children($this->auth_info);
    }

}