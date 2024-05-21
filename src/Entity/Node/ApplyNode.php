<?php


namespace Js3\ApprovalFlow\Entity\Node;




use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;
use Js3\ApprovalFlow\Model\ApprovalFlowNode;

/**
 * @explain:开始节点
 * @author: wzm
 * @date: 2024/5/14 17:25
 */
class ApplyNode extends AbstractNode
{



    /**
     * @explain:开始节点直接接后续节点即可
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/14 18:07
     * @remark:
     */
    function doExecute(ApprovalFlowContext $context)
    {
        //记录申请信息
        $authInfo = $context->getAuthInfo();
        $approval_flow_instance = $context->getApprovalFlowInstance();
        $bool_is_operator = false;
        throw_if(
            $approval_flow_instance->status != ApprovalFlowInstance::STATUS_NOT_START,
            ApprovalFlowException::class,
            "该审批流已经启动"
        );

        //将审批流开启
        $approval_flow_instance->status = ApprovalFlowInstance::STATUS_RUNNING;
        $approval_flow_instance->save();
        foreach ($this->operator as $operator) {
            if ($operator->operator_id == $authInfo->getAuthId()
                && $operator->operator_type == $authInfo->getAuthType()
            ) {
                $bool_is_operator = true;
                $operator->status = ApprovalFlowInstanceNodeOperator::STATUS_PASS;
                $operator->operate_time = date('Y-m-d H:i:s');
                $operator->payload = json_encode($context->getArgs());
                $operator->save();
                break;
            }
        }
        throw_if(!$bool_is_operator,ApprovalFlowException::class,"申请人不在当前节点操作人列表内");

    }
}
