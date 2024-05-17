<?php

namespace HttpClient;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use PHPUnit\Framework\TestCase;

class HttpClientTest extends \AbstractUnitTest
{

    public function testHttpClientIsPass() {
        $url = "http://cloud-library-exam.com/test";

        $client = new Client(["verify"=>false]);
        $query = ["para" => 1];
        $client = new HttpClient($client);
        try {
            $httpGet = $client->doHttpRequest($url, "GET",["query" => $query]);
        } catch (BadResponseException $e) {
            $this->p((string)$e->getResponse()->getBody());
        }
        $this->p($httpGet->getBody()->getContents());

}

    function p()
    {
        $ary_data = func_get_args();
        echo '<pre>';
        foreach ($ary_data as $item) {
            print_r($item);
            echo PHP_EOL;
        }
        exit();
    }
}
