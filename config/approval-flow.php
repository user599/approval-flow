<?php
/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 15:34
 */

return [

    "http" => [
        "base_uri" => env("APPROVAL_FLOW_BASE_URI"),
        "headers" => [
            "Accept" => "Application/json",
        ],
        "http_errors" => false
    ],

    "aes" => [
        "key" => env("APPROVAL_FLOW_AES_KEY"),
        "iv" => env("APPROVAL_FLOW_AES_IV"),
    ],
    /**
     * 额外提供服务方法，可自行替换
     */
    "provider" => [
        "encrypter" => \Js3\ApprovalFlow\Encrypter\AesEncrypter::class,
    ],

    /**
     * 关联应用生成器，新的应用需要填写在此处
     * key:应用标识
     * value:生成器的全限定类名，该类将被自动实例化请注意构造函数的参数处理
     * eg： "department"=> \Js3\ApprovalFlow\Generators\DepartmentApplicationGeneratorImpl::class,
     *        当获取department标识的应用数据时会调用该类的 children/options 方法
     *          options 获取下拉列表数据，返回\Js3\ApprovalFlow\Entity\SelectOptions的数组（按需设置树状结构）
     *          children 获取指定选项的所有子集 返回\Js3\ApprovalFlow\Entity\SelectOptions的数组 （一维数组即可，不需要设置属性结构）
     */
    "relate-application" => [
        //仅供参考，正式使用时请屏蔽
        "demo-department"=> \Js3\ApprovalFlow\Generators\DepartmentApplicationGeneratorImpl::class
    ]
];
