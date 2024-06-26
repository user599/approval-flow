<?php


namespace Js3\ApprovalFlow\Entity;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Parser\NodeFactory;
use Js3\ApprovalFlow\Parser\NodeParseable;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceService;
use JsonSerializable;

/**
 * @explain: 审批流执行器
 * @author: wzm
 * @date: 2024/5/14 16:30
 */
class ApprovalFlowContext implements Arrayable, Jsonable, JsonSerializable
{

    /**
     * @var ApprovalFlowInstance 当前审批流实例
     */
    private $approval_flow_instance;

    /**
     * @var Collection<AbstractNode> 节点列表
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
     * 私有化构造函数保证只能通过指定方法实例化
     */
    private function __construct()
    {
        $this->node_list = new Collection([]);
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
    public static function getContextByInstanceId($int_instance_id, AuthInfo $auth_info)
    {
        $obj_instance = app(ApprovalFlowInstanceService::class)->findById($int_instance_id);
        return self::getContextByInstance($obj_instance, $auth_info);
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
        $obj_instance = $obj_instance->loadMissing(["nodes", "nodes.relatedMembers"]);
        $approvalFlowContext = new self();
        $approvalFlowContext->setAuthInfo($auth_info);
        $approvalFlowContext->setApprovalFlowInstance($obj_instance);
        //使用节点格式化器格式化节点内容
        foreach ($obj_instance->nodes as $model_node) {
            $node = NodeFactory::make($model_node);
            //TODO 可以通过添加后置拦截器的方式优化后置的审核/抄送额外操作
            $approvalFlowContext->node_list->add($node);
        }

        /**
         * 构建节点关系
         */
        //O(2n)降低复杂度
        $ary_node_key_by_parent_id = $approvalFlowContext->node_list->keyBy(function ($item) {
            return $item->getParentId();
        });
        foreach ($approvalFlowContext->node_list as &$node) {
            $curr_node_id = $node->getId();
            //设置当前节点
            if ($curr_node_id == $obj_instance->current_node_id) {
                $approvalFlowContext->current_node = $node;
            }
            $children_node = $ary_node_key_by_parent_id[$curr_node_id] ?? null;
            if (!empty($children_node)) {
                $node->setNextNode($children_node);
                $children_node->setPreNode($node);
            }
        }
        return $approvalFlowContext;
    }

    /**
     * @explain: 获取开始节点
     * @return AbstractNode
     * @author: wzm
     * @date: 2024/5/21 14:07
     * @remark:
     */
    public function getStartNode(): AbstractNode
    {
        return $this->node_list[0];
    }

    //region getter and setter

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
    public function setCurrentNode(?AbstractNode $current_node): ApprovalFlowContext
    {
        if (!empty($current_node)) {
            $this->approval_flow_instance->current_node_id = $current_node->getId();
            $this->current_node = $current_node;
        } else {
            $this->approval_flow_instance->current_node_id = null;

            $this->current_node = null;
        }
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
     * @return Collection<AbstractNode>
     */
    public function getNodeList(): Collection
    {
        return $this->node_list;
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


    /**
     * @explain:格式化方法
     * @return array
     * @author: wzm
     * @date: 2024/5/29 14:54
     * @remark:
     */
    public function toArray()
    {
        return [
            "instance" => $this->approval_flow_instance->withoutRelations(),
            "current_node" => $this->current_node,
            "node_list" => $this->node_list,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        return $json;
    }

    public function __toString()
    {
        return $this->toJson();
    }


}
