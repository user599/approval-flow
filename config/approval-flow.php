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

    "provider" => [
        "encrypter" => \Js3\ApprovalFlow\Encrypter\AesEncrypter::class,
    ],

    "resolver" => [

    ]
];
