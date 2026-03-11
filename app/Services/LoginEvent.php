<?php

namespace App\Services;

use App\Libraries\Login\ThinkOauth;

class LoginEvent
{
    public function qq(array $token): array
    {
        $qq = ThinkOauth::getInstance('qq', $token);
        $data = $qq->call('user/get_user_info');
        if (($data['ret'] ?? -1) === 0) {
            return [
                'code' => 1,
                'msg' => 'ok',
                'info' => [
                    'type' => 'QQ',
                    'name' => (string) ($data['nickname'] ?? ''),
                    'nick' => (string) ($data['nickname'] ?? ''),
                    'head' => (string) ($data['figureurl_2'] ?? ''),
                    'openid' => $qq->openid(),
                ],
            ];
        }

        return [
            'code' => 0,
            'msg' => '获取腾讯QQ用户信息失败：' . (string) ($data['msg'] ?? 'unknown'),
        ];
    }

    public function weixin(array $token): array
    {
        $weixin = ThinkOauth::getInstance('weixin', $token);
        $data = $weixin->call('sns/userinfo');
        if (($data['errcode'] ?? 0) === 0 && !empty($data['openid'])) {
            return [
                'code' => 1,
                'msg' => 'ok',
                'info' => [
                    'type' => 'WEIXIN',
                    'name' => (string) ($data['nickname'] ?? ''),
                    'nick' => (string) ($data['nickname'] ?? ''),
                    'head' => (string) ($data['headimgurl'] ?? ''),
                    'openid' => $weixin->openid(),
                ],
            ];
        }

        return [
            'code' => 0,
            'msg' => '获取微信用户信息失败：' . (string) ($data['errmsg'] ?? 'unknown'),
        ];
    }
}
