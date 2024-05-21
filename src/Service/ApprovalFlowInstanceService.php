<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Js3\ApprovalFlow\Entity\AuthInfo;
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
    private $obj_model_instance;

    /**
     * @var ApprovalFlowInstanceNodeService
     */
    private  $obj_service_instance_node;

    /**
     * @param ApprovalFlowInstance $obj_model_approval_flow_instance
     */
    public function __construct(
        ApprovalFlowInstanceNodeService $obj_service_instance_node,
        ApprovalFlowInstance $obj_model_instance
    )
    {
        $this->obj_model_instance = $obj_model_instance;
        $this->obj_service_instance_node = $obj_service_instance_node;
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
            ->obj_model_instance
            ->newQuery()
            ->findOrFail($id);
    }

    /**
     * @explain:保存实例到数据库
     * @param $ary_data
     * @param AuthInfo $auth_info
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|ApprovalFlowInstance|\LaravelIdea\Helper\Js3\ApprovalFlow\Model\_IH_ApprovalFlowInstance_QB
     * @author: wzm
     * @date: 2024/5/20 16:58
     * @remark:
     */
    public function saveInstance($ary_data,AuthInfo $auth_info) {
        $ary_insert_data = [
            "config_id" => $ary_data["config_id"],
            "can_revocation" => $ary_data["can_vocation"],
            "revocation_type" => $ary_data["revocation_type"],
            "creator_id" => $auth_info->getAuthId(),
            "creator_time" => date('Y-m-d H:i:s'),
            "status" => ApprovalFlowInstance::STATUS_NOT_START,
        ];
        $obj_instance = $this->obj_model_instance->newQuery()->create($ary_insert_data);
        $this->obj_service_instance_node->createNode($ary_data["node"],$obj_instance->id);
        return $obj_instance;
    }

}
