<?php


namespace Js3\ApprovalFlow\Entity\Node;



use Js3\ApprovalFlow\Entity\ApprovalFlowContext;

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
        //记录抄送信息

    }
}
