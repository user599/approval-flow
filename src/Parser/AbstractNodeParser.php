<?php


namespace Js3\ApprovalFlow\Parser;


use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/20 15:18
 */
abstract class AbstractNodeParser implements NodeParseable
{

    /**
     * @var AbstractNode
     */
    private $node;


    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }




    /**
     * @explain:将雄辩模型转换为节点
     * @param Model $data
     * @author: wzm
     * @date: 2024/5/20 15:19
     * @remark:
     */
    public function parseModelToNode(Model $data)
    {
        $this->node = $this->newNode();
        $this->node->setId($data->id);
        $this->node->setName($data->name);
        $this->node->setModel($data);
        $this->node->setParentId($data->parent_id);
        $this->node->setPassTime($data->pass_time);
        $this->node->setRelatedMembers($data->relatedMembers);
        $this->parseExtra($this->node, $data);
    }

    public function getNode(): AbstractNode
    {
        return $this->node;
    }


    /**
     * @explain: 要创建的节点
     * @return mixed
     * @return AbstractNode
     * @author: wzm
     * @date: 2024/5/20 15:40
     * @remark:
     */
    abstract protected function newNode();


    /**
     * @explain:额外的格式化操作
     * @param AbstractNode $node 当前节点
     * @param Model $model 节点对应的雄辩模型
     * @return mixed
     * @author: wzm
     * @date: 2024/5/20 15:35
     * @remark: 子类重写此方法添加额外字段信息
     */
    protected function parseExtra(AbstractNode $node, Model $model)
    {

    }

}
