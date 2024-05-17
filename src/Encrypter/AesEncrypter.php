<?php

namespace Js3\ApprovalFlow\Encrypter;

/**
 * @explain:默认加密，解密类
 * @author: wzm
 * @date: 2024/5/16 16:07
 */
class AesEncrypter implements Encrypter
{


    private $key ;   //密钥
    private $iv ;   //偏移量

    /**
     * @param $key
     * @param $iv
     */
    public function __construct($key, $iv)
    {
        $this->key = $key;
        $this->iv = $iv;
    }


    public function encrypt($payload): string
    {
        if (!is_string($payload)) {
            $payload = json_encode($payload);
        }
        $payload = self::pkcs5_pad($payload, 16);
        $token = openssl_encrypt($payload, 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->iv);
        return  base64_encode($token);
    }

    public function decrypt($token)
    {
        $decrypted = openssl_decrypt(base64_decode($token), 'AES-128-CBC',$this->key, OPENSSL_RAW_DATA|OPENSSL_ZERO_PADDING, $this->iv);

        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s-1]);
        if($padding == 0){
            return $decrypted;
        }
        return base64_decode(substr($decrypted, 0, -$padding),true);
    }

    //填充
    public function pkcs5_pad($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

}
