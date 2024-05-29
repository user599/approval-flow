<?php


namespace Js3\ApprovalFlow\Service;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
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
    private $obj_service_instance_node;

    /**
     * @param ApprovalFlowInstance $obj_model_approval_flow_instance
     */
    public function __construct(
        ApprovalFlowInstanceNodeService $obj_service_instance_node,
        ApprovalFlowInstance            $obj_model_instance
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
    public function findById($id)
    {
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
     * @remark: 此方法没有事务包裹，调用时自行包裹事务
     */
    public function saveInstance($ary_data, AuthInfo $auth_info)
    {
        $ary_insert_data = [
            "config_id" => $ary_data["id"],
            "allow_withdraw" => $ary_data["allow_withdraw"],
            "withdraw_type" => $ary_data["withdraw_type"],
            "creator_id" => $auth_info->getAuthId(),
            "creator_type" => $auth_info->getAuthType(),
            "form_data" => json_encode($ary_data['form_data']),
            "status" => ApprovalFlowInstance::STATUS_NOT_START,
        ];
        $obj_instance = $this->obj_model_instance->newQuery()->create($ary_insert_data);
        $obj_node = $this->obj_service_instance_node->createNode($ary_data["node"], $obj_instance->id);
        //保存一下当前节点id
        $obj_instance->current_node_id = $obj_node->id;
        $obj_instance->save();
        return $obj_instance;


    }


}
