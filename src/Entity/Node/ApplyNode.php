<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeRelatedMember;

/**
 * @explain:开始节点
 * @author: wzm
 * @date: 2024/5/14 17:25
 */
class ApplyNode extends AbstractNode
{


    /**
     * @var ApprovalFlowInstanceNodeRelatedMember 申请人
     */
    private $applicant;

    /**
     * @explain:开始节点直接接后续节点即可
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/14 18:07
     * @remark:
     */
    function doExecute(ApprovalFlowContext $context)
    {
        //直接通过节点
        $this->obj_service_af_node->passNode($this->id,"申请节点自动通过");
    }


    /**
     * @return ApprovalFlowInstanceNodeRelatedMember
     */
    public function getApplicant(): ApprovalFlowInstanceNodeRelatedMember
    {
        return $this->applicant;
    }

    /**
     * @param ApprovalFlowInstanceNodeRelatedMember $applicant
     * @return ApplyNode
     */
    public function setApplicant(ApprovalFlowInstanceNodeRelatedMember $applicant): ApplyNode
    {
        $this->applicant = $applicant;
        return $this;
    }


}
