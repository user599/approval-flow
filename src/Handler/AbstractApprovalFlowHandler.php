<?php


namespace Js3\ApprovalFlow\Handler;


use App\Models\Admin\App;
use App\Models\Api\City;
use Illuminate\Support\Facades\DB;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeRelatedMemberService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceService;

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
     * @var ApprovalFlowInstanceService
     */
    private $obj_service_af_instance;

    /**
     * @var ApprovalFlowInstanceNodeService
     */
    private $obj_service_af_node;

    /**
     * @var ApprovalFlowInstanceNodeRelatedMemberService
     */
    private $obj_service_af_related_member;


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
        $this->obj_service_af_instance = app(ApprovalFlowInstanceService::class);
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
    public function execute($instance_id, $args = []): ApprovalFlowContext
    {
        $obj_approval_flow_context = ApprovalFlowContext::getContextByInstanceId($instance_id, $this->auth_info);
        $obj_approval_flow_context->setArgs($args);
        $obj_approval_flow_context->startInstance();
        //额外事件处理
        foreach ($obj_approval_flow_context->getExecutedNodes() as $executedNode) {
            if ($executedNode instanceof AuditNode) {
                $this->handleAuditExtraOperate($executedNode);
            } elseif ($executedNode instanceof CarbonCopyNode) {
                $this->handleCarbonCopyExtraOperate($executedNode);
            }
        }
        return $obj_approval_flow_context;


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
    public function auditPass($node_id, $remark = null)
    {
        $args = [
            "node_id" => $node_id,
            "remark" => $remark,
        ];
        return DB::transaction(function () use ($node_id, $args) {
            $obj_instance = $this->obj_service_af_node->findById($node_id)->instance ?? null;
            throw_if(empty($obj_instance), ApprovalFlowException::class, "未知的审批流信息，请重试");
            throw_if($obj_instance->status != ApprovalFlowInstance::STATUS_RUNNING, ApprovalFlowException::class, "审批流未开始或已结束");

            //格式化审批流信息
            $obj_approval_flow_context = ApprovalFlowContext::getContextByInstance($obj_instance, $this->auth_info);

            //若节点已更新则不允许审核
            $current_node = $obj_approval_flow_context->getCurrentNode();
            throw_if($current_node->getId() != $node_id, ApprovalFlowException::class, "未知或非活动节点，无法执行操作");
            $obj_approval_flow_context->setArgs($args);
            $obj_approval_flow_context->getCurrentNode()->execute($obj_approval_flow_context);
            //额外事件处理
            foreach ($obj_approval_flow_context->getExecutedNodes() as $executedNode) {
                if ($executedNode instanceof AuditNode) {
                    $this->handleAuditExtraOperate($executedNode);
                } elseif ($executedNode instanceof CarbonCopyNode) {
                    $this->handleCarbonCopyExtraOperate($executedNode);
                }
            }
            return $obj_approval_flow_context;
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
    public function reject($node_id, $remark = null)
    {
        return DB::transaction(function () use ($node_id, $remark) {
            $obj_node = $this->obj_service_af_node->findById($node_id)->instance ?? null;
            throw_if(empty($obj_node), ApprovalFlowException::class, "未知的审批流信息，请重试");
            throw_if($obj_node->instance->status != ApprovalFlowInstance::STATUS_RUNNING, ApprovalFlowException::class, "审批流未开始或已结束");
            /**
             *
             */
            switch ($obj_node->reject_type) {
                case ApprovalFlowInstanceNode::REJECT_TYPE_REJECT_TO_PRE_APPROVE:
                    //驳回到上一审批节点
                    //查找一审批节点

                    //直接结束
            }
            return null;
        });
    }

    /**
     * @explain: 撤销
     * @param $instance_id
     * @param $remark
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function withdraw($instance_id, $remark = null)
    {
        DB::transaction(function ($instance_id, $remark = null) {
            $obj_approval_flow_instance = $this->obj_service_af_instance->findById($instance_id);
            throw_if(!$obj_approval_flow_instance->allow_withdraw, ApprovalFlowException::class, "该审批流禁止撤回");
            throw_if(!$obj_approval_flow_instance->status == ApprovalFlowInstance::STATUS_WITHDRAW, ApprovalFlowException::class, "该审批流已撤回，请勿重复操作");
            //验证撤回类型
            switch ($obj_withdraw_type = $obj_approval_flow_instance->withdraw_type) {
                case ApprovalFlowInstance::WITHDRAW_TYPE_NOT_IN_PROGRESS:
                    //未进入流程时撤回：未开始，开始但未审核
                    if (!in_array($obj_approval_flow_instance->status, [ApprovalFlowInstance::STATUS_NOT_START, ApprovalFlowInstance::STATUS_RUNNING])
                        || $obj_approval_flow_instance->has_audit != ApprovalFlowInstance::HAS_AUDIT_FALSE
                    ) {
                        throw new ApprovalFlowException("该审批流已进入流程，禁止撤回");
                    }
                    break;
                case ApprovalFlowInstance::WITHDRAW_TYPE_IN_PROGRESS:
                    if ($obj_approval_flow_instance->status == ApprovalFlowInstance::STATUS_END) {
                        throw new ApprovalFlowException("该审批流已结束，禁止撤回");
                    }
                    break;
                case ApprovalFlowInstance::WITHDRAW_TYPE_END:
                    break;
                default:
                    throw new ApprovalFlowException("未知的撤回类型:{$obj_withdraw_type}");
            };

            //设置实例为已撤回
            $obj_approval_flow_instance->status = ApprovalFlowInstance::STATUS_WITHDRAW;
            $obj_approval_flow_instance->remark = $remark;
            $obj_approval_flow_instance->end_time = date('Y-m-d H:i:s');
            $obj_approval_flow_instance->save();
        });
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
        return ApprovalFlowContext::getContextByInstanceId($instance_id,$this->auth_info);
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
