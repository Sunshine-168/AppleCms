<?php
/**
 * 插件市场云端目录构建（RS256）
 *
 * 用法：
 *   php application/data/addon_market_cloud/build_catalog.php
 *   php application/data/addon_market_cloud/build_catalog.php --source=./catalog.source.json --out=./dist/catalog.json
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

$opts = getopt('', ['source::', 'out::', 'private-key::', 'help']);
if (isset($opts['help'])) {
    echo "Usage: php build_catalog.php [--source=] [--out=] [--private-key=]\n";
    exit(0);
}

$scriptDir = __DIR__;
$sourcePath = isset($opts['source']) ? $opts['source'] : $scriptDir . '/catalog.source.json';
$outPath = isset($opts['out']) ? $opts['out'] : $scriptDir . '/dist/catalog.json';
$privateKeyPath = isset($opts['private-key']) ? $opts['private-key'] : $scriptDir . '/keys/catalog_private.pem';

if (!is_file($sourcePath)) {
    fwrite(STDERR, "Source not found: {$sourcePath}\nCopy catalog.source.example.json to catalog.source.json first.\n");
    exit(1);
}
if (!is_file($privateKeyPath)) {
    fwrite(STDERR, "Private key not found: {$privateKeyPath}\nRun generate_keys.php first.\n");
    exit(1);
}

$privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath));
if ($privateKey === false) {
    fwrite(STDERR, "Invalid private key: {$privateKeyPath}\n");
    exit(1);
}

$source = json_decode(file_get_contents($sourcePath), true);
if (!is_array($source) || empty($source['items']) || !is_array($source['items'])) {
    fwrite(STDERR, "Invalid source format: items required\n");
    exit(1);
}

$baseUrl = rtrim((string) ($source['base_url'] ?? ''), '/');
$publishRoot = dirname(realpath($sourcePath) ?: $sourcePath);

$items = [];
foreach ($source['items'] as $row) {
    if (!is_array($row)) {
        continue;
    }
    foreach (['id', 'name', 'title', 'version'] as $field) {
        if (empty($row[$field])) {
            fwrite(STDERR, "Item missing field [{$field}]\n");
            exit(1);
        }
    }
    $name = strtolower((string) $row['name']);
    if (!preg_match('/^[a-z][a-z0-9_]{0,31}$/', $name)) {
        fwrite(STDERR, "Invalid name: {$name}\n");
        exit(1);
    }

    $packageUrl = trim((string) ($row['package_url'] ?? ''));
    $packageHash = trim((string) ($row['package_hash'] ?? ''));

    if ($packageUrl === '' && !empty($row['package_file'])) {
        $rel = ltrim(str_replace('\\', '/', $row['package_file']), '/');
        $localZip = $publishRoot . '/' . $rel;
        if (!is_file($localZip)) {
            fwrite(STDERR, "Package zip not found: {$localZip}\n");
            exit(1);
        }
        $packageHash = 'sha256:' . hash_file('sha256', $localZip);
        $packageUrl = ($baseUrl !== '' ? $baseUrl . '/' : '') . $rel;
    }

    if ($packageUrl === '' || $packageHash === '' || !preg_match('/^sha256:[a-f0-9]{64}$/i', $packageHash)) {
        fwrite(STDERR, "Item requires package_url and package_hash (sha256:...): {$row['id']}\n");
        exit(1);
    }

    $status = isset($row['status']) ? (string) $row['status'] : 'approved';
    if ($status !== 'approved' && $status !== 'delisted') {
        fwrite(STDERR, "Invalid status for {$row['id']}\n");
        exit(1);
    }

    $compat = isset($row['cms_compat']) && is_array($row['cms_compat']) ? $row['cms_compat'] : [];
    $items[] = [
        'id' => (string) $row['id'],
        'name' => $name,
        'title' => (string) $row['title'],
        'version' => (string) $row['version'],
        'intro' => (string) ($row['intro'] ?? ''),
        'author' => (string) ($row['author'] ?? ''),
        'image' => (string) ($row['image'] ?? ''),
        'price' => (string) ($row['price'] ?? '0.00'),
        'status' => $status,
        'cms_compat' => [
            'min' => (string) ($compat['min'] ?? ''),
            'max' => (string) ($compat['max'] ?? ''),
        ],
        'package_url' => $packageUrl,
        'package_hash' => strtolower($packageHash),
    ];
}

$signPayload = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$signature = '';
if (!openssl_sign($signPayload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
    fwrite(STDERR, "openssl_sign failed\n");
    exit(1);
}

$catalog = [
    'items' => $items,
    'sig_alg' => 'RS256',
    'signature' => base64_encode($signature),
];

$outDir = dirname($outPath);
if (!is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$json = json_encode($catalog, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
if (file_put_contents($outPath, $json) === false) {
    fwrite(STDERR, "Write failed: {$outPath}\n");
    exit(1);
}

echo "Wrote {$outPath} (" . count($items) . " items)\n";
