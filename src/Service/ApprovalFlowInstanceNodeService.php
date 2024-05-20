<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceNodeService
{

    /**
     * @var ApprovalFlowInstanceNodeOperatorService
     */
    private $obj_service_operator;

    /**
     * @var ApprovalFlowInstanceNode
     */
    private $obj_model_node;

    /**
     * @param ApprovalFlowInstanceNodeOperatorService $obj_service_operator
     * @param ApprovalFlowInstanceNode $obj_model_node
     */
    public function __construct(
        ApprovalFlowInstanceNodeOperatorService $obj_service_operator,
        ApprovalFlowInstanceNode $obj_model_node
    )
    {
        $this->obj_service_operator = $obj_service_operator;
        $this->obj_model_node = $obj_model_node;

    }

    /**
     * @explain: 基于id获取实例
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws ModelNotFoundException
     * @author: wzm
     * @date: 2024/5/17 14:03
     * @remark:
     */
    public function findById($id) {
        return $this
            ->obj_model_node
            ->newQuery()
            ->findOrFail($id);
    }

    public function createNode(array $node_data,$instance_id,$parent_id = null) {
        foreach ($node_data as $node) {
            $ary_insert_node_data = [
                "instance_id" => $instance_id,
                "parent_id" => $parent_id,
                "name" => $node["name"],
                "node_type" => $node["node_type"],
                "metadata" => null, //TODO 暂时未使用到元数据字段
            ];
            $obj_node_instance = $this->obj_model_node->newQuery()->create($ary_insert_node_data);
            throw_if(empty($node_data["operator"]),ApprovalFlowException::class,"节点{$node["name"]}未指定操作人，请联系管理员");
            $this->obj_service_operator->createOperator($node_data["operator"],$instance_id,$obj_node_instance->id);
        }
   }



}