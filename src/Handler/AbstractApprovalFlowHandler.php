<?php


namespace Js3\ApprovalFlow\Handler;


use Illuminate\Contracts\Foundation\Application;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\AuthInfo;
use Js3\ApprovalFlow\Entity\Node\AuditNode;
use Js3\ApprovalFlow\Entity\Node\CarbonCopyNode;
use Js3\ApprovalFlow\HttpClient\HttpClient;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;

/**
 * @explain:抄送审批流处理
 * @author: wzm
 * @date: 2024/5/14 14:48
 */
abstract class AbstractApprovalFlowHandler implements ApprovalFlowHandler
{

    /**
     * @var string 审批流标识
     */
    protected static $approval_flow_slug;

    /**
     * @var ApprovalFlowContext 审批流上下文
     */
    protected $approval_flow_context;
    /**
     * @var AuthInfo 当前用户身份信息
     */
    protected $auth_info;

    /**
     * @var HttpClient 发起http请求的客户端
     */
    private $http_client;

    /**
     * @param HttpClient $http_client http客户端
     */
    public function __construct(AuthInfo $auth_info)
    {
        $this->auth_info = $auth_info;
        $this->http_client = app(HttpClient::class)->setAuthInfo($auth_info);
    }

    /**
     * @explain: 生成审批流
     * @param $form_data
     * @return ApprovalFlowInstance
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Js3\ApprovalFlow\Exceptions\RemoteCallErrorException
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function generate($form_data = [])
    {
        $data =[
            "form_data" => $form_data,
            "slug" => static::$approval_flow_slug,
        ];
        $res = $this->http_client->httpPost("/api/approval-flow/generate",$data);
        //格式化返回的数据
        //TODO 将生成的结构存储到数据库中
        return new ApprovalFlowInstance($res);
    }

    /**
     * @explain: 执行审批流
     * @param $instance_id
     * @param $args
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function execute($instance_id, $args): ApprovalFlowContext
    {
        // TODO: Implement execute() method.

    }

    /**
     * @explain: 审核通过
     * @param $node_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function auditPass($node_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement auditPass() method.
    }

    /**
     * @explain: 审核拒绝
     * @param $node_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:41
     * @remark:
     */
    public function reject($node_id, $remark = null, $operate_time = null): ApprovalFlowContext
    {
        // TODO: Implement reject() method.
    }

    /**
     * @explain: 撤销
     * @param $instance_id
     * @param $remark
     * @param $operate_time
     * @return ApprovalFlowContext
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function revocation($instance_id, $remark = null, $operate_time = null)
    {
        // TODO: Implement revocation() method.
    }

    /**
     * @explain: 获取当前审批流状态
     * @param $instance_id
     * @return ApprovalFlowInstance
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public function getStatus($instance_id)
    {
        // TODO: Implement getStatus() method.
    }

    /**
     * @explain: 审批额外操作
     * @param AuditNode $node
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 15:08
     * @remark:
     */
    abstract function handleAuditExtraOperate(AuditNode $node);

    /**
     * @explain: 抄送额外操作
     * @param CarbonCopyNode $node
     * @return mixed
     * @author: wzm
     * @date: 2024/5/17 15:08
     * @remark:
     */
    abstract function handleCarbonCopy(CarbonCopyNode $node);


    /**
     * @explain: 获取当前审批流标识
     * @return string
     * @author: wzm
     * @date: 2024/5/20 9:42
     * @remark:
     */
    public static function getApprovalFlowSlug(): string
    {
        return static::$approval_flow_slug;
    }

    /**
     * @return HttpClient
     */
    public function getHttpClient(): HttpClient
    {
        return $this->http_client;
    }

    /**
     * @param HttpClient $http_client
     */
    public function setHttpClient(HttpClient $http_client): void
    {
        $this->http_client = $http_client;
    }

    /**
     * @explain: 设置用户身份信息
     * @param AuthInfo $auth_info
     * @return $this
     * @author: wzm
     * @date: 2024/5/17 15:06
     * @remark: TODO 想想别的更好的解决方法，这个样子需要每次调用上述审批方法时必须先指定用户身份
     */
    public function getAuthInfo()
    {
        return $this->auth_info;
    }

    /**
     * @explain: 设置用户身份信息
     * @param AuthInfo $auth_info
     * @return $this
     * @author: wzm
     * @date: 2024/5/17 15:06
     * @remark: TODO 想想别的更好的解决方法，这个样子需要每次调用上述审批方法时必须先指定用户身份
     */
    public function setAuthInfo(AuthInfo $auth_info)
    {
        $this->auth_info = $auth_info;
        $this->http_client->setAuthInfo($auth_info);
        return $this;
    }


}
