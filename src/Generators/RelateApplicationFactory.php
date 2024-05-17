<?php


namespace Js3\ApprovalFlow\Generators;


use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 16:06
 */
class RelateApplicationFactory
{

    private static $generator_list = [];

    /**
     * @explain: 选择生成器
     * @param string $slug
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/17 16:14
     * @remark:
     */
    public static function chooseGenerator(string $slug)
    {
        $generator_clazz = self::$generator_list[strtolower($slug)];
        if (empty($generator_clazz)) {
            throw new ApprovalFlowException("未知的关联应用标识{$slug},请在配置文件配置");
        }
        return $generator_clazz;
    }

    /**
     * @explain:注册生成器
     * @param $slug
     * @param  $generator_instance
     * @author: wzm
     * @date: 2024/5/17 16:14
     * @remark:
     */
    public static function register($slug,  $generator_clazz)
    {
        self::$generator_list[strtolower($slug)] = $generator_clazz;
    }
}