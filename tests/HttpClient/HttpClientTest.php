<?php

namespace HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Js3\ApprovalFlow\Encrypter\AesEncrypter;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Utils\OutputUtils;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends TestCase
{


    public function testHttpClientIsPass()
    {

        $http_client = new HttpClient(
            new Client(
                json_decode(file_get_contents(__DIR__ . "/../data/http.json"), true)
            ),
            new AesEncrypter(...array_values(json_decode(file_get_contents(__DIR__ . "/../data/encrypter.json"), true)['aes']))
        );

        $url = "/test";
        $query = ["para" => 1];
        $auth_info = new AuthInfo(["data" => 22,"desc" => 231321],AuthInfo::AUTH_TYPE_FRONT);
        $res = $http_client->setAuthInfo($auth_info)->doHttpRequest($url, "GET", ["query" => $query]);

        self::assertIsArray($res);

    }


}
