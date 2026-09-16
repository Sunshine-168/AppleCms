<?php
namespace app\common\util;

use think\Cache;
use think\Config;
use think\addons\Service;

/**
 * 插件云市场（RS256 签目录 + package_hash + 安全安装）
 * 对齐 TemplateCloudService 流水线；包内允许 PHP（插件主类）。
 * PHP 7.0 兼容。
 */
class AddonCloudService
{
    const CACHE_CATALOG = 'addon_market_catalog';
    const CACHE_CATALOG_HASH = 'addon_market_catalog_hash';
    const CACHE_CATALOG_BACKUP = 'addon_market_catalog_backup';
    /** 已验签的原始目录 JSON，安装时重新验签用（防缓存被篡改） */
    const CACHE_CATALOG_RAW = 'addon_market_catalog_raw';

    /** 云端包体积上限（与本地 zip 上传量级一致） */
    const MAX_PACKAGE_BYTES = 10240000;

    /**
     * 内置验签公钥（与 application/data/addon_market_cloud/catalog_public.pem 同步）
     * 首次部署请运行 generate_keys.php 后粘贴此处。
     */
    const CATALOG_PUBLIC_KEY_PEM = '';

    /** @var array */
    protected $config;

    public function __construct()
    {
        $cfg = [];
        if (isset($GLOBALS['config']['addon_cloud']) && is_array($GLOBALS['config']['addon_cloud'])) {
            $cfg = $GLOBALS['config']['addon_cloud'];
        } else {
            $tmp = config('maccms.addon_cloud');
            if (is_array($tmp)) {
                $cfg = $tmp;
            }
        }
        $this->config = array_merge([
            'status' => 0,
            'catalog_url' => 'https://api.maccms.ai/addons/catalog.json',
            'cache_ttl' => 10800,
            'rate_limit' => 10,
            'audit_max' => 200,
            'legacy_catalog' => 1,
            'mock' => 0,
        ], $cfg);
    }

    public function isEnabled()
    {
        return (int) ($this->config['status'] ?? 0) === 1;
    }

    /**
     * @param bool $force
     * @return array{items:array,error:string}
     */
    public function fetchCatalog($force = false)
    {
        if (!$this->isEnabled()) {
            return ['items' => [], 'error' => 'disabled'];
        }

        // mock 骨架：本地 JSON、不走远程验签（仅列表调试；安装仍要求合法 package_hash）
        if ((int) ($this->config['mock'] ?? 0) === 1) {
            return ['items' => $this->filterApprovedItems($this->loadMockItems()), 'error' => 'mock'];
        }

        $cacheKey = self::CACHE_CATALOG;
        $hashKey = self::CACHE_CATALOG_HASH;
        $ttl = max(60, (int) ($this->config['cache_ttl'] ?? 10800));

        if (!$force) {
            $cached = Cache::get($cacheKey);
            if (!empty($cached) && is_array($cached)) {
                return ['items' => $this->filterApprovedItems($cached), 'error' => ''];
            }
        }

        $url = trim((string) ($this->config['catalog_url'] ?? ''));
        if ($url === '' || !$this->validateRemoteUrl($url)) {
            return ['items' => $this->filterApprovedItems($this->fallbackCatalog($cacheKey)), 'error' => 'url'];
        }

        try {
            $raw = $this->fetchRemoteSecure($url, 30);
            if ($raw === false || $raw === '') {
                return ['items' => $this->filterApprovedItems($this->fallbackCatalog($cacheKey)), 'error' => 'fetch'];
            }

            $items = $this->parseVerifiedCatalog($raw);
            if ($items === null) {
                return ['items' => $this->filterApprovedItems($this->fallbackCatalog($cacheKey)), 'error' => 'signature'];
            }
            if (!$this->validateCatalogFormat($items)) {
                return ['items' => $this->filterApprovedItems($this->fallbackCatalog($cacheKey)), 'error' => 'format'];
            }

            $newHash = hash('sha256', $raw);
            $oldHash = Cache::get($hashKey);
            $cached = Cache::get($cacheKey);
            if ($oldHash === $newHash && !empty($cached) && is_array($cached)) {
                Cache::set($cacheKey, $cached, $ttl);
                Cache::set(self::CACHE_CATALOG_RAW, $raw, $ttl * 3);
                return ['items' => $this->filterApprovedItems($cached), 'error' => ''];
            }

            Cache::set($cacheKey, $items, $ttl);
            Cache::set(self::CACHE_CATALOG_BACKUP, $items, $ttl * 10);
            Cache::set($hashKey, $newHash, $ttl * 3);
            Cache::set(self::CACHE_CATALOG_RAW, $raw, $ttl * 3);

            return ['items' => $this->filterApprovedItems($items), 'error' => ''];
        } catch (\Exception $e) {
            return ['items' => $this->filterApprovedItems($this->fallbackCatalog($cacheKey)), 'error' => 'exception'];
        }
    }

