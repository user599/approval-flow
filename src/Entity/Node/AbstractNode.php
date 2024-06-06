<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeRelatedMemberService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceNodeService;
use Js3\ApprovalFlow\Service\ApprovalFlowInstanceService;
use JsonSerializable;


/**
 * @explain:节点抽象类
 * @author: wzm
 * @date: 2024/5/14 16:28
 */
abstract class AbstractNode implements Arrayable, Jsonable, JsonSerializable
{

    /**
     * @var int 数据库中的主键
     */
    protected $id;

    /**
     * @var int 数据库中的父级主键
     */
    protected $parent_id;

    /**
     * @var string 节点名称
     */
    protected $name;

    /**
     * @var string 通过时间
     */
    protected $pass_time;

    /**
     * @var Collection<ApprovalFlowInstanceNodeRelatedMember> 关联人员
     */
    protected $related_members;

    /**
     * @var ApprovalFlowInstanceNode 雄辩模型
     */
    protected $model;

    /**
     * @var AbstractNode|null 前置节点
     */
    protected $pre_node;

    /**
     * @var AbstractNode|null 后置节点
     */
    protected $next_node;

    protected $pre_interceptors = [];
    protected $post_interceptors = [];

    /**
     * @var ApprovalFlowInstanceService
     */
    protected $obj_service_af_instance;


    /**
     * @var ApprovalFlowInstanceNodeService
     */
    protected $obj_service_af_node;

    /**
     * @var ApprovalFlowInstanceNodeRelatedMemberService
     */
    protected $obj_service_af_related_member;

    /**
     * @param ApprovalFlowInstanceService $obj_service_af_instance
     * @param ApprovalFlowInstanceNodeService $obj_service_af_node
     * @param ApprovalFlowInstanceNodeRelatedMemberService $obj_service_af_related_member
     */
    public function __construct(
        ApprovalFlowInstanceService                  $obj_service_af_instance,
        ApprovalFlowInstanceNodeService              $obj_service_af_node,
        ApprovalFlowInstanceNodeRelatedMemberService $obj_service_af_related_member
    ){
        $this->obj_service_af_instance = $obj_service_af_instance;
        $this->obj_service_af_node = $obj_service_af_node;
        $this->obj_service_af_related_member = $obj_service_af_related_member;
    }


    /**
     * @explain:节点执行方法
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/14 17:31
     * @remark:
     */
    function execute(ApprovalFlowContext $context)
    {
        //设置当前节点
        $context->setCurrentNode($this);

        //各个节点重写的执行方法
        $this->doExecute($context);

        //是否可继续执行
        if ($this->canContinueExecute($context)) {
            //记录当前节点为已执行节点
            $context->setExecutedNode($this);
            $this->setPassTime(date('Y-m-d H:i:s'));
            //若还有下个节点则继续执行
            if (!empty($this->next_node)) {
                $this->next_node->execute($context);
            } else {
                //没有下一个节点了，结束审批流
                $approvalFlowInstance = $context->getApprovalFlowInstance();
                $approvalFlowInstance->end_time = date('Y-m-d H:i:s');
                $approvalFlowInstance->current_node_id = null;
                $approvalFlowInstance->status = ApprovalFlowInstance::STATUS_END;
            }
        }
        //保存各类操作信息
        $context->getApprovalFlowInstance()->push();
    }

    /**
     * @explain:各节点自定义执行方法
     * @param ApprovalFlowContext $context
     * @return mixed
     * @author: wzm
     * @date: 2024/5/14 17:35
     * @remark:
     */
    abstract function doExecute(ApprovalFlowContext $context);

    /**
     * @explain:是否可以继续执行
     * @param ApprovalFlowContext $context
     * @return bool
     * @author: wzm
     * @date: 2024/5/17 8:12
     * @remark: 默认只要存在下一个节点就可以继续执行
     */
    protected function canContinueExecute(ApprovalFlowContext $context)
    {
        return true;
    }


    //region getter // setter

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return AbstractNode
     */
    public function setId($id): AbstractNode
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AbstractNode
     */
    public function setName(string $name): AbstractNode
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param Model $model
     * @return AbstractNode
     */
    public function setModel(Model $model): AbstractNode
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return AbstractNode|null
     */
    public function getPreNode(): ?AbstractNode
    {
        return $this->pre_node;
    }

    /**
     * @param AbstractNode|null $pre_node
     * @return AbstractNode
     */
    public function setPreNode(?AbstractNode $pre_node): AbstractNode
    {
        $this->pre_node = $pre_node;
        return $this;
    }

    /**
     * @return AbstractNode|null
     */
    public function getNextNode(): ?AbstractNode
    {
        return $this->next_node;
    }

    /**
     * @param AbstractNode|null $next_node
     * @return AbstractNode
     */
    public function setNextNode(?AbstractNode $next_node): AbstractNode
    {
        $this->next_node = $next_node;
        return $this;
    }

    /**
     * @param callable $pre_interceptor
     * @return AbstractNode
     */
    public function setPreInterceptor(callable $pre_interceptor): AbstractNode
    {
        $this->pre_interceptors[] = $pre_interceptor;
        return $this;
    }

    /**
     * @param callable $post_interceptor
     * @return AbstractNode
     */
    public function setPostInterceptor(callable $post_interceptor): AbstractNode
    {
        $this->post_interceptors[] = $post_interceptor;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassTime()
    {
        return $this->pass_time;
    }

    /**
     * @param mixed $pass_time
     */
    public function setPassTime($pass_time)
    {
        $this->pass_time = $pass_time;
        $this->model->pass_time = $pass_time;
        $this->model->status = ApprovalFlowInstanceNode::STATUS_PASS;
        return $this;
    }

    /**
     * @return Collection<ApprovalFlowInstanceNodeRelatedMember>
     */
    public function getRelatedMembers()
    {
        return $this->related_members;
    }

    /**
     * @param Collection<ApprovalFlowInstanceNodeRelatedMember> $related_members
     */
    public function setRelatedMembers($related_members) : AbstractNode
    {
        $this->related_members = $related_members;
        return $this;
    }



    //endregion


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
            "id" => $this->id,
            "parent_id" => $this->parent_id,
            "name" => $this->name,
            "pass_time" => $this->pass_time,
            "related_member" => $this->related_members
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
