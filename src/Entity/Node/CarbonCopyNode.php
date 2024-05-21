<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;

/**
 * @explain:抄送节点
 * @author: wzm
 * @date: 2024/5/14 17:25
 */
class CarbonCopyNode extends AbstractNode
{

    /**
     * @explain:抄送节点直接接后续节点即可
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/14 18:07
     * @remark:
     */
    function doExecute(ApprovalFlowContext $context)
    {
        //抄送节点直接通过
        foreach ($this->model->operators as $operator) {
            $operator->status = ApprovalFlowInstanceNodeOperator::STATUS_PASS;
            $operator->operate_time = date('Y-m-d H:i:s');
            $operator->payload = json_encode($context->getArgs());
            $operator->save();
        }
    }
}