    /**
     * 按目录 id 安装（可选 force 覆盖升级）
     * @param string $id
     * @param int $adminId
     * @param string $adminIp
     * @return array{code:int,msg:string,data:array}
     */
    public function installById($id, $adminId = 0, $adminIp = '')
    {
        $id = $this->sanitizeId($id);
        if ($id === '') {
            return ['code' => 0, 'msg' => lang('param_err'), 'data' => []];
        }
        if (!$this->isEnabled()) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_disabled'), 'data' => []];
        }
        // mock 目录仅供列表联调，禁止当真安装通道
        if ((int) ($this->config['mock'] ?? 0) === 1) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_mock_no_install'), 'data' => []];
        }

        $rl = $this->checkRateLimit($adminId, $adminIp);
        if ($rl['ok'] !== true) {
            $this->appendAudit($adminId, '', $id, '', 'denied_rate', $adminIp, $rl['msg']);
            return ['code' => 0, 'msg' => $rl['msg'], 'data' => []];
        }

        // 安装不信任纯缓存 items：强制拉目录或对缓存 raw 重新验签
        $item = $this->resolveInstallItem($id);
        if ($item === null) {
            $this->appendAudit($adminId, '', $id, '', 'denied', $adminIp, 'not_found');
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_not_found'), 'data' => []];
        }

        $compat = $this->checkCmsCompat($item);
        if ($compat['ok'] !== true) {
            $this->appendAudit($adminId, $item['name'], $id, $item['version'], 'denied_compat', $adminIp, $compat['msg']);
            return ['code' => 0, 'msg' => $compat['msg'], 'data' => []];
        }

        // 尝试即计数，防止反复失败下载打满带宽/内存
        $this->hitRateLimit($adminId, $adminIp);

        $lock = $this->acquireInstallLock(isset($item['name']) ? $item['name'] : '');
        if ($lock === false) {
            $this->appendAudit($adminId, $item['name'], $id, $item['version'], 'denied_busy', $adminIp, 'lock');
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_busy'), 'data' => []];
        }

        try {
            $res = $this->installPackage($item);
        } finally {
            $this->releaseInstallLock($lock);
        }

        $result = !empty($res['code']) ? 'ok' : 'fail';
        $this->appendAudit(
            $adminId,
            isset($item['name']) ? $item['name'] : '',
            $id,
            isset($item['version']) ? $item['version'] : '',
            $result,
            $adminIp,
            isset($res['msg']) ? $res['msg'] : ''
        );
        if (!empty($res['code'])) {
            $this->recordInstall($item);
        }
        return $res;
    }

    /**
     * @param array $item
     * @return array{code:int,msg:string,data:array}
     */
    public function installPackage(array $item)
    {
        if (!class_exists('ZipArchive')) {
            return ['code' => 0, 'msg' => lang('admin/addon/zip_unavailable'), 'data' => []];
        }

        $name = isset($item['name']) ? strtolower(trim((string) $item['name'])) : '';
        if (!AddonSecureInstaller::isValidName($name)) {
            return ['code' => 0, 'msg' => lang('admin/addon/path_err'), 'data' => []];
        }

        $packageUrl = trim((string) ($item['package_url'] ?? ''));
        $expectedHash = strtolower(trim((string) ($item['package_hash'] ?? '')));
        if ($packageUrl === '' || !$this->isValidPackageHash($expectedHash)) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_hash_required'), 'data' => []];
        }
        if (!$this->validateRemoteUrl($packageUrl)) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_invalid_url'), 'data' => []];
        }

        $workDir = RUNTIME_PATH . 'addon_market' . DS;
        if (!is_dir($workDir)) {
            @mkdir($workDir, 0755, true);
        }

        $bin = $this->fetchRemoteSecure($packageUrl, 120);
        if ($bin === false || $bin === '') {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_download_fail'), 'data' => []];
        }
        if (strlen($bin) > self::MAX_PACKAGE_BYTES) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_package_too_large'), 'data' => []];
        }

        $actual = hash('sha256', $bin);
        $expected = preg_replace('/^sha256:/i', '', $expectedHash);
        if (!hash_equals(strtolower($expected), $actual)) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_hash_mismatch'), 'data' => []];
        }

        $zipPath = $workDir . $name . '_' . time() . '.zip';
        if (@file_put_contents($zipPath, $bin) === false) {
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_save_fail'), 'data' => []];
        }

        $stageDir = $workDir . 'stage_' . $name . '_' . time();
        if (is_dir($stageDir)) {
            AddonSecureInstaller::purgeDir($stageDir);
        }

        $extracted = AddonSecureInstaller::extractZipSafe($zipPath, $stageDir);
        @unlink($zipPath);
        if (empty($extracted['ok'])) {
            AddonSecureInstaller::purgeDir($stageDir);
            return ['code' => 0, 'msg' => $extracted['msg'], 'data' => []];
        }

        // zip 可能多包一层 name/
        $sourceRoot = $this->resolveAddonRoot($stageDir, $name);
        if ($sourceRoot === '') {
            AddonSecureInstaller::purgeDir($stageDir);
            return ['code' => 0, 'msg' => lang('admin/addon/lack_config_err'), 'data' => []];
        }

        $infoFile = $sourceRoot . DS . 'info.ini';
        if (!is_file($infoFile)) {
            AddonSecureInstaller::purgeDir($stageDir);
            return ['code' => 0, 'msg' => lang('admin/addon/lack_config_err'), 'data' => []];
        }
        $ini = Config::parse($infoFile, '', 'addon-cloud-stage');
        $iniName = isset($ini['name']) ? strtolower((string) $ini['name']) : '';
        if ($iniName !== $name) {
            AddonSecureInstaller::purgeDir($stageDir);
            return ['code' => 0, 'msg' => lang('admin/addon/cloud_name_mismatch'), 'data' => []];
        }

        $mainClassFile = $sourceRoot . DS . ucfirst($name) . '.php';
        if (!is_file($mainClassFile)) {
            AddonSecureInstaller::purgeDir($stageDir);
            return ['code' => 0, 'msg' => lang('admin/addon/lack_class_err'), 'data' => []];
        }

        $target = rtrim(ADDON_PATH, '/\\') . DS . $name;
        $bak = false;
        $isUpgrade = is_dir($target);
        if ($isUpgrade) {
            $bak = AddonSecureInstaller::backupAddon($name);
            // [P0] 备份失败则禁止 purge，避免装包半截不可恢复
            if ($bak === false) {
                return ['code' => 0, 'msg' => lang('admin/addon/cloud_backup_fail'), 'data' => []];
            }
            AddonSecureInstaller::purgeDir($target);
        }

        if (!@rename($sourceRoot, $target)) {
            $this->copyDirectory($sourceRoot, $target);
            AddonSecureInstaller::purgeDir($stageDir);
            if (!is_dir($target) || !is_file($target . DS . 'info.ini')) {
                if (is_dir($target)) {
                    AddonSecureInstaller::purgeDir($target);
                }
                if ($bak) {
                    AddonSecureInstaller::restoreAddon($name, $bak);
                }
                return ['code' => 0, 'msg' => lang('admin/addon/extract_dir_fail'), 'data' => []];
            }
        } else {
            if (is_dir($stageDir) && realpath($sourceRoot) !== realpath($target)) {
                AddonSecureInstaller::purgeDir($stageDir);
            }
        }

        try {
            if (class_exists('\\think\\addons\\Service')) {
                Service::check($name);
            }
            $addonInfo = get_addon_info($name);
            if (is_array($addonInfo) && !empty($addonInfo['state'])) {
                $addonInfo['state'] = 0;
                set_addon_info($name, $addonInfo);
            }

            $class = get_addon_class($name);
            if ($class && class_exists($class)) {
                $addon = new $class();
                if (!$isUpgrade && method_exists($addon, 'install')) {
                    $addon->install();
                }
            }
            if (class_exists('\\think\\addons\\Service')) {
                Service::importsql($name);
                Service::refresh();
            }
            Cache::rm('hooks');
            Cache::rm('addons');

            $addonInfo = get_addon_info($name);
            if (is_array($addonInfo)) {
                $addonInfo['config'] = get_addon_config($name) ? 1 : 0;
            } else {
                $addonInfo = ['name' => $name];
            }

            return [
                'code' => 1,
                'msg' => $isUpgrade ? lang('admin/addon/cloud_upgrade_ok') : lang('admin/addon/cloud_install_ok'),
                'data' => $addonInfo,
            ];
        } catch (\Exception $e) {
            if (is_dir($target)) {
                AddonSecureInstaller::purgeDir($target);
            }
            if ($bak) {
                AddonSecureInstaller::restoreAddon($name, $bak);
            }
            return ['code' => 0, 'msg' => lang('admin/addon/install_rollback'), 'data' => []];
        }
    }

    /**
     * 对比本地与云端版本
     * @param string $name
     * @param string $cloudVersion
     * @return string none|same|update|newer_local
     */
    public function compareLocal($name, $cloudVersion = '')
    {
        $name = strtolower(trim((string) $name));
        if (!AddonSecureInstaller::isValidName($name) || !is_dir(ADDON_PATH . $name)) {
            return 'none';
        }
        $info = get_addon_info($name);
        $local = isset($info['version']) ? (string) $info['version'] : '';
        if ($local === '' || $cloudVersion === '') {
            return $local === '' ? 'none' : 'same';
        }
        $cmp = version_compare($cloudVersion, $local);
        if ($cmp > 0) {
            return 'update';
        }
        if ($cmp < 0) {
            return 'newer_local';
        }
        return 'same';
    }

    /**
     * 丰富云目录项：本地状态、兼容、版本关系
     * @param array $items
     * @return array
     */
    public function enrichItems(array $items)
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = isset($item['name']) ? $item['name'] : '';
            $ver = isset($item['version']) ? $item['version'] : '';
            $compat = $this->checkCmsCompat($item);
            $item['compat_ok'] = $compat['ok'] ? 1 : 0;
            $item['compat_msg'] = $compat['msg'];
            $item['local_cmp'] = $this->compareLocal($name, $ver);
            $item['install'] = is_dir(ADDON_PATH . $name) ? '1' : '0';
            if ($item['install'] === '1') {
                $info = get_addon_info($name);
                $item['state'] = isset($info['state']) ? (string) $info['state'] : '0';
                $item['local_version'] = isset($info['version']) ? $info['version'] : '';
            } else {
                $item['state'] = '0';
                $item['local_version'] = '';
            }
            $out[] = $item;
        }
        return $out;
    }

    /**
     * 最近审计条数
     * @param int $limit
     * @return array
     */
    public function recentAudit($limit = 5)
    {
        $file = APP_PATH . 'extra' . DS . 'addon_market_audit.php';
        if (!is_file($file)) {
            return [];
        }
        $data = include $file;
        if (!is_array($data)) {
            return [];
        }
        $limit = max(1, (int) $limit);
        return array_slice(array_reverse($data), 0, $limit);
    }

    /**
     * @param array $item
     * @return array{ok:bool,msg:string}
     */
    public function checkCmsCompat(array $item)
    {
        $compat = isset($item['cms_compat']) && is_array($item['cms_compat']) ? $item['cms_compat'] : [];
        $min = isset($compat['min']) ? trim((string) $compat['min']) : '';
        $max = isset($compat['max']) ? trim((string) $compat['max']) : '';
        $code = (string) config('version.code');
        if ($code === '') {
            $ver = @include APP_PATH . 'extra' . DS . 'version.php';
            if (is_array($ver) && isset($ver['code'])) {
                $code = (string) $ver['code'];
            }
        }
        if ($min !== '' && version_compare($code, $min, '<')) {
            return ['ok' => false, 'msg' => lang('admin/addon/cloud_compat_low', [$min, $code])];
        }
        if ($max !== '' && version_compare($code, $max, '>')) {
            return ['ok' => false, 'msg' => lang('admin/addon/cloud_compat_high', [$max, $code])];
        }
        return ['ok' => true, 'msg' => 'ok'];
    }

    protected function filterApprovedItems(array $items)
    {
        $out = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            $status = isset($item['status']) ? (string) $item['status'] : 'approved';
            if ($status !== 'approved') {
                continue;
            }
            $out[] = $item;
        }
        return $out;
    }

    /**
     * 读取本地 mock 目录骨架（addon_cloud.mock=1）
     * @return array
     */
    protected function loadMockItems()
    {
        $file = APP_PATH . 'data' . DS . 'addon_market_cloud' . DS . 'catalog.mock.json';
        if (!is_file($file)) {
            return [];
        }
        $raw = @file_get_contents($file);
        $data = json_decode((string) $raw, true);
        if (!is_array($data) || empty($data['items']) || !is_array($data['items'])) {
            return [];
        }
        $out = [];
        foreach ($data['items'] as $row) {
            if (!is_array($row) || empty($row['id']) || empty($row['name'])) {
                continue;
            }
            if (!AddonSecureInstaller::isValidName($row['name'])) {
                continue;
            }
            if ($this->sanitizeId($row['id']) === '') {
                continue;
            }
            $out[] = [
                'id' => (string) $row['id'],
                'name' => strtolower((string) $row['name']),
                'title' => (string) (isset($row['title']) ? $row['title'] : $row['name']),
                'version' => (string) (isset($row['version']) ? $row['version'] : '0.0.0'),
                'intro' => (string) (isset($row['intro']) ? $row['intro'] : ''),
                'author' => (string) (isset($row['author']) ? $row['author'] : ''),
                'image' => (string) (isset($row['image']) ? $row['image'] : ''),
                'price' => (string) (isset($row['price']) ? $row['price'] : '0.00'),
                'status' => isset($row['status']) ? (string) $row['status'] : 'approved',
                'cms_compat' => isset($row['cms_compat']) && is_array($row['cms_compat']) ? $row['cms_compat'] : ['min' => '', 'max' => ''],
                // 故意不带真实 package，防止误装
                'package_url' => '',
                'package_hash' => '',
            ];
        }
        return $out;
    }

    protected function parseVerifiedCatalog($raw)
    {
        $payload = json_decode($raw, true);
        if (!is_array($payload) || empty($payload['items']) || !is_array($payload['items'])) {
            return null;
        }
        if (!$this->verifyCatalogSignature($payload)) {
            return null;
        }
        return $payload['items'];
    }

    protected function verifyCatalogSignature(array $payload)
    {
        $sig = isset($payload['signature']) ? (string) $payload['signature'] : '';
        if ($sig === '') {
            return false;
        }
        $alg = isset($payload['sig_alg']) ? strtoupper((string) $payload['sig_alg']) : 'RS256';
        if ($alg !== 'RS256') {
            return false;
        }
        $pem = $this->getCatalogPublicKeyPem();
        if ($pem === '') {
            return false;
        }
        $items = $payload['items'];
        $signPayload = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $sigBin = base64_decode($sig, true);
        if ($sigBin === false || $signPayload === false) {
            return false;
        }
        $pub = @openssl_pkey_get_public($pem);
        if ($pub === false) {
            return false;
        }
        $ok = openssl_verify($signPayload, $sigBin, $pub, OPENSSL_ALGO_SHA256) === 1;
        if (PHP_VERSION_ID < 80000 && is_resource($pub)) {
            openssl_free_key($pub);
        }
        return $ok;
    }

    protected function getCatalogPublicKeyPem()
    {
        $pemFile = APP_PATH . 'data' . DS . 'addon_market_cloud' . DS . 'catalog_public.pem';
        if (is_readable($pemFile)) {
            $pem = trim((string) file_get_contents($pemFile));
            if ($pem !== '' && strpos($pem, '-----BEGIN') !== false) {
                return $pem;
            }
        }
        return trim((string) self::CATALOG_PUBLIC_KEY_PEM);
    }

    protected function validateCatalogFormat($items)
    {
        if (!is_array($items) || empty($items)) {
            return false;
        }
        foreach ($items as $item) {
            if (!is_array($item)) {
                return false;
            }
            foreach (['id', 'name', 'title', 'version', 'package_url', 'package_hash'] as $field) {
                if (empty($item[$field])) {
                    return false;
                }
            }
            if (!AddonSecureInstaller::isValidName($item['name'])) {
                return false;
            }
            if ($this->sanitizeId($item['id']) === '') {
                return false;
            }
            if (!preg_match('#^https?://#i', $item['package_url'])) {
                return false;
            }
            if (!$this->isValidPackageHash($item['package_hash'])) {
                return false;
            }
        }
        return true;
    }

    protected function isValidPackageHash($hash)
    {
        $hash = strtolower(trim((string) $hash));
        return (bool) preg_match('/^sha256:[a-f0-9]{64}$/', $hash);
    }

    protected function fetchRemoteSecure($url, $timeout = 30)
    {
        if (!$this->validateRemoteUrl($url)) {
            return false;
        }
        if (!function_exists('curl_init')) {
            return false;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, max(5, (int) $timeout));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MacCMS-AddonMarket/1.0');
        if (defined('CURLOPT_MAXFILESIZE')) {
            curl_setopt($ch, CURLOPT_MAXFILESIZE, self::MAX_PACKAGE_BYTES);
        }

        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $primaryIp = (string) curl_getinfo($ch, CURLINFO_PRIMARY_IP);
        curl_close($ch);

        if ($errno !== 0 || $body === false) {
            return false;
        }
        if ($httpCode < 200 || $httpCode >= 300) {
            return false;
        }
        if ($effectiveUrl !== '' && $effectiveUrl !== $url) {
            if (!$this->validateRemoteUrl($effectiveUrl)) {
                return false;
            }
        }
        // 连接后复核实际对端 IP（空则失败关闭，缓解 DNS rebinding / 双栈漏检）
        if ($primaryIp === '' || !$this->ipIsPublicInternet($primaryIp)) {
            return false;
        }
        if (is_string($body) && strlen($body) > self::MAX_PACKAGE_BYTES) {
            return false;
        }
        return $body;
    }

    /**
     * 安装用目录项：优先强制刷新；失败则对缓存 raw 重新 RS256 验签。
     * @param string $id
     * @return array|null
     */
    protected function resolveInstallItem($id)
    {
        $catalog = $this->fetchCatalog(true);
        $item = $this->findItemById(isset($catalog['items']) ? $catalog['items'] : [], $id);
        if ($item !== null) {
            return $item;
        }

        $raw = Cache::get(self::CACHE_CATALOG_RAW);
        if (!is_string($raw) || $raw === '') {
            return null;
        }
        $items = $this->parseVerifiedCatalog($raw);
        if ($items === null || !$this->validateCatalogFormat($items)) {
            return null;
        }
        return $this->findItemById($this->filterApprovedItems($items), $id);
    }

    /**
     * @param array $items
     * @param string $id
     * @return array|null
     */
    protected function findItemById(array $items, $id)
    {
        foreach ($items as $row) {
            if (!is_array($row) || !isset($row['id'])) {
                continue;
            }
            if ($this->sanitizeId($row['id']) === $id) {
                return $row;
            }
        }
        return null;
    }

    protected function fallbackCatalog($cacheKey)
    {
        $old = Cache::get(self::CACHE_CATALOG_BACKUP);
        if (!empty($old) && is_array($old)) {
            $ttl = max(60, (int) ($this->config['cache_ttl'] ?? 10800));
            Cache::set($cacheKey, $old, $ttl);
            return $old;
        }
        return [];
    }

    protected function validateRemoteUrl($url)
    {
        $parts = parse_url($url);
        if (empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }
        if (empty($parts['host'])) {
            return false;
        }

        $host = strtolower($parts['host']);
        if ($host === 'localhost' || $host === '0.0.0.0') {
            return false;
        }

        // 字面 IP：直接判公网
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return $this->ipIsPublicInternet($host);
        }

        $ips = $this->resolveHostIps($host);
        if ($ips === []) {
            return false;
        }
        foreach ($ips as $ip) {
            if (!$this->ipIsPublicInternet($ip)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param string $host
     * @return string[]
     */
    protected function resolveHostIps($host)
    {
        $ips = [];
        if (function_exists('dns_get_record')) {
            $a = @dns_get_record($host, DNS_A);
            if (is_array($a)) {
                foreach ($a as $rec) {
                    if (!empty($rec['ip'])) {
                        $ips[] = (string) $rec['ip'];
                    }
                }
            }
            $aaaa = @dns_get_record($host, DNS_AAAA);
            if (is_array($aaaa)) {
                foreach ($aaaa as $rec) {
                    if (!empty($rec['ipv6'])) {
                        $ips[] = (string) $rec['ipv6'];
                    }
                }
            }
        }
        if ($ips === []) {
            $v4 = @gethostbynamel($host);
            if (is_array($v4)) {
                foreach ($v4 as $ip) {
                    $ips[] = (string) $ip;
                }
            }
        }
        return array_values(array_unique($ips));
    }

    /**
     * @param string $ip
     * @return bool
     */
    protected function ipIsPublicInternet($ip)
    {
        $ip = $this->unwrapMappedIpv4($ip);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        if (strpos($ip, '169.254.') === 0) {
            return false;
        }
        // IPv6 link-local / ULA 粗略拦截
        $lower = strtolower($ip);
        if (strpos($lower, 'fe80:') === 0 || strpos($lower, 'fc') === 0 || strpos($lower, 'fd') === 0) {
            return false;
        }
        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
    }

    /**
     * 展开 IPv4-mapped IPv6（::ffff:127.0.0.1 / ::ffff:7f00:1）
     * @param string $ip
     * @return string
     */
    protected function unwrapMappedIpv4($ip)
    {
        $ip = trim((string)$ip);
        $lower = strtolower($ip);
        if (preg_match('/^::ffff:(\d{1,3}(?:\.\d{1,3}){3})$/', $lower, $m)) {
            return $m[1];
        }
        if (preg_match('/^::ffff:([0-9a-f]{1,4}):([0-9a-f]{1,4})$/', $lower, $m)) {
            $hi = hexdec($m[1]);
            $lo = hexdec($m[2]);
            return (($hi >> 8) & 0xff) . '.' . ($hi & 0xff) . '.' . (($lo >> 8) & 0xff) . '.' . ($lo & 0xff);
        }
        return $ip;
    }

    protected function resolveAddonRoot($extractDir, $name)
    {
        $extractDir = rtrim($extractDir, DS . '/\\');
        if (is_file($extractDir . DS . 'info.ini')) {
            return $extractDir;
        }
        $nested = $extractDir . DS . $name;
        if (is_file($nested . DS . 'info.ini')) {
            return $nested;
        }
        $children = glob($extractDir . DS . '*', GLOB_ONLYDIR) ?: [];
        if (count($children) === 1 && is_file($children[0] . DS . 'info.ini')) {
            return $children[0];
        }
        return '';
    }

    protected function copyDirectory($src, $dst)
    {
        if (!is_dir($dst)) {
            @mkdir($dst, 0755, true);
        }
        $items = @scandir($src);
        if (!is_array($items)) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $s = $src . DS . $item;
            $d = $dst . DS . $item;
            if (is_dir($s)) {
                $this->copyDirectory($s, $d);
            } else {
                @copy($s, $d);
            }
        }
    }

    /**
     * 插件安装互斥锁（供云安装 / 本地安装复用）
     * @param string $name
     * @return resource|false
     */
    public function acquireInstallLock($name)
    {
        if (!AddonSecureInstaller::isValidName($name)) {
            return false;
        }
        $dir = RUNTIME_PATH . 'addon_market' . DS . 'locks';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $path = $dir . DS . $name . '.lock';
        $fp = @fopen($path, 'c+');
        if ($fp === false) {
            return false;
        }
        if (!flock($fp, LOCK_EX | LOCK_NB)) {
            fclose($fp);
            return false;
        }
        return $fp;
    }

    /**
     * @param resource|false $fp
     */
    public function releaseInstallLock($fp)
    {
        if (!is_resource($fp)) {
            return;
        }
        flock($fp, LOCK_UN);
        fclose($fp);
    }

    /**
     * admin_id 与 IP 独立分桶，任一超限即拒绝（cms-review）
     * @param int $adminId
     * @param string $adminIp
     * @return array{ok:bool,msg:string}
     */
    public function checkRateLimit($adminId, $adminIp = '')
    {
        $limit = max(1, (int) ($this->config['rate_limit'] ?? 10));
        $adminN = (int) Cache::get($this->rateLimitKeyAdmin($adminId));
        $ipN = (int) Cache::get($this->rateLimitKeyIp($adminIp));
        if ($adminN >= $limit || $ipN >= $limit) {
            return ['ok' => false, 'msg' => lang('admin/addon/cloud_rate_limit', [$limit])];
        }
        return ['ok' => true, 'msg' => 'ok'];
    }

    public function hitRateLimit($adminId, $adminIp = '')
    {
        $adminKey = $this->rateLimitKeyAdmin($adminId);
        $ipKey = $this->rateLimitKeyIp($adminIp);
        Cache::set($adminKey, (int) Cache::get($adminKey) + 1, 3600);
        Cache::set($ipKey, (int) Cache::get($ipKey) + 1, 3600);
    }

    /**
     * @param int $adminId
     * @return string
     */
    protected function rateLimitKeyAdmin($adminId)
    {
        return 'addon_market_rl_admin_' . (int)$adminId;
    }

    /**
     * @param string $adminIp
     * @return string
     */
    protected function rateLimitKeyIp($adminIp)
    {
        return 'addon_market_rl_ip_' . substr(md5((string)$adminIp), 0, 16);
    }

    protected function recordInstall(array $item)
    {
        $file = APP_PATH . 'extra' . DS . 'addon_market_installed.php';
        $data = [];
        if (is_file($file)) {
            $loaded = include $file;
            if (is_array($loaded)) {
                $data = $loaded;
            }
        }
        $name = isset($item['name']) ? $item['name'] : '';
        if ($name === '') {
            return;
        }
        $data[$name] = [
            'id' => isset($item['id']) ? $item['id'] : '',
            'name' => $name,
            'title' => isset($item['title']) ? $item['title'] : '',
            'version' => isset($item['version']) ? $item['version'] : '',
            'installed_at' => time(),
        ];
        mac_arr2file($file, $data);
    }

    protected function appendAudit($adminId, $addon, $id, $version, $result, $ip, $msg)
    {
        $file = APP_PATH . 'extra' . DS . 'addon_market_audit.php';
        $data = [];
        if (is_file($file)) {
            $loaded = include $file;
            if (is_array($loaded)) {
                $data = $loaded;
            }
        }
        $data[] = [
            'time' => time(),
            'admin_id' => (int) $adminId,
            'addon' => (string) $addon,
            'id' => (string) $id,
            'version' => (string) $version,
            'result' => (string) $result,
            'ip' => (string) $ip,
            'msg' => function_exists('mb_substr') ? mb_substr((string) $msg, 0, 200) : substr((string) $msg, 0, 200),
        ];
        $max = max(20, (int) ($this->config['audit_max'] ?? 200));
        if (count($data) > $max) {
            $data = array_slice($data, -$max);
        }
        mac_arr2file($file, $data);
    }

    protected function sanitizeId($id)
    {
        $id = strtolower(trim((string) $id));
        return preg_match('/^[a-z0-9][a-z0-9._-]{0,63}$/', $id) ? $id : '';
    }
}
