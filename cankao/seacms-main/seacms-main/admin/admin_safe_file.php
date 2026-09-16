<?php 
header('Content-Type:text/html;charset=utf-8');
require_once(dirname(__FILE__)."/config.php");
CheckPurview();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title> </title>
<link  href="img/style.css" rel="stylesheet" type="text/css" />
<link  href="img/style.css" rel="stylesheet" type="text/css" />
<script src="js/common.js" type="text/javascript"></script>
<script src="js/main.js" type="text/javascript"></script>
</head>
<body>
<script type="text/JavaScript">if(parent.$('admincpnav')) parent.$('admincpnav').innerHTML='后台首页&nbsp;&raquo;&nbsp;工具&nbsp;&raquo;&nbsp;文件校验工具 ';</script>
<div class="r_main">
  <div class="r_content">
    <div class="r_content_1">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tb_style">
<tbody><tr class="thead">
<td colspan="5" class="td_title">文件校验工具</td>
</tr>
</tbody></table>	
</div>
<?php

ob_start();
set_time_limit(0);
include_once('../data/config.cache.inc.php'); 
include_once('../include/common.func.php');
$config_adminpath_name='data/admin_'.cn_substr(md5($cfg_cookie_encode),24).'.php';
$username = "t00ls"; //设置用户名
$password = "t00ls"; //设置密码
$md5 = md5(md5($username).md5($password));
$version = "PHP Web木马扫描器 v1.0";
  
