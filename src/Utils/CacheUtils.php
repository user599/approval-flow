<?php


namespace Js3\ApprovalFlow\Utils;


use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

/**
 * @explain:
 * @author: wzm
 * @date: 2024/6/5 17:24
 */
class CacheUtils
{

    const CACHE_TTL = 600;


    /**
     * @explain: 缓存数据
     * @param $data
     * @author: wzm
     * @date: 2024/6/5 17:30
     * @remark:
     */
    public static function setCache($data)
    {
        $str_key = Str::uuid()->serialize();
        $data = json_encode($data);
        Redis::SETEX($str_key, self::CACHE_TTL, $data);
        return $str_key;
    }


    /**
     * @explain:获取缓存
     * @param $str_key
     * @return mixed
     * @throws \Js3\ApprovalFlow\Exceptions\ApprovalFlowException
     * @author: wzm
     * @date: 2024/6/5 17:30
     * @remark: 每个缓存只能使用一次
     */
    public static function getCache($str_key)
    {
        $str_data = Redis::get($str_key);
        approvalFlowAssert(empty($str_data), "数据异常或已过期，请刷新后重试");
        //使用后删除
        Redis::del($str_key);
        return json_decode($str_data, true);
    }
}
