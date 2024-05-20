<?php


namespace Js3\ApprovalFlow\Handler;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Validator;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;

/**
 * @explain:抄送审批流处理
 * @author: wzm
 * @date: 2024/5/14 14:48
 */
abstract class AbstractApprovalFlowHandler implements ApprovalFlowHandler
{

    /**
     * @var string 审批流标识
     */
    protected $approval_flow_slug;

    /**
     * @var AuthInfo 当前用户身份信息
     */
    protected $auth_info;

    /**
     * @var HttpClient 发起http请求的客户端
     */
    private $http_client;

    /**
     * @param AuthInfo 身份信息
     *       使用app(ApprovalFlowContext::class) 或 App::make(ApprovalFlowContext::class)即可
     * @param HttpClient $http_client http客户端
     *          使用app(HttpClient::class) 或 App::make(HttpClient::class)即可
     */
    public function __construct(?AuthInfo $auth_info, HttpClient $http_client)
    {
        $this->auth_info = $auth_info;
        $this->http_client = $http_client;

    }

    /**
     * @explain: 创建审批流
     * @param $form_data
     * @return ApprovalFlowContext
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Js3\ApprovalFlow\Exceptions\ApprovalFlowException
     * @throws \Js3\ApprovalFlow\Exceptions\RemoteCallErrorException
     * @author: wzm
     * @date: 2024/5/20 17:03
     * @remark:
     */
    public function generate($form_data = [])
    {
        $data = [
            "form_data" => $form_data,
            "slug" => $this->approval_flow_slug,
        ];
        $res = $this->http_client
            ->setAuthInfo($this->auth_info)
            ->httpPost("/api/approval-flow/generate", $data);
        return ApprovalFlowContext::storeApprovalFlowInstance($res, $this->auth_info);
    }

    /**
     * @explain: 执行审批流
     * @param $instance_id
     * @param $args
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function execute($instance_id, $args): ApprovalFlowContext
    {
        $approvalFlowContext = ApprovalFlowContext::getContextByInstanceId($instance_id);
        $approvalFlowContext->setArgs($args);
        $approvalFlowContext->getStart()->execute($approvalFlowContext);

        foreach ($approvalFlowContext->getExecutedNodes() as $executedNode) {
            if ($executedNode instanceof AuditNode) {
                $this->handleAuditExtraOperate($executedNode);
            } elseif ($executedNode instanceof CarbonCopyNode) {
                $this->handleCarbonCopy($executedNode);
            }
        }

        return $approvalFlowContext;

    }

    /**
     * @explain: 审核通过
     * @param $node_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function auditPass($node_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement auditPass() method.
    }

    /**
     * @explain: 审核拒绝
     * @param $node_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function reject($node_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement reject() method.
    }

    /**
     * @explain: 撤销
     * @param $instance_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function revocation($instance_id, $remark = null, $operate_time = null)
    {
        // TODO: Implement revocation() method.
    }

    /**
     * @explain: 获取当前审批流状态
     * @param $instance_id
     * @return ApprovalFlowInstance
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function getStatus($instance_id)
    {
        // TODO: Implement getStatus() method.
    }

    /**
     * @explain: 审批额外操作
     * @param AuditNode $node
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 15:08
     * @remark:
     */
    abstract function handleAuditExtraOperate(AuditNode $node);

    /**
     * @explain: 抄送额外操作
     * @param CarbonCopyNode $node
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 15:08
     * @remark:
     */
    abstract function handleCarbonCopy(CarbonCopyNode $node);


    /**
     * @explain: 获取当前审批流标识
     * @return string
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function getApprovalFlowSlug(): string
    {
        return $this->approval_flow_slug;
    }


    /**
     * @explain: 设置用户身份信息
     * @param AuthInfo $auth_info
     * @return $this
     * @author: wzm
     * @date: 2024/5/17 15:06
     * @remark: TODO 想想别的更好的解决方法，这个样子需要每次调用上述审批方法时必须先指定用户身份
     */
    public function setAuthInfo(AuthInfo $auth_info)
    {
        $this->auth_info = $auth_info;
        return $this;
    }


}
