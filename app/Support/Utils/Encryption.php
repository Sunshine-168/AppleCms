<?php
namespace App\Support\Utils;

use Exception;
use Illuminate\Support\Facades\Config;
use Random\RandomException;

/**
 * 数据加密工具类
 * 提供数据加密、解密和哈希功能
 * Class Encryption
 * @package app\common\utils
 */
class Encryption
{
    /**
     * 默认加密算法
     * @var string
     */
    private static string $defaultCipher = 'AES-256-GCM';

    /**
     * 默认密钥
     * @var string
     */
    private static ?string $defaultKey = null;

    /**
     * 获取默认密钥
     * @return string
     */
    private static function getDefaultKey(): string
    {
        if (self::$defaultKey === null) {
            self::$defaultKey = Config::get('security.basic.secret_key', 'default-secret-key');
        }

        return self::$defaultKey;
    }

    /**
     * 加密数据
     * @param string $data 要加密的数据
     * @param string|null $key 加密密钥
     * @param string|null $cipher 加密算法
     * @return string 加密后的数据（Base64编码）
     * @throws Exception
     */
    public static function encrypt(string $data, ?string $key = null, ?string $cipher = null): string
    {
        $key = $key ?: self::getDefaultKey();
        $cipher = $cipher ?: self::$defaultCipher;

        // 生成随机IV
        $ivLength = openssl_cipher_iv_length($cipher);
        if ($ivLength === false) {
            throw new Exception('Invalid cipher: ' . $cipher);
        }

        $iv = openssl_random_pseudo_bytes($ivLength);

        // 对于GCM模式，需要处理tag
        if (str_contains($cipher, 'GCM')) {
            $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
            if ($encrypted === false) {
                throw new Exception('Encryption failed');
            }

            // 将IV、tag和加密数据组合
            $result = $iv . $tag . $encrypted;
        } else {
            $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
            if ($encrypted === false) {
                throw new Exception('Encryption failed');
            }

            // 将IV和加密数据组合
            $result = $iv . $encrypted;
        }

        return base64_encode($result);
    }

    /**
     * 解密数据
     * @param string $encryptedData 加密的数据（Base64编码）
     * @param string|null $key 解密密钥
     * @param string|null $cipher 加密算法
     * @return string 解密后的数据
     * @throws Exception
     */
    public static function decrypt(string $encryptedData, ?string $key = null, ?string $cipher = null): string
    {
        $key = $key ?: self::getDefaultKey();
        $cipher = $cipher ?: self::$defaultCipher;

        $data = base64_decode($encryptedData);
        if ($data === false) {
            throw new Exception('Invalid encrypted data');
        }

        $ivLength = openssl_cipher_iv_length($cipher);
        if ($ivLength === false) {
            throw new Exception('Invalid cipher: ' . $cipher);
        }

        // 对于GCM模式，需要处理tag
        if (str_contains($cipher, 'GCM')) {
            $tagLength = 16; // GCM tag length

            if (strlen($data) < $ivLength + $tagLength) {
                throw new Exception('Invalid encrypted data length');
            }

            $iv = substr($data, 0, $ivLength);
            $tag = substr($data, $ivLength, $tagLength);
            $encrypted = substr($data, $ivLength + $tagLength);

            $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
        } else {
            if (strlen($data) < $ivLength) {
                throw new Exception('Invalid encrypted data length');
            }

            $iv = substr($data, 0, $ivLength);
            $encrypted = substr($data, $ivLength);

            $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        }

        if ($decrypted === false) {
            throw new Exception('Decryption failed');
        }

        return $decrypted;
    }

