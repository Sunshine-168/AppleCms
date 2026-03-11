<?php

namespace App\Http\Controllers\Api;

class PublicApiController extends BaseController
{
    public function index()
    {
        return $this->response(1, '获取成功', [
            'status' => data_get($this->config, 'api.publicapi.status', 0),
            'charge' => data_get($this->config, 'api.publicapi.charge', 0),
            'auth' => data_get($this->config, 'api.publicapi.auth', ''),
        ]);
    }

    protected function ensureApiSectionEnabled(string $section)
    {
        $sectionConfig = data_get($this->config, 'api.' . $section, []);
        if (($sectionConfig['status'] ?? 0) != 1) {
            return response('closed', 403);
        }

        if (($sectionConfig['charge'] ?? 0) == 1) {
            $auth = $sectionConfig['auth'] ?? '';
            if (!$this->checkDomainAuth($auth)) {
                return response('域名未授权', 403);
            }
        }

        return null;
    }

    protected function checkDomainAuth(string $auth): bool
    {
        $ip = request()->ip();
        $authList = ['127.0.0.1', '::1'];

        if (!empty($auth)) {
            foreach (explode('#', $auth) as $domain) {
                $domain = trim($domain);
                if ($domain === '') {
                    continue;
                }
                $authList[] = $domain;
                if (!filter_var($domain, FILTER_VALIDATE_IP)) {
                    $resolved = gethostbyname($domain);
                    if ($resolved && $resolved !== $domain) {
                        $authList[] = $resolved;
                    }
                }
            }
        }

        $authList = array_values(array_unique(array_filter($authList)));
        return in_array($ip, $authList, true);
    }
}
