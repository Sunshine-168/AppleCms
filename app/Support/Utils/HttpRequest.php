<?php
namespace App\Support\Utils;



/**
 * http请求类
 */
class HttpRequest
{

    /**
     * 替换原来的 curlPost 函数
     */
    public static function guzzlePost(string $url, $data)
    {
        $service  = HttpRequestFactory::create(HttpRequestFactory::TYPE_FORM);
        $response = $service->post($url, $data);
        return $response['body'];
    }

    /**
     * 替换原来的 curl_post (JSON版本)
     */
    public static  function guzzlePostJson(string $url, array $data = [] , array $headers = [])
    {
        $service  = HttpRequestFactory::create(HttpRequestFactory::TYPE_JSON);
        $response = $service->post($url, $data, $headers);
        return $response['body'] ?? '';
    }

    /**
     * 替换原来的 getContent 函数
     */
    public static function guzzleRequest(string $url, string $method = 'GET', $body = '', $header = [])
    {
        $contentType = $header['Content-Type'] ?? '';
        $type        = str_contains($contentType, 'json') ? HttpRequestFactory::TYPE_JSON : HttpRequestFactory::TYPE_FORM;

        $service     = HttpRequestFactory::create($type);

        if ($method == 'POST')
        {
            $data = $type === HttpRequestFactory::TYPE_JSON ? json_decode($body, true) : http_build_query($body);
            $response = $service->post($url, $data, $header);
        } else
        {
            $response = $service->get($url, $body, $header);
        }

        return $response['body'];
    }

    /**
     * 替换原来的 curl_post_formdata 函数
     */
    public static function guzzlePostFormData($url, $data = [])
    {
        $service  = HttpRequestFactory::create(HttpRequestFactory::TYPE_FORMDATA);
        $response = $service->post($url, $data);
        return $response['body'] ?? '';
    }
}
