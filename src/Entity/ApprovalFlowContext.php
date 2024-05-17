<?php


namespace Js3\ApprovalFlow\Entity;



use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;

/**
 * @explain: 审批流执行器
 * @author: wzm
 * @date: 2024/5/14 16:30
 */
abstract class ApprovalFlowContext
{

    /**
     * @var ApprovalFlowInstance 当前审批流实例
     */
    private  $approval_flow_instance;


    /**
     * @var AbstractNode 当前所处节点信息
     */
    private $current_node;

    /**
     * @var array 参数信息
     */
    private $args;

    /**
     * @var array<AbstractNode> 本次执行通过的节点
     */
    private $executed_nodes = [];

    /**
     * @param ApprovalFlowInstance $approval_flow_instance 审批流实例
     * @param array|null $args  额外参数
     */
    public function __construct(ApprovalFlowInstance $approval_flow_instance, $args = [])
    {
        $this->approval_flow_instance = $approval_flow_instance;
        $this->args = $args;
    }


    /**
     * @return mixed
     */
    public function getArgs()
    {
        return $this->args;
    }

    /**
     * @param mixed $args
     * @return ApprovalFlowContext
     */
    public function setArgs($args)
    {
        $this->args = $args;
        return $this;
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
    public function setExecutedNodes(AbstractNode $node): ApprovalFlowContext
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







}
