<?php


namespace Js3\ApprovalFlow\Entity;


use Illuminate\Support\Facades\DB;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Parser\NodeParseable;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceService;

/**
 * @explain: 审批流执行器
 * @author: wzm
 * @date: 2024/5/14 16:30
 */
class ApprovalFlowContext
{

    /**
     * @var ApprovalFlowInstance 当前审批流实例
     */
    private $approval_flow_instance;

    /**
     * @var array<AbstractNode> 节点列表
     */
    private $node_list;

    /**
     * @var AbstractNode 当前所处节点信息
     */
    private $current_node;

    /**
     * @var array<AbstractNode> 本次执行通过的节点
     */
    private $executed_nodes = [];

    /**
     * @var array 额外参数
     */
    private $args = [];

    /**
     * @var AuthInfo 当前人员信息
     */
    private $auth_info;

    /**
     * @explain: 存储审批流信息
     * @param array $approval_data
     * @param AuthInfo|null $auth_info
     * @return ApprovalFlowContext
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/20 17:02
     * @remark:
     */
    public static function storeApprovalFlowInstance(array $approval_data, ?AuthInfo $auth_info)
    {
        /** @var ApprovalFlowInstanceService $obj_service_instance */
        $obj_service_instance = app(ApprovalFlowInstanceService::class);
        $obj_instance = $obj_service_instance->saveInstance($approval_data, $auth_info);
        return self::getContextByInstance($obj_instance, $auth_info);
    }

    /**
     * @explain:
     * @param $int_instance_id
     * @param AuthInfo|null $auth_info
     * @return ApprovalFlowContext
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/20 17:02
     * @remark:
     */
    public static function getContextByInstanceId($int_instance_id, ?AuthInfo $auth_info)
    {
        /** @var ApprovalFlowInstanceService $obj_service_instance */
        $obj_service_instance = app(ApprovalFlowInstanceService::class);
        return self::getContextByInstance($obj_service_instance->findById($int_instance_id), $auth_info);
    }


    /**
     * @explain: 基于审批流实例获取审批流上下文
     * @param ApprovalFlowInstance $obj_instance
     * @param AuthInfo|null $auth_info
     * @return self
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/20 17:02
     * @remark:
     */
    public static function getContextByInstance(ApprovalFlowInstance $obj_instance, ?AuthInfo $auth_info)
    {
        $obj_instance = $obj_instance->load(["nodes", "nodes.operators"]);
        $approvalFlowContext = new self();
        $approvalFlowContext->setApprovalFlowInstance($obj_instance);
        $approvalFlowContext->setAuthInfo($auth_info);
        foreach ($obj_instance->nodes as $model_node) {
            $parse_clazz = NodeParseable::NODE_PARSER_MAP[$model_node->type];
            if (empty($parse_clazz)) {
                throw new ApprovalFlowException("未配置该类型节点的解析器:{$model_node->type}");
            }
            /** @var NodeParseable $parse_clazz */
            $parser = (new $parse_clazz());
            $parser->parseModelToNode($model_node);
            $node = $parser->getNode();
            $approvalFlowContext->node_list[] = $node;
        }

        //将节点串成链表
        foreach ($approvalFlowContext->node_list as $node) {
            //设置当前节点
            if ($node->getModel()->getKey() == $obj_instance->current_node_id) {
                $approvalFlowContext->current_node = $node;
            }
            foreach ($approvalFlowContext->node_list as $next_node) {
                if ($node->getModel()->getKey() == $next_node->getModel()->getKey()) {
                    continue;
                }
                if ($node->getModel()->getKey() == $next_node->getModel()->parent_id) {
                    $node->setNextNode($next_node);
                    $next_node->setPreNode($node);
                }
            }
        }
        return $approvalFlowContext;

    }

    public function getStart()
    {
        return $this->node_list[0];
    }

    /**
     * @return AbstractNode
     */
    public function getCurrentNode(): AbstractNode
    {
        return $this->current_node;
    }

    /**
     * @param AbstractNode $current_node
     * @return ApprovalFlowContext
     */
    public function setCurrentNode(AbstractNode $current_node): ApprovalFlowContext
    {
        $this->current_node = $current_node;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getApprovalFlowInstance()
    {
        return $this->approval_flow_instance;
    }

    public function setApprovalFlowInstance(ApprovalFlowInstance $approval_flow_instance)
    {
        return $this->approval_flow_instance = $approval_flow_instance;
    }

    /**
     * @return array<AbstractNode>
     */
    public function getExecutedNodes()
    {
        return $this->executed_nodes;
    }

    /**
     * @param AbstractNode $node
     */
    public function setExecutedNode(AbstractNode $node): ApprovalFlowContext
    {
        //去重
        $node_in_executed = false;
        foreach ($this->executed_nodes as $executed_node) {
            if ($executed_node->getSlug() == $this->current_node->getSlug()) {
                $node_in_executed = true;
                break;
            }
        }
        if ($node_in_executed) {
            $this->executed_nodes[] = $node;
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getArgs(): array
    {
        return $this->args;
    }

    /**
     * @param array $args
     * @return ApprovalFlowContext
     */
    public function setArgs(array $args): ApprovalFlowContext
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return AbstractNode[]
     */
    public function getNodeList(): array
    {
        return $this->node_list;
    }

    /**
     * @param AbstractNode[] $node_list
     * @return ApprovalFlowContext
     */
    public function setNodeList(array $node_list): ApprovalFlowContext
    {
        $this->node_list = $node_list;
        return $this;
    }

    /**
     * @return AuthInfo
     */
    public function getAuthInfo(): AuthInfo
    {
        return $this->auth_info;
    }

    /**
     * @param AuthInfo $auth_info
     * @return ApprovalFlowContext
     */
    public function setAuthInfo(AuthInfo $auth_info): ApprovalFlowContext
    {
        $this->auth_info = $auth_info;
        return $this;
    }


}
