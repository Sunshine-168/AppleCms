<?php
namespace app\common\util;

/**
 * 插件本地安装安全封装：名称白名单、Zip Slip、危险扩展名、可选 RS256 包签名、卸载前备份回滚。
 * PHP 7.0 兼容。
 */
class AddonSecureInstaller
{
    /** 禁止写入的顶层路径前缀（相对 ZIP 条目；enable 会把 application/static  overlay 到站点根） */
    protected static $blockedPrefixes = [
        'application/',
        'static/',
        'thinkphp/',
        'extend/',
        'vendor/',
        'template/',
        'public/',
        'runtime/',
        'addons/', // zip 内不应再套一层 addons/
    ];

    /** 包内禁止出现的扩展（即使落在插件目录）；不含 ini（info.ini 必需） */
    protected static $blockedExt = [
        'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'phps',
        'shtml', 'asp', 'aspx', 'jsp',
    ];

    /** 禁止的确切文件名（大小写不敏感） */
    protected static $blockedBasenames = [
        '.htaccess',
        '.htpasswd',
        '.user.ini',
        'web.config',
    ];

    /** zip 条目数上限（防 zip bomb） */
    const MAX_ZIP_ENTRIES = 5000;
    /** zip 未压缩总字节上限 */
    const MAX_ZIP_UNCOMPRESSED = 52428800;
    /** 单文件未压缩上限（与云端包上限同量级） */
    const MAX_ZIP_ENTRY_BYTES = 10240000;

    /**
     * 插件标识合法性
     * @param mixed $name
     * @return bool
     */
    public static function isValidName($name)
    {
        if (!is_string($name) || $name === '') {
            return false;
        }
        return (bool)preg_match('/^[a-z][a-z0-9_]{0,31}$/', $name);
    }

    /**
     * 是否要求本地包必须带签名（读配置，缺省 false，不改写 maccms.php）
     * @return bool
     */
    public static function requireSignature()
    {
        $cfg = isset($GLOBALS['config']['addon']) && is_array($GLOBALS['config']['addon'])
            ? $GLOBALS['config']['addon'] : [];
        return !empty($cfg['require_local_signature']);
    }

    /**
     * 安全解压 zip 到目标目录（目录须为空或不存在）
     * @param string $zipFile 绝对路径
     * @param string $destDir 绝对路径，将写入此处
     * @return array [ok=>bool, msg=>string, files=>string[]]
     */
    public static function extractZipSafe($zipFile, $destDir)
    {
        if (!class_exists('ZipArchive')) {
            return ['ok' => false, 'msg' => lang('admin/addon/zip_unavailable'), 'files' => []];
        }
        if (!is_file($zipFile)) {
            return ['ok' => false, 'msg' => lang('admin/addon/zip_missing'), 'files' => []];
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFile) !== true) {
            return ['ok' => false, 'msg' => lang('admin/addon/zip_open_fail'), 'files' => []];
        }

        if (!is_dir($destDir)) {
            if (!@mkdir($destDir, 0755, true) && !is_dir($destDir)) {
                $zip->close();
                return ['ok' => false, 'msg' => lang('admin/addon/extract_dir_fail'), 'files' => []];
            }
        }

        $destReal = realpath($destDir);
        if ($destReal === false) {
            $zip->close();
            return ['ok' => false, 'msg' => lang('admin/addon/extract_dir_fail'), 'files' => []];
        }
        $destReal = str_replace('\\', '/', $destReal);
        $files = [];

        if ($zip->numFiles > self::MAX_ZIP_ENTRIES) {
            $zip->close();
            return ['ok' => false, 'msg' => lang('admin/addon/zip_too_many'), 'files' => []];
        }