    /**
     * 生成安全的随机密钥
     * @param int $length 密钥长度
     * @return string
     */
    public static function generateKey(int $length = 32): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    /**
     * 哈希密码
     * @param string $password 原始密码
     * @param array|null $options 哈希选项
     * @return string 哈希后的密码
     */
    public static function hashPassword(string $password, ?array $options = null): string
    {
        $algorithm = Config::get('security.password.hash_algorithm', PASSWORD_ARGON2ID);
        $defaultOptions = Config::get('security.password.hash_options', [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);

        $options = $options ?: $defaultOptions;

        return password_hash($password, $algorithm, $options);
    }

    /**
     * 验证密码
     * @param string $password 原始密码
     * @param string $hash 哈希值
     * @return bool
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * 检查密码是否需要重新哈希
     * @param string $hash 当前哈希值
     * @param array|null $options 哈希选项
     * @return bool
     */
    public static function needsRehash(string $hash, ?array $options = null): bool
    {
        $algorithm = Config::get('security.password.hash_algorithm', PASSWORD_ARGON2ID);
        $defaultOptions = Config::get('security.password.hash_options', [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3,
        ]);

        $options = $options ?: $defaultOptions;

        return password_needs_rehash($hash, $algorithm, $options);
    }

    /**
     * 生成HMAC签名
     * @param string $data 要签名的数据
     * @param string|null $key 签名密钥
     * @param string $algorithm 哈希算法
     * @return string
     */
    public static function hmac(string $data, ?string $key = null, string $algorithm = 'sha256'): string
    {
        $key = $key ?: self::getDefaultKey();
        return hash_hmac($algorithm, $data, $key);
    }

    /**
     * 验证HMAC签名
     * @param string $data 原始数据
     * @param string $signature 签名
     * @param string|null $key 签名密钥
     * @param string $algorithm 哈希算法
     * @return bool
     */
    public static function verifyHmac(string $data, string $signature, ?string $key = null, string $algorithm = 'sha256'): bool
    {
        $expectedSignature = self::hmac($data, $key, $algorithm);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * 安全的字符串比较
     * @param string $known 已知字符串
     * @param string $user 用户输入字符串
     * @return bool
     */
    public static function safeCompare(string $known, string $user): bool
    {
        return hash_equals($known, $user);
    }

    /**
     * 生成安全的随机字符串
     * @param int $length 长度
     * @param string $characters 字符集
     * @return string
     * @throws RandomException
     */
    public static function randomString(int $length = 32, string $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'): string
    {
        $charactersLength = strlen($characters);
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * 生成UUID v4
     * @return string
     */
    public static function generateUuid(): string
    {
        $data = openssl_random_pseudo_bytes(16);

        // 设置版本号 (4) 和变体位
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return sprintf('%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }

    /**
     * 数据脱敏
     * @param string $data 原始数据
     * @param string $type 数据类型
     * @return string 脱敏后的数据
     */
    public static function maskData(string $data, string $type): string
    {
        return match ($type) {
            'phone' => self::maskPhone($data),
            'email' => self::maskEmail($data),
            'id_card' => self::maskIdCard($data),
            'bank_card' => self::maskBankCard($data),
            'name' => self::maskName($data),
            default => self::maskDefault($data),
        };
    }

    /**
     * 手机号脱敏
     * @param string $phone
     * @return string
     */
    private static function maskPhone(string $phone): string
    {
        if (strlen($phone) < 7) {
            return $phone;
        }

        return substr($phone, 0, 3) . '****' . substr($phone, -4);
    }

    /**
     * 邮箱脱敏
     * @param string $email
     * @return string
     */
    private static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return $email;
        }

        $username = $parts[0];
        $domain = $parts[1];

        if (strlen($username) <= 2) {
            return $email;
        }

        $maskedUsername = substr($username, 0, 1) . str_repeat('*', strlen($username) - 2) . substr($username, -1);

        return $maskedUsername . '@' . $domain;
    }

    /**
     * 身份证脱敏
     * @param string $idCard
     * @return string
     */
    private static function maskIdCard(string $idCard): string
    {
        if (strlen($idCard) < 8) {
            return $idCard;
        }

        return substr($idCard, 0, 4) . str_repeat('*', strlen($idCard) - 8) . substr($idCard, -4);
    }

    /**
     * 银行卡脱敏
     * @param string $bankCard
     * @return string
     */
    private static function maskBankCard(string $bankCard): string
    {
        if (strlen($bankCard) < 8) {
            return $bankCard;
        }

        return substr($bankCard, 0, 4) . str_repeat('*', strlen($bankCard) - 8) . substr($bankCard, -4);
    }

    /**
     * 姓名脱敏
     * @param string $name
     * @return string
     */
    private static function maskName(string $name): string
    {
        $length = mb_strlen($name, 'UTF-8');

        if ($length <= 1) {
            return $name;
        } elseif ($length === 2) {
            return mb_substr($name, 0, 1, 'UTF-8') . '*';
        } else {
            return mb_substr($name, 0, 1, 'UTF-8') . str_repeat('*', $length - 2) . mb_substr($name, -1, 1, 'UTF-8');
        }
    }

    /**
     * 默认脱敏
     * @param string $data
     * @return string
     */
    private static function maskDefault(string $data): string
    {
        $length = strlen($data);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return substr($data, 0, 2) . str_repeat('*', $length - 4) . substr($data, -2);
    }
}
