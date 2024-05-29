<?php


namespace Js3\ApprovalFlow\Controller;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Js3\ApprovalFlow\Encrypter\Encrypter;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Generators\RelateApplicationFactory;
use Js3\ApprovalFlow\Generators\RelateApplicationGenerator;

/**
 * @explain: 审批流关联应用相关控制器
 * @author: wzm
 * @date: 2024/5/17 15:26
 */
class ApprovalFlowRelatedApplicationController extends Controller
{

    /**
     * @var Application
     */
    private $application;

    /**
     * @var AuthInfo $auth_info
     */
    private $auth_info;

    public function __construct(Application $application, Request $request, Encrypter $encrypter)
    {
        $this->application = $application;
        //验证身份信息
        $str_token = $request->header("token");
        if (empty($str_token)) {
            throw new ApprovalFlowException("身份验证失败：未知的身份信息");
        } else {
            $auth_info = $encrypter->decrypt($str_token);
            $auth_info = json_decode($auth_info,true);
            if (empty($auth_info["auth_data"]) || empty($auth_info["auth_data"]["id"]) || empty($auth_info["auth_type"])) {
                throw new ApprovalFlowException("身份验证失败：缺少必要参数");
            }
            $this->auth_info = new AuthInfo($auth_info['auth_data'], $auth_info['auth_type']);
        }
        return $this->auth_info;
    }

    /**
     * @explain: 获取指定关联应用下拉列表数据
     * @param $slug 关联应用标识
     * @return array
     * @throws ApprovalFlowException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author: wzm
     * @date: 2024/5/20 9:15
     * @remark:
     */
    public function getOptions($slug): array
    {
        /** @var RelateApplicationGenerator $generator */
        $generator = $this->application->make(RelateApplicationFactory::chooseGenerator($slug));
        return $generator->options($this->auth_info);

    }

    /**
     * @explain: 获取指定选项下的所有子集
     * @param $slug     关联应用标识
     * @param $id       指定选项的id
     * @return array
     * @throws ApprovalFlowException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author: wzm
     * @date: 2024/5/20 9:16
     * @remark:
     */
    public function getChildren($slug, $id): array
    {
        /** @var RelateApplicationGenerator $generator */
        $generator = $this->application->make(RelateApplicationFactory::chooseGenerator($slug));
        return $generator->children($this->auth_info, $id);
    }

}