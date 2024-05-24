<?php


namespace Js3\ApprovalFlow\Handler;


use Illuminate\Support\Facades\DB;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeRelatedMemberService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeService;

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
     * @var ApprovalFlowInstanceNodeService
     */
    private $obj_service_af_node;

    /**
     * @var ApprovalFlowInstanceNodeRelatedMemberService
     */
    private $obj_service_af_related_member;

    /**
     * @var ApprovalFlowContext
     */
    private $obj_approval_context;

    /**
     * @param AuthInfo 身份信息，需要用户自行填入
     *
     * @param HttpClient $http_client http客户端
     *          使用app(HttpClient::class) 或 App::make(HttpClient::class)即可
     */
    public function __construct(?AuthInfo $auth_info = null)
    {
        $this->auth_info = $auth_info;
        $this->http_client = app(HttpClient::class);
        $this->obj_approval_context = app(ApprovalFlowContext::class);
        $this->obj_service_af_node = app(ApprovalFlowInstanceNodeService::class);
        $this->obj_service_af_related_member = app(ApprovalFlowInstanceNodeRelatedMemberService::class);

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
        return $this->obj_approval_context->storeApprovalFlowInstance($res, $this->auth_info);
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
    public function execute($instance_id, $args = []): ApprovalFlowContext
    {
        return DB::transaction(function () use ($instance_id, $args) {
            $this->obj_approval_context->getContextByInstanceId($instance_id);
            $this->obj_approval_context->setArgs($args);
            $this->obj_approval_context->startInstance();
            //额外事件处理
            foreach ($this->obj_approval_context->getExecutedNodes() as $executedNode) {
                if ($executedNode instanceof AuditNode) {
                    $this->handleAuditExtraOperate($executedNode);
                } elseif ($executedNode instanceof CarbonCopyNode) {
                    $this->handleCarbonCopyExtraOperate($executedNode);
                }
            }
            return $this->obj_approval_context;
        });

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
        $args = [
            "node_id" => $node_id,
            "remark" => $remark,
            "operate_time" => empty($operate_time) ? date('Y-m-d H:i:s') : $operate_time
        ];
        DB::transaction(function () use ($instance_id,$node_id, $args) {

            $obj_instance = $this->obj_service_af_node->findById($node_id)->instance??null;
            throw_if(empty($obj_instance),ApprovalFlowException::class,"未知的审批流信息，请重试");

            $approvalFlowContext = ApprovalFlowContext::getContextByInstance($obj_instance, $this->auth_info);
            $current_node = $approvalFlowContext->getCurrentNode();
            throw_if($current_node->id != $node_id,ApprovalFlowException::class,"未知或非活动节点，无法执行操作");
            $approvalFlowContext->setArgs($args);
            $approvalFlowContext->getCurrentNode()->execute($approvalFlowContext);
        });
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
    abstract function handleCarbonCopyExtraOperate(CarbonCopyNode $node);


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
     * @remark:
     */
    public function setAuthInfo(AuthInfo $auth_info)
    {
        $this->auth_info = $auth_info;
        return $this;
    }


}
