<?php

namespace Js3\ApprovalFlow\Test\Handler;

use GuzzleHttp\Client;
use Js3\ApprovalFlow\Encrypter\AesEncrypter;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Utils\OutputUtils;
use PHPUnit\Framework\TestCase;

class ApprovalFlowHandlerTest extends TestCase
{

    private $auth_info;
    /**
     * @var QjApprovalFlowHandler
     */
    private $handler;

    private $config = [
        "http" => [
            "base_uri" => "http://cloud-library-exam.com",
            "headers" => [
                "Accept" => "Application/json",
            ],
        ],
        "aes" => [
            "key" => "5efd3f6060e20330",
            "iv" => "625202f9149e0611",
        ],
    ];


    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);


        $client = new Client($this->config["http"]);
        $encrypter = new AesEncrypter($this->config["aes"]["key"],$this->config["aes"]["iv"]);
        $http_client = new HttpClient(
            $client, $encrypter
        );
        $this->handler = new QjApprovalFlowHandler($http_client);

    }


    public function testGenerate()
    {
        $auth_info = new AuthInfo(["id" => 11], AuthInfo::AUTH_TYPE_FRONT);
        $this->handler->setAuthInfo($auth_info);
        $form_data = [
            "name" => "测试",
            "age" => 18,
            "sex" => "男",
            "address" => "北京",
            "remark" => "备注",

        ];
        $res = $this->handler->generate($form_data);
        OutputUtils::p($res->toArray());
    }


}
