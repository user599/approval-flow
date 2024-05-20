<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceNodeOperatorService
{

    /**
     * @var ApprovalFlowInstanceNodeOperator
     */
    private $obj_model_operator;

    /**
     * @param ApprovalFlowInstanceNodeOperator $obj_model_operator
     */
    public function __construct(ApprovalFlowInstanceNodeOperator $obj_model_operator)
    {
        $this->obj_model_operator = $obj_model_operator;
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
            ->obj_model_operator
            ->newQuery()
            ->findOrFail($id);
    }

    public function createOperator($operator_data,$instance_id,$node_id) {
        foreach ($operator_data as $operator_data) {
            $ary_insert_operator_data = [
                "node_id" => $node_id,
                "instance_id" => $instance_id,
                "operator_id" => $operator_data["operator_id"],
                "operator_type" => $operator_data["operator_type"],
                "operate_status"=> ApprovalFlowInstanceNodeOperator::OPERATOR_STATUS_UN_OPERATE
            ];
            $this->obj_model_operator->newQuery()->create($ary_insert_operator_data);
        }
   }



}