        $totalUncompressed = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                $zip->close();
                return ['ok' => false, 'msg' => lang('admin/addon/zip_entry_invalid'), 'files' => []];
            }
            $rawName = str_replace('\\', '/', isset($stat['name']) ? $stat['name'] : '');
            $isDir = ($rawName !== '' && substr($rawName, -1) === '/');
            $norm = self::normalizeZipEntryRelPath($rawName);
            if ($norm === false) {
                $zip->close();
                return ['ok' => false, 'msg' => lang('admin/addon/zip_slip'), 'files' => []];
            }
            if ($norm === '' && $isDir) {
                continue;
            }
            // 目录条目也做前缀拦截，避免留下可被 enable 拷贝的空壳树
            if ($isDir) {
                $dirCheck = self::validateZipEntryName($norm . '/');
                if ($dirCheck !== true) {
                    $zip->close();
                    return ['ok' => false, 'msg' => $dirCheck, 'files' => []];
                }
                continue;
            }
            $check = self::validateZipEntryName($norm);
            if ($check !== true) {
                $zip->close();
                return ['ok' => false, 'msg' => $check, 'files' => []];
            }
            $size = isset($stat['size']) ? (int)$stat['size'] : 0;
            if ($size < 0 || $size > self::MAX_ZIP_ENTRY_BYTES) {
                $zip->close();
                return ['ok' => false, 'msg' => lang('admin/addon/zip_too_large'), 'files' => []];
            }
            $totalUncompressed += $size;
            if ($totalUncompressed > self::MAX_ZIP_UNCOMPRESSED) {
                $zip->close();
                return ['ok' => false, 'msg' => lang('admin/addon/zip_too_large'), 'files' => []];
            }
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if ($stat === false) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/zip_entry_invalid'), 'files' => []];
            }
            $rawName = str_replace('\\', '/', isset($stat['name']) ? $stat['name'] : '');
            if ($rawName === '' || substr($rawName, -1) === '/') {
                continue;
            }
            $name = self::normalizeZipEntryRelPath($rawName);
            if ($name === false || $name === '') {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/zip_slip'), 'files' => []];
            }
            $check = self::validateZipEntryName($name);
            if ($check !== true) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => $check, 'files' => []];
            }

            $target = $destReal . '/' . $name;
            $targetDir = dirname($target);
            if (!is_dir($targetDir) && !@mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/extract_dir_fail'), 'files' => []];
            }

            // Zip Slip：落盘路径必须仍在 destReal 下（带边界斜杠，防前缀误判）
            $parentReal = realpath($targetDir);
            $destNorm = rtrim(str_replace('\\', '/', $destReal), '/') . '/';
            $parentNorm = $parentReal === false ? '' : rtrim(str_replace('\\', '/', $parentReal), '/') . '/';
            if ($parentReal === false || strpos($parentNorm, $destNorm) !== 0) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/zip_slip'), 'files' => []];
            }

            $content = $zip->getFromIndex($i);
            if ($content === false) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/zip_entry_invalid'), 'files' => []];
            }
            if (strlen($content) > self::MAX_ZIP_ENTRY_BYTES) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/zip_too_large'), 'files' => []];
            }
            if (@file_put_contents($target, $content) === false) {
                $zip->close();
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/extract_write_fail'), 'files' => []];
            }
            $files[] = $name;
        }
        $zip->close();

        // 解压后再校验真实文件未逃出目录
        $destBoundary = rtrim($destReal, '/') . '/';
        foreach ($files as $rel) {
            $full = realpath($destReal . '/' . $rel);
            $fullNorm = $full === false ? '' : str_replace('\\', '/', $full);
            if ($full === false || strpos($fullNorm, $destBoundary) !== 0) {
                self::purgeDir($destReal);
                return ['ok' => false, 'msg' => lang('admin/addon/zip_slip'), 'files' => []];
            }
        }

        return ['ok' => true, 'msg' => 'ok', 'files' => $files];
    }

    /**
     * 规范化 zip 相对路径：去掉空段与 '.'，拒绝 '..' / 绝对路径。
     * 防止 ./application/... 绕过前缀黑名单后被 enable 拷入站点根。
     * @param string $name
     * @return string|false 目录条目可带尾 /
     */
    public static function normalizeZipEntryRelPath($name)
    {
        $name = str_replace('\\', '/', (string)$name);
        if ($name === '' || strpos($name, "\0") !== false) {
            return false;
        }
        if ($name[0] === '/' || preg_match('#^[a-zA-Z]:/#', $name)) {
            return false;
        }
        $isDir = substr($name, -1) === '/';
        $parts = explode('/', $name);
        $out = [];
        foreach ($parts as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }
            if ($p === '..') {
                return false;
            }
            $out[] = $p;
        }
        if ($out === []) {
            return $isDir ? '' : false;
        }
        $rel = implode('/', $out);
        return $isDir ? ($rel . '/') : $rel;
    }

    /**
     * @param string $name zip 内相对路径（建议已 normalize）
     * @return true|string
     */
    public static function validateZipEntryName($name)
    {
        $norm = self::normalizeZipEntryRelPath($name);
        if ($norm === false) {
            return lang('admin/addon/zip_slip');
        }
        if ($norm === '' || $norm === '/') {
            return lang('admin/addon/zip_entry_invalid');
        }
        $isDir = substr($norm, -1) === '/';
        $path = $isDir ? rtrim($norm, '/') : $norm;
        if ($path === '') {
            return lang('admin/addon/zip_entry_invalid');
        }
        $lower = strtolower($path);
        // 精确顶层目录名（无尾斜杠）与前缀均拦
        foreach (self::$blockedPrefixes as $prefix) {
            $p = rtrim($prefix, '/');
            if ($lower === $p || strpos($lower . '/', $prefix) === 0) {
                return lang('admin/addon/zip_blocked_path', [$prefix]);
            }
        }
        if ($isDir) {
            return true;
        }
        // 双扩展名伪装：file.php.jpg 仍看最后一段；file.jpg.php 禁止
        $base = basename($lower);
        if (in_array($base, self::$blockedBasenames, true)) {
            return lang('admin/addon/zip_blocked_ext', [$base]);
        }
        if (preg_match('/\.(php|phtml|phar)(\.|$)/', $base) && !preg_match('/\.php$/', $base)) {
            // e.g. shell.php.jpg
            if (preg_match('/\.php\./', $base)) {
                return lang('admin/addon/zip_blocked_ext', ['php.*']);
            }
        }
        $ext = pathinfo($base, PATHINFO_EXTENSION);
        if ($ext !== '' && in_array($ext, self::$blockedExt, true)) {
            return lang('admin/addon/zip_blocked_ext', [$ext]);
        }
        return true;
    }

    /**
     * 可选 RS256 验签：存在 package.manifest.json + package.sig 时校验；
     * 若配置 require_local_signature=1 则必须通过。
     * 完整性：manifest 内逐文件 sha256 + 解压目录全量枚举（未列入且非签名材料的文件一律拒绝）。
     * @param string $dir 已解压目录
     * @return array [ok=>bool, msg=>string]
     */
    public static function verifyPackageSignature($dir)
    {
        $baseDir = rtrim($dir, '/\\');
        $manifestFile = $baseDir . DIRECTORY_SEPARATOR . 'package.manifest.json';
        $sigFile = $baseDir . DIRECTORY_SEPARATOR . 'package.sig';
        $hasManifest = is_file($manifestFile);
        $hasSig = is_file($sigFile);
        $require = self::requireSignature();

        if (!$hasManifest && !$hasSig) {
            if ($require) {
                return ['ok' => false, 'msg' => lang('admin/addon/sig_required')];
            }
            return ['ok' => true, 'msg' => 'skip'];
        }
        if (!$hasManifest || !$hasSig) {
            return ['ok' => false, 'msg' => lang('admin/addon/sig_incomplete')];
        }

        $raw = @file_get_contents($manifestFile);
        $manifest = json_decode($raw, true);
        if (!is_array($manifest) || empty($manifest['files']) || !is_array($manifest['files'])) {
            return ['ok' => false, 'msg' => lang('admin/addon/sig_manifest_invalid')];
        }

        $files = $manifest['files'];
        ksort($files);
        $allowed = [];
        foreach ($files as $rel => $hash) {
            $rel = self::normalizeManifestRelPath($rel);
            if ($rel === false) {
                return ['ok' => false, 'msg' => lang('admin/addon/sig_manifest_invalid')];
            }
            if ($rel === 'package.sig' || $rel === 'package.manifest.json') {
                continue;
            }
            $path = $baseDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
            if (!is_file($path)) {
                return ['ok' => false, 'msg' => lang('admin/addon/sig_file_missing', [$rel])];
            }
            $calc = hash_file('sha256', $path);
            if (!is_string($hash) || !hash_equals(strtolower($hash), strtolower($calc))) {
                return ['ok' => false, 'msg' => lang('admin/addon/sig_hash_mismatch', [$rel])];
            }
            $allowed[$rel] = true;
        }

        // 拒绝 manifest 未列出的多余文件（防 MITM 向已签名包追加 evil.php）
        $onDisk = self::listPackageRelFiles($baseDir);
        if ($onDisk === null) {
            return ['ok' => false, 'msg' => lang('admin/addon/sig_verify_fail')];
        }
        foreach ($onDisk as $rel) {
            if ($rel === 'package.sig' || $rel === 'package.manifest.json') {
                continue;
            }
            if (!isset($allowed[$rel])) {
                return ['ok' => false, 'msg' => lang('admin/addon/sig_file_extra', [$rel])];
            }
        }

        $signPayload = json_encode($files, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $sigBin = base64_decode(trim((string)@file_get_contents($sigFile)), true);
        if ($sigBin === false || $signPayload === false) {
            return ['ok' => false, 'msg' => lang('admin/addon/sig_verify_fail')];
        }

        $pem = self::loadPublicKeyPem();
        if ($pem === '') {
            if ($require) {
                return ['ok' => false, 'msg' => lang('admin/addon/sig_pubkey_missing')];
            }
            // 有签名材料但无公钥：拒绝（避免“假签名”被忽略）
            return ['ok' => false, 'msg' => lang('admin/addon/sig_pubkey_missing')];
        }

        $pub = @openssl_pkey_get_public($pem);
        if ($pub === false) {
            return ['ok' => false, 'msg' => lang('admin/addon/sig_pubkey_missing')];
        }
        $ok = openssl_verify($signPayload, $sigBin, $pub, OPENSSL_ALGO_SHA256) === 1;
        if (PHP_VERSION_ID < 80000 && is_resource($pub)) {
            openssl_free_key($pub);
        }
        if (!$ok) {
            return ['ok' => false, 'msg' => lang('admin/addon/sig_verify_fail')];
        }
        return ['ok' => true, 'msg' => 'ok'];
    }

    /**
     * 规范化 manifest 相对路径；非法（空、绝对、含 ..）返回 false。
     * @param mixed $rel
     * @return string|false
     */
    protected static function normalizeManifestRelPath($rel)
    {
        $rel = str_replace('\\', '/', (string)$rel);
        $rel = ltrim($rel, '/');
        if ($rel === '' || strpos($rel, ':') !== false) {
            return false;
        }
        $parts = explode('/', $rel);
        foreach ($parts as $part) {
            if ($part === '' || $part === '.' || $part === '..') {
                return false;
            }
        }
        return implode('/', $parts);
    }

    /**
     * 枚举解压目录下全部普通文件的相对路径（/ 分隔）。
     * @param string $dir
     * @return array|null 失败返回 null
     */
    protected static function listPackageRelFiles($dir)
    {
        $real = realpath($dir);
        if ($real === false || !is_dir($real)) {
            return null;
        }
        $out = [];
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($real, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            $prefixLen = strlen($real) + 1;
            foreach ($iterator as $file) {
                if (!$file->isFile()) {
                    continue;
                }
                $path = $file->getPathname();
                if (strlen($path) <= $prefixLen) {
                    continue;
                }
                $rel = substr($path, $prefixLen);
                $rel = str_replace('\\', '/', $rel);
                $norm = self::normalizeManifestRelPath($rel);
                if ($norm === false) {
                    return null;
                }
                $out[] = $norm;
            }
        } catch (\Exception $e) {
            return null;
        }
        return $out;
    }

    /**
     * @return string PEM
     */
    protected static function loadPublicKeyPem()
    {
        $cfg = isset($GLOBALS['config']['addon']) && is_array($GLOBALS['config']['addon'])
            ? $GLOBALS['config']['addon'] : [];
        if (!empty($cfg['local_public_key']) && is_string($cfg['local_public_key'])) {
            $v = trim($cfg['local_public_key']);
            if (strpos($v, '-----BEGIN') !== false) {
                return $v;
            }
            if (is_file($v)) {
                $c = @file_get_contents($v);
                return is_string($c) ? $c : '';
            }
        }
        $default = APP_PATH . 'data' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . 'local_public.pem';
        if (is_file($default)) {
            $c = @file_get_contents($default);
            return is_string($c) ? $c : '';
        }
        return '';
    }

    /**
     * 备份插件目录为 zip，供卸载失败回滚
     * @param string $name
     * @return string|false 备份文件绝对路径
     */
    public static function backupAddon($name)
    {
        if (!self::isValidName($name) || !class_exists('ZipArchive')) {
            return false;
        }
        $dir = rtrim(ADDON_PATH, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!is_dir($dir)) {
            return false;
        }
        $bakDir = RUNTIME_PATH . 'addons' . DIRECTORY_SEPARATOR . 'backup';
        if (!is_dir($bakDir)) {
            @mkdir($bakDir, 0755, true);
        }
        $bakFile = $bakDir . DIRECTORY_SEPARATOR . $name . '-' . date('YmdHis') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($bakFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $dir = realpath($dir);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $local = substr($path, strlen($dir) + 1);
            $local = str_replace('\\', '/', $local);
            if ($file->isDir()) {
                $zip->addEmptyDir($local);
            } else {
                $zip->addFile($path, $local);
            }
        }
        $zip->close();
        return is_file($bakFile) ? $bakFile : false;
    }

    /**
     * 从备份 zip 恢复插件目录
     * @param string $name
     * @param string $bakFile
     * @return bool
     */
    public static function restoreAddon($name, $bakFile)
    {
        if (!self::isValidName($name) || !is_file($bakFile)) {
            return false;
        }
        $dest = rtrim(ADDON_PATH, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (is_dir($dest)) {
            self::purgeDir($dest);
        }
        $res = self::extractZipSafe($bakFile, $dest);
        return !empty($res['ok']);
    }

    /**
     * 递归删除目录
     * @param string $dir
     */
    public static function purgeDir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = @scandir($dir);
        if (!is_array($items)) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                self::purgeDir($path);
            } else {
                @unlink($path);
            }
        }
        @rmdir($dir);
    }
}
