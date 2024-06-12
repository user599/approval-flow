<?php


namespace Js3\ApprovalFlow\Controller;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Middleware\CheckApprovalFlowAuthMiddleware;
use Js3\ApprovalFlow\RelatedApplication\RelatedApplication;
use Js3\ApprovalFlow\RelatedApplication\RelatedApplicationFactory;

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

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    /**
     * @explain: 获取指定关联应用下拉列表数据
     * @param string $slug 关联应用标识
     * @param Request $request 请求数据
     * @return array
     * @throws ApprovalFlowException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author: wzm
     * @date: 2024/5/20 9:15
     * @remark:
     */
    public function getOptions($slug, Request $request): array
    {
        /** @var RelatedApplication $generator */
        $generator = $this->application->make(RelatedApplicationFactory::chooseRelatedApplication($slug));
        return $generator->options($request->offsetGet(CheckApprovalFlowAuthMiddleware::CACHE_AUTH_KEY));

    }

    /**
     * @explain: 获取指定选项下的所有子集
     * @param string $slug 关联应用标识
     * @param int $id 指定选项的id
     * @param Request $request 请求参数
     * @return array
     * @throws ApprovalFlowException
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @author: wzm
     * @date: 2024/5/20 9:16
     * @remark:
     */
    public function getChildren($slug, $id, Request $request): array
    {
        /** @var RelatedApplication $generator */
        $generator = $this->application->make(RelatedApplicationFactory::chooseRelatedApplication($slug));
        return $generator->children($request->offsetGet(CheckApprovalFlowAuthMiddleware::CACHE_AUTH_KEY), $id);
    }

}
