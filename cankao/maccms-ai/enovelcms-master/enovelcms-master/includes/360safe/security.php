<?php
function x_real_ip(){
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

function cc_defender(){
    // 判断语言
    $acceptLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
    $isZh = stripos($acceptLang, 'zh') === 0;

    $iptoken = md5(x_real_ip().date('Ymd')).md5(time().rand(11111,99999));
    if(!isset($_COOKIE['sec_defend']) || substr($_COOKIE['sec_defend'],0,32)!==substr($iptoken,0,32)){
        $sec_defend_time = $_COOKIE['sec_defend_time'] ?? 0;
        $sec_defend_time++;

        require_once('hieroglyphy.php');
        $x = new hieroglyphy();
        $setCookie = $x->hieroglyphyString($iptoken);

        header('Content-type:text/html;charset=utf-8');

        // 超过10次失败，双语提示
        if($sec_defend_time>=10){
            if($isZh){
                exit('浏览器不支持COOKIE或者非正常访问！');
            }else{
                exit('Your browser does not support COOKIE or abnormal access behavior detected!');
            }
        }

        // 页面标题、文字双语
        $titleZh = '正在加载中';
        $titleEn = 'Verifying Access';
        $pageTitle = $isZh ? $titleZh : $titleEn;

        echo <<<HTML
<html>
<head>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache">
<meta http-equiv="content-type" content="text/html;charset=utf-8">
<title>{$pageTitle}</title>
<script>
function setCookie(name,value){
    var exp = new Date();
    exp.setTime(exp.getTime() + 60*60*1000);
    document.cookie = name + "="+ escape (value).replace(/\+/g, '%2B') + ";expires=" + exp.toGMTString() + ";path=/";
}
function getCookie(name){
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
    if(arr=document.cookie.match(reg))return unescape(arr[2]);
    else return null;
}
var sec_defend_time=getCookie('sec_defend_time')||0;
sec_defend_time++;
setCookie('sec_defend',$setCookie);
setCookie('sec_defend_time',sec_defend_time);
if(sec_defend_time>1)window.location.href="./index.php";
else window.location.reload();
</script>
</head>
<body></body>
</html>
HTML;
        exit;
    }elseif(isset($_COOKIE['sec_defend_time'])){
        setcookie("sec_defend_time", "", time() - 604800, '/');
    }
}

@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Pragma: no-cache");

// ===== 修复点：安全防护开关（默认关闭，避免未定义变量错误） =====
if(isset($is_defend) && $is_defend==true){
    $ajaxHeader = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    if(strtolower($ajaxHeader) != 'xmlhttprequest'){
        include_once __DIR__ . '/txprotect.php';
    }
    cc_defender();
}
?>