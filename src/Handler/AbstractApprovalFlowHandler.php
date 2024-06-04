<?php


namespace Js3\ApprovalFlow\Handler;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeRelatedMemberService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceService;
use Throwable;

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
        $approval_data = $this->http_client
            ->setAuthInfo($this->auth_info)
            ->httpPost("/api/approval-flow/generate", $data);

        return approvalFlowTransaction(
            function () use ($approval_data) {
                $obj_instance = $this->obj_service_af_instance->saveInstance($approval_data, $this->auth_info);
                return ApprovalFlowContext::getContextByInstance($obj_instance, $this->auth_info);
            }
        );

    }

    /**
     * @explain: 执行审批流
     * @param int $instance_id
     * @param array $args
     * @return ApprovalFlowContext
     * @throws ApprovalFlowException|Throwable
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function execute($instance_id, $args = []): ApprovalFlowContext
    {
        return approvalFlowTransaction(
            function () use ($instance_id, $args) {
                // 生成上下文数据
                $obj_approval_flow_context = ApprovalFlowContext::getContextByInstanceId($instance_id, $this->auth_info);
                $obj_instance = $obj_approval_flow_context->getApprovalFlowInstance();
                approvalFlowAssert(
                    $obj_instance->status != ApprovalFlowInstance::STATUS_NOT_START,
                    "该审批流已开始执行，请勿重复操作"
                );
                $obj_approval_flow_context->setArgs($args);
                //开始执行
                $obj_approval_flow_context->getStartNode()->execute($obj_approval_flow_context);
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
        );


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

        return approvalFlowTransaction(
            function () use ($node_id, $remark) {
                $args = [
                    "node_id" => $node_id,
                    "remark" => $remark,
                ];
                $obj_node = $this->obj_service_af_node->findById($node_id);

                approvalFlowAssert(
                    empty($obj_instance = $obj_node->instance)
                    , "未知或已删除的审批流信息-[{$obj_node->instance_id}]"
                );
                approvalFlowAssert(
                    $obj_instance->status != ApprovalFlowInstance::STATUS_RUNNING
                    , "审批流未开始或已结束"
                );

                approvalFlowAssert(
                    ($obj_instance->currentNode->id ?? null) != $node_id
                    , "未知或非活动节点，无法执行操作"
                );

                //格式化审批流信息
                $obj_approval_flow_context = ApprovalFlowContext::getContextByInstance($obj_instance, $this->auth_info);
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
            }
        );
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
    public function auditRefuse($node_id, $remark = null)
    {
        return $this->obj_service_af_instance->refuseByNodeId($node_id, $this->auth_info, $remark);
    }

    /**
     * @explain: 撤回
     * @param $instance_id
     * @param $remark
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function withdraw($instance_id, $remark = null)
    {
        return approvalFlowTransaction(
            function ($instance_id, $remark = null) {
                $obj_approval_flow_instance = $this->obj_service_af_instance->findById($instance_id);
                approvalFlowAssert(
                    !$obj_approval_flow_instance->allow_withdraw
                    , "该审批流禁止撤回"
                );
                approvalFlowAssert(
                    $obj_approval_flow_instance->status == ApprovalFlowInstance::STATUS_WITHDRAW
                    , "该审批流已撤回，请勿重复操作"
                );
                //验证撤回类型
                switch ($obj_withdraw_type = $obj_approval_flow_instance->withdraw_type) {
                    case ApprovalFlowInstance::WITHDRAW_TYPE_NOT_IN_PROGRESS:
                        //未进入流程时撤回：状态为未开始，进行中，且未存在审核记录（即操作记录）
                        approvalFlowAssert(
                            !in_array($obj_approval_flow_instance->status, [ApprovalFlowInstance::STATUS_NOT_START, ApprovalFlowInstance::STATUS_RUNNING])
                            , "该审批流已进入流程，禁止撤回"
                        );
                        approvalFlowAssert(
                            $obj_approval_flow_instance->operateRecords()->exists()
                            , "该审批流已进入流程，禁止撤回"
                        );
                        break;
                    case ApprovalFlowInstance::WITHDRAW_TYPE_IN_PROGRESS:
                        approvalFlowAssert(
                            $obj_approval_flow_instance->status == ApprovalFlowInstance::STATUS_END
                            , "该审批流已结束，禁止撤回"
                        );
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
            }
        );
    }

    /**
     * @explain: 获取当前审批流状态
     * @param $instance_id
     * @return mixed
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function getStatus($instance_id)
    {
        return ApprovalFlowContext::getContextByInstanceId($instance_id, $this->auth_info);
    }


    /**
     * @explain:向节点存入新成员
     * @param $node_id 节点id
     * @param array<AuthInfo> $ary_insert_auth_info 要插入的用户数组-请格式化为 AuthInfo类
     * @return mixed        插入的人员数量
     * @throws \Throwable
     * @author: wzm
     * @date: 2024/6/3 10:11
     * @remark: 若该人员已经在节点中，则该人员将会跳过
     */
    public function insertMember($node_id, array $ary_insert_auth_info)
    {
        return approvalFlowTransaction(
            function () use ($node_id, $ary_insert_auth_info) {
                //节点已经操作完成则不允许插入
                $obj_node_info = $this->obj_service_af_node->findById($node_id);
                approvalFlowAssert(
                    !in_array($obj_node_info->instance->status, [ApprovalFlowInstance::STATUS_NOT_START, ApprovalFlowInstance::STATUS_RUNNING])
                    , "该审批流已结束，无法添加人员"
                );
                approvalFlowAssert(
                    $obj_node_info->status != ApprovalFlowInstanceNode::STATUS_UN_OPERATE
                    , "该节点已操作，无法添加成员"
                );
                //过滤已经存在于节点的人员
                $ary_exists_auth_key = $obj_node_info->relatedMembers->map(function ($item) {
                    return implode('-', [$item->member_id, $item->member_type]);
                })->toArray();

                /**
                 * 1.过滤插入数组中的重复项
                 * 2.过滤数据库中已存在的人员
                 * 3.格式化人员信息
                 */
                $col_insert_member = collect($ary_insert_auth_info)->unique(function ($item) {
                    return $item->getAuthKey();
                })->filter(function ($auth_info) use ($ary_exists_auth_key) {
                    return !in_array($auth_info->getAuthKey(), $ary_exists_auth_key);
                })->map(function ($auth_info) use ($obj_node_info) {
                    return new ApprovalFlowInstanceNodeRelatedMember([
                        "instance_id" => $obj_node_info->instance_id,
                        "member_id" => $auth_info->getAuthId(),
                        "member_type" => $auth_info->getAuthType(),
                        "status" => ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE,
                    ]);
                });
                $obj_node_info->updated_at = date('Y-m-d H:i:s');
                $obj_node_info->save();
                $obj_node_info->relatedMembers()->saveMany($col_insert_member);
                return $col_insert_member->count();
            }
        );
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
