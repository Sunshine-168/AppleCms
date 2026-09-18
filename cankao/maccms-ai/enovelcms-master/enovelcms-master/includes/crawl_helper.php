<?php
/**
 * 采集辅助函数
 */

if (!defined('ROOT_PATH')) {
    require_once __DIR__ . '/config.php';
}

function downloadImage($url, $novelId, &$errorMsg = '', $config = []) {
    echo "<div style='margin:10px 0; padding:8px; background:#f0f9ff; border-left:4px solid #0284c7;'>";
    echo "📷 开始下载封面：<a href='".htmlspecialchars($url)."' target='_blank'>".htmlspecialchars($url)."</a><br>";
    
    if (empty($url)) {
        $errorMsg = '封面URL为空';
        echo "❌ 错误：{$errorMsg}</div>";
        return false;
    }
    if (strpos($url, '/data/covers/') !== false || strpos($url, 'data:image') === 0) {
        echo "✅ 封面已是本地路径，直接使用：{$url}</div>";
        return $url;
    }
    if (!empty($config['skip_cover_pattern']) && strpos($url, $config['skip_cover_pattern']) !== false) {
        $errorMsg = '命中忽略规则：' . $config['skip_cover_pattern'];
        echo "⚠️ 忽略规则：{$errorMsg}</div>";
        return false;
    }
    
    $baseSite = isset($config['site_url']) ? rtrim($config['site_url'], '/') : '';
    
    if (substr($url, 0, 2) === '//') {
        if ($baseSite) {
            $protocol = parse_url($baseSite, PHP_URL_SCHEME);
            if ($protocol) {
                $url = $protocol . ':' . $url;
                echo "📌 协议相对URL补充：{$url}<br>";
            } else {
                $url = 'http:' . $url;
                echo "📌 协议相对URL补充（默认http）：{$url}<br>";
            }
        } else {
            $url = 'http:' . $url;
            echo "📌 协议相对URL补充（默认http）：{$url}<br>";
        }
    }

    elseif ($baseSite && !preg_match('#^https?://#i', $url)) {
        $originalUrl = $url;
        $url = ($url[0] == '/') ? $baseSite . $url : $baseSite . '/' . $url;
        echo "📌 相对路径补全：{$originalUrl} → {$url}<br>";
    }
    
    if (empty($baseSite) && !preg_match('#^https?://#i', $url)) {
        $errorMsg = '无法确定完整URL：请检查规则中的站点URL或封面地址格式';
        echo "❌ 错误：{$errorMsg}</div>";
        return false;
    }
    
    $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
    if (!in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp'])) $ext = 'jpg';
    $filename = uniqid() . '.' . $ext;
    $savePath = ROOT_PATH . 'data/covers/' . $filename;
    if (!is_dir(dirname($savePath))) mkdir(dirname($savePath), 0755, true);
    
    echo "📁 保存文件名：{$filename}<br>";
    echo "💾 完整路径：{$savePath}<br>";
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        CURLOPT_REFERER => $baseSite ?: '',
        CURLOPT_ENCODING => '',
    ]);
    $imgData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "🌐 HTTP状态码：{$httpCode}<br>";
    if ($curlError) echo "⚠️ cURL错误：{$curlError}<br>";
    
    if ($httpCode == 200 && $imgData && !$curlError) {
        if (file_put_contents($savePath, $imgData)) {
            $relativePath = 'data/covers/' . $filename;
            $errorMsg = '';
            echo "✅ 封面下载成功！保存路径：{$relativePath}<br>";
            echo "</div>";
            return $relativePath;
        } else {
            $errorMsg = '写入文件失败：' . $savePath;
            echo "❌ 写入文件失败：{$errorMsg}<br>";
            echo "</div>";
            return false;
        }
    } else {
        $errorMsg = "HTTP {$httpCode}, cURL错误: {$curlError}";
        echo "❌ 下载失败：{$errorMsg}<br>";
        echo "</div>";
        return false;
    }
}

