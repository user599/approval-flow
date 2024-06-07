<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
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
        if (!empty($this->model->pass_time)) {
            throw new ApprovalFlowException("抄送节点已通过,无法重复执行");
        }
        //抄送节点直接通过
        $current_date = date('Y-m-d H:i:s');
        $this->setPassTime($current_date);
        foreach ($this->carbon_copy_recipients as $carbon_copy_recipient) {
            $carbon_copy_recipient->status = ApprovalFlowInstanceNodeRelatedMember::STATUS_PASS;
            $carbon_copy_recipient->operate_time = $current_date;
            $carbon_copy_recipient->remark = "抄送节点自动通过";
        }
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
