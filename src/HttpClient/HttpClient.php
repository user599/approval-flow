<?php


namespace Js3\ApprovalFlow\HttpClient;


use GuzzleHttp\Client;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Resolvers\AuthInfoResolver;
use Js3\ApprovalFlow\Traits\HasHttpRequests;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/16 17:24
 */
class HttpClient
{

    use HasHttpRequests;

    const HTTP_METHOD_GET = "GET";
    const HTTP_METHOD_POST = "POST";

    protected $base_uri;

    /**
     * @var AuthInfo|null 用户身份信息
     */
    protected $auth_info;

    public function __construct(Client $client,$auth_info = null)
    {
        $this->httpClient = $client;
        $this->auth_info = $auth_info;
    }


    public function httpGet(string $url, $query = [])
    {
        return $this->doHttpRequest($url, self::HTTP_METHOD_GET, ["query" => $query]);
    }

    public function httpPost(string $url, $data = [])
    {
        return $this->doHttpRequest($url, self::HTTP_METHOD_POST, ["form_params" => $data]);
    }

    public function httpPostJson(string $url, array $data = [], array $query = [])
    {
        return $this->doHttpRequest($url, self::HTTP_METHOD_POST, ['query' => $query, 'json' => $data]);
    }

    public function doHttpRequest(string $url, string $method = self::HTTP_METHOD_GET, array $options = [])
    {
        if (empty($this->middlewares)) {
            //注册 http 中间件
            $this->registerHttpMiddlewares();
        }
        $response = $this->request($url, $method, $options);
        return $response;

    }


    protected function registerHttpMiddlewares()
    {
        //1.身份验证中间件
        if (!empty($this->auth_info)) {

        }

        //2.返回值验证中间件

    }

}
