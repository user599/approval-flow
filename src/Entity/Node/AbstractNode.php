<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Interceptor\NodeInterceptor;


/**
 * @explain:节点抽象类
 * @author: wzm
 * @date: 2024/5/14 16:28
 */
abstract class AbstractNode
{

    /**
     * @var AbstractNode|null 前置节点
     */
    protected  $pre_node;

    /**
     * @var AbstractNode|null 后置节点
     */
    protected $next_node;


    /**
     * @var 当前节点标识
     */
    protected $slug;


    /**
     * @var string[] 当前节点前置拦截器
     */
    private $pre_interceptor_list = [LogInterceptor::class];

    /**
     * @var array 当前节点后置拦截器
     */
    private $post_interceptor_list = [];

    /**
     * @param AbstractNode $pre_node
     * @param AbstractNode $next_node
     * @param array $pre_interceptor_list
     * @param array $post_interceptor_list
     */
    public function __construct(AbstractNode $pre_node = null, AbstractNode $next_node = null)
    {
        $this->pre_node = $pre_node;
        $this->next_node = $next_node;
    }


    /**
     * @explain:节点执行方法
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/14 17:31
     * @remark:
     */
    function execute(ApprovalFlowContext $context)
    {
        $context->setCurrentNode($this);
        $this->intercept($this->pre_interceptor_list, $context);
        $this->doExecute($context);

        /**
         * 当可以执行下一节点时触发后置拦截器并继续执行
         */
        if ($this->canContinueExecute($context)) {
            //记录当前节点为已执行节点
            $context->setExecutedNode($this);
            $this->intercept($this->pre_interceptor_list, $context);
            $this->next_node->execute($context);
        }
    }

    /**
     * @explain:各节点自定义执行方法
     * @param ApprovalFlowContext $context
     * @return mixed
     * @author: wzm
     * @date: 2024/5/14 17:35
     * @remark:
     */
    abstract function doExecute(ApprovalFlowContext $context);

    /**
     * @explain:是否可以继续执行
     * @param ApprovalFlowContext $context
     * @return bool
     * @author: wzm
     * @date: 2024/5/17 8:12
     * @remark: 默认只要存在下一个节点就可以继续执行
     */
    protected function canContinueExecute(ApprovalFlowContext $context){
        return !empty($this->next_node);
    }

    /**
     * @explain:执行拦截器
     * @param array $interceptor_list
     * @param ApprovalFlowContext $context
     * @author: wzm
     * @date: 2024/5/14 17:59
     * @remark:
     */
    protected function intercept(array $interceptor_list, ApprovalFlowContext $context)
    {
        foreach ($interceptor_list as $interceptor) {
            //1.拦截器是回调函数
            if (is_callable($interceptor)) {
                $interceptor($context);
            } else {
                //拦截器是限定类名，且该类存在
                $interceptor_clazz = str_replace('/', '\\', !empty($interceptor) ? $interceptor : '');
                if (class_exists($interceptor_clazz)) {
                    (new $interceptor_clazz)->intercept($context);
                } else {
                    throw new ApprovalFlowException("未知的拦截器信息:{$interceptor}");
                }
            }
        }
    }

    /**
     * @explain:设置前置拦截器
     * @param callable|NodeInterceptor $interceptor
     * @author: wzm
     * @date: 2024/5/14 18:00
     * @remark:
     */
    public function setPreInterceptor($interceptor) {
         $this->pre_interceptor_list[] = $interceptor;
    }

    /**
     * @explain:设置后置拦截器
     * @param callable|NodeInterceptor $interceptor
     * @author: wzm
     * @date: 2024/5/14 18:00
     * @remark:
     */
    public function setPostInterceptor($interceptor) {
        $this->post_interceptor_list[] = $interceptor;
    }

    /**
     * @return
     */
    public function getSlug(): string
    {
        return $this->slug;
    }







}
