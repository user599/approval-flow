<?php


namespace Js3\ApprovalFlow\Parser;


use Illuminate\Database\Eloquent\Model;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/20 15:16
 */
interface NodeParseable
{

    /**
     * @explain:将雄辩模型转换为节点
     * @param Model $data
     * @author: wzm
     * @date: 2024/5/20 15:19
     * @remark:
     */
    public function parseModelToNode(Model $data);

    /**
     * @explain: 获取节点
     * @return AbstractNode
     * @author: wzm
     * @date: 2024/6/7 11:58
     * @remark:
     */
    public function getNode(): AbstractNode;

}
