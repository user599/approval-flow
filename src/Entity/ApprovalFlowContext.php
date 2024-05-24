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
     * @var ApprovalFlowInstanceService
     */
    private $obj_service_af_instance;

    /**
     * @param ApprovalFlowInstanceService $obj_service_af_instance
     */
    public function __construct(ApprovalFlowInstanceService $obj_service_af_instance)
    {
        $this->obj_service_af_instance = $obj_service_af_instance;
    }


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
    public function storeApprovalFlowInstance(array $approval_data)
    {
        $obj_instance = $this->obj_service_af_instance->saveInstance($approval_data);
        return $this->getContextByInstance($obj_instance);
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
    public function getContextByInstanceId($int_instance_id)
    {
        return self::getContextByInstance($this->obj_service_af_instance->findById($int_instance_id));
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
    public function getContextByInstance(ApprovalFlowInstance $obj_instance)
    {
        $obj_instance = $obj_instance->load(["nodes", "nodes.operators"]);
        $this->setApprovalFlowInstance($obj_instance);
        foreach ($obj_instance->nodes as $model_node) {
            $parse_clazz = NodeParseable::NODE_PARSER_MAP[$model_node->type] ?? null;
            if (empty($parse_clazz)) {
                throw new ApprovalFlowException("未配置该类型节点的解析器:{$model_node->type}");
            }
            /** @var NodeParseable $parse_clazz */
            try {
                $parser = app($parse_clazz);
            } catch (\Exception $e) {
                throw new ApprovalFlowException("解析器实例化失败:{$parse_clazz}-{$e->getMessage()}");
            }
            $parser->parseModelToNode($model_node);
            $node = $parser->getNode();
            $this->node_list[] = $node;
        }

        /**
         * 构建节点关系
         */
        //O(2n)降低复杂度
        $ary_node_key_by_parent_id = array_column($this->node_list, null, 'parent_id');
        foreach ($this->node_list as $node) {
            $curr_node_id = $node->getId();
            //设置当前节点
            if ($curr_node_id == $obj_instance->current_node_id) {
                $this->current_node = $node;
            }
            $children_node = $ary_node_key_by_parent_id[$curr_node_id] ?? null;
            if (!empty($children_node)) {
                $node->setNextNode($children_node);
                $children_node->setPreNode($node);
            }
        }
        return $this;
    }

    /**
     * @explain: 获取开始节点
     * @return AbstractNode
     * @author: wzm
     * @date: 2024/5/21 14:07
     * @remark:
     */
    public function getStartNode() : AbstractNode
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
        $this->executed_nodes[] = $node;
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
