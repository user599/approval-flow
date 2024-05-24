<?php

namespace Js3\ApprovalFlow\Handler;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;

interface ApprovalFlowHandler
{


    /**
     * @explain: 生成审批流
     * @param $form_data
     * @return ApprovalFlowInstance
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark:
     */
    public function generate($form_data = []);

    /**
     * @explain: 执行审批流
     * @param $instance_id
     * @param $args
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark:
     */
    public function execute($instance_id, $args): ApprovalFlowContext;

    /**
     * @explain: 通过
     * @param $node_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark:
     */
    public function auditPass($node_id, $remark = null, $operate_time = null): ApprovalFlowContext;

    /**
     * @explain: 拒绝
     * @param $node_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark:
     */
    public function reject($node_id, $remark = null, $operate_time = null): ApprovalFlowContext;

    /**
     * @explain:撤销
     * @param $remark
     * @return mixed
     */
    public function revocation($instance_id, $remark = null, $operate_time = null);

    /**
     * @explain 获取审批实例状态
     * @param $instance_id
     * @return mixed
     */
    public function getStatus($instance_id);


}
