<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;

final class HttpSsl
{
    public static function verify(): bool|string
    {
        $env = env('HTTP_VERIFY_SSL');
        if ($env !== null && $env !== '') {
            if (is_string($env) && is_file($env)) {
                return $env;
            }
            $bool = filter_var($env, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($bool === false) {
                return false;
            }
        }

        $ca = env('HTTP_CA_FILE');
        if (is_string($ca) && $ca !== '' && is_file($ca)) {
            return $ca;
        }

        foreach ([
            resource_path('certs/cacert.pem'),
            (string) ini_get('curl.cainfo'),
            (string) ini_get('openssl.cafile'),
            '/etc/ssl/certs/ca-certificates.crt',
            '/etc/pki/tls/certs/ca-bundle.crt',
        ] as $path) {
            $path = trim((string) $path);
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }

        return PHP_OS_FAMILY === 'Windows' ? false : true;
    }

    public static function apply(PendingRequest $request): PendingRequest
    {
        $verify = self::verify();
        if ($verify === false) {
            return $request->withoutVerifying();
        }
        if (is_string($verify)) {
            return $request->withOptions(['verify' => $verify]);
        }

        return $request;
    }
}
