<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Illuminate\Support\Facades\DB;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNode;
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
        /**
         * 1.开始审批实例
         * 2.申请节点值直接通过
         * 3.申请人设置为通过
         */
        $obj_instance = $context->getApprovalFlowInstance();
        if ($obj_instance->status != ApprovalFlowInstance::STATUS_NOT_START) {
           throw new ApprovalFlowException("审批流已开始");
        }
        $obj_instance->status = ApprovalFlowInstance::STATUS_RUNNING;
        $current_date = date('Y-m-d H:i:s');
        $this->setPassTime($current_date);
        $this->applicant->status =  ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS;
        $this->applicant->operate_time = $current_date;
        $this->applicant->remark = "申请节点自动通过";
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
