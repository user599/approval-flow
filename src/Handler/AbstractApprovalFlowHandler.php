<?php


namespace Js3\ApprovalFlow\Handler;


use Illuminate\Contracts\Foundation\Application;
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
     * @var ApprovalFlowContext 审批流上下文
     */
    protected $approval_flow_context;
    /**
     * @var AuthInfo 当前用户身份信息
     */
    protected $auth_info;

    /**
     * @var HttpClient 发起http请求的客户端
     */
    private $http_client;

    /**
     * @param $approval_flow_slug
     * @param AuthInfo $auth_info
     */
    public function __construct(HttpClient $http_client)
    {
        $this->http_client = $http_client;
        $this->http_client->setAuthInfo($this->setAuthInfo());
    }

    public function generate($form_data = []): ApprovalFlowInstance
    {
        $data =[
            "form_data" => $form_data,
            "slug" => $this->approval_flow_slug
        ];
        $res = $this->http_client->httpPostJson("/api/approval-flow/generate",$data);
        //TODO 将生成的结构存储到数据库中
        return $res;
    }

    public function execute($instance_id, $args): ApprovalFlowContext
    {
        // TODO: Implement execute() method.
    }

    public function auditPass($node_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement auditPass() method.
    }

    public function reject($snapshot_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement reject() method.
    }

    public function revocation($instance_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement revocation() method.
    }

    public function getStatus($instance_id): ApprovalFlowInstance
    {
        // TODO: Implement getStatus() method.
    }

    public function getApprovalFlowSlug(): string
    {
        return $this->approval_flow_slug;
    }

    /**
     * @explain:审批额外操作
     * @param
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 14:50
     * @remark:
     */
    abstract function handleAuditExtraOperate(AuditNode $node);

    /**
     * @explain: 抄送额外操作
     * @param $carbon_copy_slug
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 14:50
     * @remark:
     */
    abstract function handleCarbonCopy(CarbonCopyNode $node);

}
