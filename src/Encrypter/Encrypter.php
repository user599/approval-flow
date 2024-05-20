<?php


namespace Js3\ApprovalFlow\Encrypter;

interface Encrypter
{

    /**
     * @explain: 加密方法
     * @param $payload  需要加密的信息
     * @return string
     * @author: wzm
     * @date: 2024/5/20 9:23
     * @remark:
     */
    public function encrypt($payload): string;


    /**
     * @explain:解密方法
     * @param $token    需要解密的信息
     * @return mixed
     * @author: wzm
     * @date: 2024/5/20 9:24
     * @remark:
     */
    public function decrypt($token);
}
