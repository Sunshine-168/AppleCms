<?php

namespace App\Utils;

/**
 * 密码
 * Class Password
 * @package app\common\utils
 */
class Password
{

   public static mixed $key;

    public function __construct()
    {
        self::$key  = env('APP.PASSWORD_SALT');
    }

    /**
     * 加密
     * @param $data
     * @return string
     */
   public static function encrypt($data): string
   {
        // 使用 AES-256-CBC 算法
        $cipher                 = "AES-256-CBC";
        // 生成随机的初始向量（IV）
        $iv                     = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
        // 加密数据
        $encryptedData          = openssl_encrypt($data, $cipher, self::$key, 0, $iv);
       // 将 IV 和加密后的数据组合
       return base64_encode($encryptedData . '::' . $iv);
    }


    /**
     * 解密
     * @param $encryptedDataWithIv
     * @return false|string
     */
   public static function decrypt($encryptedDataWithIv): bool|string
   {
       // 使用 AES-256-CBC 算法
       $cipher                      = "AES-256-CBC";
       // 分离加密数据和 IV
       list($encryptedData, $iv)    = explode('::', base64_decode($encryptedDataWithIv), 2);
        // 解密数据
       return openssl_decrypt($encryptedData, $cipher, self::$key, 0, $iv);
   }

}