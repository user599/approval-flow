<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 14:02
 */
class ApprovalFlowInstanceService
{

    /**
     * @var ApprovalFlowInstance
     */
    private $obj_model_approval_flow_instance;

    /**
     * @param ApprovalFlowInstance $obj_model_approval_flow_instance
     */
    public function __construct(ApprovalFlowInstance $obj_model_approval_flow_instance)
    {
        $this->obj_model_approval_flow_instance = $obj_model_approval_flow_instance;
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
            ->obj_model_approval_flow_instance
            ->newQuery()
            ->findOrFail($id);
    }


}