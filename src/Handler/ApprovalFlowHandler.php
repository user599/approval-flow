<?php

namespace Js3\ApprovalFlow\Handler;

use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AbstractNode;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Throwable;

interface ApprovalFlowHandler
{


    /**
     * @explain: 生成审批流
     * @param $form_data 表单数据
     * @return array
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark: 基于如下数据获取审批流实例
     *          1.审批标识 protected $approval_flow_slug;
     *          2.当前用户信息     protected $auth_info;
     *          3.表单数据
     * @remark: 本方法会先将实例数据放入缓存，在执行审批流时才实例化到数据库中
     */
    public function generate($form_data = []);

    /**
     * @explain: 执行审批流
     * @param int $instance_id 实例id
     * @param array $args 额外参数，可以在后续调用中使用，如：审批、抄送后置处理
     * @return ApprovalFlowContext
     * @throws ApprovalFlowException|Throwable
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function execute($instance_id, $args);

    /**
     * @explain: 通过
     * @param int $node_id
     * @param string $remark
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark:
     */
    public function auditPass($node_id, $remark = null);

    /**
     * @explain: 拒绝
     * @param int $node_id
     * @param string $remark
     * @return AbstractNode|null
     * @author: wzm
     * @date: 2024/5/17 14:45
     * @remark:返回空说明直接结束
     *          返回节点说明重新回到了该节点，需要重新审批
     */
    public function auditRefuse($node_id, $remark = null);

    /**
     * @explain:撤销
     * @param int $instance_id
     * @param string $remark
     * @return mixed
     */
    public function withdraw($instance_id, $remark = null);

    /**
     * @explain 获取审批实例状态
     * @param int $instance_id
     * @return ApprovalFlowContext
     */
    public function getStatus($instance_id);

    /**
     * @explain:向节点存入新成员
     * @param int $node_id 节点id
     * @param array<AuthInfo> $ary_auth_info 要插入的用户数组-请格式化为 AuthInfo类
     * @return int        插入的人员数量
     * @throws \Throwable
     * @author: wzm
     * @date: 2024/6/3 10:11
     * @remark: 若该人员已经在节点中，则该人员将会跳过
     */
    public function insertMember($node_id, array $ary_insert_auth_info);


}
