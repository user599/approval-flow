<?php

namespace Js3\ApprovalFlow\RelatedApplication;

use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\SelectOptions;

/**
 * 关联应用生成器
 * 步骤：
 *      1.实现本生成器
 *      2.在配置文件 config/approval-flow.php 中 generator 下添加生成器
 *          若不存在请通过发布配置的方式创建:php artisan vendor:publish =》 选择Js3\ApprovalFlow
 *      3.generator 下生成器标识即是数组的key，value为生成器的全限定类名
 *          eg:   "demo-department"=> \Js3\ApprovalFlow\Generators\DepartmentApplicationGeneratorImpl::class
 *
 */
interface RelatedApplication
{

    /**
     * @explain:获取应用列表数据
     * @param AuthInfo $authInfo 当前登陆用户信息
     * @return array
     * @author: wzm
     * @date: 2024/5/17 15:47
     * @remark: 用于分支条件的下拉框，存在children时会渲染为树装选择器
     *      数据结构
     *          [
     *             'name'=>节点名
     *             'value' => 节点值
     *              'children' => [子节点信息]
     *          ]
     *
     */
    public function options(AuthInfo $authInfo):array;

    /**
     * @explain:获取指定节点的子集
     * @param AuthInfo $authInfo 当前用户信息
     * @param int|string $node_primary_value  指定节点的标识，即上方应用列表数据中的节点值
     * @return array
     * @author: wzm
     * @date: 2024/5/17 15:48
     * @remark: 用于在分支条件使用 “从属于/不从属于”这类集合判断时拿去指定节点的子集
     *          数据结构与上方 获取应用列表数据 一致
     */
    public function children(AuthInfo $authInfo,$node_value);

}