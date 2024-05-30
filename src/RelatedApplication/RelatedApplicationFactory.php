<?php


namespace Js3\ApprovalFlow\RelatedApplication;


use Js3\ApprovalFlow\Exceptions\ApprovalFlowException;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 16:06
 */
class RelatedApplicationFactory
{

    /**
     * @var array 关联应用列表
     */
    private static $related_application_list = [];

    /**
     * @explain: 选择关联应用
     * @param string $slug
     * @throws ApprovalFlowException
     * @author: wzm
     * @date: 2024/5/17 16:14
     * @remark:
     */
    public static function chooseRelatedApplication(string $slug)
    {
        $generator_clazz = self::$related_application_list[strtolower($slug)] ?? null;
        if (empty($generator_clazz)) {
            throw new ApprovalFlowException("未知的关联应用标识{$slug},请在配置文件配置");
        }
        return $generator_clazz;
    }

    /**
     * @explain:注册关联应用
     * @param $slug
     * @param  $application_clazz
     * @author: wzm
     * @date: 2024/5/17 16:14
     * @remark:
     */
    public static function register($slug, $application_clazz)
    {
        self::$related_application_list[strtolower($slug)] = $application_clazz;
    }
}