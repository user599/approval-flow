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
     * 数据库链接信息
     */
    "db" => [
        "connection" => "mysql_approval_flow"
    ],
    /**
     * 额外提供服务方法，可自行替换
     */
    "provider" => [
        "encrypter" => \Js3\ApprovalFlow\Encrypter\AesEncrypter::class,
    ],

    /**
     * 配置关联应用，新的应用需要填写在此处
     * key:应用标识
     * value:生成器的全限定类名，该类将被自动实例化请注意构造函数的参数处理
     * eg： 关联应用可以通过 php artisan make:related-application [类名] 创建
     *
     *      "department"=> \Js3\ApprovalFlow\Generators\DepartmentApplicationImpl::class,
     *        当获取department标识的应用数据时会调用该类的 children/options 方法
     *          options 获取下拉列表数据，返回数组（按需设置树状结构）
     *          children 获取指定选项的所有子集 返回数组 （一维数组即可，不需要设置树型结构）
     */
    "related-application" => [

    ]
];
