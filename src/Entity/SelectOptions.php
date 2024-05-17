<?php


namespace Js3\ApprovalFlow\Entity;


/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 15:46
 */
class SelectOptions
{

    /**
     * @var string 选项名
     */
    private $name;

    /**
     * @var string 选项标识
     */
    private $value;

    /**
     * @var SelectOptions<array> 子选项
     */
    private $children;

}