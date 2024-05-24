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
class ApprovalFlowRelateApplicationController extends Controller
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
        try {
            $str_token = $request->header("token");
            if (empty($str_token)) {
                //TODO 调试用
                if (!$request->exists("debug")) {
                    throw new ApprovalFlowException("未知的身份信息");
                } else {
                    $this->auth_info = new AuthInfo(["id" => 15], AuthInfo::AUTH_TYPE_FRONT);
                }
            } else {
                $auth_info = $encrypter->decrypt($str_token);
                $this->auth_info = new AuthInfo($auth_info['auth_data'], $auth_info['auth_type']);
            }
        } catch (\Exception $e) {
            throw new ApprovalFlowException("验证身份信息失败", 500, $e);
        }

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
    public function getRelateApplicationOptions($slug): array
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
    public function getRelateApplicationChildren($slug, $id): array
    {
        /** @var RelateApplicationGenerator $generator */
        $generator = $this->application->make(RelateApplicationFactory::chooseGenerator($slug));
        return $generator->children($this->auth_info, $id);
    }

}
