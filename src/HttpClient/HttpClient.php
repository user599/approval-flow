<?php


namespace Js3\ApprovalFlow\HttpClient;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Promise\PromiseInterface;
use Js3\ApprovalFlow\Encrypter\Encrypter;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Exceptions\RemoteCallErrorException;
use Js3\ApprovalFlow\Traits\HasHttpRequests;
use Js3\ApprovalFlow\Utils\OutputUtils;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

    /**
     * @var Encrypter 交互用加密，解密方法
     */
    protected $encrypter;

    public function __construct(Client $client, Encrypter $encrypter)
    {
        $this->client = $client;
        $this->encrypter = $encrypter;
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

    /**
     * @explain:实际请求方法
     * @param string $url   请求url
     * @param string $method    请求方法
     * @param array $options    请求选项信息 {@see https://guzzle-cn.readthedocs.io/zh-cn/latest/request-options.html}
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws RemoteCallErrorException
     * @author: wzm
     * @date: 2024/5/17 13:58
     * @remark:
     */
    public function doHttpRequest(string $url, string $method = self::HTTP_METHOD_GET, array $options = [])
    {
        if (empty($this->middlewares)) {
            //注册 http 中间件
            $this->registerHttpMiddlewares();
        }
        $response = $this->request($url, $method, $options);
        return $this->formatResponse($response)['data']??[];

    }

    /**
     * @explain:设置身份信息
     * @param AuthInfo $authInfo
     * @author: wzm
     * @date: 2024/5/17 13:57
     * @remark:
     */
    public function setAuthInfo(AuthInfo $authInfo)
    {
        $this->auth_info = $authInfo;
    }

    /**
     * @explain: 注册中间件
     * @author: wzm
     * @date: 2024/5/17 13:57
     * @remark:
     */
    protected function registerHttpMiddlewares()
    {
        //1.身份验证中间件
        $this->pushMiddleware($this->authHeaderMiddleware(), 'auth-token');

        //...待定其他处理
        $this->pushMiddleware($this->wrapperResponseMiddleware(),"wrapper-response");
    }

    /**
     * @explain: 身份信息请求头中间件
     * @return \Closure
     * @author: wzm
     * @date: 2024/5/17 11:14
     * @remark:
     */
    private function authHeaderMiddleware()
    {
        return function (callable $handler) {
            return function (
                RequestInterface $request,
                array            $options
            ) use ($handler) {
                $encrypt = $this->encrypter->encrypt($this->auth_info->getAuthPayload());
                $request = $request->withHeader("token", $encrypt);
                return $handler($request, $options);
            };
        };
    }

    /**
     * @explain:包装返回值中间件
     * @return \Closure
     * @author: wzm
     * @date: 2024/5/17 13:57
     * @remark:
     */
    private function wrapperResponseMiddleware()
    {
        return function (callable $handler) {
            return function (
                RequestInterface $request,
                array            $options
            ) use ($handler) {
                /** @var PromiseInterface $promise */
                $promise = $handler($request, $options);
                return $promise->then(function (ResponseInterface $response) {

                    $response_body = $this->formatResponse($response);
                    if (
                        $response->getStatusCode() >= 400
                        ||
                        (!empty($response_body) && !($response_body["status"]??false))
                    ) {
                        $msg = $response_body["msg"] ??'服务器错误';
                        throw new RemoteCallErrorException("请求远程服务器失败:" . $msg,$response);
                    }
                    return $response;
                });
            };
        };
    }

    /**
     * @explain:格式化返回值
     * @param ResponseInterface $response
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 13:57
     * @remark:
     */
    private function formatResponse(ResponseInterface $response)
    {
        return  json_decode((string) $response->getBody(), true);
    }


}