$realpath = realpath('./');
$selfpath = $_SERVER['PHP_SELF'];
$selfpath = substr($selfpath, 0, strrpos($selfpath,'/'));
define('REALPATH', str_replace('//','/',str_replace('\\','/',substr($realpath, 0, strlen($realpath) - strlen($selfpath)))));
define('MYFILE', basename(__FILE__));
define('MYPATH', str_replace('\\', '/', dirname(__FILE__)).'/');
define('MYFULLPATH', str_replace('\\', '/', (__FILE__)));
define('HOST', "http://".$_SERVER['HTTP_HOST']);
?>
<html>
<head>
<title><?php echo $version?></title>
<meta http-equiv="Content-Type" content="text/html; charset=gb2312" />
<style>
body{margin:0px;}
body,td{font: 12px Arial,Tahoma;line-height: 16px;}
a {color: #00f;text-decoration:underline;}
a:hover{color: #f00;text-decoration:none;}
.alt1 td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#f1f1f1;padding:5px 10px 5px 5px;}
.alt2 td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#f9f9f9;padding:5px 10px 5px 5px;}
.focus td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#ffffaa;padding:5px 10px 5px 5px;}
.head td{border-top:1px solid #fff;border-bottom:1px solid #ddd;background:#e9e9e9;font-weight:bold;}
.head td span{font-weight:normal;}
</style>
</head>
<body>
<?php
if(!(isset($_COOKIE['t00ls']) && $_COOKIE['t00ls'] == $md5) && !(isset($_POST['username']) && isset($_POST['password']) && (md5(md5($_POST['username']).md5($_POST['password']))==$md5)))
{
 setcookie("t00ls", $md5, time()+60*60*24*365,"/");
 header( 'refresh: 1; url='.MYFILE.'?action=scan' );
 exit();
}
elseif(isset($_POST['username']) && isset($_POST['password']) && (md5(md5($_POST['username']).md5($_POST['password']))==$md5))
{
 setcookie("t00ls", $md5, time()+60*60*24*365,"/");
 header( 'refresh: 1; url='.MYFILE.'?action=scan' );
 exit();
}
else
{
 setcookie("t00ls", $md5, time()+60*60*24*365,"/");
 $setting = getSetting();
 $action = isset($_GET['action'])?$_GET['action']:"";
  
 if($action=="logout")
 {
  setcookie ("t00ls", "", time() - 3600);
  Header("Location: ".MYFILE);
  exit();
 }
 if($action=="download" && isset($_GET['file']) && trim($_GET['file'])!="")
 {
  $file = $_GET['file'];
  ob_clean();
  if (@file_exists($file)) {
   header("Content-type: application/octet-stream");
      header("Content-Disposition: filename=\"".basename($file)."\"");
   echo file_get_contents($file);
  }
  exit();
 }
?>

<?php
 if($action=="setting")
 {
  if(isset($_POST['btnsetting']))
  {
   $Ssetting = array();
   $Ssetting['user']=isset($_POST['checkuser'])?$_POST['checkuser']:"php | php?";
   $Ssetting['all']=isset($_POST['checkall'])&&$_POST['checkall']=="on"?1:0;
   $Ssetting['hta']=isset($_POST['checkhta'])&&$_POST['checkhta']=="on"?1:0;
   setcookie("t00ls_s", base64_encode(serialize($Ssetting)), time()+60*60*24*365,"/");
   echo "&nbsp;&nbsp;&nbsp;&nbsp;设置完成！";
   header( 'refresh: 1; url='.MYFILE.'?action=setting' );
   exit();
  }
?>
<form name="frmSetting" method="post" action="?action=setting">
<fieldset style="width:400px;margin-left:10px;">
<LEGEND>扫描设定</LEGEND>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="60">文件后缀:</td>
    <td width="300"><input type="text" name="checkuser" id="checkuser" style="width:300px;" value="<?php echo $setting['user']?>"></td>
  </tr>
  <tr>
    <td><label for="checkall">所有文件</label></td>
    <td><input type="checkbox" name="checkall" id="checkall" <?php if($setting['all']==1) echo "checked"?>></td>
  </tr>
  <tr>
    <td><label for="checkhta">设置文件</label></td>
    <td><input type="checkbox" name="checkhta" id="checkhta" <?php if($setting['hta']==1) echo "checked"?>></td>
  </tr>
  <tr>
    <td> </td>
    <td>
      <input type="submit" name="btnsetting" id="btnsetting" value="提交">
    </td>
  </tr>
</table>
</fieldset>
</form>
<?php
 }
 else
 {
  $dir = isset($_POST['path'])?$_POST['path']:MYPATH;
  $dir = substr($dir,-1)!="/"?$dir."/":$dir;
 // $dir = dirname(dirname(__FILE__));
  //$dir = substr($dir,-1)!="/"?$dir."/":$dir;
?>

<div style="margin: 20px 20px 20px 20px;
    padding: 5px;
    font-size: 14px;
    line-height: 26px;
    background-color: #e6f2fb;
    border-radius: 2px;
    width: 800px;">
· 根据服务器文件数量不同，本操作可能需要较长时间，请耐心等待<br>
· 受限于服务器性能，超大文件数量可能造成超时失败，可尝试删除生成的静态页面，或输入单个文件夹扫描<br>
· 部分文件为网站配置文件，会随着网站设置修改而改动，需人工检查是否被篡改<br>
· 路径格式： ../(根目录,默认） ../include(include文件夹)  ../data/cache(data/cache文件夹)
</div>
<script>
function loading() {
document.getElementById('loaddiv1').style.visibility = 'hidden';
document.getElementById('spinner').style.display = 'block';
}
</script>
<form name="frmScan" method="post" action="">
<div id="loaddiv1">
<table width="99%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="690">
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;扫描路径：<input type="text" name="path" id="path" style="width:200px" value="../">
          <input type="submit" name="btnScan" id="btnScan" value="开始扫描" onclick="loading();"></td>
  </tr>
</table>
</div>
<style>
.spinner {

  width: 150px;
  text-align: center;
  display:none;
}
 
.spinner > div {
  width: 15px;
  height: 15px;
  background-color: #0099CC;
 
  border-radius: 100%;
  display: inline-block;
  -webkit-animation: bouncedelay 1.4s infinite ease-in-out;
  animation: bouncedelay 1.4s infinite ease-in-out;
  /* Prevent first frame from flickering when animation starts */
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
}
 
.spinner .bounce1 {
  -webkit-animation-delay: -0.32s;
  animation-delay: -0.32s;
}
 
.spinner .bounce2 {
  -webkit-animation-delay: -0.16s;
  animation-delay: -0.16s;
}
 
@-webkit-keyframes bouncedelay {
  0%, 80%, 100% { -webkit-transform: scale(0.0) }
  40% { -webkit-transform: scale(1.0) }
}
 
@keyframes bouncedelay {
  0%, 80%, 100% {
    transform: scale(0.0);
    -webkit-transform: scale(0.0);
  } 40% {
    transform: scale(1.0);
    -webkit-transform: scale(1.0);
  }
}
</style>
<div class="spinner" id="spinner" name="spinner">
  <div class="bounce1"></div>
  <div class="bounce2"></div>
  <div class="bounce3"></div>
</div>
</form>
<?php
  if(isset($_POST['btnScan']))
  {
   $start=time();
   $is_user = array();
   $is_ext = "";
   $list = "";
    
   if(trim($setting['user'])!="")
   {
    $is_user = explode("|",$setting['user']);
    if(count($is_user)>0)
    {
     foreach($is_user as $key=>$value)
      $is_user[$key]=trim(str_replace("?","(.)",$value));
     $is_ext = "(\.".implode("($|\.))|(\.",$is_user)."($|\.))";
    }
   }
   if($setting['hta']==1)
   {
    $is_hta=1;
    $is_ext = strlen($is_ext)>0?$is_ext."|":$is_ext;
    $is_ext.="(^\.htaccess$)";
   }
   if($setting['all']==1 || (strlen($is_ext)==0 && $setting['hta']==0))
   {
    $is_ext="(.+)";
   }
    
   $php_code = getCode();
   if(!is_readable($dir))
    $dir = MYPATH;
   $count=$scanned=0;
   scan($dir,$is_ext);
   $end=time();
   $spent = ($end - $start);
?>
<div style="padding:10px; background-color:#ccc;margin:10px;">扫描: <?php echo $scanned?> 文件 | 发现: <?php echo $count?> 可疑文件 | 耗时: <?php echo $spent?> 秒 </div>
<table width="99%" border="0" cellspacing="0" cellpadding="0" style="margin-left:10px;">
  <tr>
    <td width="15" align="center">No.</td>
    <td width="25%">&nbsp;文件</td>
    <td width="12%">&nbsp;更新时间</td>
    <td width="20%">&nbsp;类型</td>
    <td>&nbsp;动作</td>
  </tr>
<?php echo $list?>
</table>
<?php
  }
 }
}
ob_flush();
?>
</body>
</html>
<?php
function scan($path = '.',$is_ext){
 global $php_code,$count,$scanned,$list,$dir,$scanned,$cfg_cookie_encode,$config_adminpath_name;
    $ignore = array('.', '..' );
    $dh = @opendir( $path );
  
    while(false!==($file=readdir($dh))){
        if( !in_array( $file, $ignore ) ){                
            if( is_dir( "$path$file" ) ){
                scan("$path$file/",$is_ext);           
            } else {
    $current = $path.$file; 
    if(MYFULLPATH==$current) continue;
    if(!preg_match("/$is_ext/i",$file)) continue;
    if(is_readable($current))
    {
    // $current2=str_replace($dir,"",$current);
	// $current3=str_replace(basename(getcwd()),"admin",$current2);
    $current2=str_replace('../',"",$current);
	$current3=str_replace(basename(getcwd()),"admin",$current2);	
		switch ($current3) {
		case $config_adminpath_name:
		$file_info='<font style="color: #20B2AA">&nbsp;后台目录名配置</font>';
		break;
		
		case 'data/cache/all_group.php':
		$file_info='<font style="color: #20B2AA">&nbsp;会员组数据缓存</font>';
		break;
		
		case 'data/config.cache.inc.php':
		$file_info='<font style="color: #20B2AA">&nbsp;网站设置</font>';
		break;
		
		case 'data/config.ftp.php':
		$file_info='<font style="color: #20B2AA">&nbsp;远程图片设置</font>';
		break;
		
		case 'data/mark/inc_photowatermark_config.php':
		$file_info='<font style="color: #20B2AA">&nbsp;图片水印设置</font>';
		break;
		
		case 'js/player/dmplayer/admin/data.php':
		$file_info='<font style="color: #20B2AA">&nbsp;弹幕播放器设置</font>';
		break;
		
		case 'data/admin/ip.php':
		$file_info='<font style="color: #20B2AA">&nbsp;后台IP安全设置</font>';
		break;
		
		case 'data/admin/smtp.php':
		$file_info='<font style="color: #20B2AA">&nbsp;邮件服务器设置</font>';
		break;
		
		case 'data/admin/weixin.php':
		$file_info='<font style="color: #20B2AA">&nbsp;微信公众号设置</font>';
		break;
		
		case 'data/admin/ping.php':
		$file_info='<font style="color: #20B2AA">&nbsp;百度推送设置</font>';
		break;
		
		case 'data/admin/notify.php':
		$file_info='<font style="color: #20B2AA">&nbsp;会员中心消息发送内容</font>';
		break;
		
		case 'data/config.cache.bak.php':
		$file_info='<font style="color: #20B2AA">&nbsp;网站初始设置备份</font>';
		break;
		
		case 'data/cache/collect_xml.php':
		$file_info='<font style="color: #20B2AA">&nbsp;采集中断位置记录</font>';
		break;
		
		case 'data/common.inc.php':
		$file_info='<font style="color: #20B2AA">&nbsp;数据库连接信息配置</font>';
		break;
		
		default: $file_info='';
		}
		
	 $scanned++;
     $file_md5=strtoupper(md5_file($current)); 
	 $file_size=filesize($current);
     for ($i=0; $i<=$scanned; $i++)
     {
      if(array_key_exists($file_md5,$php_code)==false AND $file_size>0)
      {
	   $count++;
       $j = $count % 2 + 1;
       $filetime = date('Y-m-d H:i:s',filemtime($current));
       $reason = "";
	   if(in_array($current3,$php_code)){ 
		   $reason='<font style="color: #DC143C">和原始文件不一致</font>'.$file_info;
		   }
		   else{
			   if($current3==$config_adminpath_name){$reason='<font style="color: #DC143C">和原始文件不一致</font>'.$file_info;}
			   else{$reason='<font style="color: #FF8C00">新增未知文件</font>'.$file_info;}
			   }
       $list.="
   <tr class='alt$j' onmouseover='this.className=\"focus\";' onmouseout='this.className=\"alt$j\";'>
  <td>$count</td>
  <td><a href='javascript:'>$current2</a></td>
  <td>$filetime</td>
  <td>$reason</td>
  <td><a href='?action=download&file=$current' target='_blank'>查看</a></td>
   </tr>";
       //echo $key . "-" . $path . $file ."(" . $arr[0] . ")" ."<br />";
       //echo $path . $file ."<br />";
       break;
      }
     }
    }
            }
        }
    }
    closedir( $dh );
}
function getSetting()
{
 $Ssetting = array();
 if(isset($_COOKIE['t00ls_s_file']))
 {
  $Ssetting = unserialize(base64_decode($_COOKIE['t00ls_s']));
  $Ssetting['user']=isset($Ssetting['user'])?$Ssetting['user']:"php | php?";
  $Ssetting['all']=isset($Ssetting['all'])?intval($Ssetting['all']):0;
  $Ssetting['hta']=isset($Ssetting['hta'])?intval($Ssetting['hta']):1;
 }
 else
 {
  $Ssetting['user']="php | php?";
  $Ssetting['all']=0;
  $Ssetting['hta']=1;
  setcookie("t00ls_s", base64_encode(serialize($Ssetting)), time()+60*60*24*365,"/");
 }
 return $Ssetting;
}



function getCode()
{
	
	
	$ver = file_get_contents('../data/admin/ver.txt'); 
	if(!$ver){echo '<font style="color: #DC143C"><br><br><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;获取CMS版本号失败，请勿擅自修改版本号！</strong></font>';exit;}	
	
	$md5_url='https://www.seacms.com/api/md5/MD5'.$ver.'.txt';

	
	//$str = file_get_contents($md5_url);
	// 初始化cURL会话
	$ch = curl_init();

	// 要请求的URL
	$url = $md5_url;

	// 设置cURL选项
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 将结果返回为字符串，而不是直接输出
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁用对证书来源的检查
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书的主机名是否存在
	// 执行cURL请求
	$str = curl_exec($ch);

	// 检查是否有错误
	if(curl_errno($ch)) {
		echo '<font style="color: #DC143C"><br><br><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;获取文件指纹数据失败，请检查CMS版本号是否正确！<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;检查网站服务器能否正常访问指纹文件：'.$md5_url.'</strong></font>';exit;
	} 
	curl_close($ch);// 关闭cURL会话

	
	if(strpos($str,'ass.php')==FALSE){echo '<font style="color: #DC143C"><br><br><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;获取文件指纹数据失败，请检查CMS版本号是否正确！<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;检查网站服务器能否正常访问指纹文件：'.$md5_url.'</strong></font>';exit;}
	
	$str = str_replace(array("\r","\n"," "), '', $str);
	$arr = explode('&',$str);
	$arr2 = array();
	foreach($arr as $k=>$v){
			$arr = explode('=',$v);
			$arr2[$arr[0]] = $arr[1];
	}
	return $arr2;
}

?>
<br><br>
</div>
</div>
<?php 
viewFoot();
?>
</body>
</html>