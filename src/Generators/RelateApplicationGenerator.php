<?php

namespace Js3\ApprovalFlow\Generators;

use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\SelectOptions;

/**
 * 关联应用生成器
 */
interface RelateApplicationGenerator
{

    /**
     * @explain:获取应用列表数据
     * @param AuthInfo $authInfo 当前登陆用户信息
     * @return array<SelectOptions>
     * @author: wzm
     * @date: 2024/5/17 15:47
     * @remark:
     */
    public function options(AuthInfo $authInfo):array;

    /**
     * @explain:获取子集
     * @param AuthInfo $authInfo 当前用户信息
     * @param int $parent_slug  父级标识-id
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 15:48
     * @remark:
     */
    public function children(AuthInfo $authInfo,$parent_slug);

}