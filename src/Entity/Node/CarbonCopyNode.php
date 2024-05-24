<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;

/**
 * @explain:抄送节点
 * @author: wzm
 * @date: 2024/5/14 17:25
 */
class CarbonCopyNode extends AbstractNode
{

    /**
     * @var array<ApprovalFlowInstanceNodeRelatedMember> 抄送人
     */
    private $carbon_copy_recipients;

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
        $this->obj_service_af_node->passNode($this->id,"抄送节点自动通过");
    }

    /**
     * @return ApprovalFlowInstanceNodeRelatedMember[]
     */
    public function getCarbonCopyRecipients()
    {
        return $this->carbon_copy_recipients;
    }

    /**
     * @param ApprovalFlowInstanceNodeRelatedMember[] $carbon_copy_recipients
     * @return CarbonCopyNode
     */
    public function setCarbonCopyRecipients($carbon_copy_recipients): CarbonCopyNode
    {
        $this->carbon_copy_recipients = $carbon_copy_recipients;
        return $this;
    }


}
