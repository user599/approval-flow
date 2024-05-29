<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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
    private $obj_service_related_member;

    private $obj_service_operate_record;

    /**
     * @var ApprovalFlowInstanceNode
     */
    private $obj_model_node;

    /**
     * @param ApprovalFlowInstanceNodeOperatorService $obj_service_related_member
     * @param ApprovalFlowInstanceNode $obj_model_node
     */
    public function __construct(
        ApprovalFlowInstanceNodeRelatedMemberService $obj_service_related_member,
        ApprovalFlowInstanceNodeOperateRecordService $obj_service_operate_record,
        ApprovalFlowInstanceNode                     $obj_model_node
    )
    {
        $this->obj_service_related_member = $obj_service_related_member;
        $this->obj_service_operate_record = $obj_service_operate_record;
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
    public function findById($id)
    {
        return $this
            ->obj_model_node
            ->newQuery()
            ->findOrFail($id);
    }

    /**
     * @explain: 创建节点
     * @param array $node_data
     * @param $instance_id
     * @param $parent_id
     * @author: wzm
     * @date: 2024/5/24 14:09
     * @remark:
     */
    public function createNode(array $node_data, $instance_id, $parent_id = null)
    {
        $ary_insert_node_data = [
            "instance_id" => $instance_id,
            "parent_id" => $parent_id,
            "name" => $node_data["name"],
            "type" => $node_data["type"],
            "metadata" => json_encode($node_data["metadata"]??null),
        ];
        $obj_node_instance = $this->obj_model_node->newQuery()->create($ary_insert_node_data);
        $this->obj_service_related_member->createRelatedMember($node_data["related_member"], $instance_id, $obj_node_instance->id);
        if (!empty($node_data['children'])) {
            $this->createNode($node_data['children'], $instance_id, $obj_node_instance->id);
        }
        return $obj_node_instance;

    }



}
