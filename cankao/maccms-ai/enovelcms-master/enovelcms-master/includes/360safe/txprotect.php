<?php
error_reporting(0);
// 获取真实访问IP（复用你现有x_real_ip逻辑，统一函数）
function x_real_ip_tx(){
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
        foreach ($matches[0] AS $xip) {
            if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                $ip = $xip;
                break;
            }
        }
    } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (isset($_SERVER['HTTP_X_REAL_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    return $ip;
}

// 判断语言
$acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
$isZh = stripos($acceptLang, 'zh') === 0;

// 识别微信、QQ内置浏览器标识
$ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
$isWechat = strpos($ua, 'micromessenger') !== false;
$isQQBrowser = strpos($ua, 'qqbrowser') !== false || strpos($ua, 'qq/') !== false;

// 非微信QQ直接放行
if(!$isWechat && !$isQQBrowser){
    return;
}

// 双语文案
if($isZh){
    $title = '请在浏览器打开';
    $tipMain = '当前在微信/QQ内置浏览器访问，链接存在拦截风险';
    $tipStep1 = '1. 点击右上角 ··· 更多按钮';
    $tipStep2 = '2. 选择「在浏览器打开」即可正常访问';
    $btnText = '复制链接手动打开';
}else{
    $title = 'Open in External Browser';
    $tipMain = "Links may be blocked inside WeChat/QQ built-in browser";
    $tipStep1 = "1. Tap the ... menu button at top right";
    $tipStep2 = "2. Select 'Open in Browser' to continue";
    $btnText = 'Copy Link';
}

$currentUrl = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="<?php echo $isZh ? 'zh-CN' : 'en'; ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1,user-scalable=no">
<title><?php echo $title; ?></title>
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:system-ui,-apple-system}
body{background:#f7f8fa;padding:40px 20px;text-align:center;color:#333}
.box{max-width:420px;margin:0 auto;background:#fff;border-radius:16px;padding:32px 24px;box-shadow:0 2px 12px rgba(0,0,0,0.06)}
.icon{width:90px;height:90px;margin:0 auto 20px;background:#e8f3ff;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:40px;color:#2576e8}
h2{font-size:20px;margin-bottom:14px;font-weight:600}
p.desc{color:#666;line-height:1.6;margin-bottom:24px;font-size:15px}
.steps{text-align:left;background:#f5f7fa;border-radius:12px;padding:16px;margin-bottom:26px}
.steps p{margin:8px 0;font-size:14px;color:#444}
.copy-btn{display:block;width:100%;padding:14px;background:#2576e8;color:#fff;border:none;border-radius:12px;font-size:16px}
.copy-btn:active{background:#1f68d1}
.url-text{margin-top:16px;font-size:12px;color:#999;word-break:break-all}
</style>
</head>
<body>
<div class="box">
    <div class="icon">🌐</div>
    <h2><?php echo $title; ?></h2>
    <p class="desc"><?php echo $tipMain; ?></p>
    <div class="steps">
        <p><?php echo $tipStep1; ?></p>
        <p><?php echo $tipStep2; ?></p>
    </div>
    <button class="copy-btn" onclick="copyUrl()"><?php echo $btnText; ?></button>
    <p class="url-text" id="linkTxt"><?php echo htmlspecialchars($currentUrl); ?></p>
</div>
<script>
function copyUrl(){
    const text = document.getElementById('linkTxt').innerText;
    navigator.clipboard.writeText(text).then(()=>{
        alert(<?php echo $isZh ? '"链接已复制，请粘贴到手机浏览器打开"' : '"Link copied, paste to your browser"'; ?>);
    }).catch(()=>{
        alert(<?php echo $isZh ? '"复制失败，请手动复制页面链接"' : '"Copy failed, copy link manually"'; ?>);
    })
}
</script>
</body>
</html>
<?php exit; ?>