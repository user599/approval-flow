<?php

namespace Js3\ApprovalFlow\Handler;

use App\ApprovalFlow\Entity\AuthInfo;

interface ApprovalFlowHandler
{


    /**
     * @param $slug 审批流
     * @return mixed
     */
    public function generate($form_data);

    /**
     * @explain:执行审批流
     * @return mixed
     * @author: wzm
     * @date: 2024/5/14 15:05
     * @remark:
     */
    public function execute($instance_id,$form_data);

    /**
     * @explain:审核审核通过
     * @param $snapshot_id
     * @param $remark
     * @param $operate_time
     * @return mixed
     */
    public function auditPass($snapshot_id, $remark = null,$operate_time = null);

    /**
     * @explain:审核通过
     * @param $snapshot_id
     * @param $remark
     * @param $operate_time
     * @return mixed
     * @author: wzm
     * @date: 2024/5/14 15:05
     * @remark:
     */
    public function reject($snapshot_id, $remark = null,$operate_time = null);

    /**
     * @explain:撤销
     * @param $remark
     * @return mixed
     */
    public function revocation($instance_id,$remark = null,$operate_time = null);


    /**
     * @explain 获取审批实例状态
     * @param $instance_id
     * @return mixed
     */
    public function getStatus($instance_id);

    public function getAuthInfo() : AuthInfo;

    public function getApprovalFlowSlug() : string;

}
