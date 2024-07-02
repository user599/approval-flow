<?php


namespace Js3\ApprovalFlow\Parser;


use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/6/5 17:34
 */
class InstanceParser
{

    /**
     * @explain: 验证响应是否合法
     * @param $response_data
     * @throws \Js3\ApprovalFlow\Exceptions\ApprovalFlowException
     * @author: wzm
     * @date: 2024/6/6 11:15
     * @remark:
     */
    protected static function validateResponse($response_data)
    {
        $ary_validate_rule = [
            "id" => "required|integer",
            "allow_withdraw" => [
                "required",
                Rule::in([ApprovalFlowInstance::ALLOW_WITHDRAW_TRUE, ApprovalFlowInstance::ALLOW_WITHDRAW_FALSE])
            ],
            "withdraw_type" => [
                "required_if:allow_withdraw," . ApprovalFlowInstance::ALLOW_WITHDRAW_TRUE,
                Rule::in([ApprovalFlowInstance::WITHDRAW_TYPE_NOT_IN_PROGRESS, ApprovalFlowInstance::WITHDRAW_TYPE_IN_PROGRESS, ApprovalFlowInstance::WITHDRAW_TYPE_END])
            ],
            "form_data" => "nullable",
            "node" => "required|array",
            "node.*.name" => "required|string",
            "node.*.type" => "required|integer",
            "node.*.metadata" => "nullable",
            "node.*.related_member" => "required|array",
            "node.*.related_member.*.member_id" => "required|integer",
            "node.*.related_member.*.member_type" => "required|integer",
        ];

        $validator = Validator::make($response_data, $ary_validate_rule);
        if ($validator->fails()) {
            Log::error("审批流数据验证失败:", ["data" => $response_data, "errors" => $validator->errors()]);
        }
        approvalFlowAssert($validator->fails(), "审批流数据验证失败:" . $validator->errors()->first());
    }

    /**
     * @explain: 将响应数据格式化为数组
     * @param array $response_data
     * @param AuthInfo $auth_info
     * @return array
     * @throws \Js3\ApprovalFlow\Exceptions\ApprovalFlowException
     * @author: wzm
     * @date: 2024/6/6 11:15
     * @remark:
     */
    public static function parseFromResponseToArr(array $response_data, AuthInfo $auth_info)
    {
        self::validateResponse($response_data);
        $ary_instance_data = [
            "config_id" => $response_data["id"],
            "allow_withdraw" => $response_data["allow_withdraw"],
            "withdraw_type" => $response_data["withdraw_type"],
            "creator_id" => $auth_info->getAuthId(),
            "creator_type" => $auth_info->getAuthType(),
            "form_data" => json_encode($response_data['form_data']),
            "status" => ApprovalFlowInstance::STATUS_NOT_START,
        ];

        $ary_instance_data["node"] = collect($response_data["node"])
            ->map(function ($node) {
                $ary_node_base_info = [
                    "name" => $node["name"],
                    "type" => $node["type"],
                    "metadata" => empty($node["metadata"] ) ? null : json_encode($node["metadata"]),
                ];
                $ary_node_base_info["related_member"] = collect($node["related_member"])
                    ->map(function ($member) {
                        return [
                            "member_id" => $member["member_id"],
                            "member_type" => $member["member_type"],
                            "name" => $member["name"],
                            "avatar" => $member["avatar"]??null,
                            "user_no" => $member["user_no"]??null,
                        ];
                    });
                return $ary_node_base_info;
            });

        return $ary_instance_data;
    }

    /**
     * @explain: 将数组格式化为模型
     * @param $ary_data
     * @return mixed
     * @throws \Throwable
     * @author: wzm
     * @date: 2024/6/6 11:16
     * @remark:
     */
    public static function parseFromArrToModel($ary_data)
    {
        return approvalFlowTransaction(function () use ($ary_data) {
            $ary_instance_base_data = Arr::except($ary_data, "node");
            $obj_instance = new ApprovalFlowInstance($ary_instance_base_data);
            $obj_instance->save();
            //处理节点
            $node_parent_id = null;
            foreach ($ary_data["node"] as $node_data) {
                $ary_node_base_data = Arr::except($node_data, "related_member");
                $ary_node_base_data["parent_id"] = $node_parent_id;
                $ary_node_base_data["status"] = ApprovalFlowInstanceNode::STATUS_UN_OPERATE;
                $obj_node = new ApprovalFlowInstanceNode($ary_node_base_data);
                $obj_instance->nodes()->save($obj_node);
                //处理关联人员
                $ary_related_member_base_data = [];
                foreach ($node_data["related_member"] as $related_member) {
                    //添加冗余字段方便查询
                    $related_member["instance_id"] = $obj_instance->id;
                    $related_member["status"] = ApprovalFlowInstanceNodeRelatedMember::STATUS_UN_OPERATE;
                    $ary_related_member_base_data[] = new ApprovalFlowInstanceNodeRelatedMember($related_member);
                }

                //
                $obj_node->relatedMembers()->saveMany($ary_related_member_base_data);
                $node_parent_id = $obj_node->id;
            }

            return $obj_instance;
        });
    }

}
