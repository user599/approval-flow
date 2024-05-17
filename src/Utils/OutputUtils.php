<?php


namespace Js3\ApprovalFlow\Utils;


/**
 * @explain:
 * @author: wzm
 * @date: 2024/5/17 11:27
 */
class OutputUtils
{

    public static function p(...$args) {
        $ary_data = func_get_args();
        echo '<pre>';
        foreach ($ary_data as $item) {
            print_r($item);
            echo PHP_EOL;
        }
        exit();
    }

}