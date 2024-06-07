<?php


namespace Js3\ApprovalFlow\Parser;


use Illuminate\Database\Eloquent\Model;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Parser\impl\ApplyNodeParser;
use Js3\ApprovalFlow\Parser\impl\AuditNodeParser;
use Js3\ApprovalFlow\Parser\impl\CarbonCopyNodeParser;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/20 15:16
 */
interface NodeParseable
{

    /**
     * 审批节点映射
     * 添加新的节点和解析器时时需要添加映射关系
     */
    const NODE_PARSER_MAP = [
        ApprovalFlowInstanceNode::NODE_TYPE_APPLY => ApplyNodeParser::class,
        ApprovalFlowInstanceNode::NODE_TYPE_APPROVE => AuditNodeParser::class,
        ApprovalFlowInstanceNode::NODE_TYPE_CARBON_COPY => CarbonCopyNodeParser::class,
    ];

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
