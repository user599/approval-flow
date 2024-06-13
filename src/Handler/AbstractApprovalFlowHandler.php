<?php


namespace Js3\ApprovalFlow\Handler;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;
use Js3\ApprovalFlow\Parser\InstanceParser;
use Js3\ApprovalFlow\Parser\NodeFactory;
use Js3\ApprovalFlow\Parser\NodeParseable;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeOperateRecordService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeRelatedMemberService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceService;
use Js3\ApprovalFlow\Utils\CacheUtils;

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
     * @var ApprovalFlowInstanceNodeOperateRecordService
     */
    private $obj_service_af_operate_record;

    /**
     * @param AuthInfo 身份信息，需要用户自行填入
     */
    public function __construct(?AuthInfo $auth_info = null)
    {
        $this->auth_info = $auth_info;
        $this->http_client = app(HttpClient::class);
        $this->obj_service_af_instance = app(ApprovalFlowInstanceService::class);
        $this->obj_service_af_node = app(ApprovalFlowInstanceNodeService::class);
        $this->obj_service_af_related_member = app(ApprovalFlowInstanceNodeRelatedMemberService::class);
        $this->obj_service_af_operate_record = app(ApprovalFlowInstanceNodeOperateRecordService::class);
    }

    /**
     * @inheritDoc
     */
    public function generate($form_data = [])
    {
        //发送请求获取数据
        $data = [
            "form_data" => $form_data,
            "slug" => $this->approval_flow_slug,
        ];
        $approval_data = $this->http_client
            ->setAuthInfo($this->auth_info)
            ->httpPost("/api/approval-flow/generate", $data);
        //格式化响应
        /**
         * 格式化响应
         * 1.将数据格式化后放入redis
         * 2.前台使用时通过redis的key启动审批流
         */
        $ary_parse_data = InstanceParser::parseFromResponseToArr($approval_data, $this->auth_info);
        $ary_parse_data["id"] = CacheUtils::setCache($ary_parse_data);
        return $ary_parse_data;
    }

    /**
     * @inheritDoc
     */
    public function execute($cache_id, $args = [])
    {
        return approvalFlowTransaction(function () use ($cache_id, $args) {
            //取出缓存信息并创建审批流实例
            $cache_data = CacheUtils::getCache($cache_id);
            $obj_approval_instance = InstanceParser::parseFromArrToModel($cache_data);

            //格式化
            $obj_approval_flow_context = ApprovalFlowContext::getContextByInstance($obj_approval_instance, $this->auth_info);
            $obj_instance = $obj_approval_flow_context->getApprovalFlowInstance();
            approvalFlowAssert($obj_instance->status != ApprovalFlowInstance::STATUS_NOT_START, "该审批流已开始执行，请勿重复操作");
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
        });

    }

    /**
     * @inheritDoc
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
                approvalFlowAssert(empty($obj_instance = $obj_node->instance), "未知或已删除的审批流信息-[{$obj_node->instance_id}]");
                approvalFlowAssert($obj_instance->status != ApprovalFlowInstance::STATUS_RUNNING, "审批流未开始或已结束");
                approvalFlowAssert(($obj_instance->current_node_id ?? null) != $node_id, "未知或非活动节点，无法执行操作");

                //执行通过方法
                $this->obj_service_af_related_member->auditByNodeIdAndAuthInfo($obj_node->id, $this->auth_info, ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS, $remark);

                //格式化审批流信息并继续执行
                $obj_approval_flow_context = ApprovalFlowContext::getContextByInstance($obj_node->instance, $this->auth_info);
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
     * @inheritDoc
     */
    public function auditRefuse($node_id, $remark = null)
    {

        return approvalFlowTransaction(
            function () use ($node_id, $remark) {
                $current_model_node = $this->obj_service_af_node->findById($node_id);

                approvalFlowAssert(empty($obj_instance = $current_model_node->instance), "未知或已删除的审批流信息-[{$current_model_node->instance_id}]");
                approvalFlowAssert($obj_instance->status != ApprovalFlowInstance::STATUS_RUNNING, "审批流未开始或已结束");
                approvalFlowAssert(($obj_instance->current_node_id ?? null) != $node_id, "未知或非活动节点，无法执行操作");

                //查找当前操作人
                try {
                    $current_related_member = $this->obj_service_af_related_member->findByNodeIdAndAuthInfo($node_id, $this->auth_info);
                } catch (\Exception $e) {
                    throw new ApprovalFlowException("当前人员无法执行驳回操作");
                }
                //是否驳回到上一审核节点
                $current_node = NodeFactory::make($current_model_node);
                if ($current_node instanceof AuditNode
                    && $current_node->getRejectType() == ApprovalFlowInstanceNode::REJECT_TYPE_REJECT_TO_PRE_APPROVE) {
                    //获取上一级审核节点
                    $model_node_group_id = $obj_instance->nodes->keyBy("id");
                    $pre_model_node = $current_model_node;
                    $ary_rollback_model_node_info = [
                        $current_model_node
                    ];
                    while (!empty($pre_model_node = ($model_node_group_id[$pre_model_node->parent_id]??null))) {
                        $ary_rollback_model_node_info[] = $pre_model_node;
                        if ($pre_model_node->type == ApprovalFlowInstanceNode::NODE_TYPE_APPROVE) {
                            break;
                        }
                    }
                }
                //判断上级审核节点是否存在
                if (!empty($pre_model_node)) {
                    /**
                     * 则将从该节点开始的所有节点状态为未操作
                     * 将所有相关人设置为未操作
                     */
                    foreach ($ary_rollback_model_node_info??[] as $node) {
                        $this->obj_service_af_node->rollbackNode($node);
                    }
                    //设置实例的当前节点
                    $obj_instance->current_node_id = $pre_model_node->id;
                    //添加一条当前人员的拒绝记录
                    $this->obj_service_af_operate_record->createOperateRecordByRelatedMember(
                        $current_related_member,
                        ApprovalFlowInstanceNodeRelatedMember::STATUS_REJECT,
                        $remark
                    );
                } else {
                    /**
                     * 若节点为拒绝即结束，或者没有上级审批节点，则直接结束
                     * 1. 添加拒绝记录并维护当前人员数据
                     * 2. 结束当前实例
                     */
                    $this->obj_service_af_related_member->auditByNodeIdAndAuthInfo($current_model_node->id, $this->auth_info, ApprovalFlowInstanceNodeRelatedMember::STATUS_REJECT, $remark);
                    $this->obj_service_af_instance->endInstance($obj_instance->id, $remark);
                }
                $obj_instance->save();
                return empty($pre_model_node) ? null : NodeFactory::make($pre_model_node);
            }
        );


    }

    /**
     * @inheritDoc
     */
    public function withdraw($instance_id, $remark = null)
    {
        return approvalFlowTransaction(
            function () use ($instance_id, $remark) {
                $obj_approval_flow_instance = $this->obj_service_af_instance->findById($instance_id);
                approvalFlowAssert(!$obj_approval_flow_instance->allow_withdraw, "该审批流禁止撤回");
                approvalFlowAssert($obj_approval_flow_instance->status == ApprovalFlowInstance::STATUS_WITHDRAW, "该审批流已撤回，请勿重复操作");

                //只有发起人能撤回
                $same_with_creator = $this->auth_info->isSameMember($obj_approval_flow_instance->creator_id, $obj_approval_flow_instance->creator_type);
                approvalFlowAssert(!$same_with_creator, "只有发起人可以撤销审批流");

                //验证撤回类型
                switch ($obj_withdraw_type = $obj_approval_flow_instance->withdraw_type) {
                    case ApprovalFlowInstance::WITHDRAW_TYPE_NOT_IN_PROGRESS:
                        //未进入流程时撤回：状态为未开始，进行中，且未存在审核记录（即操作记录）
                        approvalFlowAssert(!in_array($obj_approval_flow_instance->status, [ApprovalFlowInstance::STATUS_NOT_START, ApprovalFlowInstance::STATUS_RUNNING]), "该审批流已进入流程，禁止撤回");
                        approvalFlowAssert($obj_approval_flow_instance->operateRecords()->exists(), "该审批流已进入流程，禁止撤回");
                        break;
                    case ApprovalFlowInstance::WITHDRAW_TYPE_IN_PROGRESS:
                        approvalFlowAssert($obj_approval_flow_instance->status == ApprovalFlowInstance::STATUS_END, "该审批流已结束，禁止撤回");
                        break;
                    case ApprovalFlowInstance::WITHDRAW_TYPE_END:
                        //结束后撤回，任意时刻可以撤回
                        break;
                    default:
                        throw new ApprovalFlowException("未知的撤回类型:{$obj_withdraw_type}");
                }
                //设置实例为已撤回
                $obj_approval_flow_instance->status = ApprovalFlowInstance::STATUS_WITHDRAW;
                $obj_approval_flow_instance->remark = $remark;
                $obj_approval_flow_instance->end_time = date('Y-m-d H:i:s');
                $obj_approval_flow_instance->save();
                return true;
            }
        );
    }

    /**
     * @inheritDoc
     */
    public function getStatus($instance_id)
    {
        return ApprovalFlowContext::getContextByInstanceId($instance_id, $this->auth_info);
    }


    /**
     * @inheritDoc
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
