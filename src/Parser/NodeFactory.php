<?php


namespace Js3\ApprovalFlow\Parser;


use Illuminate\Foundation\Application;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Parser\impl\ApplyNodeParser;
use Js3\ApprovalFlow\Parser\impl\AuditNodeParser;
use Js3\ApprovalFlow\Parser\impl\CarbonCopyNodeParser;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/6/13 9:43
 */
class NodeFactory
{


    /**
     * @var Application
     */
    private $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }


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
     * @explain:
     * @param ApprovalFlowInstanceNode $model
     * @return AbstractNode
     * @author: wzm
     * @date: 2024/6/13 9:44
     * @remark:
     */
    public static function make(ApprovalFlowInstanceNode $model_node)
    {
        $factory = app(self::class);
        $parse_clazz = self::NODE_PARSER_MAP[$model_node->type] ?? null;
        if (empty($parse_clazz)) {
            throw new ApprovalFlowException("未配置该类型节点的解析器:{$model_node->type}");
        }
        /** @var NodeParseable $parser */
        $parser = $factory->app->make($parse_clazz);
        $parser->parseModelToNode($model_node);
        return $parser->getNode();
    }

}