function cleanCrawledContent($content, $config, $isChapter = false) {
    if (empty($content)) return '';
    
    if ($isChapter) {
        $content = preg_replace('/<br\s*\/?>/i', "\r\n", $content);
        $content = preg_replace('/<\/p>/i', "\r\n\r\n", $content);
        $content = strip_tags($content);
        $content = preg_replace('/&nbsp;/i', ' ', $content);
        $content = preg_replace("/\r\n{3,}/", "\r\n\r\n", $content);
        if (!empty($config['filter_blacklist'])) {
            $blacklist = explode("\n", str_replace("\r", "", $config['filter_blacklist']));
            foreach ($blacklist as $word) {
                $word = trim($word);
                if ($word !== '') $content = str_replace($word, '', $content);
            }
        }
        if (!empty($config['random_insert_chars'])) {
            $insertList = explode("\n", str_replace("\r", "", $config['random_insert_chars']));
            $insertList = array_map('trim', $insertList);
            $insertList = array_filter($insertList);
            if (!empty($insertList)) {
                $randomStr = $insertList[array_rand($insertList)];
                $content .= "\r\n\r\n" . $randomStr;
            }
        }
        return trim($content);
    } else {
        $content = preg_replace('/<br\s*\/?>/i', ' ', $content);
        $content = preg_replace('/<\/p>/i', ' ', $content);
        $content = strip_tags($content);
        $content = preg_replace('/&nbsp;/i', ' ', $content);
        $content = str_replace(["\r\n", "\r", "\n", "\t"], ' ', $content);
        $content = str_replace(['\\n', '\\r'], ' ', $content);
        $content = preg_replace('/\s+/', ' ', $content);
        $content = trim($content);
        if (!empty($config['filter_blacklist'])) {
            $blacklist = explode("\n", str_replace("\r", "", $config['filter_blacklist']));
            foreach ($blacklist as $word) {
                $word = trim($word);
                if ($word !== '') $content = str_replace($word, '', $content);
            }
        }
        return $content;
    }
}

function fetchWithRetry($crawler, $url, $maxRetries = 3, $baseDelay = 10) {
    $attempt = 0;
    $lastError = '';
    while ($attempt <= $maxRetries) {
        try {
            $crawler->fetch($url, 30, 0);
            if ($crawler->getHttpCode() == 200) return true;
            else {
                $lastError = "HTTP: " . $crawler->getHttpCode();
                if ($crawler->getHttpCode() >= 400 && $crawler->getHttpCode() < 500) throw new Exception($lastError);
            }
        } catch (Exception $e) {
            $lastError = $e->getMessage();
        }
        $attempt++;
        if ($attempt <= $maxRetries) {
            $delay = $baseDelay * $attempt;
            echo "<div class='progress-message'>⚠️ 网络异常，{$delay}秒后重试（{$attempt}/{$maxRetries}）... 错误：{$lastError}</div>";
            sleep($delay);
        }
    }
    throw new Exception("网络请求失败，已重试{$maxRetries}次：{$lastError}");
}

function removeSpacesFromUrl($url) {
    return str_replace(' ', '', $url);
}

function redirectDelay($url, $seconds = 3, $msg = '') {
    if ($msg) echo '<div class="alert alert-info">⏳ ' . htmlspecialchars($msg) . "，{$seconds}秒后自动跳转...</div>";
    echo '<meta http-equiv="refresh" content="' . $seconds . ';url=' . htmlspecialchars($url) . '">';
    echo '<p><a href="' . htmlspecialchars($url) . '" class="btn btn-sm btn-outline">如果未跳转，请点击这里</a></p>';
    flush();
    exit;
}
function checkMbstring() {
    if (!extension_loaded('mbstring') || !function_exists('mb_strlen')) {
        die("PHP 必须开启 mbstring 扩展才能正常运行！");
    }
}