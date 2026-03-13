<?php

namespace App\Http\Middleware;

use App\Enums\System\StatusEnum;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JwtCertificationMiddleware
{
    private string $secretKey;
    private string $algorithm;
    private int $expireTime;

    public function __construct()
    {
        $this->secretKey = Config::get('jwt_conf.key', 'default-secret-key');
        $this->algorithm = Config::get('jwt_conf.algorithm', 'HS256');
        $this->expireTime = (int) Config::get('jwt_conf.expire', 7200);
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->getToken($request);

        if ($token === '') {
            return $this->unauthorized('Token is required');
        }

        $payload = $this->validateToken($token);

        if ($payload === false) {
            return $this->unauthorized('Invalid or expired token');
        }

        if ($this->isTokenBlacklisted($token)) {
            return $this->unauthorized('Token has been revoked');
        }

        $request->attributes->set('jwt_payload', $payload);
        $request->attributes->set('jwt_token', $token);

        $this->logAccess($request, $payload);

        return $next($request);
    }

    private function getToken(Request $request): string
    {
        $authorization = $request->header('Authorization', '');

        if ($authorization !== '' && stripos($authorization, 'Bearer ') === 0) {
            return substr($authorization, 7);
        }

        return (string) ($request->input('token', '') ?: $request->cookie('access_token', ''));
    }

    private function validateToken(string $token): false|array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return false;
            }

            [$header, $payload, $signature] = $parts;

            $headerData = json_decode($this->base64UrlDecode($header), true);
            $payloadData = json_decode($this->base64UrlDecode($payload), true);

            if (!is_array($headerData) || !is_array($payloadData)) {
                return false;
            }

            if (($headerData['alg'] ?? '') !== $this->algorithm) {
                return false;
            }

            $expectedSignature = $this->generateSignature($header . '.' . $payload);
            if (!hash_equals($expectedSignature, $signature)) {
                return false;
            }

            if (isset($payloadData['exp']) && (int) $payloadData['exp'] < time()) {
                return false;
            }

            if (isset($payloadData['nbf']) && (int) $payloadData['nbf'] > time()) {
                return false;
            }

            $issuer = Config::get('jwt_conf.issuer', '');
            if ($issuer !== '' && ($payloadData['iss'] ?? '') !== $issuer) {
                return false;
            }

            $audience = Config::get('jwt_conf.audience', '');
            if ($audience !== '' && ($payloadData['aud'] ?? '') !== $audience) {
                return false;
            }

            return $payloadData;
        } catch (\Throwable) {
            return false;
        }
    }

    public function generateToken(array $payload): string
    {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $this->expireTime;
        $payload['nbf'] = $now;
        $payload['iss'] = Config::get('jwt_conf.issuer', 'shop-api');
        $payload['aud'] = Config::get('jwt_conf.audience', 'shop-api-client');
        $payload['jti'] = uniqid('', true);

        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm,
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->generateSignature($headerEncoded . '.' . $payloadEncoded);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signature;
    }

    public static function blacklistToken(string $token, ?int $expireTime = null): bool
    {
        $ttl = $expireTime ?? (int) Config::get('jwt_conf.expire', 7200);
        return Cache::put('jwt_blacklist:' . md5($token), true, $ttl);
    }

    private function generateSignature(string $data): string
    {
        return match ($this->algorithm) {
            'HS256' => $this->base64UrlEncode(hash_hmac('sha256', $data, $this->secretKey, true)),
            'HS384' => $this->base64UrlEncode(hash_hmac('sha384', $data, $this->secretKey, true)),
            'HS512' => $this->base64UrlEncode(hash_hmac('sha512', $data, $this->secretKey, true)),
            default => throw new \InvalidArgumentException('Unsupported algorithm: ' . $this->algorithm),
        };
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder !== 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return (string) base64_decode(strtr($data, '-_', '+/'));
    }

    private function isTokenBlacklisted(string $token): bool
    {
        return Cache::has('jwt_blacklist:' . md5($token));
    }

    private function logAccess(Request $request, array $payload): void
    {
        if (!Config::get('jwt_conf.audit.enable', true)) {
            return;
        }

        Log::channel(Config::get('jwt_conf.audit.channel', 'stack'))->info('JWT Auth Access', [
            'user_id' => $payload['user_id'] ?? 0,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'path' => $request->path(),
            'method' => $request->method(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    private function unauthorized(string $message): Response
    {
        return response()->json([
            'code' => StatusEnum::FAILED->value,
            'msg' => $message,
            'data' => (object) [],
        ], 401);
    }
}
