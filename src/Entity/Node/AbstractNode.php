<?php


namespace Js3\ApprovalFlow\Entity\Node;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Js3\ApprovalFlow\Entity\ApprovalFlowContext;
use Js3\ApprovalFlow\Entity\Interceptor\LogInterceptor;
use Js3\ApprovalFlow\Entity\Interceptor\NodeInterceptor;
use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Js3\ApprovalFlow\Model\ApprovalFlowInstance;
use Js3\ApprovalFlow\Model\ApprovalFlowInstanceNodeOperator;
use Js3\ApprovalFlow\Model\ApprovalFlowNode;


/**
 * @explain:节点抽象类
 * @author: wzm
 * @date: 2024/5/14 16:28
 */
abstract class AbstractNode
{

    /**
     * @var string 节点名称
     */
    protected $name;

    /**
     * @var ApprovalFlowNode 雄辩模型
     */
    protected $model;

    /**
     * @var array 静态拦截器
     */
    private static $static_pre_interceptor_list = [];
    private static $static_post_interceptor_list = [];


    /**
     * @var string[] 当前节点前置拦截器
     */
    private $pre_interceptor_list = [LogInterceptor::class];

    /**
     * @var array 当前节点后置拦截器
     */
    private $post_interceptor_list = [];


    /**
     * @var AbstractNode|null 前置节点
     */
    protected $pre_node;

    /**
     * @var AbstractNode|null 后置节点
     */
    protected $next_node;


    /**
     * @var array<ApprovalFlowInstanceNodeOperator> 当前节点操作人
     */
    protected $operator = [];

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
        //设置当前节点
        $context->setCurrentNode($this);

        //前置拦截器
        $this->intercept(static::$static_pre_interceptor_list, $context);
        $this->intercept($this->pre_interceptor_list, $context);

        //各个节点重写的执行方法
        $this->doExecute($context);

        /**
         * 当可以执行下一节点时触发后置拦截器并继续执行
         */
        if ($this->canContinueExecute($context)) {
            //记录当前节点为已执行节点
            $context->setExecutedNode($this);

            //当前节点通过时间
            $this->model->pass_time = date('Y-m-d H:i:s');

            //后置拦截器
            $this->intercept($this->pre_interceptor_list, $context);
            $this->intercept(static::$static_post_interceptor_list, $context);

            //若还有下个节点则继续执行
            if (!empty($this->next_node)) {
                $this->next_node->execute($context);
            } else {
                //否则说明结束了
                $context->getApprovalFlowInstance()->update(
                    [
                        "status" => ApprovalFlowInstance::STATUS_FINISH,
                        "finish_time" => date('Y-m-d H:i:s')
                    ]
                );
            }
        }
        $this->model->save();
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
    protected function canContinueExecute(ApprovalFlowContext $context)
    {
        return true;
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
     * @return array
     */
    public static function getStaticPreInterceptorList(): array
    {
        return self::$static_pre_interceptor_list;
    }

    /**
     * @param array $static_pre_interceptor_list
     */
    public static function setStaticPreInterceptorList(array $static_pre_interceptor_list): void
    {
        self::$static_pre_interceptor_list = $static_pre_interceptor_list;
    }

    /**
     * @return array
     */
    public static function getStaticPostInterceptorList(): array
    {
        return self::$static_post_interceptor_list;
    }

    /**
     * @param array $static_post_interceptor_list
     */
    public static function setStaticPostInterceptorList(array $static_post_interceptor_list): void
    {
        self::$static_post_interceptor_list = $static_post_interceptor_list;
    }


    /**
     * @explain:设置前置拦截器
     * @param callable|NodeInterceptor $interceptor
     * @author: wzm
     * @date: 2024/5/14 18:00
     * @remark:
     */
    public function setPreInterceptor($interceptor)
    {
        $this->pre_interceptor_list[] = $interceptor;
    }

    /**
     * @explain:设置后置拦截器
     * @param callable|NodeInterceptor $interceptor
     * @author: wzm
     * @date: 2024/5/14 18:00
     * @remark:
     */
    public function setPostInterceptor($interceptor)
    {
        $this->post_interceptor_list[] = $interceptor;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return AbstractNode
     */
    public function setName(string $name): AbstractNode
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     * @return AbstractNode
     */
    public function setModel(Model $model): AbstractNode
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return AbstractNode|null
     */
    public function getPreNode(): ?AbstractNode
    {
        return $this->pre_node;
    }

    /**
     * @param AbstractNode|null $pre_node
     * @return AbstractNode
     */
    public function setPreNode(?AbstractNode $pre_node): AbstractNode
    {
        $this->pre_node = $pre_node;
        return $this;
    }

    /**
     * @return AbstractNode|null
     */
    public function getNextNode(): ?AbstractNode
    {
        return $this->next_node;
    }

    /**
     * @param AbstractNode|null $next_node
     * @return AbstractNode
     */
    public function setNextNode(?AbstractNode $next_node): AbstractNode
    {
        $this->next_node = $next_node;
        return $this;
    }

    /**
     * @return ApprovalFlowInstanceNodeOperator[]
     */
    public function getOperator(): array
    {
        return $this->operator;
    }

    /**
     * @param ApprovalFlowInstanceNodeOperator[] $operator
     * @return AbstractNode
     */
    public function setOperator($operator): AbstractNode
    {

        $this->operator = is_array($operator) ? $operator : [$operator];
        return $this;
    }


}
