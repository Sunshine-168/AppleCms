<?php  
//包含函数库
require_once(sea_INC.'/inc/mysql.php' );
//拼音的缓冲数组
$pinyins = Array();

//获得当前的脚本网址
function GetCurUrl()
{
	if(!empty($_SERVER["REQUEST_URI"]))
	{
		$scriptName = $_SERVER["REQUEST_URI"];
		$nowurl = $scriptName;
	}
	else
	{
		$scriptName = $_SERVER["PHP_SELF"];
		if(empty($_SERVER["QUERY_STRING"]))
		{
			$nowurl = $scriptName;
		}
		else
		{
			$nowurl = $scriptName."?".$_SERVER["QUERY_STRING"];
		}
	}
	return $nowurl;
}

//返回格林威治标准时间
function MyDate($format='Y-m-d H:i:s',$timest=0)
{
	global $cfg_cli_time;
	$addtime = $cfg_cli_time * 3600;
	if(empty($format))
	{
		$format = 'Y-m-d H:i:s';
	}
	return gmdate ($format,$timest+$addtime);
}

function GetDateMk($mktime)
{
	return MyDate("Y-m-d",$mktime);
}


//中文截取2，单字节截取模式
//如果是request的内容，必须使用这个函数
function cn_substrR($str,$slen,$startdd=0)
{
	$str = cn_substr(stripslashes($str),$slen,$startdd);
	return addslashes($str);
}

//中文截取2，单字节截取模式
function cn_substr_utf8($str, $length, $start=0)
{
	$lgocl_str=$str;
	//echo strlen($lgocl_str)."||".$length;
    if(strlen($str) < $start+1)
    {
        return '';
    }
    preg_match_all("/./su", $str, $ar);
    $str = '';
    $tstr = '';

    //为了兼容mysql4.1以下版本,与数据库varchar一致,这里使用按字节截取
    for($i=0; isset($ar[0][$i]); $i++)
    {
        if(strlen($tstr) < $start)
        {
            $tstr .= $ar[0][$i];
        }
        else
        {
            if(strlen($str) < $length  )
            {
                $str .= $ar[0][$i];
            }
            else
            {
                break;
            }
        }
    }
    return $str;
}   

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true){
	$re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
	$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
	$re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
	$re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
	preg_match_all($re[$charset], $str, $match);
	$length_new = $length;
	for($i=$start; $i<$length; $i++){
		if (ord($match[0][$i]) > 0xa0){
			//中文
		}else{
			$length_new++;
			$length_chi++;
		}
	}
	if($length_chi<$length){
		$length_new = $length+($length_chi/2);
	}
	$slice = join("",array_slice($match[0], $start, $length_new));
    if($suffix && $slice != $str){
		return $slice."…";
	}
    return $slice;
}

function cn_substr($str,$slen,$startdd=0)
{
	global $cfg_soft_lang;
	if($cfg_soft_lang=='utf-8')
	{
		return cn_substr_utf8($str,$slen,$startdd);
	}
	$restr = '';
	$c = '';
	$str_len = strlen($str);
	if($str_len < $startdd+1)
	{
		return '';
	}
	if($str_len < $startdd + $slen || $slen==0)
	{
		$slen = $str_len - $startdd;
	}
	$enddd = $startdd + $slen - 1;
	for($i=0;$i<$str_len;$i++)
	{
		if($startdd==0)
		{
			$restr .= $c;
		}
		else if($i > $startdd)
		{
			$restr .= $c;
		}

		if(ord($str[$i])>0x80)
		{
			if($str_len>$i+1)
			{
				$c = $str[$i].$str[$i+1];
			}
			$i++;
		}
		else
		{
			$c = $str[$i];
		}

		if($i >= $enddd)
		{
			if(strlen($restr)+strlen($c)>$slen)
			{
				break;
			}
			else
			{
				$restr .= $c;
				break;
			}
		}
	}
	return $restr;
}

function GetCkVdValue()
{
	@session_start();
	return isset($_SESSION['sea_ckstr']) ? $_SESSION['sea_ckstr'] : '';
}

//php某些版本有Bug，不能在同一作用域中同时读session并改注销它，因此调用后需执行本函数
function ResetVdValue()
{
	@session_start();
	$_SESSION['sea_ckstr'] = '';
	$_SESSION['sea_ckstr_last'] = '';
}

function ExecTime()
{
	$time = explode(" ", microtime());
	$usec = (double)$time[0];
	$sec = (double)$time[1];
	return $sec + $usec;
}

function getRunTime($t1)
{
	$t2=ExecTime() - $t1;
	return "页面执行时间: ".number_format($t2, 6)."秒";
}

function getPowerInfo()
{
	return "<p>Powered by <strong><a href=\"http://www.seacms.com\" title=\"".$GLOBALS['cfg_softname']."\" target=\"_blank\">".$GLOBALS['cfg_soft_enname']."</a></strong> <em>".$GLOBALS['cfg_version']."</em></p>";
}

function dd2char($ddnum)
{
	$ddnum = strval($ddnum);
	$slen = strlen($ddnum);
	$okdd = '';
	$nn = '';
	for($i=0;$i<$slen;$i++)
	{
		if(isset($ddnum[$i+1]))
		{
			$n = $ddnum[$i].$ddnum[$i+1];
			if( ($n>96 && $n<123) || ($n>64 && $n<91) )
			{
				$okdd .= chr($n);
				$i++;
			}
			else
			{
				$okdd .= $ddnum[$i];
			}
		}
		else
		{
			$okdd .= $ddnum[$i];
		}
	}
	return $okdd;
}

function PutCookie($key,$value,$kptime=0,$pa="/")
{
	global $cfg_cookie_encode;
	setcookie($key,$value,time()+$kptime,$pa);
	setcookie($key.'__ckMd5',substr(md5($cfg_cookie_encode.$value),0,16),time()+$kptime,$pa);
}

function DropCookie($key)
{
	setcookie($key,'',time()-360000,"/");
	setcookie($key.'__ckMd5','',time()-360000,"/");
}

function GetCookie($key)
{
	global $cfg_cookie_encode;
	if( !isset($_COOKIE[$key]) || !isset($_COOKIE[$key.'__ckMd5']) )
	{
		return '';
	}
	else
	{
		if($_COOKIE[$key.'__ckMd5']!=substr(md5($cfg_cookie_encode.$_COOKIE[$key]),0,16))
		{
			return '';
		}
		else
		{
			return $_COOKIE[$key];
		}
	}
}

function GetIP()
{
	if(!empty($_SERVER["HTTP_CLIENT_IP"]))
	{
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	}
	else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
	{
		$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
	}
	else if(!empty($_SERVER["REMOTE_ADDR"]))
	{
		$cip = $_SERVER["REMOTE_ADDR"];
	}
	else
	{
		$cip = '';
	}
	preg_match("/[\d\.]{7,15}/", $cip, $cips);
	$cip = isset($cips[0]) ? $cips[0] : 'unknown';
	unset($cips);
	return $cip;
}

function ShowMsg($msg,$gourl,$onlymsg=0,$limittime=0,$extraJs='')
{
	global $cfg_basehost;
	if(empty($GLOBALS['cfg_phpurl']))
	{
		$GLOBALS['cfg_phpurl'] = '..';
	}
	$htmlhead  = "<html>\r\n<head>\r\n<title>提示信息</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta name=\"viewport\" content=\"width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no\">\r\n";
	$htmlhead .= "<base target='_self'/>\r\n<style>body{background:#f9fafd;color:#818181}a {text-decoration: none}.mac_msg_jump{width:90%;max-width:420px;margin:5% auto 0;border: 1px solid #293846;border-radius: 4px;box-shadow: 0px 1px 2px rgba(0,0,0,0.1);border: 1px solid #0099CC;background-color: #F2F9FD;}.mac_msg_jump .title{margin-bottom:11px}.mac_msg_jump .text{margin-top: 20px;font-size:14px;color:#555;font-weight: normal;}.msg_jump_tit{height: 32px;padding: 0px;line-height: 32px;font-size: 14px;color: #DFE4ED;text-align: left;background: #0099CC;}</style></head>\r\n<body leftmargin='0' topmargin='0'>\r\n<center>\r\n<script>\r\n";
	$htmlfoot  = "</script>\r\n$extraJs</center>\r\n</body>\r\n</html>\r\n";

	if($limittime==0)
	{
		$litime = 1000;
	}
	else
	{
		$litime = $limittime;
	}

	if($gourl=="-1")
	{
		if($limittime==0)
		{
			$litime = 1000;
		}
		$gourl = "javascript:history.go(-1);";
	}

	if($gourl==''||$onlymsg==1)
	{
		$msg = "<script>alert(\"".str_replace("\"","“",$msg)."\");</script>";
	}
	else
	{
		$func = "      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='$gourl'; pgo=1; }
      }\r\n";
		$rmsg = $func;
		$rmsg .= "document.write(\"<br /><div class='mac_msg_jump'>";
	    $rmsg .= "<div class='text'>\");\r\n";

		$rmsg .= "document.write(\"<img style='height: 28px;margin-bottom: 15px;'; src='{$cfg_basehost}/pic/i2.png'><br>".str_replace("\"","“",$msg)."\");\r\n";
		$rmsg .= "document.write(\"";
		if($onlymsg==0)
		{
			if($gourl!="javascript:;" && $gourl!="")
			{
				$rmsg .= "<div style='margin-top:20px;'><a href='{$gourl}'><font style='color:#0099CC;font-size:12px;'>[点击这里手动跳转]</font></a></div>";
			}
			$rmsg .= "<br/></div></div>\");\r\n";
			if($gourl!="javascript:;" && $gourl!='')
			{
				$rmsg .= "setTimeout('JumpUrl()',$litime);";
			}
		}
		else
		{
			$rmsg .= "<br/><br/></div></div>\");\r\n";
		}
		$msg  = $htmlhead.$rmsg.$htmlfoot;
	}
	echo $msg;
}


function alertMsg($str,$url=-1)
{
	if(!empty($url)) $urlstr="location.href='".$url."';";
	if($url==-1) $urlstr = 'history.go(-1)';
	if(!empty($str)) $str ="alert('".$str."');";
	echo("<script>".$str.$urlstr."</script>");
}

function selectMsg($str,$url1,$url2)
{
	echo("<script>if(confirm('$str')){location.href='$url1'}else{location.href='$url2'}</script>");
}

function AjaxHead()
{
	@header("Pragma:no-cache\r\n");
	@header("Cache-Control:no-cache\r\n");
	@header("Expires:0\r\n");
}

function Html2Text($str,$r=0)
{
	if(!function_exists('SpHtml2Text'))
	{
		require_once(sea_INC."/inc/inc_fun_funString.php");
	}
	if($r==0)
	{
		return SpHtml2Text($str);
	}
	else
	{
		$str = SpHtml2Text(stripslashes($str));
		return addslashes($str);
	}
}

function CreateDir($spath)
{
	if(!function_exists('SpCreateDir'))
	{
		require_once(sea_INC.'/inc/inc_fun_funAdmin.php');
	}
	return SpCreateDir($spath);
}

function GetNewInfo()
{
	if(!function_exists('SpGetNewInfo'))
	{
		require_once(sea_INC."/inc/inc_fun_funAdmin.php");
	}
	return SpGetNewInfo();
}

function MkdirAll($truepath,$mmode)
{
	global $cfg_dir_purview;
		if(!file_exists($truepath))
		{
			mkdir($truepath,$cfg_dir_purview);
			chmod($truepath,$cfg_dir_purview);
			return true;
		}
		else
		{
			return true;
		}
}

function GetDateTimeMk($mktime)
{
	return MyDate('Y-m-d H:i:s',$mktime);
}

function GetMkTime($dtime)
{
	global $cfg_cli_time;
	if(!m_ereg("[^0-9]",$dtime))
	{
		return $dtime;
	}
	$dtime = trim($dtime);
	$dt = Array(1970,1,1,0,0,0);
	$dtime = m_ereg_replace("[\r\n\t]|日|秒"," ",$dtime);
	$dtime = str_replace("年","-",$dtime);
	$dtime = str_replace("月","-",$dtime);
	$dtime = str_replace("时",":",$dtime);
	$dtime = str_replace("分",":",$dtime);
	$dtime = trim(m_ereg_replace("[ ]{1,}"," ",$dtime));
	$ds = explode(" ",$dtime);
	$ymd = explode("-",$ds[0]);
	if(!isset($ymd[1]))
	{
		$ymd = explode(".",$ds[0]);
	}
	if(isset($ymd[0]))
	{
		$dt[0] = $ymd[0];
	}
	if(isset($ymd[1]))
	{
		$dt[1] = $ymd[1];
	}
	if(isset($ymd[2]))
	{
		$dt[2] = $ymd[2];
	}
	if(strlen($dt[0])==2)
	{
		$dt[0] = '20'.$dt[0];
	}
	if(isset($ds[1]))
	{
		$hms = explode(":",$ds[1]);
		if(isset($hms[0]))
		{
			$dt[3] = $hms[0];
		}
		if(isset($hms[1]))
		{
			$dt[4] = $hms[1];
		}
		if(isset($hms[2]))
		{
			$dt[5] = $hms[2];
		}
	}
	foreach($dt as $k=>$v)
	{
		$v = m_ereg_replace("^0{1,}",'',trim($v));
		if($v=='')
		{
			$dt[$k] = 0;
		}
	}
	$mt = @gmmktime($dt[3],$dt[4],$dt[5],$dt[1],$dt[2],$dt[0]) - 3600 * $cfg_cli_time;
	if(!empty($mt))
	{
		return $mt;
	}
	else
	{
		return time();
	}
}

function GetEditor($fname,$fvalue)
{
	if(!function_exists('SpGetEditor'))
	{
		require_once(sea_INC."/inc/inc_fun_funAdmin.php");
	}
	return SpGetEditor($fname,$fvalue);
}

function HtmlReplace($str,$rptype=0)
{
	$str = stripslashes($str);
	if($rptype==0)
	{
		$str = htmlspecialchars($str);
	}
	else if($rptype==1)
	{
		$str = htmlspecialchars($str);
		$str = str_replace("　",' ',$str);
		$str = m_ereg_replace("[\r\n\t ]{1,}",' ',$str);
	}
	else if($rptype==2)
	{
		$str = htmlspecialchars($str);
		$str = str_replace("　",'',$str);
		$str = m_ereg_replace("[\r\n\t ]",'',$str);
	}
	else
	{
		$str = m_ereg_replace("[\r\n\t ]{1,}",' ',$str);
		$str = m_eregi_replace('script','ｓｃｒｉｐｔ',$str);
		$str = m_eregi_replace("<[/]{0,1}(link|meta|ifr|fra)[^>]*>",'',$str);
	}
	return addslashes($str);
}

function AttDef($oldvar,$nv)
{
	return empty($oldvar) ? $nv : $oldvar;
}

function gettextsegment()
{
	$textsegment=sea_DATA.'/admin/textsegment.xml';
	$xml = simplexml_load_file($textsegment);
	if(!$xml){$xml = simplexml_load_string(file_get_contents($textsegment));}
	$i=0;
	$segArr = array();
	$items = $xml->item;
	foreach($items as $item)
	{
		$segArr[] =(string)stripslashes($item);
		$i++;
	}
	return $segArr;
}

function gbutf8($str)
{
	global $cfg_soft_lang;
	require_once(sea_INC.'/charset.func.php');
	if($cfg_soft_lang=='gb2312')
	{
		return utf82gb($str);
	}elseif($cfg_soft_lang=='utf-8')
	{
		return gb2utf8($str);
	}
}

function doPseudo($v_des,$v_id)
{
	$rs = gettextsegment();
	$num = count($rs);
	$iType = $v_id % 3;
	if($num == 0||$v_des=='')
	{
		return $v_des;
	}
	elseif ($iType==1)
	{
		$v_des = $rs[$v_id%$num].$v_des;
	}
	elseif ($iType==2)	
	{
		$v_des = $v_des.$rs[$v_id%$num];
	}
	else 
	{
		$pos = strpos($v_des, '<br>');
		if($pos==0)$pos = strpos($v_des, '<br/>');
		if($pos==0)$pos = strpos($v_des, '<br />');
		if($pos==0)$pos = strpos($v_des,vbcrlf);
		if($pos==0)$pos = strpos($v_des,'。')+1;
		$pos = ceil(($pos-1)/2);
		if($pos>0)
		$v_des = cn_substr($v_des,$pos).$rs[$v_id%$num]. cn_substr($v_des,strlen($v_des)-$pos, $pos);
		else 
		$v_des = $rs[$v_id%$num].$v_des;
	}
	return $v_des;
}

function buildregx($regstr,$regopt)
{
	return '/'.str_replace('/','\/',$regstr).'/'.$regopt;
}

function parsm_eregx($con,$regstr,$regopt)
{
	$regx = buildregx($regstr,$regopt);
	preg_match($regx,$con,$ar);
	return $ar[1];
}

function getFromStr($playurl)
{
	if (empty($playurl)) return "";
	$span1="$$$";
	$span2="$$";
	$urlstr='';
	$playurlArray=explode($span1,$playurl);
	$playurlLen=count($playurlArray);
	for($i=0;$i<$playurlLen;$i++){
		if(strpos($playurlArray[$i],$span2)===false)continue;
		$playFromArray=explode($span2,$playurlArray[$i]);
		if($i==$playurlLen-1){
			$urlstr=$urlstr.$playFromArray[0];
		}else{
			$urlstr=$urlstr.$playFromArray[0]." ";
		}
	}
	return $urlstr;
}

function makePageNumber($currentPage,$pagelistLen,$totalPages,$linkType,$currentTypeId=0,$vid=0){
	$strPageNumber="";
	$currentPage=intval($currentPage);
	$beforePages=(($pagelistLen % 2)==0) ? ($pagelistLen / 2) : (ceil($pagelistLen / 2) - 1);
	if($currentPage < 1){
		$currentPage=1;
	}elseif($currentPage > $totalPages){
		$currentPage=$totalPages;
	}
	if ($pagelistLen > $totalPages) $pagelistLen=$totalPages;
	if ($currentPage - $beforePages < 1){
		$beginPage=1 ; 
		$endPage=$pagelistLen;
	}elseif($currentPage - $beforePages + $pagelistLen > $totalPages){
		$beginPage=$totalPages - $pagelistLen + 1 ; $endPage=$totalPages;
	}else{
		$beginPage=$currentPage - $beforePages ; $endPage=$currentPage - $beforePages + $pagelistLen - 1;
	}
	for($pagenumber=$beginPage;$pagenumber<=$endPage;$pagenumber++){
		$page=($pagenumber==1) ? "" : $pagenumber;
		if($pagenumber==$currentPage){
			if ($linkType=="search" || $linkType=="channel" || $linkType=="comment" || $linkType=="topicpage"|| $linkType=="cascade"|| $linkType=="gbook"||$linkType=="newssearch"||$linkType=="newspage"||$linkType=="topicindex"){
				$strPageNumber.="<em>".$pagenumber."</em>";
			}else{
				$strPageNumber.="<span><font color=red>".$pagenumber."</font></span>";
			}
		}else{
			switch (trim($linkType)) {
			case "channel":
				$strPageNumber.="<a href='".getChannelPagesLink($currentTypeId,$pagenumber)."'>".$pagenumber."</a>";
			break;
			case "tag":
				global $tag;
				$strPageNumber.="<a href='".getTagLink($tag,$pagenumber)."'>".$pagenumber."</a>";
			break;
			case "search":
				global $searchType,$searchword;
				$searchword1 = urlencode($searchword);
				$strPageNumber.="<a href='?page=".$pagenumber."&searchword=".$searchword1."&searchtype=".$searchType."'>".$pagenumber."</a>";
			break;
			case "cascade":
				global $schwhere;
				$schwhere = preg_replace("/page=[^\&]*\&?/i","",$schwhere);
				$strPageNumber.="<a href='?page=".$pagenumber."&$schwhere'>".$pagenumber."</a>";
			break;
			case "comment":
				$strPageNumber.="<a onclick=\"viewComment(".$currentTypeId.",".$pagenumber.");return false;\" href='javascript:'>".$pagenumber."</a>";
			break;
			case "gbook":
			case "adslist":
			case "selflabellist":
			case "templist":
				$strPageNumber.="<a href='?page=".$pagenumber."'>".$pagenumber."</a>";
			break;
			case "videolist":
			global $action,$order,$type,$keyword,$v_state,$v_commend,$repeat,$topic,$playfrom,$downfrom,$etype,$empty,$rlen,$allrepeat,$v_recycled,$v_isunion,$jqtype,$area,$year,$yuyan,$letter,$commend,$ver,$tid,$v_ispsd,$v_ismoney;
				$strPageNumber.="<a href='?page=".$pagenumber."&action=".$action."&order=".$order."&type=".$type."&etype=".$etype."&keyword=".$keyword."&v_state=".$v_state."&v_commend=".$v_commend."&repeat=".$repeat."&allrepeat=".$allrepeat."&topic=".$topic."&playfrom=".$playfrom."&downfrom=".$downfrom."&empty=".$empty."&rlen=".$rlen."&v_recycled=".$v_recycled."&v_isunion=".$v_isunion."&v_ismoney=".$v_ismoney."&v_ispsd=".$v_ispsd."&jqtype=".$jqtype."&area=".$area."&year=".$year."&yuyan=".$yuyan."&letter=".$letter."&commend=".$commend."&ver=".$ver."&tid=".$tid."'>".$pagenumber."</a>";
			break;
			case "collectfilters":
				$strPageNumber.="<a href='?action=filters&page=".$pagenumber."'>".$pagenumber."</a>";
			break;
			case "topicpage":
				$strPageNumber.="<a href='".getTopicPageLink($currentTypeId,$pagenumber)."'>".$pagenumber."</a>";
			break;
			case "topicindex":
				$strPageNumber.="<a href='".getTopicIndexPageLink($pagenumber)."'>".$pagenumber."</a>";
			break;
			case "newspage":
				$strPageNumber.="<a href='".getnewspageLink($currentTypeId,$pagenumber)."'>".$pagenumber."</a>";
			break;
			case "newssearch":
				global $searchType,$searchword;
				$strPageNumber.="<a href='?page=".$pagenumber."&searchword=".$searchword."&searchtype=".$searchType."'>".$pagenumber."</a>";
			break;
			case "newssubpage":
				$strPageNumber.="<a href='".getArticleLink($currentTypeId,$vid,'',$pagenumber)."'>".$pagenumber."</a>";
			break;
			case "customvideo":
				$strPageNumber.="<a href='".getCustomLink($pagenumber)."'>".$pagenumber."</a>";
			break;
			}
		}
	}
	return $strPageNumber;
}

function makePageNumberLoop($currentPage,$pagelistLen,$totalPages,$loopStr){
	$j=ceil($pagelistLen/2);
	$ret='';
	if($currentPage<1)$currentPage=1;
	if($currentPage>$totalPages) $currentPage=$totalPages;
	if($pagelistLen>$totalPages) $pagelistLen=$totalPages;
	if($currentPage-$j<1)
	{
		$istart=1;
		$iEnd=$pagelistLen;
	}elseif ($currentPage - $j + $pagelistLen > $totalPages)
	{
		$istart=$totalPages - $pagelistLen + 1;
		$iEnd=$totalPages;
	}else{
		$istart=$currentPage - $j;
		$iEnd=$currentPage - $j + $pagelistLen - 1;
	}
	for($i=$istart;$i<=$iEnd;$i++)
	{
		$tmp=str_replace("[pagenumber:link]", getTopicIndexLink($i), $loopStr);
		$tmp=str_replace("[pagenumber:page]", $i, $tmp);
		$ret.=$tmp;
	}
	unset($tmp);
	return $ret;
}

function makePageNumberLoop2($currentPage,$pagelistLen,$totalPages,$loopStr,$pageListType='channel',$currentTypeId=0){
	$j=ceil($pagelistLen/2);
	$ret='';
	if($currentPage<1)$currentPage=1;
	if($currentPage>$totalPages) $currentPage=$totalPages;
	if($pagelistLen>$totalPages) $pagelistLen=$totalPages;
	if($currentPage-$j<1)
	{
		$istart=1;
		$iEnd=$pagelistLen;
	}elseif ($currentPage - $j + $pagelistLen > $totalPages)
	{
		$istart=$totalPages - $pagelistLen + 1;
		$iEnd=$totalPages;
	}else{
		$istart=$currentPage - $j;
		$iEnd=$currentPage - $j + $pagelistLen - 1;
	}
	for($i=$istart;$i<=$iEnd;$i++)
	{
		$tmp=str_replace("[pagenumber:link]", getPageLink($i,$pageListType,$currentTypeId), $loopStr);
		$tmp=str_replace("[pagenumber:page]", $i, $tmp);
		$ret.=$tmp;
	}
	unset($tmp);
	return $ret;
}

function getPageLink($page=1,$pageListType='channel',$currentTypeId=0)
{
	switch ($pageListType)
	{
		case "channel":
			return getChannelPagesLink($currentTypeId,$page);
			break;
		case "newspage":
			return getnewspageLink($currentTypeId,$page);
			break;
		case "topicpage":
			return getTopicPageLink($currentTypeId,$page);
			break;
		case "search":
		case "newssearch":
			global $searchType,$searchword;
			$searchword1 = urlencode($searchword);
			return '?page='.$page.'&searchword='.$searchword1.'&searchtype='.$searchType;
			break;
		case "tag":
			global $tag;
			return gettaglink($tag,$page);
			break;
		case "customvideo":
			return getCustomLink($page);
			break;
		case "cascade":
			global $schwhere;
			$schwhere = preg_replace("/page=[^\&]*\&?/i","",$schwhere);
			return "?page=".$page."&$schwhere";
		break;
		
	}
	
}

function newsSubPageLinkInfo($currentPage=1,$pageListLen=10,$TotalPages=1,$currentTypeId,$videoId)
{
	$pageNumber=makePageNumber($currentPage,$pageListLen,$TotalPages,'newssubpage',$currentTypeId,$videoId);
	if ($currentPage==1){
			$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";
		}else{
			$firstPageLink="<a href='".getArticleLink($currentTypeId,$videoId,'',1)."'>首页</a>" ; 
			$lastPagelink="<a href='".getArticleLink($currentTypeId,$videoId,'',($currentPage-1))."'>上一页</a>";
		}
	if($currentPage==$TotalPages){
		$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";
	}else{
		$nextPagelink="<a  href='".getArticleLink($currentTypeId,$videoId,'',($currentPage+1))."'>下一页</a>" ; 
		$finalPageLink="<a  href='".getArticleLink($currentTypeId,$videoId,'',$TotalPages)."'>尾页</a>";
	}
	$pageNumberInfo=$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink;
	return $pageNumberInfo;
}

function pageNumberLinkInfo($currentPage=1,$pageListLen=10,$TotalPages=1,$linkType="channel",$TotalResult=0,$currentTypeId=0){
	$pageNumber=makePageNumber($currentPage,$pageListLen,$TotalPages,$linkType,$currentTypeId);
	switch (trim($linkType)) {
		case "channel":
			if ($currentPage==1){
					$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";
				}else{
					$firstPageLink="<a href='".getChannelPagesLink($currentTypeId,1)."'>首页</a>" ; 
					$lastPagelink="<a href='".getChannelPagesLink($currentTypeId,($currentPage-1))."'>上一页</a>";
				}
			if($currentPage==$TotalPages){
				$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";
			}else{
				$nextPagelink="<a  href='".getChannelPagesLink($currentTypeId,($currentPage+1))."'>下一页</a>" ; 
				$finalPageLink="<a  href='".getChannelPagesLink($currentTypeId,$TotalPages)."'>尾页</a>";
			}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"getPageGoUrl(".$TotalPages.",'page',".getChannellinkStr().",'".$GLOBALS['cfg_channelpage_name2'].$GLOBALS['cfg_filesuffix2']."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink.$pagesStr;
		break;
		case "newspage":
			if ($currentPage==1){
					$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";
				}else{
					$firstPageLink="<a href='".getnewspageLink($currentTypeId,1)."'>首页</a>" ; 
					$lastPagelink="<a href='".getnewspageLink($currentTypeId,($currentPage-1))."'>上一页</a>";
				}
			if($currentPage==$TotalPages){
				$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";
			}else{
				$nextPagelink="<a  href='".getnewspageLink($currentTypeId,($currentPage+1))."'>下一页</a>" ; 
				$finalPageLink="<a  href='".getnewspageLink($currentTypeId,$TotalPages)."'>尾页</a>";
			}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"getPageGoUrl(".$TotalPages.",'page',".getChannellinkStr().",'".$GLOBALS['cfg_channelpage_name2'].$GLOBALS['cfg_filesuffix2']."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink.$pagesStr;
		break;
		case "topicpage":
			$pageStyle=getTopicPageLinkStyle();
			if ($currentPage==1){
				$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";
			}else{
				$firstPageLink="<a  href='".getTopicPageLink($currentTypeId,1)."'>首页</a>"; $lastPagelink="<a  href='".getTopicPageLink($currentTypeId,($currentPage-1))."'>上一页</a>";
			}
			if ($currentPage==$TotalPages){
				$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";
			}else{
				$nextPagelink="<a  href='".getTopicPageLink($currentTypeId,($currentPage+1))."'>下一页</a>" ; $finalPageLink="<a  href='".getTopicPageLink($currentTypeId,$TotalPages)."'>尾页</a>";
			}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"getPageGoUrl(".$TotalPages.",'page',".$pageStyle.",'".$GLOBALS['cfg_channelpage_name2'].$GLOBALS['cfg_filesuffix2']."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber."".$nextPagelink."".$finalPageLink.$pagesStr;
		break;
		case "topicindex":
			$pageStyle=getTopicIndexLinkStyle();
			if ($currentPage==1){
				$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";
			}else{
				$firstPageLink="<a  href='".getTopicIndexLink(1)."'>首页</a>"; $lastPagelink="<a  href='".getTopicIndexLink($currentPage-1)."'>上一页</a>";
			}
			if ($currentPage==$TotalPages){
				$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";
			}else{
				$nextPagelink="<a  href='".getTopicIndexLink($currentPage+1)."'>下一页</a>" ; $finalPageLink="<a  href='".getTopicIndexLink($TotalPages)."'>尾页</a>";
			}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"getPageGoUrl(".$TotalPages.",'page',".$pageStyle.",'".$GLOBALS['cfg_channelpage_name2'].$GLOBALS['cfg_filesuffix2']."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber."".$nextPagelink."".$finalPageLink.$pagesStr;
		break;
		case "search":
			global $searchType,$searchword;
			$searchword1 = urlencode($searchword);
			if ($currentPage==1){$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";}else{$firstPageLink="<a href='?page=1&searchword=".$searchword1."&searchtype=".$searchType."'>首页</a>" ; $lastPagelink="<a href='?page=".($currentPage-1)."&searchword=".$searchword1."&searchtype=".$searchType."'>上一页</a>";}
			if($currentPage==$TotalPages){$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";}else{$nextPagelink="<a href='?page=".($currentPage+1)."&searchword=".$searchword1."&searchtype=".$searchType."'>下一页</a>" ; $finalPageLink="<a href='?page=".$TotalPages."&searchword=".$searchword1."&searchtype=".$searchType."'>尾页</a>";}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"goSearchPage(".$TotalPages.",'page','".$searchType."','".$searchword1."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink.$pagesStr;
		break;
		case "cascade":
			global $schwhere;
			if ($currentPage==1){$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";}else{$firstPageLink="<a href='?page=1&$schwhere'>首页</a>" ; $lastPagelink="<a href='?page=".($currentPage-1)."&$schwhere'>上一页</a>";}
			if($currentPage==$TotalPages){$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";}else{$nextPagelink="<a href='?page=".($currentPage+1)."&$schwhere'>下一页</a>" ; $finalPageLink="<a href='?page=".$TotalPages."&$schwhere'>尾页</a>";}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"goCascadePage(".$TotalPages.",'page','".$schwhere."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink.$pagesStr;
		break;
		case "newssearch":
			global $searchType,$searchword;
			if ($currentPage==1){$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";}else{$firstPageLink="<a href='?page=1&searchword=".$searchword."&searchtype=".$searchType."'>首页</a>" ; $lastPagelink="<a href='?page=".($currentPage-1)."&searchword=".$searchword."&searchtype=".$searchType."'>上一页</a>";}
			if($currentPage==$TotalPages){$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";}else{$nextPagelink="<a href='?page=".($currentPage+1)."&searchword=".$searchword."&searchtype=".$searchType."'>下一页</a>" ; $finalPageLink="<a href='?page=".$TotalPages."&searchword=".$searchword."&searchtype=".$searchType."'>尾页</a>";}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"goSearchPage(".$TotalPages.",'page','".$searchType."','".$searchword."')\" class='btn' /></form>";
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink.$pagesStr;
		break;
		case "tag":
			global $tag;
			if ($currentPage==1){$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";}else{$firstPageLink="<a href='".gettaglink($tag)."'>首页</a>" ; $lastPagelink="<a href='".gettaglink($tag,($currentPage-1))."'>上一页</a>";}
			if($currentPage==$TotalPages){$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";}else{$nextPagelink="<a href='".gettaglink($tag,($currentPage+1))."'>下一页</a>" ; $finalPageLink="<a href='".gettaglink($tag,($TotalPages))."'>尾页</a>";}
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink;
		break;
		case "comment":
			if ($currentPage==1){$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";}else{$firstPageLink="<a href='javascript:' onclick=\"viewComment(".$currentTypeId.",1);return false;\">首页</a>" ; $lastPagelink="<a href='javascript:' onclick=\"viewComment(".$currentTypeId.",".($currentPage-1).");return false;\">上一页</a>";}
			if($currentPage==$TotalPages){$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";}else{$nextPagelink="<a href='javascript:' onclick=\"viewComment(".$currentTypeId.",".($currentPage+1).");return false;\">下一页</a>" ; $finalPageLink="<a href='javascript:' onclick=\"viewComment(".$currentTypeId.",".$TotalPages.");return false;\">尾页</a>";}
			$pageNumberInfo="<span>共".$TotalResult."条评论 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink;
		break;
		case "gbook":
			if ($currentPage==1){$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";}else{$firstPageLink="<a href='?page=1'>首页</a>" ; $lastPagelink="<a href='?page=".($currentPage-1)."'>上一页</a>";}
			if($currentPage==$TotalPages){$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";}else{$nextPagelink="<a href='?page=".($currentPage+1)."'>下一页</a>" ; $finalPageLink="<a href='?page=".$TotalPages."'>尾页</a>";}
			$pageNumberInfo="<span>共".$TotalResult."条数据 页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink; 
		break;
		case "customvideo":
			if ($currentPage==1){
					$firstPageLink="<em class='nolink'>首页</em>" ; $lastPagelink="<em class='nolink'>上一页</em>";
				}else{
					$firstPageLink="<a href='".getCustomLink(1)."'>首页</a>" ; 
					$lastPagelink="<a href='".getCustomLink($currentPage-1)."'>上一页</a>";
				}
			if($currentPage==$TotalPages){
				$nextPagelink="<em class='nolink'>下一页</em>" ; $finalPageLink="<em class='nolink'>尾页</em>";
			}else{
				$nextPagelink="<a  href='".getCustomLink($currentPage+1)."'>下一页</a>" ; 
				$finalPageLink="<a  href='".getCustomLink($TotalPages)."'>尾页</a>";
			}
			$pagesStr="<form><input type='input' name='page' size=4  /><input type='button' value='跳转' onclick=\"getPageGoUrl(".$TotalPages.",'page',2,'".$GLOBALS['cfg_channelpage_name2'].$GLOBALS['cfg_filesuffix2']."')\" class='btn' /></form>";
			$pageNumberInfo="<span>页次:".$currentPage."/".$TotalPages."页</span>".$firstPageLink.$lastPagelink.$pageNumber.$nextPagelink.$finalPageLink.$pagesStr;
		break;
	}
	return $pageNumberInfo;
}

function getTopicPageLink($topicId,$page)
{
	global $dsql;
	if($GLOBALS['cfg_runmode']=='0'){
		$row=$dsql->GetOne("select enname from sea_topic where id=".$topicId);
		$topicname=$row['enname'];
		if (intval($page)==1) $tempStr=""; else $tempStr="-".$page;
		$linkStr="/".$GLOBALS['cfg_cmspath'].$GLOBALS['cfg_filesuffix']."/".$topicname.$tempStr.$GLOBALS['cfg_filesuffix2'];
	}elseif($GLOBALS['cfg_runmode']=='1'){
		if (intval($page)==1) $tempStr=""; else $tempStr="-".$page;
		$linkStr="/".$GLOBALS['cfg_cmspath'].$GLOBALS['cfg_filesuffix']."/?".$topicId.$tempStr.$GLOBALS['cfg_filesuffix2'];
	}elseif($GLOBALS['cfg_runmode']=='2'){
		if (intval($page)==1) $tempStr=""; else $tempStr="-".$page;
		$linkStr="/".$GLOBALS['cfg_cmspath'].$GLOBALS['cfg_filesuffix']."/".$topicId.$tempStr.$GLOBALS['cfg_filesuffix2'];
	}
	return $linkStr;
}

function getTopicIndexPageLink($page){
	global $dsql;
	switch($GLOBALS['cfg_runmode'])
	{
		case 0:
			$pageStr=$page==1?'':$page;
			$linkStr="/".$GLOBALS['cfg_cmspath'].$GLOBALS['cfg_album_name']."/index".$pageStr.$GLOBALS['cfg_filesuffix2'];
			break;
		case 1:
			$linkStr="/".$GLOBALS['cfg_cmspath'].$GLOBALS['cfg_album_name']."/?".$page.$GLOBALS['cfg_filesuffix2'];
			break;
		case 2:
			$pageStr=$page==1?'':$page;
			$linkStr="/".$GLOBALS['cfg_cmspath'].$GLOBALS['cfg_album_name']."/index".$pageStr.$GLOBALS['cfg_filesuffix2'];
			break;
	}
	return $linkStr;
}


function getSubStrByFromAndEnd($str,$startStr,$endStr,$operType){
	switch ($operType) {
		case "start":
		$location1=strpos($str,$startStr)+strlen($startStr);$location2=strlen($str)+1;
		break;
		case "end":
		$location1=1;$location2=strpos($str,$endStr,$location1);
		break;
		default:
		$location1=strpos($str,$startStr)+strlen($startStr);$location2=strpos($str,$endStr,$location1);
	}
	$location3=$location2-$location1;
	$getSubStrByFromAndEnd=cn_substr($str,$location3,$location1);
	return $getSubStrByFromAndEnd;
}

function getSubStrByFromAndEnd_en($str,$startStr,$endStr,$operType){

	$location1=strpos($str,$startStr)+strlen($startStr);$location2=strpos($str,$endStr,$location1);
	$location3=$location2-$location1;
	$getSubStrByFromAndEnd=substr($str,$location1,$location3);
	return $getSubStrByFromAndEnd;
}

function getPlayurlArray($playurl){
	$span1="$$$";
	if(empty($playurl)) $playurl = '';
	$getPlayurlArray=explode($span1,$playurl);
	return $getPlayurlArray;
}



function getPlayerKindsArray(){
	$PlayerKindsArray=array();
	$m_file = sea_DATA."/admin/playerKinds.xml";
	$xml = simplexml_load_file($m_file);
	if(!$xml){$xml = simplexml_load_string(file_get_contents($m_file));}
	foreach($xml as $player){
		$conv = stripslashes($player['flag']);
		$PlayerKindsArray[$conv]['des']='';
		$PlayerKindsArray[$conv]['intro']=stripslashes($player->intro);
		$PlayerKindsArray[$conv]['open']=$player['open'];	
		$PlayerKindsArray[$conv]['postfix']=$player['postfix'];
		$PlayerKindsArray[$conv]['sort']=$player['sort'];
		
		}
	return $PlayerKindsArray;


/*	foreach($allPlayerKinds as $v)
	{
		$v = trim($v);
		if($v!="")
		{
		$vstr = explode("|",$v);
		if(count($vstr)>0){
		$PlayerKindsArray[$vstr[0]]['des']=$vstr[1];
		$PlayerKindsArray[$vstr[0]]['intro']=$vstr[2];
		$PlayerKindsArray[$vstr[0]]['open']=$vstr[3];
		}
		}
	}
*/	
}

function getPlayerKindsArray2(){
	$PlayerKindsArray=array();
	$m_file = "../data/admin/playerKinds.xml";
	$xml = simplexml_load_file($m_file);
	if(!$xml){$xml = simplexml_load_string(file_get_contents($m_file));}
	foreach($xml as $player){
		$name = stripslashes($player['flag']);
		$open = stripslashes($player['open']);
		$PlayerKindsArray[$name][]=$open;
		
		}
	return $PlayerKindsArray;
}

function getPlayerIntroArray()
{
	$temp=array();
	$m_file = sea_DATA."/admin/playerKinds.xml";
	
	$xml = simplexml_load_file($m_file);
	if(!$xml){$xml = simplexml_load_string(file_get_contents($m_file));}
	$i=0;
	foreach($xml as $player){
			
		$temp[$i]['flag']=stripslashes($player['flag']);
		$temp[$i]['open']=intval($player['open']);
		$temp[$i]['sort']=intval($player['sort']);
		$i+=1;
			
	
	}
	if($GLOBALS['cfg_isfromsort']=='1')
	{
		$l=count($temp);
		for($i=0;$i<=$l;$i++)
		{
			for($j=($i+1);$j<=$l;$j++)
			{
				if($temp[$i]['sort'] < $temp[$j]['sort'])
				{
					$tmp=$temp[$j];$temp[$j]=$temp[$i];$temp[$i]=$tmp;
				}
			}
		}
	}
	return $temp;
}

function getDownIntroArray()
{
	$temp=array();
	$m_file = sea_DATA."/admin/downKinds.xml";
	
	$xml = simplexml_load_file($m_file);
	if(!$xml){$xml = simplexml_load_string(file_get_contents($m_file));}
	$i=0;
	foreach($xml as $player){
			
		$temp[$i]['flag']=stripslashes($player['flag']);
		$temp[$i]['open']=$player['open'];
		$temp[$i]['sort']=$player['sort'];
		$i+=1;
			
	
	}
/*	$l=count($temp);
	for($i=0;$i<=$l;$i++)
	{
		for($j=($i+1);$j<=$l;$j++)
		{
			if($temp[$i]['sort'] < $temp[$j]['sort'])
			{
				$tmp=$temp[$j];$temp[$j]=$temp[$i];$temp[$i]=$tmp;
			}
		}
	}*/
	return $temp;
}

function getArrayElementID($parray,$itemid,$compareValue)
{
	foreach($parray as $k=>$v)
	{
		if(trim($v[$itemid])==trim($compareValue)){
			$getArrayElementID=$k;
			return $getArrayElementID;
		}
	}
	return $getArrayElementID;
}

function getPlayerIntro($flag)
{
	$playerArray=getPlayerIntroArray();
	$id=getArrayElementID($playerArray,"flag",$flag);
	$getPlayerIntro=$playerArray[$id]["flag"];
	return $getPlayerIntro;
}

function getPlayerParas()
{
	if($GLOBALS['cfg_runmode']=='0') $fileSuffix=$GLOBALS['cfg_filesuffix2'];
	if($GLOBALS['cfg_runmode']=='1') $fileSuffix=$GLOBALS['cfg_filesuffix2'];
	if($GLOBALS['cfg_runmode']=='2') $fileSuffix=$GLOBALS['cfg_filesuffix2'];
	$paras=str_replace($fileSuffix,'',$_SERVER['QUERY_STRING']);
	if(strpos($paras,"-")>0){
		$parasArray=explode("-",$paras);
		if(count($parasArray>1)){
			$getPlayerParas[0]=$parasArray[1];
			$getPlayerParas[1]=$parasArray[2];
		}else{
			$getPlayerParas[0]=-1;
			$getPlayerParas[1]=-1;
		}
	}else{
			$getPlayerParas[0]=-1;
			$getPlayerParas[1]=-1;
	}
	return $getPlayerParas;
}

function getPlayUrlList($ifrom,$url,$typeid,$vId,$starget,$sdate,$enname,$listyle='li'){
	global $cfg_isalertwin,$dsql;
	$row=$dsql->GetOne("SELECT v_vip FROM sea_data where v_id=".$vId);
	$vip=$row['v_vip'];

	$paras=getPlayerParas();
	if(empty($url)) return '';
	if($starget!=""){
		$target=" target=\"".$starget."\"";
	}else{
		$target=" target=\"_blank\"";
	}
	$urlArray=explode("#",$url);
	$urlCount=count($urlArray);
	
	if(strpos($vip,'s')!==false)
	{
		$vips=str_ireplace('s', "", $vip);
		$viparr=array_flip(array_slice($urlArray,0,$vips,true));
	}
	elseif(strpos($vip,'e')!==false)
	{
		$vipe=str_ireplace('e', "", $vip);
		$vipes=$urlCount - $vipe;
		$viparr=array_flip(array_slice($urlArray,$vipes,$vipe,true));		
	}
	elseif(strpos($vip,'a')!==false)
		{
			$viparr=array_flip(array_slice($urlArray,0,$urlCount,true));		
		}
	elseif(strpos($vip,'f')!==false)
	{
		$vips=str_ireplace('f', "", $vip);
		$viparr=array_flip(array_slice($urlArray,$vips,NULL,true));
	}
	else
	{
		$viparr2=explode(',',$vip);
		foreach ($viparr2 as $value) 
		{
		  $viparr[]=$value-1;
		}
	}
	//print_r($viparr);die;
	
	
	$urlStr="";
	for($i=0;$i<=$urlCount;$i++){
		if(!empty($urlArray[$i])){
			$singleUrlArray=explode("$",$urlArray[$i]);
			if (count($singleUrlArray)<2) $singleUrlArray=Array("","","");
			$style=' class="';
			if(in_array($i,$viparr)){$style.="playvip";} else {$style.="";}
			if($paras[0]==$ifrom && $i==$paras[1]) $style.=" playon\""; else $style.="\"";			
			if($style==' class=""' OR $style==' class=" "'){$style='';}
			if ($cfg_isalertwin){
				$urlStr.="<".$listyle."><a title='".$singleUrlArray[0]."' href=\"javascript:openWin('".getPlayLink2($typeid,$vId,$sdate,$enname,$ifrom,$i)."',".($GLOBALS['cfg_alertwinw']).",".($GLOBALS['cfg_alertwinh']).",250,100,1)\"".$style.">".$singleUrlArray[0]."</a></".$listyle.">";
			}else{
				$urlStr.="<".$listyle.$style." id=\"".$ifrom.$i."\"><a title=\"".$singleUrlArray[0]."\" href=\"".getPlayLink2($typeid,$vId,$sdate,$enname,$ifrom,$i)."\"".$target.">".$singleUrlArray[0]."</a></".$listyle.">";
			}
		}
	}
	//$urlStr.="</ul>";
	return $urlStr;
}

function getPlayUrlList2($ifrom,$url,$typeid,$vId,$starget,$sdate,$enname,$listyle='li'){
	global $cfg_isalertwin;
	$paras=getPlayerParas();
	if(empty($url)) return '';
	if($starget!=""){
		$target=" target=\"".$starget."\"";
	}else{
		$target=" target=\"_blank\"";
	}
	$urlArray=explode("#",$url);
	$urlCount=count($urlArray);
	$urlStr="";
	for($i=0;$i<=$urlCount;$i++){
		if(!empty($urlArray[$i])){
			$singleUrlArray=explode("$",$urlArray[$i]);
			if (count($singleUrlArray)<2) $singleUrlArray=Array("","","");
			if($paras[0]==$ifrom && $i==$paras[1]) $style=" style=\"color:red\""; else $style="";
			if ($cfg_isalertwin){
				$urlStr.=$singleUrlArray[1]."' href=\"javascript:openWin('".getPlayLink2($typeid,$vId,$sdate,$enname,$ifrom,$i)."',".($GLOBALS['cfg_alertwinw']).",".($GLOBALS['cfg_alertwinh']).",250,100,1)\"".$style.">".$singleUrlArray[1]."</a></".$listyle.">";
			}else{
				$urlStr.="<".$listyle."><a title='".$singleUrlArray[1]."' href='".getPlayLink2($typeid,$vId,$sdate,$enname,$ifrom,$i)."'".$style.$target.">".$singleUrlArray[1]."</a></".$listyle.">";
			}
		}
	}
	//$urlStr.="</ul>";
	return $urlStr;
}

function getPlayUrlList3($ifrom,$url,$typeid,$vId,$starget,$sdate,$enname,$listyle='li'){
	global $cfg_isalertwin;
	$paras=getPlayerParas();
	if(empty($url)) return '';
	if($starget!=""){
		$target=" target=\"".$starget."\"";
	}else{
		$target=" target=\"_blank\"";
	}
	$urlArray=explode("#",$url);
	$urlCount=count($urlArray);
	$urlStr="";
	for($i=0;$i<=$urlCount;$i++){
		if(!empty($urlArray[$i])){
			$singleUrlArray=explode("$",$urlArray[$i]);
			if (count($singleUrlArray)<2) $singleUrlArray=Array("","","");
			if($paras[0]==$ifrom && $i==$paras[1]) $style=" style=\"color:red\""; else $style="";
			if ($cfg_isalertwin){
				$urlStr.=$singleUrlArray[1]."' href=\"javascript:openWin('".getPlayLink2($typeid,$vId,$sdate,$enname,$ifrom,$i)."',".($GLOBALS['cfg_alertwinw']).",".($GLOBALS['cfg_alertwinh']).",250,100,1)\"".$style.">".$singleUrlArray[1]."</a></".$listyle.">";
			}else{
				$urlStr.="<".$listyle.">".$singleUrlArray[1]."</".$listyle.">";
			}
		}
	}
	//$urlStr.="</ul>";
	return $urlStr;
}


function getDownUrlList($url,$starget,$listyle='li',$linkstr=false){
	global $cfg_isalertwin;
	$listyle='li';
	if(empty($url)) return '';
	if($starget!=""){
		$target=" target=\"".$starget."\"";
	}else{
		$target=" target=\"_blank\"";
	}
	$urlArray=explode("#",$url);
	$urlCount=count($urlArray);
	$urlStr="";
	for($i=0;$i<=$urlCount;$i++){
		if(!empty($urlArray[$i])){
			$singleUrlArray=explode("$",$urlArray[$i]);
			$urlStr.="<".$listyle."><a title='".$singleUrlArray[0]."' href='".$singleUrlArray[1]."'".$target.">".($linkstr?$singleUrlArray[1]:$singleUrlArray[0])."</a></".$listyle.">";
		}
	}
	return $urlStr;
}

function getDownUrlList2($url,$starget,$listyle='li',$k,$linkstr=false){
	global $cfg_isalertwin;
	if(empty($url)) return '';
	if($starget!=""){
		$target=" target=\"".$starget."\"";
	}else{
		$target=" target=\"_blank\"";
	}
	$urlArray=explode("#",$url);
	$urlCount=count($urlArray);
	$urlStr="";
	for($i=0;$i<=$urlCount;$i++){
		if(!empty($urlArray[$i])){
			$singleUrlArray=explode("$",$urlArray[$i]);
			$urlStr.=$singleUrlArray[0]."$".$singleUrlArray[1]."###";
			$urlStr2=rtrim($urlStr,"###");
		}
	}
	//$urlStr.="</ul>";
	return $urlStr2;
}





function GetTruePath()
{
	$truepath = $GLOBALS["cfg_basedir"];
	return $truepath;
}

function escape($str){
	preg_match_all("/[\x80-\xff].|[\x01-\x7f]+/",$str,$r);
	$ar = $r[0];
	foreach($ar as $k=>$v) {
		if(ord($v[0]) < 128)
		  $ar[$k] = rawurlencode($v);
		else
		  $ar[$k] = "%u".bin2hex(iconv("GB2312","UCS-2",$v));
	}
	return join("",$ar);
}



function unescape($str) { 

 if(function_exists(mb_convert_encoding))
 {
 $str = rawurldecode($str); 
 preg_match_all("/%u.{4}|&#x.{4};|&#d+;|.+/U",$str,$r); 
 $ar = $r[0]; 
 foreach($ar as $k=>$v) { 
  if(substr($v,0,2) == "%u") 
   $ar[$k] = mb_convert_encoding(pack("H4",substr($v,-4)),"utf-8","UCS-2");
  elseif(substr($v,0,3) == "&#x") 
   $ar[$k] = mb_convert_encoding(pack("H4",substr($v,3,-1)),"utf-8","UCS-2");
  elseif(substr($v,0,2) == "&#") { 
   $ar[$k] = mb_convert_encoding(pack("H4",substr($v,2,-1)),"utf-8","UCS-2");
  } 
 }
 }
  else
  {
  
  $str = rawurldecode($str);
  preg_match_all("/(?:%u.{4})|.+/U",$str,$r);
  $ar = $r[0];
  foreach($ar as $k=>$v) {
    if(substr($v,0,2) == "%u" && strlen($v) == 6)
      $ar[$k] = iconv("UCS-2","utf-8",pack("H4",substr($v,-4)));
  }}
 
  
 return join("",$ar); 
}

function getKeywordsList($key,$span){
	if($key=='')return $key;
	$keyWordsStr="";
	$keystr=str_replace("，",",",$key);
	if (strpos($keystr,",")>0){$keyWordsArray=explode(",",$keystr);}else{$keyWordsArray=explode(" ",$keystr);}
	for($kli=0;$kli<count($keyWordsArray);$kli++){
		$keyWordsStr.="<a href='/".$GLOBALS['cfg_cmspath']."search.php?searchword=".urlencode($keyWordsArray[$kli])."'>".$keyWordsArray[$kli]."</a>".$span;
	}
	return $keyWordsStr;
}

function getnewsKeywordsList($key,$span){
	if($key=='')return $key;
	$keyWordsStr="";
	$keystr=str_replace("，",",",$key);
	if (strpos($keystr,",")>0){$keyWordsArray=explode(",",$keystr);}else{$keyWordsArray=explode(" ",$keystr);}
	for($kli=0;$kli<count($keyWordsArray);$kli++){
		$keyWordsStr.="<a href='/".$GLOBALS['cfg_cmspath']."so.php?searchword=".urlencode($keyWordsArray[$kli])."'>".$keyWordsArray[$kli]."</a>".$span;
	}
	return $keyWordsStr;
}

function getJqList($key,$span){
	if($key=='')return $key;
	$keyWordsStr="";
	$keystr=str_replace("，",",",$key);
	if (strpos($keystr,",")>0){$keyWordsArray=explode(",",$keystr);}else{$keyWordsArray=explode(" ",$keystr);}
	for($kli=0;$kli<count($keyWordsArray);$kli++){
		$keyWordsStr.="<a href='/".$GLOBALS['cfg_cmspath']."search.php?searchtype=5&jq=".urlencode($keyWordsArray[$kli])."'>".$keyWordsArray[$kli]."</a>".$span;
	}
	return $keyWordsStr;
}

function getTagsList($key,$span){
	$keyWordsStr="";
	$keystr=str_replace("，",",",$key);
	if (strpos($keystr,",")>0){$keyWordsArray=explode(",",$keystr);}else{$keyWordsArray=explode(" ",$keystr);}
	for($kli=0;$kli<count($keyWordsArray);$kli++){
		$keyWordsStr.="<a href='".gettaglink($keyWordsArray[$kli])."'>".$keyWordsArray[$kli]."</a>".$span;
	}
	return $keyWordsStr;
}

function parseLabelHaveLen($content,$str,$label){
	$labelHaveLen = buildregx("{playpage:".$label."\s+len=(\d+)?\s*}","is");
	preg_match_all($labelHaveLen,$content,$labelHaveLenar);
	$HaveLenarcount=count($labelHaveLenar[0]);
	if($HaveLenarcount){
		for($hm=0;$hm<$HaveLenarcount;$hm++){
			$strLen=$labelHaveLenar[1][$hm];
			if ($label=="actor"){
				$strByLen=getKeywordsList(trimmed_title($str,$strLen),"&nbsp;&nbsp;");
			}else{
				$strByLen=trimmed_title($str,$strLen);
			}
			$content=str_replace($labelHaveLenar[0][$hm],$strByLen,$content);
			
		}
		return $content;
	}else{
		return $content;
	}	
}

function parseNewsLabelHaveLen($content,$str,$label){
	$labelHaveLen = buildregx("{news:".$label."\s+len=(\d+)?\s*}","is");
	preg_match_all($labelHaveLen,$content,$labelHaveLenar);
	$HaveLenarcount=count($labelHaveLenar[0]);
	if($HaveLenarcount){
		for($hm=0;$hm<$HaveLenarcount;$hm++){
			$strLen=$labelHaveLenar[1][$hm];
			$strByLen=cn_substr($str,$strLen);
			$content=str_replace($labelHaveLenar[0][$hm],$strByLen,$content);
		}
		return $content;
	}else{
		return $content;
	}	
}

//截取字数
function trimmed_title($text, $limit=12) {
	if ($limit) {
		$val = csubstr($text, 0, $limit-1);
		return $val[1] ? $val[0].".." : $val[0];
//		return cnSubStr($text, $limit)."...";
	} else {
		return $text;
	}
}

function cnSubStr($string,$sublen) 
{ 
	if($sublen>=strlen($string)) 
	{ 
		return $string; 
	} 
	$s=""; 
	for($i=0;$i<$sublen;$i++) 
	{ 
		if(ord($string{$i})>127) 
		{ 
			$s.=$string{$i}.$string{++$i}; 
			continue; 
		}else{ 
			$s.=$string{$i}; 
			continue; 
		} 
	} 
	return $s; 
}

function csubstr($text, $start=0, $limit=12) {
	if (function_exists('mb_substr')) {
		$more = (mb_strlen($text, 'utf-8') > $limit) ? TRUE : FALSE;
		$text = mb_substr($text, 0, $limit, 'utf-8');
		return array($text, $more);
	} elseif (function_exists('iconv_substr')) {
		$more = (iconv_strlen($text) > $limit) ? TRUE : FALSE;
		$text = iconv_substr($text, 0, $limit, 'utf-8');
		return array($text, $more);
	} else {
		preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);   
		if(func_num_args() >= 3) {   
			if (count($ar[0])>$limit) {
				$more = TRUE;
				$text = join("",array_slice($ar[0],0,$limit)).".."; 
			} else {
				$more = FALSE;
				$text = join("",array_slice($ar[0],0,$limit)); 
			}
		} else {
			$more = FALSE;
			$text =  join("",array_slice($ar[0],0)); 
		}
		return array($text, $more);
	} 
}

function setCache($p_cacheName,$sql="",$arr=""){
	global $dsql,$cfg_iscache,$cfg_cachetime,$cfg_cachemark;
	$cacheFile=sea_ROOT.'/data/cache/'.$cfg_cachemark.$p_cacheName.'.inc';
	$mintime = time() - $cfg_cachetime*60;
	if(!file_exists($cacheFile) || ( file_exists($cacheFile) && ($mintime > filemtime($cacheFile)))){
		if (!empty($sql)){
		$dsql->SetQuery($sql);
		$dsql->Execute('hw');
		$cacher=array();
			while($cache=$dsql->GetObject('hw'))
			{
			$cacher[]=$cache;
			}
		}else{
			$cacher=$arr;
		}
		if (!empty($p_cacheName)){
			$fp = fopen($cacheFile,'w') or die("Write Cache File Error! ");
			fwrite($fp,serialize($cacher));
			fclose($fp);
		}
		unset($cacher);
	}
}

function getCache($p_cacheName){
	global $cfg_cachemark;
	$cacheFile=sea_ROOT.'/data/cache/'.$cfg_cachemark.$p_cacheName.'.inc';
	if(file_exists($cacheFile)){
		return unserialize(file_get_contents($cacheFile));
	}else{
		return array();
	}
}

//处理禁用HTML但允许换行的内容
function TrimMsg($msg)
{
	$msg = trim(stripslashes($msg));
	$msg = nl2br(htmlspecialchars($msg));
	$msg = str_replace("  ","&nbsp;&nbsp;",$msg);
	return addslashes($msg);
}

function showFace($message)   
{   
	$message=preg_replace("/\[ps:([0-9]{1,2})\]/is","<img src=\"/".$GLOBALS['cfg_cmspath']."pic/faces/\\1.gif\" border=\"0\"/>",$message);
	return $message;
}

function lib_hotwords($num=5)
{
	global $cfg_phpurl,$dsql;
	$nowtime = time();
	if(empty($subday)) $subday = 365;
	if(empty($num)) $num = 10;
	if(empty($maxlength)) $maxlength = 20;
	$maxlength = $maxlength+1;
	$mintime = $nowtime - ($subday * 24 * 3600);
	$dsql->SetQuery("Select keyword From `sea_search_keywords` where lasttime>$mintime And length(keyword)<$maxlength order by count desc limit 0,$num");
	$dsql->Execute('hw');
	$hotword = '';
	$i=1;
	while($row=$dsql->GetArray('hw')){	
		if($i>$num)break;
		$hotword .= "<a href='".$cfg_phpurl."search.php?searchword=".urlencode($row['keyword'])."'>".$row['keyword']."</a> ";
		$i++;
	}
	return $hotword;
}

function site_keywords()
{
	global $cfg_phpurl,$dsql,$cfg_sitekeywords;
	$hotword='';
	$siteKeyArr = explode('|',trim($cfg_sitekeywords));
	foreach ($siteKeyArr as $siteKey){
	$hotword .= "<a href='".$cfg_phpurl."search.php?searchword=".urlencode($siteKey)."'>".$siteKey."</a> ";
	}
	return $hotword;
}

function front_member()
{
	global $cfg_user;
	if($cfg_user==0){
	$member = '';
	}
	else{
	$member = '<span id="seacms_member"></span><script>member()</script>';
	}
	return $member;
}

//过滤用于搜索的字符串
function FilterSearch($keyword)
{
	global $cfg_soft_lang;
	if($cfg_soft_lang=='utf-8')
	{
		$keywords = m_ereg_replace("[ \"\r\n\t\$\\><']",'',$keyword);
		if($keywords != stripslashes($keywords))
		{
			return '';
		}
		else
		{
			return $keyword;
		}
	}
	else
	{
		$restr = '';
		for($i=0;isset($keyword[$i]);$i++)
		{
			if(ord($keyword[$i]) > 0x80)
			{
				if(isset($keyword[$i+1]) && ord($keyword[$i+1]) > 0x40)
				{
					$restr .= $keyword[$i].$keyword[$i+1];
					$i++;
				}
				else
				{
					$restr .= ' ';
				}
			}
			else
			{
				if(m_eregi("[^0-9a-z@#\.]",$keyword[$i]))
				{
					$restr .= ' ';
				}
				else
				{
					$restr .= $keyword[$i];
				}
			}
		}
	}
	return $restr;
}

function RemoveXSS($val) {  
   // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed  
   // this prevents some character re-spacing such as <java\0script>  
   // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs  
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);  
     
   // straight replacements, the user should never need these since they're normal characters  
   // this prevents like <IMG SRC=@avascript:alert('XSS')>  
   $search = 'abcdefghijklmnopqrstuvwxyz'; 
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
   $search .= '1234567890!@#$%^&*()'; 
   $search .= '~`";:?+/={}[]-_|\'\\'; 
   for ($i = 0; $i < strlen($search); $i++) { 
      // ;? matches the ;, which is optional 
      // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
    
      // @ @ search for the hex values 
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 
      // @ @ 0{0,7} matches '0' zero to seven times  
      $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
   } 
    
   // now the only remaining whitespace attacks are \t, \n, and \r 

   $ra1 = Array('_GET','_POST','_COOKIE','_REQUEST','if:','javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base', 'eval', 'passthru', 'exec', 'assert', 'system', 'chroot', 'chgrp', 'chown', 'shell_exec', 'proc_open', 'ini_restore', 'dl', 'readlink', 'symlink', 'popen', 'stream_socket_server', 'pfsockopen', 'putenv', 'cmd','base64_decode','fopen','fputs','replace','input','contents'); 
   $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
   $ra = array_merge($ra1, $ra2); 
    
   $found = true; // keep replacing as long as the previous round replaced something 
   while ($found == true) { 
      $val_before = $val; 
      for ($i = 0; $i < sizeof($ra); $i++) { 
         $pattern = '/'; 
         for ($j = 0; $j < strlen($ra[$i]); $j++) { 
            if ($j > 0) { 
               $pattern .= '(';  
               $pattern .= '(&#[xX]0{0,8}([9ab]);)'; 
               $pattern .= '|';  
               $pattern .= '|(&#0{0,8}([9|10|13]);)'; 
               $pattern .= ')*'; 
            } 
            $pattern .= $ra[$i][$j]; 
         } 
         $pattern .= '/i';  
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag  
         $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
         if ($val_before == $val) {  
            // no replacements were made, so exit the loop  
            $found = false;  
         }  
      }  
   } 

   return $val;  
}   

function getDataCount($countType){
	global $dsql;
	$todayStart = strtotime("today");
	$todayEnd = $todayStart + 86400;
	$whereStr=" where v_addtime >= $todayStart and v_addtime < $todayEnd";
	switch ($countType) {
		case "all":
		$row = $dsql->GetOne("select count(*) as dd From `sea_data`");
		$DataCount=$row['dd'];
		break;
		case "day":
		$row = $dsql->GetOne("select count(*) as dd From `sea_data` $whereStr");
		$DataCount=$row['dd'];
		break;
	}
	return $DataCount;
}

function getNewsDataCount($countType){
	global $dsql;
	$todayStart = strtotime("today");
	$todayEnd = $todayStart + 86400;
	$whereStr=" where n_addtime >= $todayStart and n_addtime < $todayEnd";
	switch ($countType) {
		case "all":
		$row = $dsql->GetOne("select count(*) as dd From `sea_news`");
		$DataCount=$row['dd'];
		break;
		case "day":
		$row = $dsql->GetOne("select count(*) as dd From `sea_news` $whereStr");
		$DataCount=$row['dd'];
		break;
	}
	return $DataCount;
}

function moveFolder($oldFolder,$newFolder){
	if($oldFolder != $newFolder){
		$voldFolder='../'.$oldFolder;
		$vnewFolder='../'.$newFolder;
		if(!file_exists($vnewFolder) && file_exists($voldFolder)){
			rename($voldFolder,$vnewFolder);
		}
	}
}

function isCurrentDay($timeStr){
	if(empty($timeStr)) return "";
	if(GetDateMk($timeStr)==GetDateMk(time())){
		return "<span style='color:red;font-size:10px'>".MyDate('Y-m-d H:i:s',$timeStr)."</span>";
	}else{
		return "<span style='font-size:10px'>".MyDate('Y-m-d H:i:s',$timeStr)."</span>";
	}
}

function jstrim($str,$len)
{
	$str = preg_replace("/{quote}(.*){\/quote}/is",'',$str);
	$str = str_replace('&lt;br/&gt;',' ',$str);
	$str = cn_substr($str,$len);
	$str = m_ereg_replace("['\"\r\n]","",$str);
	return $str;
}

//文本转HTML
function Text2Html($txt)
{
	$txt = str_replace("  ","　",$txt);
	$txt = str_replace("<","&lt;",$txt);
	$txt = str_replace(">","&gt;",$txt);
	$txt = preg_replace("/[\r\n]{1,}/isU","<br/>\r\n",$txt);
	return $txt;
}

function GetAlabNum($fnum)
{
	$nums = array("０","１","２","３","４","５","６","７","８","９");
	//$fnums = "0123456789";
	$fnums = array("0","1","2","3","4","5","6","7","8","9");
	$fnum = str_replace($nums,$fnums,$fnum);
	$fnum = m_ereg_replace("[^0-9\.-]",'',$fnum);
	if($fnum=='')
	{
		$fnum=0;
	}
	return $fnum;
}

function getTypeListsOnCache($type=0)
{
	global $cfg_iscache;
	static $gtypelist0;
	static $gtypelist1;
	$cacheName="obj_get_type_list_".$type;
	if(!is_array(${'gtypelist'.$type}))
	{
		if($cfg_iscache){
			if (chkFileCache($cacheName)){${'gtypelist'.$type}=unserialize(getFileCache($cacheName));}else{${'gtypelist'.$type}=getTypeLists($type);setFileCache($cacheName,serialize(${'gtypelist'.$type}));}	
		}else{
			${'gtypelist'.$type}=getTypeLists($type);
		}
	}
	return ${'gtypelist'.$type};
}

function getjqTypeListsOnCache($type=0)
{
	global $cfg_iscache;
	static $gjqtypelist0;
	static $gjqtypelist1;
	$cacheName="obj_get_jqtype_list_".$type;
	if(!is_array(${'gjqtypelist'.$type}))
	{
		if($cfg_iscache){
			if (chkFileCache($cacheName)){${'gjqtypelist'.$type}=unserialize(getFileCache($cacheName));}else{${'gjqtypelist'.$type}=getjqTypeLists($type);setFileCache($cacheName,serialize(${'gjqtypelist'.$type}));}	
		}else{
			${'gjqtypelist'.$type}=getjqTypeLists($type);
		}
	}
	return ${'gjqtypelist'.$type};
}



function getTypeLists($type=0)
{
	global $dsql,$cfg_iscache;
	$sql="select tid,upid,tname,tenname,torder,templist,templist_1,templist_2,keyword,description,ishidden,unionid,tptype,title,-1 as tcount from sea_type where tptype = '$type' order by torder asc";
	$rows=array();
	$dsql->SetQuery($sql);
	$dsql->Execute('al');
	while($rowr=$dsql->GetObject('al'))
	{
	$rows[]=$rowr;
	}
	unset($rowr);
	return $rows;
}

function getjqTypeLists($type=0)
{
	global $dsql,$cfg_iscache;
	$sql="select tid,upid,tname,ishidden,-1 as tcount from sea_jqtype order by upid asc";
	$rows=array();
	$dsql->SetQuery($sql);
	$dsql->Execute('al');
	while($rowr=$dsql->GetObject('al'))
	{
	$rows[]=$rowr;
	}
	unset($rowr);
	return $rows;
}

function getTypeTitle($id,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			return $row->title;
		}	
	}
}

function getTypeKeywords($id,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			return $row->keyword;
		}	
	}
}

function getNewsTypeKeywords($id,$tptype=1)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			return $row->keyword;
		}	
	}
}

function getNewsTypeDescription($id,$tptype=1)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			return $row->description;
		}	
	}
}

function getTypeDescription($id,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			return $row->description;
		}	
	}
}


function makeTypeOption($topId,$separateStr,$tptype=0,$span="")
{
	$tlist = getTypeListsOnCache($tptype);
	if ($topId!=0){$span.=$separateStr;}else{$span="";}
	foreach($tlist as $row)
	{
		if($row->upid==$topId)
		{
			if($row->tptype==$tptype)
			{
				echo "<option value='".$row->tid."'>".$span."&nbsp;|—".$row->tname."</option>";
				makeTypeOption($row->tid,$separateStr,$tptype,$span);
			}
		}
	}
	if (!empty($span)){$span=substr($span,(strlen($span)-strlen($separateStr)));}
}

function makeTypeOption2($topId,$separateStr,$tptype=0,$span="")
{
	$tlist = getTypeListsOnCache($tptype);
	$span="";
	foreach($tlist as $row)
	{
		if($row->upid==$topId)
		{
			if($row->tptype==$tptype)
			{
				echo "<input name=v_type_extra[] type=checkbox value=".$row->tid.">".$row->tname."&nbsp;&nbsp;";
				makeTypeOption2($row->tid,$separateStr,$tptype,$span);
			}
		}
	}
	if (!empty($span)){$span=substr($span,(strlen($span)-strlen($separateStr)));}
}

function makeTypeOption3($topId,$separateStr,$tptype=0,$span="")
{
	$tlist = getjqTypeListsOnCache($tptype);
	$span="";
	foreach($tlist as $row)
	{
		if($row->upid==$topId)
		{
			if($row->tptype==$tptype)
			{
				echo "<input name=v_jqtype_extra[] type=checkbox value=".$row->tname.">".$row->tname."&nbsp;&nbsp;";
				makeTypeOption3($row->tid,$separateStr,$tptype,$span);
			}
		}
	}
	if (!empty($span)){$span=substr($span,(strlen($span)-strlen($separateStr)));}
}

function makeTypeOption4($topId,$separateStr,$tptype=0,$span="")
{
	$tlist = getjqTypeListsOnCache($tptype);
	$span="";
	foreach($tlist as $row)
	{
		
			if($row->tptype==$tptype)
			{
				echo "<input name=v_jqtype_extra[] type=checkbox value=".$row->tname.">".$row->tname."&nbsp;&nbsp;";
				
			}
		
	}
	if (!empty($span)){$span=substr($span,(strlen($span)-strlen($separateStr)));}
}

function getTypeId($id,$tptype=0)
{
	$ret="";
	$tlist=getTypeListsOnCache($tptype);
	if (intval($id)>0) $ret=$id;
	foreach($tlist as $row)
	{
		if($row->upid==$id)
		{
			if($ret=="")
			{
				$ret=getTypeId($row->tid,$tptype);
			}else{
				$ret.=",".getTypeId($row->tid,$tptype);
			}
		}
	}
	return $ret;
}

function getTypeIdOnCache($id,$tptype=0){
        global $cfg_iscache;
	    $cacheName="str_get_subtypes_type".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$typeid=getFileCache($cacheName);}else{$typeid=getTypeId($id,$tptype);setFileCache($cacheName,$typeid);}
		}else{
			$typeid=getTypeId($id,$tptype);
		}
	   return $typeid;
}

function getUpId($id,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			return $row->upid;
		}
	}
}

function getHideTypeIDS($tptype=0)
{
	$ret="";
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->ishidden==1)
		{
			if($ret=="")
			{
				$ret=$row->tid;
			}else{
				$ret.=",".$row->tid;
			}
		}
	}
	return $ret;
}

function getNumPerTypeOfNewsOnCache($id,$type=0){
	$cacheName="str_get_num_pertype_news_".$id;
	if($cfg_iscache){
		if (chkFileCache($cacheName))
		{
			$num=getFileCache($cacheName);
		}else
		{
			$num=getNumPerTypeOfNews($id);setFileCache($cacheName,$num);
		}
	}else
	{
		$num=getNumPerTypeOfNews($id);
	}
   return $num;
}

function getNumPerTypeOfNews($id)
{
	global $dsql;
	$tlist=getTypeListsOnCache(1);
	$ids = getTypeIdOnCache($id,1);
	$sql = "SELECT count(n_id) as dd FROM `sea_news` where tid in (".$ids.")";
	$row=$dsql->GetOne($sql);
	if(is_array($row))
	{
		$num=$row['dd'];
	}
	else
	{
		$num=0;
	}
	return $num;

}


function getNumPerTypeOnCache($id,$type=0){
	$cacheName="str_get_num_pertype".$id;
	if($cfg_iscache){
		if (chkFileCache($cacheName))
		{
			$num=getFileCache($cacheName);
		}else
		{
			$num=getNumPerType($id);setFileCache($cacheName,$num);
		}
	}else
	{
		$num=getNumPerType($id);
	}
   return $num;
}

function getNumPerjqTypeOnCache($id,$type=0){
	$cacheName="str_get_num_perjqtype".$id;
	if($cfg_iscache){
		if (chkFileCache($cacheName))
		{
			$num=getFileCache($cacheName);
		}else
		{
			$num=getNumPerjqType($id);setFileCache($cacheName,$num);
		}
	}else
	{
		$num=getNumPerjqType($id);
	}
   return $num;
}


function getNumPerType($id)
{
	global $dsql;
	$num = 0;
	$tlist = getTypeListsOnCache();
	$ids = getTypeIdOnCache($id);
	$row=$dsql->GetOne("SELECT count(v_id) as dd FROM `sea_data` where tid in (".$ids.")");
	if(is_array($row))
	{
		$num=$row['dd'];
	}
	else
	{
		$num=0;
	}	
	return $num;
}

function getNumPerjqType($id)
{
	global $dsql;
	$num = 0;
	$tlist = getTypeListsOnCache();
	$ids = getTypeIdOnCache($id);
	$row=$dsql->GetOne("SELECT count(v_id) as dd FROM `sea_data` where v_jq like '%$ids%'");
	if(is_array($row))
	{
		$num=$row['dd'];
	}
	else
	{
		$num=0;
	}	
	return $num;
}

function getTypePathOnCache($id,$immediate=false,$type=0){
	global $cfg_iscache;
	$cacheName="str_get_curtype_dir_type".$id;
	if($cfg_iscache){
		if (chkFileCache($cacheName)){$pathStr=getFileCache($cacheName);}else{$pathStr=getTypePath($id,$immediate,$type);setFileCache($cacheName,$pathStr);}
	}else{
		$pathStr=getTypePath($id,$immediate,$type);
	}
   return $pathStr;
}

function getTypePath($id,$immediate=false,$type=0)
{
	$tlist=getTypeListsOnCache($type);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			if($row->upid==0||$immediate)
			{
				$getTypePath=$row->tenname."/";
				return $getTypePath;
			}
			else
			{
				$getTypePath=getTypePathOnCache($row->upid,$immediate,$type).$row->tenname."/";
			}
		}
	}
	return $getTypePath;
}



function delFile($filename)
{
	if (file_exists($filename)) 
	{
		@unlink($filename); 
	}
}

function delFolder($dirName) 
{ 
    if(!is_dir($dirName))
    { 
        @unlink($dirName);
        return false; 
    } 
    $handle = @opendir($dirName); 
    while(($file = @readdir($handle)) !== false) 
    { 
        if($file != '.' && $file != '..') 
        { 
            $dir = $dirName . '/' . $file; 
            is_dir($dir) ? delFolder($dir) : @unlink($dir); 
        } 
    } 
    closedir($handle); 
    return rmdir($dirName) ; 
} 

function encodeHtml($str)
{
	if(strlen($str)==0 || trim($str)=="") return "";
		$str=str_replace("<","&lt;",$str);
		$str=str_replace(">","&gt;",$str);
		$str=str_replace(CHR(34),"&quot;",$str);
		$str=str_replace(CHR(39),"&apos;",$str);
		return $str;
}

function decodeHtml($str)
{
	if(strlen($str)==0 || trim($str)=="") return "";
		$str=str_replace("&lt;","<",$str);
		$str=str_replace("&gt;",">",$str);
		$str=str_replace("&quot;",CHR(34),$str);
		$str=str_replace("&apos;",CHR(39),$str);
		return $str;
}

function getNewsTitle($Id)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT n_title FROM sea_news WHERE n_id=$Id");
	return $row['n_title'];
}

function getNewsEnname($Id)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT n_entitle FROM sea_news WHERE n_id=$Id");
	return $row['n_entitle'];
}

function getVideoEnname($videoId)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT v_name,v_enname FROM sea_data WHERE v_id=$videoId");
	if($row['v_enname']!="")
	{
		return $row['v_enname'];
	}
	else
	{
		return Pinyin($row['v_name']);	
	}
}

function getVideoName($videoId)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT v_name FROM sea_data WHERE v_id=$videoId");
	return $row['v_name'];
}

function getVideoSdate($videoId)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT v_addtime FROM sea_data WHERE v_id=$videoId");
	return $row['v_addtime'];
}

function getNewsSdate($Id)
{
	global $dsql;
	$row = $dsql->GetOne("SELECT n_addtime FROM sea_news WHERE n_id=$Id");
	return $row['n_addtime'];
}


function getletterlist()
{
	for($i=65;$i<=90;$i++){
		$mystr.="<a href='/".$GLOBALS['cfg_cmspath']."search.php?searchtype=4&searchword=".chr($i)."'>".chr($i)."</a>";
	}
	return $mystr;
}

function getFileFormat($str)
{
	$str=trim($str);
	$ext="";
	if(!empty($str)){
		if (strpos(" ".$str,"?")>0){
			$strt=explode('?',$str);
			$str=$strt[0];
		}
		$ps=explode(".",$str);
		$ext='.'.$ps[count($ps)-1];
	}
	return $ext;
}

function cget($url,$isref){
	if($isref=='1'){return getRemoteContent($url);}else{return get($url);}
}

function curl_get_contents($url,$conall=false,$timeout = 30) {  
    $user_agent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.94 Safari/537.36";  
	$curl = curl_init();                                         //初始化 curl
    curl_setopt($curl, CURLOPT_URL, $url);                       //要访问网页 URL 地址
	curl_setopt($curl, CURLOPT_USERAGENT,$user_agent);		    //模拟用户浏览器信息 
    curl_setopt($curl, CURLOPT_REFERER,$url) ;                 //伪装网页来源 URL
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);                //当Location:重定向时，自动设置header中的Referer:信息                   
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);             //数据传输的最大允许时间 
    curl_setopt($curl, CURLOPT_HEADER, $conall);                     //不返回 header 部分
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);            //返回字符串，而非直接输出到屏幕上
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION,1);             //跟踪爬取重定向页面
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, '0');        //不检查 SSL 证书来源
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, '0');        //不检查 证书中 SSL 加密算法是否存在
	curl_setopt($curl, CURLOPT_ENCODING, '');	          //解决网页乱码问题
    $data = curl_exec($curl);
	curl_close($curl);
    return $data;
}


function getRemoteContent($url,$conall=false)
{
	$content = "";
	if(!empty($url)) { 
		if( function_exists('curl_init') ){			
              $content =curl_get_contents($url,$conall);
		}
		else if( ini_get('allow_url_fopen')==1){
			$content = file_get_contents($url);
		}
		else{
		  return false;
		}
	
	}
	
	return $content;
}



function getRemoteContentBAK($url,$conall=null)
{
	$purl = parse_url($url);
	$host = $purl['host'];
	$path = $purl['path'];
	$port = empty($purl['port']) ? 80 : $purl['port'];
	
	if (isset($purl['query']))
		$path.='?'.$purl['query'];
	$fp = fsockopen($host, $port, $errno, $errstr, 10);
	if (!$fp) {
		return false;
	} else {
		$out = "GET $path HTTP/1.1\r\n";
		$out.= "Accept: */*\r\n";
		$out.= "Accept-Language: zh-cn\r\n";
		$out.= "Referer: http://$host\r\n";
		$out.= "User-Agent: Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.2; SV1; .NET CLR 1.1.4322)\r\n";
		$out.= "Host: $host\r\n";
		$out.= "Connection: Close\r\n";
		$out.="\r\n";
	    fwrite($fp, $out);
	    while (!feof($fp)) {
	        $con.= fgets($fp, 1024);
	    }
	    fclose($fp);
	}

    if ($conall==null)
    {
		$tmp = explode("\r\n\r\n",$con,2);
		$con = $tmp[1];
    }
	return $con;
}

function get($url)
{
	return @file_get_contents($url);
}

function createStreamFile($stream,$fileDir)
{
	$fileDir=str_replace( "\\", "/",$fileDir);
	createfolder($fileDir,"filedir");
	@$f=fopen($fileDir,"wb");   
	@fwrite($f,$stream);   
	@fclose($f);
	if(file_exists($fileDir)) return true; else return false;
}

function createfolder($spath,$dirType)
{
	global $cfg_dir_purview,$isSafeMode;
	if($spath=='')
	{
		return true;
	}
	$flink = false;
	$truepath = sea_ROOT;
	$truepath = str_replace("\\","/",$truepath);
	$spath = str_replace($truepath,'',$spath);
	$spaths = explode("/",$spath);
	$spath = "";
	if($dirType=='filedir') $lenSubPathArray=count($spaths) - 1;
	if($dirType=='folderdir') $lenSubPathArray=count($spaths);
	for($i=1;$i<$lenSubPathArray;$i++){
		$spath=$spaths[$i];
		if($spath=="")
		{
			continue;
		}
		$spath = trim($spath);
		$truepath .= "/".$spath;
		if(!is_dir($truepath) || !is_writeable($truepath))
		{
			if(!is_dir($truepath))
			{
				$isok = MkdirAll($truepath,$cfg_dir_purview);
			}
			else
			{
				$isok = chmod($truepath,'0',$cfg_dir_purview);
			}
			if(!$isok)
			{
				echo "创建或修改目录：".$truepath." 失败！<br>";
				return false;
			}
		}
	}
	return true;
}

function checkRunMode()
{
	global $cfg_runmode;
	if($cfg_runmode){
		echo "<div style='width:50%;'><font color='red'>网站运行模式非静态，不允许生成</font><br><br></div>";
		exit();
	}
}

function checkNewsRunMode()
{
	global $cfg_runmode2;
	if($cfg_runmode2){
		echo "<div style='width:50%;'><font color='red'>网站运行模式非静态，不允许生成</font><br><br></div>";
		exit();
	}
}

function chkFileCache($cacheName)
{
	global $cfg_cachetime,$cfg_cachemark;
	$cacheFile=sea_ROOT.'/data/cache/'.$cfg_cachemark.$cacheName.'.inc';
	$mintime = time() - $cfg_cachetime*60;
	if(!file_exists($cacheFile) || ( file_exists($cacheFile) && ($mintime > filemtime($cacheFile)))){
		return false;
	}else{
		return true;
	}
}

function setFileCache($cacheName,$cacheValue)
{
	global $cfg_cachemark;
	$cacheFile=sea_ROOT.'/data/cache/'.$cfg_cachemark.$cacheName.'.inc';
	if($cacheName){
		$fp = fopen($cacheFile,'w') or dir("Write Cache File Error! ");
		fwrite($fp,$cacheValue);
		fclose($fp);
	}
}

function getFileCache($cacheName)
{
	global $cfg_cachemark;
	$cacheFile=sea_ROOT.'/data/cache/'.$cfg_cachemark.$cacheName.'.inc';
	if(file_exists($cacheFile)){
		@$fp = fopen($cacheFile,'r');
		@$cacheValue = fread($fp,filesize($cacheFile));
		@fclose($fp);
		return $cacheValue;
	}else{
		return "";
	}
}

function getTypeText($id,$tptype=0)
{
	$tlist=getTypeListsOnCache($tptype);
	foreach($tlist as $row)
	{
		if($row->tid==$id)
		{
			if($tptype==1)
			{
			if($row->upid==0)
			{
				$str="<a href='".getnewsxLink()."' >首页</a>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;<a href='".getnewspageLink($row->tid)."' >".$row->tname."</a>";
				return $str;
			}else{
				$str=getTypeText($row->upid,$tptype)."&nbsp;&nbsp;&raquo;&nbsp;&nbsp;<a href='".getnewspageLink($row->tid)."'>".$row->tname."</a>";
			}	
			}else{
			if($row->upid==0)
			{
				$str="<a href='".getIndexLink()."' >首页</a>&nbsp;&nbsp;&raquo;&nbsp;&nbsp;<a href='".getChannelPagesLink($row->tid)."' >".$row->tname."</a>";
				return $str;
			}else{
				$str=getTypeText($row->upid,$tptype)."&nbsp;&nbsp;&raquo;&nbsp;&nbsp;<a href='".getChannelPagesLink($row->tid)."'>".$row->tname."</a>";
			}
			}
		}
	}
	return $str;
}

function loadFile($filePath)
{
	if(!file_exists($filePath)){
		echo "模版文件读取失败!";
		exit();
	}
	$fp = @fopen($filePath,'r');
	$sourceString = @fread($fp,filesize($filePath));
	@fclose($fp);
	return $sourceString;
}

function getMenuArray($sId,$m,$type=0,$getall=false)
{
	$i=0;
	$tlist=getTypeListsOnCache($type);
	if($m=="tid") $m="tid"; else $m="upid";
	foreach($tlist as $row)
	{
		if($getall && $row->ishidden==0)
		{
			$rsArray[$i]['tid']=$row->tid;
			$rsArray[$i]['upid']=$row->upid;
			$rsArray[$i]['tname']=$row->tname;
			$i++;
			continue;
		}
		if (strpos(" ,".$sId.",",",".$row->$m.",")>0&& $row->ishidden==0)
		{
			$rsArray[$i]['tid']=$row->tid;
			$rsArray[$i]['upid']=$row->upid;
			$rsArray[$i]['tname']=$row->tname;
			$i++;
		}
	}
	return $rsArray;
}

//获取一个类目的顶级类目id
function GetTopid($tid,$tytype=0)
{
	$tlist=getTypeListsOnCache($tytype);
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$upid=$row->upid;
		}
	}
	return $upid;
}

function getTypeTemplate($tid,$tytype=0)
{
	$tlist=getTypeListsOnCache($tytype);
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$templist=$row->templist;
		}
	}
	return $templist;
}

function getContentTemplate($tid,$tytype=0)
{
	$tlist=getTypeListsOnCache($tytype);
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$templist=$row->templist_1;
		}
	}
	return $templist;
}

function getPlayTemplate($tid,$tytype=0)
{
	$tlist=getTypeListsOnCache($tytype);
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$templist=$row->templist_2;
		}
	}
	return $templist;
}

function getContentTemplateOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_content_templist".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$templist=getFileCache($cacheName);}else{$templist=getContentTemplate($id);setFileCache($cacheName,$templist);}
		}else{
			$templist=getContentTemplate($id);
		}
	   return $templist;
}

function getPlayTemplateOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_play_templist".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$templist=getFileCache($cacheName);}else{$templist=getPlayTemplate($id);setFileCache($cacheName,$templist);}
		}else{
			$templist=getPlayTemplate($id);
		}
	   return $templist;
}

function getTypeTemplateOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_type_templist".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$templist=getFileCache($cacheName);}else{$templist=getTypeTemplate($id);setFileCache($cacheName,$templist);}
		}else{
			$templist=getTypeTemplate($id);
		}
	   return $templist;
}

function getTopicName($id)
{
	global $dsql;
	$sql="select name from sea_topic where id=".$id;
	$row=$dsql->GetOne($sql);
	$tpname=$row['name'];
	return $tpname;
}

function getTypeName($tid)
{
	$tlist=getTypeListsOnCache();
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$tname=$row->tname;
		}
	}
	return $tname;
}

function getExtraTypeName($tid,$connector=" ")
{
	$tlist=getTypeListsOnCache();
        if($tid==""){
        $ids_arr="";}
        else{
        $ids_arr = preg_split('[,]',$tid);}
	$j=0;
	foreach($tlist as $row)
	{
			for($i=0;$i<count($ids_arr);$i++)
			{
				if ($row->tid==$ids_arr[$i]){
						if($connector==" ")
						{
							$tname= $tname . " ".$row->tname;
						}
						else
						{
							if($j<count($ids_arr)-1)
							{
								$tname= $tname . " <a href='".getChannelPagesLink($row->tid)."'>".$row->tname."</a>";	
							}
							else
							{
								$tname= $tname . " <a href='".getChannelPagesLink($row->tid)."'>".$row->tname;	
							}
							
						}
						$j++;
						break;
					}
			}
	}
	return $tname;
}

function getTypeEnName($tid)
{
	$tlist=getTypeListsOnCache();
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$tenname=$row->tenname;
		}
	}
	return $tenname;
}

function getNewsTypeName($tid)
{
	$tlist=getTypeListsOnCache(1);
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$tname=$row->tname;
		}
	}
	return $tname;
}

function getNewsTypeEnName($tid)
{
	$tlist=getTypeListsOnCache(1);
	foreach($tlist as $row)
	{
		if($row->tid==$tid){
			$tenname=$row->tenname;
		}
	}
	return $tenname;
}

function getTypeNameOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_type_name".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$tname=getFileCache($cacheName);}else{$tname=getTypeName($id);setFileCache($cacheName,$tname);}
		}else{
			$tname=getTypeName($id);
		}
	   return $tname;
}

function getTypeEnNameOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_type_enname".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$tenname=getFileCache($cacheName);}else{$tenname=getTypeEnName($id);setFileCache($cacheName,$tenname);}
		}else{
			$tenname=getTypeEnName($id);
		}
	   return $tenname;
}

function getNewsTypeNameOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_newstype_name".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$tname=getFileCache($cacheName);}else{$tname=getNewsTypeName($id);setFileCache($cacheName,$tname);}
		}else{
			$tname=getNewsTypeName($id);
		}
	   return $tname;
}

function getNewsTypeEnNameOnCache($id){
        global $cfg_iscache;
	    $cacheName="str_get_newstype_enname".$id;
		if($cfg_iscache){
			if (chkFileCache($cacheName)){$tenname=getFileCache($cacheName);}else{$tenname=getNewsTypeEnName($id);setFileCache($cacheName,$tenname);}
		}else{
			$tenname=getNewsTypeEnName($id);
		}
	   return $tenname;
}

function getPageSize($str,$ptype)
{
	$labelRule = buildregx("{seacms:".$ptype."list(.*?)size=([0-9]+)(.*?)}","is");
	preg_match_all($labelRule,$str,$pzar);
	$getPageSize=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	if(empty($getPageSize)) $getPageSize=10;
	return $pzar[2][0];
}

function ZgetPagesize($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)size=([0-9]+)(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$size=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$size=str_replace("}","",$size);
	if(empty($size)) $size=10;
	return $size;
}
function ZgetPagelang($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)lang=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$lang=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$lang=str_replace("}","",$lang);
	if(empty($lang)) $lang="";
	return $lang;
}
function ZgetPageyear($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)year=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$year=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$year=str_replace("}","",$year);
	if(empty($year)) $year="";
	return $year;
}
function ZgetPagearea($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)area=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$area=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$area=str_replace("}","",$area);
	if(empty($area)) $area="";
	return $area;
}
function ZgetPageorder($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)order=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$order=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$order=str_replace("}","",$order);
	if(empty($order)) $order="";
	return $order;
}
function ZgetPagetime($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)time=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$time=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$time=str_replace("}","",$time);
	if(empty($time)) $time="";
	return $time;
}
function ZgetPagecommend($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)commend=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$commend=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$commend=str_replace("}","",$commend);
	if(empty($commend)) $commend="";
	return $commend;
}
function ZgetPagetype($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)type=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$type=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$type=str_replace("}","",$type);
	if(empty($type)) $type="";
	return $type;
}
function ZgetPageletter($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)letter=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$letter=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$letter=str_replace("}","",$letter);
	if(empty($letter)) $letter="";
	return $letter;
}
function ZgetPagestate($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)state=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$state=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$state=str_replace("}","",$state);
	if(empty($state)) $state="";
	return $state;
}
function ZgetPagemaxpage($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)maxpage=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$maxpage=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$maxpage=str_replace("}","",$maxpage);
	if(empty($maxpage)) $maxpage="";
	return $maxpage;
}
function ZgetPagejq($str,$ptype)
{
	$labelRule = buildregx("{seacms:customvideolist(.*?)jq=(.*?) ","is");
	preg_match_all($labelRule,$str,$pzar);
	$jq=trim(preg_replace("/[ \r\n\t\f]{1,}/"," ",$pzar[2][0]));
	$jq=str_replace("}","",$jq);
	if(empty($jq)) $jq="";
	return $jq;
}

function getCustomLink($page)
{
	global $customLink;
	$link=str_replace('<page>',$page==1?'':$page,$customLink);
	return $link;
}

function getPageSizeOnCache($templatePath,$Flag,$Flag2)
{
	global $cfg_iscache;
	$templatePath=str_replace("//","/",$GLOBALS['cfg_basedir'].$templatePath);
	$cacheName=$Flag."_pagesize_".$Flag2;
	if($cfg_iscache){
		if (chkFileCache($cacheName)){
			$pSize=getFileCache($cacheName);
		}else{
			$pSize=getPageSize(loadFile($templatePath),$Flag);
			setFileCache($cacheName,$pSize);
		}
	}else{
		$pSize=getPageSize(loadFile($templatePath),$Flag);
	}
	return $pSize;
}

function replaceCurrentTypeId($str,$currentTypeId)
{
	$str=str_replace("{seacms:currenttypeid}",$currentTypeId,$str);
	return $str;
}

function getTopicNum($topicId)
{
	global $dsql;
	$rowc=$dsql->GetOne("select vod as dd from sea_topic where id=".$topicId);
	$topicvod = $rowc['dd'];
	$topicvodArr = explode("ttttt",$topicvod);
	$rowd = count($topicvodArr)-1;
	return $rowd;

}

function createTextFile($content,$fileDir)
{
	createfolder($fileDir,"filedir");
	$fp = @fopen($fileDir,"w");
	@fwrite($fp,$content);
	@fclose($fp);
	return true;
}

function dhtmlspecialchars($string) {
	if(is_array($string)) {
		foreach($string as $key => $val) {
			$string[$key] = dhtmlspecialchars($val);
		}
	} else {
		$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1',
		//$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4})|[a-zA-Z][a-z0-9]{2,5});)/', '&\\1',
		str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string));
	}
	return $string;
}

function implodeids($array) {
	if(!empty($array)) {
		return "'".implode("','", is_array($array) ? $array : array($array))."'";
	} else {
		return '';
	}
}

function updatecronscache()
{
	global $dsql;
	$cachefile = sea_DATA.'/cron.cache.php';
	$row = $dsql->GetOne("SELECT nextrun,filename FROM sea_crons WHERE available>'0' AND nextrun>'0' ORDER BY nextrun");
	$contents = "\$cronnextrun = '".$row['nextrun']."';";
	$filename = $row['filename'];
	if(strpos($filename,'$')!==false){
		$filenameArr=explode("$",$filename);
		$rid=$filenameArr[1];
		$url=$filenameArr[2];
		$downpic=$filenameArr[3];
		$contents.="\r\n";
		$contents.= "\$rid1 = '".$rid."';\r\n";
		$contents.= "\$var_url1 = '".$url."';\r\n";
	}elseif(strpos($filename,'#')!==false){
		$filenameArr=explode("#",$filename);
		$collectID=$filenameArr[1];
		$collectPageNum=$filenameArr[2];
		$autogetconnum=$filenameArr[3];
		$contents.="\r\n";
		$contents.= "\$collectID = '".$collectID."';\r\n";
		$contents.= "\$collectPageNum = '".$collectPageNum."';\r\n";
		$contents.= "\$getconnum = '".$autogetconnum."';";
	}
	$cachedata = "<?php \r\n//seacms cache file\r\n//Created on ".MyDate('Y-m-d H:i:s',time())."\r\n\r\nif(!defined('sea_INC')) exit('Access Denied');\r\n\r\n".$contents."\r\n\r\n?>";
	if($fp = fopen($cachefile,'wb')) {
		@flock($fp, LOCK_EX);
		fwrite($fp, $cachedata);
		fclose($fp);
	}
}
function playData2Ary($playData)
{
		$sorts = explode("$$$", $playData);
		$json = array();
		$json[0] = count($sorts);
		foreach ($sorts as $i=>$sort){
			$params = explode("$$", $sort);
			$json[1][$i] = $params[0];
			$params[1] = explode("#", $params[1]);
			foreach ($params[1] as $pname)
			{
				$pname = explode("$", $pname);
				$json[2][$i][] = $pname[0];
			}
			
		}
		return $json;
}

function url_exists($url) {
 $head=@get_headers($url);
 if(is_array($head)) {
 return true;
 }
 return false;
}
 
function echoHead()
{
	viewHead();
	echo "<div style='font-size:13px;text-align:center'>";
}

function echoFoot()
{
	echo "</div>";
	viewFoot();
}


/*
php5.3.x版本ereg函数兼容
*/
function chgreg($reg)
{
$nreg=str_replace("/","\\/",$reg);
return "/".$nreg."/";
}
function m_ereg($reg,$p)
{
return preg_match(chgreg($reg),$p);
}

function m_eregi($reg,$p)
{
$nreg=chgreg($reg)."i";
return preg_match(chgreg($reg),$p);
}

function m_ereg_replace($reg,$mix,$str)
{
$nreg=chgreg($reg);
$rst=preg_replace($nreg,$mix,$str);
return $rst;
}

function m_eregi_replace($reg,$mix,$str)
{
$nreg=chgreg($reg)."i";
$rst=preg_replace($nreg,$mix,$str);
return $rst;
}
/*
php5.3.x版本ereg函数兼容
*/

//下载文件
function get_file($url,$folder,$filename){         
	$destination_folder = $folder?$folder.'/':'';//文件下载保存目录  
	$newfname = $destination_folder .$filename;       
	$file = fopen ($url, "rb");    
	if ($file){    
		$newf = fopen ($newfname, "wb");  
		if ($newf)
		{        
			while(!feof($file)) {   
				fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );      
			}
		}
	}       
	if ($file){        
		fclose($file);
	}        
	if ($newf){   
		fclose($newf);    
	}     
}
// 快速文件数据读取和保存 针对简单类型数据 字符串、数组
function RWCache($name,$value='') {
    static $_cache = array();
    $filename   =   sea_DATA.'/cache/'.$name.'.php';
    if('' !== $value) {
        if(is_null($value)) {
            // 删除缓存
            return unlink($filename);
        }else{
            // 缓存数据
            $dir   =  dirname($filename);
            // 目录不存在则创建
            if(!is_dir($dir))  mkdir($dir);
            return file_put_contents($filename,"<?php \nreturn ".var_export($value,true).";\n?>");
        }
    }
    if(isset($_cache[$name])) return $_cache[$name];
    // 获取缓存数据
    if(is_file($filename)) {
        $value   =  include $filename;
        $_cache[$name]   =   $value;
    }else{
        $value  =   false;
    }
    return $value;
}



/**
 * 等价PHP5版本的array_combine函数
 */
function _Array_Combine($_Arr1, $_Arr2) {
    for($i=0; $i<count($_Arr1); $i++) $_Res[$_Arr1[$i]] = $_Arr2[$i];
    return $_Res;

}

function filterChar($str)
{
	for($i=1;$i<=31;$i++)
	{
		if($i!=10&&$i!=13)
		$str=str_replace(chr($i),'',$str);	
	}
	return $str;	
}

function gatherIntoLibTransfer($data,$str)
{
	$str=str_replace('$$$$$$','',$str);
	$str=str_replace('$$$$$','',$str);
	$str1=$data;$str2=$str;
	if($str1!='' && $str2!=''){
		$str1Array=explode("$$$",$str1);$str2Array=explode("$$$",$str2);$m=count($str1Array);$n=count($str2Array);
		for($k=0;$k<=$n;$k++){
			$str2fromarray=explode("$$",$str2Array[$k]);
			$x=findIsExistFrom($str1Array,$str2fromarray[0]);
			if(is_numeric($x)) $str1Array[$x]=$str2Array[$k]; else $transtr.=$str2Array[$k]."$$$";
		}
		for($j=0;$j<=$m;$j++){
			$transtr.=$str1Array[$j]."$$$";
		}
		$transtr = rtrim($transtr,"$$$");
	}elseif($str1!='' && empty($str2)){
		$transtr=$str1;
	}elseif(empty($str1) && $str2!=''){
		$transtr=$str2;
	}
	$transtr=str_replace('$$$$$$','',$transtr);
	$transtr=str_replace('$$$$$','',$transtr);
	return $transtr;
}

function findIsExistFrom($array1,$from)
{
	$m=count($array1);
	for($i=0;$i<=$m;$i++){
		$array1fromarray=explode("$$",$array1[$i]);
		if (trim($array1fromarray[0])==trim($from)) return $i;
	}
	return "";
}

function insert_record($table,$data=array(),$escape=false)
{
	global $dsql;
	if(!$escape)
	{
		foreach($data as $key=>$value)
		{
			$data[$key] = $dsql->realescape($value);
		}
	}
	$fileds=implode(',',array_keys($data));
	$values="'".implode("','",array_values($data))."'";
	$query="insert into {$table}($fileds) values ($values)";
	return $dsql->ExecuteNoneQuery($query);
}

function update_record($table,$where='',$data=array(),$escape=false)
{
	global $dsql;
	$updatesql='';
	if(!$escape)
	{
		foreach($data as $key=>$value)
		{
			$data[$key] = $dsql->realescape($value);
		}
	}
	foreach($data as $key=>$value)
	{
		$updatesql.="$key='$value',";	
	}
	$updatesql=rtrim($updatesql,',');
	$query="update {$table} set {$updatesql} $where";
	return $dsql->ExecuteNoneQuery($query);
}


function getReferedId($str)
{
	$playerKindsfile="../data/admin/playerKinds.xml";
					$xml = simplexml_load_file($playerKindsfile);
					if(!$xml){$xml = simplexml_load_string(file_get_contents($playerKindsfile));}
					$id=0;
					$z=array();
					foreach($xml as $player){
					$k=$player['postfix'];
					$z=$player['flag'];
					if (m_ereg("$z",$str)) return "$k";
					}
	
	
	
}

function ResetFromSort($sData)
{
	if($sData=="")return "";
	$dd=getPlayurlArray($sData);
	$dl=count($dd);
	$li=array();
	$ret="";
	if($dl>0)
	{	
		$ay=getPlayerIntroArray();
		$ul=count($ay);
		for($i=0;$i<$dl;$i++)
		{
			$ff=explode("$$",$dd[$i]);
			$j=getArrayElementID($ay,"flag",$ff[0]);
			if($li[$j]!="")
			{
				$li[$j]=$li[$j]."$$$".$dd[$i];
			}else
			{
				$li[$j]=$dd[$i];
			}
		}
		for($i=0;$i<$ul;$i++)
		{
			if($li[$i]!="")
			{
				if($ret!="")$ret.="$$$".$li[$i];
				else $ret=$li[$i];
			}
		}
		$ResetFromSort=$ret;
	}else
	{
		$ResetFromSort=$sData;
	} 
	return $ResetFromSort;
}




function getUserAuth($id,$flag)
{
	@session_start();
	$usergroup=$_SESSION["sea_user_group"];
	if (empty($usergroup)) { $usergroup=1; } else { $usergroup=intval($usergroup);}
	
	if($usergroup>2){
	$ccuid=intval($_SESSION['sea_user_id']);
	global $dsql;
	$cc2=$dsql->GetOne("select vipendtime from sea_member where id=$ccuid");
	$ccvipendtime=$cc2['vipendtime'];
		if($ccvipendtime<time()){
			$_SESSION['sea_user_group'] = 2;
			$dsql->ExecuteNoneQuery("update `sea_member` set gid=2 where id=$ccuid");
		}
	}
	
	$result=false;
	if ($flag== "list"){
		$flag = "1";
	}
	else if ($flag== "detail"){
		$flag = "2";
	}
	else if ($flag=="play"){
		$flag = "3";
	}
	else if ($flag=="pay"){
		$flag = "4";
	}
	$cache = GetGroupOnCache();
	for ($i=0;$i<count($cache);$i++){
		$gid=$cache[$i]["gid"];
		$gtype = explode(',',$cache[$i]["gtype"]);
		$g_auth = explode(',',$cache[$i]["g_auth"]);
		if ($gid==$usergroup){
			if (in_array($id,$gtype) && in_array($flag,$g_auth)){
				$result=true;
				break;
			}
		}
	}
	return $result;
}

function GetGroup()
{
	global $dsql;
	$dsql->SetQuery("select * from sea_member_group");
	$dsql->Execute("get_group");
	$arow = array();
	while($row=$dsql->GetAssoc("get_group"))
	{
		$arow[]=$row;
	}
	return $arow;
}

function GetGroupOnCache()
{
	global $cfg_iscache;
	$cacheName="all_group";
	if($cfg_iscache){
		if (RWCache($cacheName)){$groupArr=RWCache($cacheName);}else{$groupArr=GetGroup();RWCache($cacheName,$groupArr);}
	}else{
		$groupArr=GetGroup();
	}
   return $groupArr;
}
function checkIP()
{
	global $cfg_banIPS;
	$iplisttxt=sea_DATA."/admin/iplist.txt";
	$fp = @fopen($iplisttxt,'r');
	$iplist = @fread($fp,filesize($iplisttxt));
	@fclose($fp);
	$ipArr = explode('|',$iplist);
	$rip = GetIP();
	if($cfg_banIPS==0)
	{
		return ;
	}elseif($cfg_banIPS==1)
	{
		if(in_array($rip,$ipArr))
		{
			exit("您所在的IP不允许访问此页面！");	
		}
	}elseif($cfg_banIPS==2)
	{
		if(!in_array($rip,$ipArr))
		{
			exit("您所在的IP不允许访问此页面！");	
		}
	}
}


function gatherPicHandle($pic)
{
	global $image;
	return $image->gatherPicHandle($pic);
}


function Pinyin($s, $isfirst = false) {
	static $pinyins;

	$s = trim($s);
	$s = str_replace('·','',$s);
	$s = str_replace('~','',$s);
	$s = str_replace('·','',$s);
	$s = str_replace('#','',$s);
	$s = str_replace('@','',$s);
	$len = strlen($s);
	if($len < 3) return $s;

	if(!isset($pinyins)) {
		$data = '一:yi|丁:ding|丂:kao|七:qi|丄:shang|丅:xia|丆:mu|万:wan|丈:zhang|三:san|上:shang|下:xia|丌:ji|不:bu|与:yu|丏:mian|丐:gai|丑:chou|丒:chou|专:zhuan|且:qie|丕:pi|世:shi|丗:shi|丘:qiu|丙:bing|业:ye|丛:cong|东:dong|丝:si|丞:cheng|丟:diu|丠:qiu|両:liang|丢:diu|丣:you|两:liang|严:yan|並:bing|丧:sang|丨:gun|丩:jiu|个:ge|丫:ya|丬:zhuang|中:zhong|丮:ji|丯:jie|丰:feng|丱:guan|串:chuan|丳:chan|临:lin|丵:zhuo|丶:zhu|丷:ha|丸:wan|丹:dan|为:wei|主:zhu|丼:jing|丽:li|举:ju|丿:pie|乀:fu|乁:yi|乂:yi|乃:nai|乄:wu|久:jiu|乆:jiu|乇:tuo|么:me|义:yi|乊:ho|之:zhi|乌:wu|乍:zha|乎:hu|乏:fa|乐:le|乑:yin|乒:ping|乓:pang|乔:qiao|乕:hu|乖:guai|乗:cheng|乘:cheng|乙:yi|乚:yin|乛:wan|乜:mie|九:jiu|乞:qi|也:ye|习:xi|乡:xiang|乢:gai|乣:jiu|乤:hai|乥:ho|书:shu|乧:dou|乨:shi|乩:ji|乪:nang|乫:kai|乬:keng|乭:ting|乮:mo|乯:ou|买:mai|乱:luan|乲:cai|乳:ru|乴:xue|乵:yan|乶:peng|乷:sha|乸:na|乹:qian|乺:si|乻:er|乼:cui|乽:ceng|乾:qian|乿:zhi|亀:gui|亁:gan|亂:luan|亃:lin|亄:yi|亅:jue|了:liao|亇:ma|予:yu|争:zheng|亊:shi|事:shi|二:er|亍:chu|于:yu|亏:kui|亐:yu|云:yun|互:hu|亓:qi|五:wu|井:jing|亖:si|亗:sui|亘:gen|亙:geng|亚:ya|些:xie|亜:ya|亝:qi|亞:ya|亟:ji|亠:tou|亡:wang|亢:kang|亣:da|交:jiao|亥:hai|亦:yi|产:chan|亨:heng|亩:mu|亪:ye|享:xiang|京:jing|亭:ting|亮:liang|亯:xiang|亰:jing|亱:ye|亲:qin|亳:bo|亴:you|亵:xie|亶:dan|亷:lian|亸:duo|亹:wei|人:ren|亻:ren|亼:ji|亽:ra|亾:wang|亿:yi|什:shi|仁:ren|仂:le|仃:ding|仄:ze|仅:jin|仆:pu|仇:chou|仈:ba|仉:zhang|今:jin|介:jie|仌:bing|仍:reng|从:cong|仏:fo|仐:tao|仑:lun|仒:er|仓:cang|仔:zi|仕:shi|他:ta|仗:zhang|付:fu|仙:xian|仚:xian|仛:duo|仜:hong|仝:tong|仞:ren|仟:qian|仠:gan|仡:yi|仢:bo|代:dai|令:ling|以:yi|仦:chao|仧:chang|仨:sa|仩:shang|仪:yi|仫:mu|们:men|仭:ren|仮:fan|仯:chao|仰:yang|仱:qian|仲:zhong|仳:pi|仴:wo|仵:wu|件:jian|价:jia|仸:yao|仹:feng|仺:cang|任:ren|仼:wang|份:fen|仾:di|仿:fang|伀:zhong|企:qi|伂:pei|伃:yu|伄:diao|伅:dun|伆:wu|伇:yi|伈:xin|伉:kang|伊:yi|伋:ji|伌:ai|伍:wu|伎:ji|伏:fu|伐:fa|休:xiu|伒:jin|伓:pi|伔:dan|伕:fu|伖:nu|众:zhong|优:you|伙:huo|会:hui|伛:yu|伜:cui|伝:yun|伞:san|伟:wei|传:chuan|伡:che|伢:ya|伣:qian|伤:shang|伥:chang|伦:lun|伧:cang|伨:xun|伩:xin|伪:wei|伫:zhu|伬:ze|伭:xian|伮:nu|伯:bo|估:gu|伱:ni|伲:ni|伳:xie|伴:ban|伵:xu|伶:ling|伷:zhou|伸:shen|伹:qu|伺:si|伻:beng|似:si|伽:ga|伾:pi|伿:yi|佀:si|佁:yi|佂:zheng|佃:dian|佄:han|佅:mai|但:dan|佇:zhu|佈:bu|佉:qu|佊:bi|佋:zhao|佌:ci|位:wei|低:di|住:zhu|佐:zuo|佑:you|佒:yang|体:ti|佔:zhan|何:he|佖:bi|佗:tuo|佘:she|余:yu|佚:yi|佛:fo|作:zuo|佝:gou|佞:ning|佟:tong|你:ni|佡:xian|佢:qu|佣:yong|佤:wa|佥:qian|佦:shi|佧:ka|佨:bao|佩:pei|佪:hui|佫:he|佬:lao|佭:xiang|佮:ge|佯:yang|佰:bai|佱:fa|佲:ming|佳:jia|佴:er|併:bing|佶:ji|佷:hen|佸:huo|佹:gui|佺:quan|佻:tiao|佼:jiao|佽:ci|佾:yi|使:shi|侀:xing|侁:shen|侂:tuo|侃:kan|侄:zhi|侅:gai|來:lai|侇:yi|侈:chi|侉:kua|侊:guang|例:li|侌:yin|侍:shi|侎:mi|侏:zhu|侐:xu|侑:you|侒:an|侓:lu|侔:mou|侕:er|侖:lun|侗:dong|侘:cha|侙:chi|侚:xun|供:gong|侜:zhou|依:yi|侞:ru|侟:jian|侠:xia|価:si|侢:dai|侣:lv|侤:ta|侥:jiao|侦:zhen|侧:ce|侨:qiao|侩:kuai|侪:chai|侫:ning|侬:nong|侭:jin|侮:wu|侯:hou|侰:jiong|侱:cheng|侲:zhen|侳:zuo|侴:chou|侵:qin|侶:lv|侷:ju|侸:shu|侹:ting|侺:shen|侻:tuo|侼:bo|侽:nan|侾:xiao|便:bian|俀:tui|俁:yu|係:xi|促:cu|俄:e|俅:qiu|俆:xu|俇:guang|俈:ku|俉:wu|俊:jun|俋:yi|俌:fu|俍:liang|俎:zu|俏:qiao|俐:li|俑:yong|俒:hun|俓:jing|俔:qian|俕:san|俖:pei|俗:su|俘:fu|俙:xi|俚:li|俛:mian|俜:ping|保:bao|俞:yu|俟:si|俠:xia|信:xin|俢:xiu|俣:yu|俤:di|俥:che|俦:chou|俧:zhi|俨:yan|俩:lia|俪:li|俫:lai|俬:si|俭:jian|修:xiu|俯:fu|俰:huo|俱:ju|俲:xiao|俳:pai|俴:jian|俵:biao|俶:chu|俷:fei|俸:feng|俹:ya|俺:an|俻:bei|俼:yu|俽:xin|俾:bi|俿:chi|倀:chang|倁:zhi|倂:bing|倃:jiu|倄:yao|倅:cui|倆:lia|倇:wan|倈:lai|倉:cang|倊:zong|個:ge|倌:guan|倍:bei|倎:tian|倏:shu|倐:shu|們:men|倒:dao|倓:tan|倔:jue|倕:chui|倖:xing|倗:peng|倘:tang|候:hou|倚:yi|倛:qi|倜:ti|倝:gan|倞:jing|借:jie|倠:sui|倡:chang|倢:jie|倣:fang|値:zhi|倥:kong|倦:juan|倧:zong|倨:ju|倩:qian|倪:ni|倫:lun|倬:zhuo|倭:wo|倮:luo|倯:song|倰:leng|倱:hun|倲:dong|倳:zi|倴:ben|倵:wu|倶:ju|倷:nai|倸:cai|倹:jian|债:zhai|倻:ye|值:zhi|倽:sha|倾:qing|倿:qie|偀:ying|偁:cheng|偂:qian|偃:yan|偄:ruan|偅:zhong|偆:chun|假:jia|偈:ji|偉:wei|偊:yu|偋:bing|偌:ruo|偍:ti|偎:wei|偏:pian|偐:yan|偑:feng|偒:tang|偓:wo|偔:e|偕:xie|偖:che|偗:sheng|偘:kan|偙:di|做:zuo|偛:cha|停:ting|偝:bei|偞:ye|偟:huang|偠:yao|偡:zhan|偢:chou|偣:yan|偤:you|健:jian|偦:xu|偧:zha|偨:ci|偩:fu|偪:bi|偫:zhi|偬:zong|偭:mian|偮:ji|偯:yi|偰:xie|偱:xun|偲:cai|偳:duan|側:ce|偵:zhen|偶:ou|偷:tou|偸:tou|偹:bei|偺:zan|偻:lou|偼:jie|偽:wei|偾:fen|偿:chang|傀:kui|傁:sou|傂:zhi|傃:su|傄:xia|傅:fu|傆:yuan|傇:rong|傈:li|傉:nu|傊:yun|傋:jiang|傌:ma|傍:bang|傎:dian|傏:tang|傐:hao|傑:jie|傒:xi|傓:shan|傔:qian|傕:jue|傖:cang|傗:chu|傘:san|備:bei|傚:xiao|傛:yong|傜:yao|傝:tan|傞:suo|傟:yang|傠:fa|傡:bing|傢:jia|傣:dai|傤:zai|傥:tang|傦:gu|傧:bin|储:chu|傩:nuo|傪:can|傫:lei|催:cui|傭:yong|傮:zao|傯:zong|傰:peng|傱:song|傲:ao|傳:chuan|傴:yu|債:zhai|傶:zu|傷:shang|傸:chuang|傹:jing|傺:chi|傻:sha|傼:han|傽:zhang|傾:qing|傿:yan|僀:di|僁:xie|僂:lou|僃:bei|僄:piao|僅:jin|僆:lian|僇:lu|僈:man|僉:qian|僊:xian|僋:tan|僌:ying|働:dong|僎:zhuan|像:xiang|僐:shan|僑:qiao|僒:jiong|僓:tui|僔:zun|僕:pu|僖:xi|僗:lao|僘:chang|僙:guang|僚:liao|僛:qi|僜:deng|僝:chan|僞:wei|僟:ji|僠:bo|僡:hui|僢:chuan|僣:tie|僤:dan|僥:jiao|僦:jiu|僧:seng|僨:fen|僩:xian|僪:ju|僫:e|僬:jiao|僭:jian|僮:tong|僯:lin|僰:bo|僱:gu|僲:xian|僳:su|僴:xian|僵:jiang|僶:min|僷:ye|僸:jin|價:jia|僺:qiao|僻:pi|僼:feng|僽:zhou|僾:ai|僿:sai|儀:yi|儁:jun|儂:nong|儃:chan|億:yi|儅:dang|儆:jing|儇:xuan|儈:kuai|儉:jian|儊:chu|儋:dan|儌:jiao|儍:sha|儎:zai|儏:can|儐:bin|儑:an|儒:ru|儓:tai|儔:chou|儕:chai|儖:lan|儗:ni|儘:jin|儙:qian|儚:meng|儛:wu|儜:ning|儝:qiong|儞:ni|償:chang|儠:lie|儡:lei|儢:lv|儣:kuang|儤:bao|儥:yu|儦:biao|儧:zan|儨:zhi|儩:si|優:you|儫:hao|儬:qing|儭:chen|儮:li|儯:teng|儰:wei|儱:long|儲:chu|儳:chan|儴:rang|儵:shu|儶:hui|儷:li|儸:luo|儹:zan|儺:nuo|儻:tang|儼:yan|儽:lei|儾:nang|儿:er|兀:wu|允:yun|兂:zan|元:yuan|兄:xiong|充:chong|兆:zhao|兇:xiong|先:xian|光:guang|兊:dui|克:ke|兌:dui|免:mian|兎:tu|兏:chang|児:er|兑:dui|兒:er|兓:jin|兔:tu|兕:si|兖:yan|兗:yan|兘:shi|兙:shi|党:dang|兛:qiang|兜:dou|兝:gong|兞:hao|兟:shen|兠:dou|兡:bai|兢:jing|兣:gong|兤:huang|入:ru|兦:wang|內:nei|全:quan|兩:liang|兪:yu|八:ba|公:gong|六:liu|兮:xi|兯:han|兰:lan|共:gong|兲:tian|关:guan|兴:xing|兵:bing|其:qi|具:ju|典:dian|兹:zi|兺:pou|养:yang|兼:jian|兽:shou|兾:ji|兿:yi|冀:ji|冁:chan|冂:jiong|冃:mao|冄:ran|内:nei|円:yan|冇:mao|冈:gang|冉:ran|冊:ce|冋:jiong|册:ce|再:zai|冎:gua|冏:jiong|冐:mao|冑:zhou|冒:mao|冓:gou|冔:xu|冕:mian|冖:mi|冗:rong|冘:yin|写:xie|冚:kan|军:jun|农:nong|冝:yi|冞:mi|冟:shi|冠:guan|冡:meng|冢:zhong|冣:ju|冤:yuan|冥:ming|冦:kou|冧:min|冨:fu|冩:xie|冪:mi|冫:bing|冬:dong|冭:tai|冮:gang|冯:feng|冰:bing|冱:hu|冲:chong|决:jue|冴:hu|况:kuang|冶:ye|冷:leng|冸:pan|冹:fu|冺:min|冻:dong|冼:xian|冽:lie|冾:qia|冿:jian|净:jing|凁:sou|凂:mei|凃:tu|凄:qi|凅:gu|准:zhun|凇:song|凈:jing|凉:liang|凊:qing|凋:diao|凌:ling|凍:dong|凎:gan|减:jian|凐:yin|凑:cou|凒:ai|凓:li|凔:cang|凕:ming|凖:zhun|凗:cui|凘:si|凙:duo|凚:jin|凛:lin|凜:lin|凝:ning|凞:xi|凟:du|几:ji|凡:fan|凢:fan|凣:fan|凤:feng|凥:ju|処:chu|凧:zheng|凨:feng|凩:mu|凪:zhi|凫:fu|凬:feng|凭:ping|凮:feng|凯:kai|凰:huang|凱:kai|凲:gan|凳:deng|凴:ping|凵:qian|凶:xiong|凷:kuai|凸:tu|凹:ao|出:chu|击:ji|凼:dang|函:han|凾:han|凿:zao|刀:dao|刁:diao|刂:dao|刃:ren|刄:ren|刅:chuang|分:fen|切:qie|刈:yi|刉:ji|刊:kan|刋:qian|刌:cun|刍:chu|刎:wen|刏:ji|刐:dan|刑:xing|划:hua|刓:wan|刔:jue|刕:li|刖:yue|列:lie|刘:liu|则:ze|刚:gang|创:chuang|刜:fu|初:chu|刞:qu|刟:diao|删:shan|刡:min|刢:ling|刣:zhong|判:pan|別:bie|刦:jie|刧:jie|刨:pao|利:li|刪:shan|别:bie|刬:chan|刭:jing|刮:gua|刯:geng|到:dao|刱:chuang|刲:kui|刳:ku|刴:duo|刵:er|制:zhi|刷:shua|券:quan|刹:sha|刺:ci|刻:ke|刼:jie|刽:gui|刾:ci|刿:gui|剀:kai|剁:duo|剂:ji|剃:ti|剄:jing|剅:dou|剆:luo|則:ze|剈:yuan|剉:cuo|削:xiao|剋:ke|剌:la|前:qian|剎:cha|剏:chuang|剐:gua|剑:jian|剒:cuo|剓:li|剔:ti|剕:fei|剖:po|剗:chan|剘:qi|剙:chuang|剚:zi|剛:gang|剜:wan|剝:bao|剞:ji|剟:duo|剠:qing|剡:shan|剢:du|剣:jian|剤:ji|剥:bao|剦:yan|剧:ju|剨:huo|剩:sheng|剪:jian|剫:duo|剬:tuan|剭:wu|剮:gua|副:fu|剰:sheng|剱:jian|割:ge|剳:da|剴:kai|創:chuang|剶:chuan|剷:chan|剸:tuan|剹:lu|剺:li|剻:peng|剼:shan|剽:piao|剾:kou|剿:jiao|劀:gua|劁:qiao|劂:jue|劃:hua|劄:zha|劅:zhuo|劆:lian|劇:ju|劈:pi|劉:liu|劊:gui|劋:jiao|劌:gui|劍:jian|劎:jian|劏:tang|劐:huo|劑:ji|劒:jian|劓:yi|劔:jian|劕:zhi|劖:chan|劗:zuan|劘:mo|劙:li|劚:zhu|力:li|劜:ya|劝:quan|办:ban|功:gong|加:jia|务:wu|劢:mai|劣:lie|劤:jin|劥:keng|劦:xie|劧:zhi|动:dong|助:zhu|努:nu|劫:jie|劬:qu|劭:shao|劮:yi|劯:zhu|劰:mo|励:li|劲:jin|劳:lao|労:lao|劵:juan|劶:kou|劷:yang|劸:wa|効:xiao|劺:mou|劻:kuang|劼:jie|劽:lie|劾:he|势:shi|勀:ke|勁:jin|勂:gao|勃:bo|勄:min|勅:chi|勆:lang|勇:yong|勈:yong|勉:mian|勊:ke|勋:xun|勌:juan|勍:qing|勎:lu|勏:bu|勐:meng|勑:chai|勒:le|勓:kai|勔:mian|動:dong|勖:xu|勗:xu|勘:kan|務:wu|勚:yi|勛:xun|勜:weng|勝:sheng|勞:lao|募:mu|勠:lu|勡:piao|勢:shi|勣:ji|勤:qin|勥:jiang|勦:jiao|勧:quan|勨:xiang|勩:yi|勪:jue|勫:fan|勬:juan|勭:tong|勮:ju|勯:dan|勰:xie|勱:mai|勲:xun|勳:xun|勴:lv|勵:li|勶:che|勷:rang|勸:quan|勹:bao|勺:shao|勻:yun|勼:jiu|勽:bao|勾:gou|勿:wu|匀:yun|匁:mang|匂:bi|匃:gai|匄:gai|包:bao|匆:cong|匇:yi|匈:xiong|匉:peng|匊:ju|匋:tao|匌:ge|匍:pu|匎:e|匏:pao|匐:fu|匑:gong|匒:da|匓:jiu|匔:gong|匕:bi|化:hua|北:bei|匘:nao|匙:chi|匚:fang|匛:jiu|匜:yi|匝:za|匞:jiang|匟:kang|匠:jiang|匡:kuang|匢:hu|匣:xia|匤:qu|匥:fan|匦:gui|匧:qie|匨:cang|匩:kuang|匪:fei|匫:hu|匬:yu|匭:gui|匮:kui|匯:hui|匰:dan|匱:kui|匲:lian|匳:lian|匴:suan|匵:du|匶:jiu|匷:qu|匸:xi|匹:pi|区:qu|医:yi|匼:ke|匽:yan|匾:bian|匿:ni|區:qu|十:shi|卂:xun|千:qian|卄:nian|卅:sa|卆:zu|升:sheng|午:wu|卉:hui|半:ban|卋:shi|卌:xi|卍:wan|华:hua|协:xie|卐:wan|卑:bei|卒:zu|卓:zhuo|協:xie|单:dan|卖:mai|南:nan|単:dan|卙:ji|博:bo|卛:shuai|卜:bo|卝:guan|卞:bian|卟:bu|占:zhan|卡:ka|卢:lu|卣:you|卤:lu|卥:xi|卦:gua|卧:wo|卨:xie|卩:jie|卪:jie|卫:wei|卬:yang|卭:qiong|卮:zhi|卯:mao|印:yin|危:wei|卲:shao|即:ji|却:que|卵:luan|卶:chi|卷:juan|卸:xie|卹:xu|卺:jin|卻:que|卼:kui|卽:ji|卾:e|卿:qing|厀:xi|厁:san|厂:chang|厃:wei|厄:e|厅:ting|历:li|厇:zhe|厈:han|厉:li|厊:ya|压:ya|厌:yan|厍:she|厎:di|厏:zha|厐:pang|厑:a|厒:qie|厓:ya|厔:zhi|厕:ce|厖:mang|厗:ti|厘:li|厙:she|厚:hou|厛:ting|厜:zui|厝:cuo|厞:fei|原:yuan|厠:ce|厡:yuan|厢:xiang|厣:yan|厤:li|厥:jue|厦:xia|厧:dian|厨:chu|厩:jiu|厪:jin|厫:ao|厬:gui|厭:yan|厮:si|厯:li|厰:chang|厱:lan|厲:li|厳:yan|厴:yan|厵:yuan|厶:si|厷:gong|厸:lin|厹:rou|厺:qu|去:qu|厼:keng|厽:lei|厾:du|县:xian|叀:zhuan|叁:san|参:can|參:can|叄:can|叅:can|叆:ai|叇:dai|又:you|叉:cha|及:ji|友:you|双:shuang|反:fan|収:shou|叏:guai|叐:ba|发:fa|叒:ruo|叓:shi|叔:shu|叕:zhuo|取:qu|受:shou|变:bian|叙:xu|叚:jia|叛:pan|叜:sou|叝:ji|叞:wei|叟:sou|叠:die|叡:rui|叢:cong|口:kou|古:gu|句:ju|另:ling|叧:gua|叨:dao|叩:kou|只:zhi|叫:jiao|召:zhao|叭:ba|叮:ding|可:ke|台:tai|叱:chi|史:shi|右:you|叴:qiu|叵:po|叶:ye|号:hao|司:si|叹:tan|叺:chi|叻:le|叼:diao|叽:ji|叾:dui|叿:hong|吀:mie|吁:yu|吂:mang|吃:chi|各:ge|吅:xuan|吆:yao|吇:zi|合:he|吉:ji|吊:diao|吋:cun|同:tong|名:ming|后:hou|吏:li|吐:tu|向:xiang|吒:zha|吓:xia|吔:ye|吕:lv|吖:a|吗:ma|吘:ou|吙:huo|吚:yi|君:jun|吜:chou|吝:lin|吞:tun|吟:yin|吠:fei|吡:bi|吢:qin|吣:qin|吤:jie|吥:bu|否:fou|吧:ba|吨:dun|吩:fen|吪:e|含:han|听:ting|吭:keng|吮:shun|启:qi|吰:hong|吱:zhi|吲:yin|吳:wu|吴:wu|吵:chao|吶:na|吷:xue|吸:xi|吹:chui|吺:dou|吻:wen|吼:hou|吽:hou|吾:wu|吿:gao|呀:ya|呁:jun|呂:lv|呃:e|呄:ge|呅:mei|呆:dai|呇:qi|呈:cheng|呉:wu|告:gao|呋:fu|呌:jiao|呍:yun|呎:chi|呏:sheng|呐:na|呑:tun|呒:fu|呓:yi|呔:dai|呕:ou|呖:li|呗:bai|员:yuan|呙:guo|呚:wen|呛:qiang|呜:wu|呝:e|呞:shi|呟:juan|呠:pen|呡:wen|呢:ne|呣:m|呤:ling|呥:ran|呦:you|呧:di|周:zhou|呩:shi|呪:zhou|呫:tie|呬:xi|呭:yi|呮:qi|呯:ping|呰:zi|呱:gua|呲:ci|味:wei|呴:xu|呵:he|呶:nao|呷:xia|呸:pei|呹:yi|呺:xiao|呻:shen|呼:hu|命:ming|呾:da|呿:qu|咀:ju|咁:han|咂:za|咃:tuo|咄:duo|咅:pou|咆:pao|咇:bie|咈:fu|咉:yang|咊:he|咋:zha|和:he|咍:hai|咎:jiu|咏:yong|咐:fu|咑:da|咒:zhou|咓:wa|咔:ka|咕:gu|咖:ka|咗:zuo|咘:bu|咙:long|咚:dong|咛:ning|咜:ta|咝:si|咞:xian|咟:huo|咠:qi|咡:er|咢:e|咣:guang|咤:zha|咥:xi|咦:yi|咧:lie|咨:zi|咩:mie|咪:mi|咫:zhi|咬:yao|咭:ji|咮:zhou|咯:ka|咰:shu|咱:zan|咲:xiao|咳:ke|咴:hui|咵:kua|咶:huai|咷:tao|咸:xian|咹:e|咺:xuan|咻:xiu|咼:guo|咽:yan|咾:lao|咿:yi|哀:ai|品:pin|哂:shen|哃:tong|哄:hong|哅:xiong|哆:duo|哇:wa|哈:ha|哉:zai|哊:you|哋:die|哌:pai|响:xiang|哎:ai|哏:gen|哐:kuang|哑:ya|哒:da|哓:xiao|哔:bi|哕:hui|哖:nian|哗:hua|哘:xing|哙:kuai|哚:duo|哛:pou|哜:ji|哝:nong|哞:mou|哟:yo|哠:hao|員:yuan|哢:long|哣:pou|哤:mang|哥:ge|哦:o|哧:chi|哨:shao|哩:li|哪:na|哫:zu|哬:he|哭:ku|哮:xiao|哯:xian|哰:lao|哱:po|哲:zhe|哳:zha|哴:liang|哵:ba|哶:mie|哷:lv|哸:sui|哹:fu|哺:bu|哻:han|哼:heng|哽:geng|哾:shui|哿:ge|唀:you|唁:yan|唂:gu|唃:gu|唄:bai|唅:han|唆:suo|唇:chun|唈:yi|唉:ai|唊:jia|唋:tu|唌:xian|唍:wan|唎:li|唏:xi|唐:tang|唑:zuo|唒:qiu|唓:che|唔:wu|唕:zao|唖:ya|唗:dou|唘:qi|唙:di|唚:qin|唛:ma|唜:mao|唝:gong|唞:teng|唟:keng|唠:lao|唡:liang|唢:suo|唣:zao|唤:huan|唥:lang|唦:sha|唧:ji|唨:zi|唩:wo|唪:feng|唫:yin|唬:hu|唭:qi|售:shou|唯:wei|唰:shua|唱:chang|唲:er|唳:li|唴:qiang|唵:an|唶:ze|唷:yo|唸:dian|唹:yu|唺:tian|唻:lai|唼:sha|唽:xi|唾:tuo|唿:hu|啀:ai|啁:zhao|啂:nou|啃:ken|啄:zhuo|啅:zhuo|商:shang|啇:di|啈:heng|啉:lin|啊:a|啋:xiao|啌:xiang|啍:tun|啎:wu|問:wen|啐:cui|啑:die|啒:gu|啓:qi|啔:qi|啕:tao|啖:dan|啗:dan|啘:wa|啙:zi|啚:bi|啛:cui|啜:chuai|啝:he|啞:ya|啟:qi|啠:zhe|啡:fei|啢:liang|啣:xian|啤:pi|啥:sha|啦:la|啧:ze|啨:ying|啩:gua|啪:pa|啫:ze|啬:se|啭:zhuan|啮:nie|啯:guo|啰:luo|啱:n|啲:di|啳:quan|啴:tan|啵:bo|啶:ding|啷:lang|啸:xiao|啹:geng|啺:tang|啻:chi|啼:ti|啽:an|啾:jiu|啿:dan|喀:ka|喁:yong|喂:wei|喃:nan|善:shan|喅:yu|喆:zhe|喇:la|喈:jie|喉:hou|喊:han|喋:die|喌:zhou|喍:chai|喎:wai|喏:nuo|喐:yu|喑:yin|喒:zan|喓:yao|喔:wo|喕:mian|喖:hu|喗:yun|喘:chuan|喙:hui|喚:huan|喛:huan|喜:xi|喝:he|喞:ji|喟:kui|喠:zhong|喡:wei|喢:sha|喣:xu|喤:huang|喥:duo|喦:nie|喧:xuan|喨:liang|喩:yu|喪:sang|喫:chi|喬:qiao|喭:yan|單:dan|喯:pen|喰:can|喱:li|喲:yo|喳:zha|喴:wei|喵:miao|営:ying|喷:pen|喸:peng|喹:kui|喺:bei|喻:yu|喼:geng|喽:lou|喾:ku|喿:zao|嗀:huo|嗁:ti|嗂:yao|嗃:he|嗄:a|嗅:xiu|嗆:qiang|嗇:se|嗈:yong|嗉:su|嗊:hong|嗋:xie|嗌:ai|嗍:shuo|嗎:ma|嗏:cha|嗐:hai|嗑:ke|嗒:da|嗓:sang|嗔:chen|嗕:ru|嗖:sou|嗗:wa|嗘:ji|嗙:pang|嗚:wu|嗛:qian|嗜:shi|嗝:ge|嗞:zi|嗟:jie|嗠:lao|嗡:weng|嗢:wa|嗣:si|嗤:chi|嗥:hao|嗦:suo|嗧:jia|嗨:hai|嗩:suo|嗪:qin|嗫:nie|嗬:he|嗭:ci|嗮:sai|嗯:n|嗰:geng|嗱:na|嗲:dia|嗳:ai|嗴:qiang|嗵:tong|嗶:bi|嗷:ao|嗸:ao|嗹:lian|嗺:zui|嗻:zhe|嗼:mo|嗽:su|嗾:sou|嗿:tan|嘀:di|嘁:qi|嘂:jiao|嘃:chong|嘄:jiao|嘅:kai|嘆:tan|嘇:shan|嘈:cao|嘉:jia|嘊:ai|嘋:xiao|嘌:piao|嘍:lou|嘎:ga|嘏:gu|嘐:xiao|嘑:hu|嘒:hui|嘓:guo|嘔:ou|嘕:xian|嘖:ze|嘗:chang|嘘:xu|嘙:po|嘚:de|嘛:ma|嘜:ma|嘝:hu|嘞:lei|嘟:du|嘠:ga|嘡:tang|嘢:ye|嘣:beng|嘤:ying|嘥:sai|嘦:jiao|嘧:mi|嘨:xiao|嘩:hua|嘪:mai|嘫:ran|嘬:chuai|嘭:peng|嘮:lao|嘯:xiao|嘰:ji|嘱:zhu|嘲:chao|嘳:kui|嘴:zui|嘵:xiao|嘶:si|嘷:hao|嘸:m|嘹:liao|嘺:qiao|嘻:xi|嘼:chu|嘽:tan|嘾:dan|嘿:hei|噀:xun|噁:e|噂:zun|噃:fan|噄:chi|噅:hui|噆:can|噇:chuang|噈:cu|噉:dan|噊:yu|噋:kuo|噌:ceng|噍:jiao|噎:ye|噏:xi|噐:qi|噑:hao|噒:lian|噓:xu|噔:deng|噕:hui|噖:yin|噗:pu|噘:jue|噙:qin|噚:xun|噛:nie|噜:lu|噝:si|噞:yan|噟:ying|噠:da|噡:zhan|噢:ou|噣:zhou|噤:jin|噥:nong|噦:hui|噧:xie|器:qi|噩:e|噪:zao|噫:yi|噬:shi|噭:jiao|噮:yuan|噯:ai|噰:yong|噱:jue|噲:kuai|噳:yu|噴:pen|噵:dao|噶:ga|噷:xin|噸:dun|噹:dang|噺:xin|噻:sai|噼:pi|噽:pi|噾:yin|噿:zui|嚀:ning|嚁:di|嚂:lan|嚃:ta|嚄:huo|嚅:ru|嚆:hao|嚇:xia|嚈:ye|嚉:duo|嚊:pi|嚋:zhou|嚌:ji|嚍:jin|嚎:hao|嚏:ti|嚐:chang|嚑:xun|嚒:me|嚓:ca|嚔:ti|嚕:lu|嚖:hui|嚗:bo|嚘:you|嚙:nie|嚚:yin|嚛:hu|嚜:mei|嚝:hong|嚞:zhe|嚟:li|嚠:liu|嚡:hai|嚢:nang|嚣:xiao|嚤:mo|嚥:yan|嚦:li|嚧:lu|嚨:long|嚩:mo|嚪:dan|嚫:chen|嚬:pin|嚭:pi|嚮:xiang|嚯:huo|嚰:me|嚱:xi|嚲:duo|嚳:ku|嚴:yan|嚵:chan|嚶:ying|嚷:rang|嚸:die|嚹:la|嚺:ta|嚻:xiao|嚼:jiao|嚽:chuo|嚾:huan|嚿:huo|囀:zhuan|囁:nie|囂:xiao|囃:ca|囄:li|囅:chan|囆:chai|囇:li|囈:yi|囉:luo|囊:nang|囋:zan|囌:su|囍:heng|囎:zen|囏:jian|囐:za|囑:zhu|囒:lan|囓:nie|囔:nang|囕:ra|囖:liu|囗:wei|囘:hui|囙:yin|囚:qiu|四:si|囜:nin|囝:jian|回:hui|囟:xin|因:yin|囡:nan|团:tuan|団:tuan|囤:dun|囥:kang|囦:yuan|囧:jiong|囨:pian|囩:yun|囪:cong|囫:hu|囬:hui|园:yuan|囮:e|囯:guo|困:kun|囱:cong|囲:tong|図:tu|围:wei|囵:lun|囶:guo|囷:qun|囸:ri|囹:ling|固:gu|囻:guo|囼:tai|国:guo|图:tu|囿:you|圀:guo|圁:yin|圂:hun|圃:pu|圄:yu|圅:han|圆:yuan|圇:lun|圈:quan|圉:yu|圊:qing|國:guo|圌:chuan|圍:wei|圎:yuan|圏:quan|圐:ku|圑:pu|園:yuan|圓:yuan|圔:ya|圕:tuan|圖:tu|圗:tu|團:tuan|圙:lve|圚:hui|圛:yi|圜:yuan|圝:luan|圞:luan|土:tu|圠:ya|圡:tu|圢:ting|圣:sheng|圤:pu|圥:lu|圦:kuai|圧:ju|在:zai|圩:wei|圪:ge|圫:yu|圬:wu|圭:gui|圮:pi|圯:yi|地:di|圱:qian|圲:qian|圳:zhen|圴:zhuo|圵:dang|圶:qia|圷:xia|圸:shan|圹:kuang|场:chang|圻:qi|圼:nie|圽:mo|圾:ji|圿:jia|址:zhi|坁:zhi|坂:ban|坃:xun|坄:yi|坅:qin|坆:fen|均:jun|坈:keng|坉:tun|坊:fang|坋:fen|坌:ben|坍:tan|坎:kan|坏:huai|坐:zuo|坑:keng|坒:bi|坓:jing|坔:di|坕:jing|坖:ji|块:kuai|坘:chi|坙:jing|坚:jian|坛:tan|坜:li|坝:ba|坞:wu|坟:fen|坠:zhui|坡:po|坢:pan|坣:tang|坤:kun|坥:qu|坦:tan|坧:zhi|坨:tuo|坩:gan|坪:ping|坫:dian|坬:gua|坭:ni|坮:tai|坯:pi|坰:jiong|坱:yang|坲:fo|坳:ao|坴:lu|坵:qiu|坶:mu|坷:ke|坸:gou|坹:xue|坺:ba|坻:di|坼:che|坽:ling|坾:zhu|坿:fu|垀:hu|垁:zhi|垂:chui|垃:la|垄:long|垅:long|垆:lu|垇:ao|垈:dai|垉:pao|垊:min|型:xing|垌:dong|垍:ji|垎:he|垏:lv|垐:ci|垑:chi|垒:lei|垓:gai|垔:yin|垕:hou|垖:dui|垗:zhao|垘:fu|垙:guang|垚:yao|垛:duo|垜:duo|垝:gui|垞:cha|垟:yang|垠:yin|垡:fa|垢:gou|垣:yuan|垤:die|垥:xie|垦:ken|垧:shang|垨:shou|垩:e|垪:bing|垫:dian|垬:hong|垭:ya|垮:kua|垯:da|垰:ka|垱:dang|垲:kai|垳:hang|垴:nao|垵:an|垶:xing|垷:xian|垸:yuan|垹:bang|垺:fou|垻:ba|垼:yi|垽:yin|垾:han|垿:xu|埀:chui|埁:qin|埂:geng|埃:ai|埄:beng|埅:fang|埆:que|埇:yong|埈:jun|埉:jia|埊:di|埋:mai|埌:lang|埍:juan|城:cheng|埏:shan|埐:jin|埑:zhe|埒:lie|埓:lie|埔:pu|埕:cheng|埖:hua|埗:bu|埘:shi|埙:xun|埚:guo|埛:jiong|埜:ye|埝:nian|埞:di|域:yu|埠:bu|埡:ya|埢:quan|埣:sui|埤:pi|埥:qing|埦:wan|埧:ju|埨:lun|埩:zheng|埪:kong|埫:chong|埬:dong|埭:dai|埮:tan|埯:an|埰:cai|埱:chu|埲:beng|埳:kan|埴:zhi|埵:duo|埶:yi|執:zhi|埸:yi|培:pei|基:ji|埻:zhun|埼:qi|埽:sao|埾:ju|埿:ni|堀:ku|堁:ke|堂:tang|堃:kun|堄:ni|堅:jian|堆:dui|堇:jin|堈:gang|堉:yu|堊:e|堋:peng|堌:gu|堍:tu|堎:leng|堏:fang|堐:ya|堑:qian|堒:kun|堓:an|堔:shen|堕:duo|堖:nao|堗:tu|堘:cheng|堙:yin|堚:huan|堛:bi|堜:lian|堝:guo|堞:die|堟:zhuan|堠:hou|堡:bao|堢:bao|堣:yu|堤:di|堥:mao|堦:jie|堧:ruan|堨:ye|堩:geng|堪:kan|堫:zong|堬:yu|堭:huang|堮:e|堯:yao|堰:yan|報:bao|堲:ci|堳:mei|場:chang|堵:du|堶:tuo|堷:pou|堸:feng|堹:zhong|堺:jie|堻:jin|堼:heng|堽:gang|堾:chun|堿:kan|塀:ping|塁:lei|塂:xing|塃:huang|塄:leng|塅:duan|塆:wan|塇:xuan|塈:xi|塉:ji|塊:kuai|塋:ying|塌:ta|塍:cheng|塎:yong|塏:kai|塐:su|塑:su|塒:shi|塓:mi|塔:ta|塕:weng|塖:cheng|塗:tu|塘:tang|塙:que|塚:zhong|塛:li|塜:peng|塝:bang|塞:sai|塟:zang|塠:dui|塡:tian|塢:wu|塣:zheng|塤:xun|塥:ge|塦:zhen|塧:ai|塨:gong|塩:yan|塪:kan|填:tian|塬:yuan|塭:wen|塮:xie|塯:liu|塰:hai|塱:lang|塲:chang|塳:peng|塴:beng|塵:chen|塶:lu|塷:lu|塸:ou|塹:qian|塺:mei|塻:mo|塼:tuan|塽:shuang|塾:shu|塿:lou|墀:chi|墁:man|墂:biao|境:jing|墄:ce|墅:shu|墆:zhi|墇:zhang|墈:kan|墉:yong|墊:dian|墋:chen|墌:zhi|墍:ji|墎:guo|墏:qiang|墐:jin|墑:di|墒:shang|墓:mu|墔:cui|墕:yan|墖:ta|増:zeng|墘:qian|墙:qiang|墚:liang|墛:wei|墜:zhui|墝:qiao|增:zeng|墟:xu|墠:shan|墡:shan|墢:ba|墣:pu|墤:kuai|墥:dong|墦:fan|墧:que|墨:mo|墩:dun|墪:dun|墫:cun|墬:di|墭:sheng|墮:duo|墯:duo|墰:tan|墱:deng|墲:mu|墳:fen|墴:huang|墵:tan|墶:da|墷:ye|墸:zhu|墹:jian|墺:ao|墻:qiang|墼:ji|墽:qiao|墾:ken|墿:yi|壀:pi|壁:bi|壂:dian|壃:jiang|壄:ye|壅:yong|壆:bo|壇:tan|壈:lan|壉:ju|壊:huai|壋:dang|壌:rang|壍:qian|壎:xun|壏:lan|壐:xi|壑:he|壒:ai|壓:ya|壔:dao|壕:hao|壖:ruan|壗:jin|壘:lei|壙:kuang|壚:lu|壛:yan|壜:tan|壝:wei|壞:huai|壟:long|壠:long|壡:rui|壢:li|壣:lin|壤:rang|壥:chan|壦:xun|壧:yan|壨:lei|壩:ba|壪:wan|士:shi|壬:ren|壭:san|壮:zhuang|壯:zhuang|声:sheng|壱:yi|売:mai|壳:ke|壴:zhu|壵:zhuang|壶:hu|壷:hu|壸:kun|壹:yi|壺:hu|壻:xu|壼:kun|壽:shou|壾:mang|壿:dun|夀:shou|夁:yi|夂:zhi|夃:gu|处:chu|夅:jiang|夆:feng|备:bei|夈:zhai|変:bian|夊:sui|夋:qun|夌:ling|复:fu|夎:cuo|夏:xia|夐:xiong|夑:xie|夒:nao|夓:xia|夔:kui|夕:xi|外:wai|夗:yuan|夘:mao|夙:su|多:duo|夛:duo|夜:ye|夝:qing|夞:ou|够:gou|夠:gou|夡:qi|夢:meng|夣:meng|夤:yin|夥:huo|夦:chen|大:da|夨:ze|天:tian|太:tai|夫:fu|夬:guai|夭:yao|央:yang|夯:hang|夰:gao|失:shi|夲:tao|夳:tai|头:tou|夵:yan|夶:bi|夷:yi|夸:kua|夹:jia|夺:duo|夻:huo|夼:kuang|夽:yun|夾:jia|夿:ba|奀:en|奁:lian|奂:huan|奃:di|奄:yan|奅:pao|奆:juan|奇:qi|奈:nai|奉:feng|奊:xie|奋:fen|奌:dian|奍:yang|奎:kui|奏:zou|奐:huan|契:qi|奒:kai|奓:zha|奔:ben|奕:yi|奖:jiang|套:tao|奘:zang|奙:ben|奚:xi|奛:huang|奜:fei|奝:diao|奞:xun|奟:beng|奠:dian|奡:ao|奢:she|奣:weng|奤:po|奥:ao|奦:wu|奧:ao|奨:jiang|奩:lian|奪:duo|奫:yun|奬:jiang|奭:shi|奮:fen|奯:huo|奰:bi|奱:luan|奲:che|女:nv|奴:nu|奵:ding|奶:nai|奷:qian|奸:jian|她:ta|奺:jiu|奻:nuan|奼:cha|好:hao|奾:xian|奿:fan|妀:ji|妁:shuo|如:ru|妃:fei|妄:wang|妅:hong|妆:zhuang|妇:fu|妈:ma|妉:dan|妊:ren|妋:fu|妌:jing|妍:yan|妎:ha|妏:wen|妐:zhong|妑:pa|妒:du|妓:ji|妔:hang|妕:zhong|妖:yao|妗:jin|妘:yun|妙:miao|妚:fou|妛:chi|妜:jue|妝:zhuang|妞:niu|妟:yan|妠:na|妡:xin|妢:fen|妣:bi|妤:yu|妥:tuo|妦:feng|妧:wan|妨:fang|妩:wu|妪:yu|妫:gui|妬:du|妭:ba|妮:ni|妯:zhou|妰:zhuo|妱:zhao|妲:da|妳:nai|妴:yuan|妵:tou|妶:xian|妷:yi|妸:e|妹:mei|妺:mo|妻:qi|妼:bi|妽:shen|妾:qie|妿:e|姀:he|姁:xu|姂:fa|姃:zheng|姄:min|姅:ban|姆:mu|姇:fu|姈:ling|姉:zi|姊:zi|始:shi|姌:ran|姍:shan|姎:yang|姏:man|姐:jie|姑:gu|姒:si|姓:xing|委:wei|姕:zi|姖:ju|姗:shan|姘:pin|姙:ren|姚:yao|姛:dong|姜:jiang|姝:shu|姞:ji|姟:gai|姠:xiang|姡:hua|姢:juan|姣:jiao|姤:gou|姥:lao|姦:jian|姧:jian|姨:yi|姩:nian|姪:zhi|姫:zhen|姬:ji|姭:xian|姮:heng|姯:guang|姰:jun|姱:kua|姲:yan|姳:ming|姴:lie|姵:pei|姶:e|姷:you|姸:yan|姹:cha|姺:shen|姻:yin|姼:shi|姽:gui|姾:quan|姿:zi|娀:song|威:wei|娂:hong|娃:wa|娄:lou|娅:ya|娆:rao|娇:jiao|娈:lian|娉:pin|娊:xian|娋:shao|娌:li|娍:cheng|娎:xie|娏:mang|娐:fu|娑:suo|娒:mu|娓:wei|娔:ke|娕:chuo|娖:chuo|娗:ting|娘:niang|娙:xing|娚:nan|娛:yu|娜:na|娝:po|娞:nei|娟:juan|娠:shen|娡:zhi|娢:han|娣:di|娤:zhuang|娥:e|娦:ping|娧:tui|娨:xian|娩:mian|娪:wu|娫:yan|娬:wu|娭:xi|娮:yan|娯:yu|娰:si|娱:yu|娲:wa|娳:li|娴:xian|娵:ju|娶:qu|娷:zhui|娸:qi|娹:xian|娺:zhuo|娻:dong|娼:chang|娽:lu|娾:ai|娿:e|婀:e|婁:lou|婂:mian|婃:cong|婄:pou|婅:ju|婆:po|婇:cai|婈:ling|婉:wan|婊:biao|婋:xiao|婌:shu|婍:qi|婎:hui|婏:fu|婐:wo|婑:wo|婒:tan|婓:fei|婔:fei|婕:jie|婖:tian|婗:ni|婘:quan|婙:jing|婚:hun|婛:jing|婜:qian|婝:dian|婞:xing|婟:hu|婠:wan|婡:lai|婢:bi|婣:yin|婤:zhou|婥:nao|婦:fu|婧:jing|婨:lun|婩:an|婪:lan|婫:kun|婬:yin|婭:ya|婮:ju|婯:li|婰:dian|婱:xian|婲:hua|婳:hua|婴:ying|婵:chan|婶:shen|婷:ting|婸:dang|婹:yao|婺:wu|婻:nan|婼:ruo|婽:jia|婾:tou|婿:xu|媀:yu|媁:wei|媂:di|媃:rou|媄:mei|媅:dan|媆:ruan|媇:qin|媈:hui|媉:wo|媊:qian|媋:chun|媌:miao|媍:fu|媎:jie|媏:duan|媐:yi|媑:zhong|媒:mei|媓:huang|媔:mian|媕:an|媖:ying|媗:xuan|媘:jie|媙:wei|媚:mei|媛:yuan|媜:zheng|媝:qiu|媞:ti|媟:xie|媠:tuo|媡:lian|媢:mao|媣:ran|媤:si|媥:pian|媦:wei|媧:wa|媨:cu|媩:hu|媪:ao|媫:jie|媬:bao|媭:xu|媮:tou|媯:gui|媰:zou|媱:yao|媲:pi|媳:xi|媴:yuan|媵:ying|媶:rong|媷:ru|媸:chi|媹:liu|媺:mei|媻:pan|媼:ao|媽:ma|媾:gou|媿:kui|嫀:qin|嫁:jia|嫂:sao|嫃:zhen|嫄:yuan|嫅:jie|嫆:rong|嫇:ming|嫈:ying|嫉:ji|嫊:su|嫋:niao|嫌:xian|嫍:tao|嫎:pang|嫏:lang|嫐:nao|嫑:bao|嫒:ai|嫓:pi|嫔:pin|嫕:yi|嫖:piao|嫗:yu|嫘:lei|嫙:xuan|嫚:man|嫛:yi|嫜:zhang|嫝:kang|嫞:yong|嫟:ni|嫠:li|嫡:di|嫢:gui|嫣:yan|嫤:jin|嫥:zhuan|嫦:chang|嫧:ze|嫨:han|嫩:nen|嫪:lao|嫫:mo|嫬:zhe|嫭:hu|嫮:hu|嫯:ao|嫰:ruan|嫱:qiang|嫲:ma|嫳:pie|嫴:gu|嫵:wu|嫶:qiao|嫷:tuo|嫸:zhan|嫹:miao|嫺:xian|嫻:xian|嫼:mo|嫽:liao|嫾:lian|嫿:hua|嬀:gui|嬁:deng|嬂:zhi|嬃:xu|嬄:yi|嬅:hua|嬆:xi|嬇:kui|嬈:rao|嬉:xi|嬊:yan|嬋:chan|嬌:jiao|嬍:mei|嬎:fan|嬏:fan|嬐:xian|嬑:yi|嬒:hei|嬓:jiao|嬔:fan|嬕:shi|嬖:bi|嬗:shan|嬘:sui|嬙:qiang|嬚:lian|嬛:xuan|嬜:xin|嬝:niao|嬞:dong|嬟:yi|嬠:can|嬡:ai|嬢:niang|嬣:ning|嬤:ma|嬥:tiao|嬦:chou|嬧:jin|嬨:ci|嬩:yu|嬪:pin|嬫:rong|嬬:ru|嬭:nai|嬮:yan|嬯:tai|嬰:ying|嬱:qian|嬲:niao|嬳:yue|嬴:ying|嬵:mian|嬶:bi|嬷:ma|嬸:shen|嬹:xing|嬺:ni|嬻:du|嬼:liu|嬽:yuan|嬾:lan|嬿:yan|孀:shuang|孁:ling|孂:jiao|孃:niang|孄:lan|孅:xian|孆:ying|孇:shuang|孈:hui|孉:quan|孊:mi|孋:li|孌:luan|孍:yan|孎:zhu|孏:lan|子:zi|孑:jie|孒:jue|孓:jue|孔:kong|孕:yun|孖:zi|字:zi|存:cun|孙:sun|孚:fu|孛:bo|孜:zi|孝:xiao|孞:xin|孟:meng|孠:si|孡:tai|孢:bao|季:ji|孤:gu|孥:nu|学:xue|孧:you|孨:zhuan|孩:hai|孪:luan|孫:sun|孬:nao|孭:mie|孮:cong|孯:qian|孰:shu|孱:chan|孲:ya|孳:zi|孴:ni|孵:fu|孶:zi|孷:li|學:xue|孹:bo|孺:ru|孻:nai|孼:nie|孽:nie|孾:ying|孿:luan|宀:mian|宁:ning|宂:rong|它:ta|宄:gui|宅:zhai|宆:qiong|宇:yu|守:shou|安:an|宊:jia|宋:song|完:wan|宍:rou|宎:yao|宏:hong|宐:yi|宑:jing|宒:zhun|宓:mi|宔:zhu|宕:dang|宖:hong|宗:zong|官:guan|宙:zhou|定:ding|宛:wan|宜:yi|宝:bao|实:shi|実:shi|宠:chong|审:shen|客:ke|宣:xuan|室:shi|宥:you|宦:huan|宧:yi|宨:tiao|宩:shi|宪:xian|宫:gong|宬:cheng|宭:qun|宮:gong|宯:xiao|宰:zai|宱:zha|宲:dao|害:hai|宴:yan|宵:xiao|家:jia|宷:shen|宸:chen|容:rong|宺:huang|宻:mi|宼:kou|宽:kuan|宾:bin|宿:su|寀:cai|寁:zan|寂:ji|寃:yuan|寄:ji|寅:yin|密:mi|寇:kou|寈:qiu|寉:he|寊:zhen|寋:jian|富:fu|寍:ning|寎:bing|寏:huan|寐:mei|寑:qin|寒:han|寓:yu|寔:shi|寕:ning|寖:jin|寗:ning|寘:zhi|寙:yu|寚:bao|寛:kuan|寜:ning|寝:qin|寞:mo|察:cha|寠:ju|寡:gua|寢:qin|寣:hu|寤:wu|寥:liao|實:shi|寧:ning|寨:zhai|審:shen|寪:wei|寫:xie|寬:kuan|寭:hui|寮:liao|寯:jun|寰:huan|寱:yi|寲:yi|寳:bao|寴:qin|寵:chong|寶:bao|寷:feng|寸:cun|对:dui|寺:si|寻:xun|导:dao|寽:lv|対:dui|寿:shou|尀:po|封:feng|専:zhuan|尃:fu|射:she|尅:ke|将:jiang|將:jiang|專:zhuan|尉:wei|尊:zun|尋:xun|尌:shu|對:dui|導:dao|小:xiao|尐:jie|少:shao|尒:er|尓:er|尔:er|尕:ga|尖:jian|尗:shu|尘:chen|尙:shang|尚:shang|尛:mo|尜:ga|尝:chang|尞:liao|尟:xian|尠:xian|尡:kun|尢:you|尣:wang|尤:you|尥:liao|尦:liao|尧:yao|尨:mang|尩:wang|尪:wang|尫:wang|尬:ga|尭:yao|尮:duo|尯:kui|尰:zhong|就:jiu|尲:gan|尳:gu|尴:gan|尵:tui|尶:gan|尷:gan|尸:shi|尹:yin|尺:chi|尻:kao|尼:ni|尽:jin|尾:wei|尿:niao|局:ju|屁:pi|层:ceng|屃:xi|屄:bi|居:ju|屆:jie|屇:tian|屈:qu|屉:ti|届:jie|屋:wu|屌:diao|屍:shi|屎:shi|屏:ping|屐:ji|屑:xie|屒:zhen|屓:xie|屔:ni|展:zhan|屖:xi|屗:xi|屘:man|屙:e|屚:lou|屛:ping|屜:ti|屝:fei|属:shu|屟:xie|屠:tu|屡:lv|屢:lv|屣:xi|層:ceng|履:lv|屦:ju|屧:xie|屨:ju|屩:jue|屪:liao|屫:jue|屬:zhu|屭:xie|屮:che|屯:tun|屰:ni|山:shan|屲:wa|屳:xian|屴:li|屵:e|屶:dao|屷:hui|屸:long|屹:yi|屺:qi|屻:ren|屼:wu|屽:han|屾:shen|屿:yu|岀:chu|岁:sui|岂:qi|岃:yen|岄:yue|岅:ban|岆:yao|岇:ang|岈:ya|岉:wu|岊:jie|岋:e|岌:ji|岍:qian|岎:fen|岏:wan|岐:qi|岑:cen|岒:qian|岓:qi|岔:cha|岕:jie|岖:qu|岗:gang|岘:xian|岙:ao|岚:lan|岛:dao|岜:ba|岝:zuo|岞:zuo|岟:yang|岠:ju|岡:gang|岢:ke|岣:gou|岤:xue|岥:po|岦:li|岧:tiao|岨:qu|岩:yan|岪:fu|岫:xiu|岬:jia|岭:ling|岮:tuo|岯:pi|岰:ao|岱:dai|岲:kuang|岳:yue|岴:qu|岵:hu|岶:po|岷:min|岸:an|岹:tiao|岺:ling|岻:chi|岼:ping|岽:dong|岾:ceng|岿:kui|峀:bang|峁:mao|峂:tong|峃:xue|峄:yi|峅:bian|峆:he|峇:ke|峈:luo|峉:e|峊:fu|峋:xun|峌:die|峍:lu|峎:en|峏:er|峐:gai|峑:quan|峒:dong|峓:yi|峔:mu|峕:shi|峖:an|峗:wei|峘:huan|峙:zhi|峚:mi|峛:li|峜:ji|峝:dong|峞:wei|峟:you|峠:gu|峡:xia|峢:lie|峣:yao|峤:jiao|峥:zheng|峦:luan|峧:jiao|峨:e|峩:e|峪:yu|峫:xie|峬:bu|峭:qiao|峮:qun|峯:feng|峰:feng|峱:nao|峲:li|峳:you|峴:xian|峵:rong|島:dao|峷:shen|峸:cheng|峹:tu|峺:geng|峻:jun|峼:gao|峽:xia|峾:yin|峿:yu|崀:lang|崁:kan|崂:lao|崃:lai|崄:xian|崅:que|崆:kong|崇:chong|崈:chong|崉:ta|崊:lin|崋:hua|崌:ju|崍:lai|崎:qi|崏:min|崐:kun|崑:kun|崒:zu|崓:gu|崔:cui|崕:ya|崖:ya|崗:gang|崘:lun|崙:lun|崚:ling|崛:jue|崜:duo|崝:zheng|崞:guo|崟:yin|崠:dong|崡:han|崢:zheng|崣:wei|崤:xiao|崥:pi|崦:yan|崧:song|崨:jie|崩:beng|崪:zu|崫:ku|崬:dong|崭:zhan|崮:gu|崯:yin|崰:zi|崱:ze|崲:huang|崳:yu|崴:wai|崵:yang|崶:feng|崷:qiu|崸:yang|崹:ti|崺:yi|崻:zhi|崼:shi|崽:zai|崾:yao|崿:e|嵀:zhu|嵁:kan|嵂:lv|嵃:yan|嵄:mei|嵅:han|嵆:ji|嵇:ji|嵈:huan|嵉:ting|嵊:sheng|嵋:mei|嵌:qian|嵍:wu|嵎:yu|嵏:zong|嵐:lan|嵑:ke|嵒:yan|嵓:yan|嵔:wei|嵕:zong|嵖:cha|嵗:sui|嵘:rong|嵙:ke|嵚:qin|嵛:yu|嵜:qi|嵝:lou|嵞:tu|嵟:dui|嵠:xi|嵡:weng|嵢:cang|嵣:tang|嵤:rong|嵥:jie|嵦:ai|嵧:liu|嵨:wu|嵩:song|嵪:qiao|嵫:zi|嵬:wei|嵭:beng|嵮:dian|嵯:cuo|嵰:qian|嵱:yong|嵲:nie|嵳:cuo|嵴:ji|嵵:shi|嵶:ruo|嵷:song|嵸:zong|嵹:jiang|嵺:liao|嵻:kang|嵼:chan|嵽:die|嵾:cen|嵿:ding|嶀:tu|嶁:lou|嶂:zhang|嶃:zhan|嶄:zhan|嶅:ao|嶆:cao|嶇:qu|嶈:qiang|嶉:cui|嶊:zui|嶋:dao|嶌:dao|嶍:xi|嶎:yu|嶏:pei|嶐:long|嶑:xiang|嶒:ceng|嶓:bo|嶔:qin|嶕:jiao|嶖:yan|嶗:lao|嶘:zhan|嶙:lin|嶚:liao|嶛:liao|嶜:jin|嶝:deng|嶞:duo|嶟:zun|嶠:jiao|嶡:jue|嶢:yao|嶣:jiao|嶤:yao|嶥:jue|嶦:zhan|嶧:yi|嶨:xue|嶩:nao|嶪:ye|嶫:ye|嶬:yi|嶭:nie|嶮:xian|嶯:ji|嶰:xie|嶱:ke|嶲:gui|嶳:di|嶴:ao|嶵:zui|嶶:wei|嶷:yi|嶸:rong|嶹:dao|嶺:ling|嶻:jie|嶼:yu|嶽:yue|嶾:yin|嶿:ru|巀:jie|巁:li|巂:gui|巃:long|巄:long|巅:dian|巆:ying|巇:xi|巈:ju|巉:chan|巊:ying|巋:kui|巌:yan|巍:wei|巎:nao|巏:quan|巐:chao|巑:cuan|巒:luan|巓:dian|巔:dian|巕:nie|巖:yan|巗:yan|巘:yan|巙:kui|巚:yan|巛:chuan|巜:kuai|川:chuan|州:zhou|巟:huang|巠:jing|巡:xun|巢:chao|巣:chao|巤:lie|工:gong|左:zuo|巧:qiao|巨:ju|巩:gong|巪:keng|巫:wu|巬:pu|巭:pu|差:cha|巯:qiu|巰:qiu|己:ji|已:yi|巳:si|巴:ba|巵:zhi|巶:zhao|巷:xiang|巸:yi|巹:jin|巺:sun|巻:quan|巼:peng|巽:xun|巾:jin|巿:fu|帀:za|币:bi|市:shi|布:bu|帄:ding|帅:shuai|帆:fan|帇:nie|师:shi|帉:fen|帊:pa|帋:zhi|希:xi|帍:hu|帎:dan|帏:wei|帐:zhang|帑:tang|帒:dai|帓:mo|帔:pei|帕:pa|帖:tie|帗:fu|帘:lian|帙:zhi|帚:zhou|帛:bo|帜:zhi|帝:di|帞:mo|帟:yi|帠:yi|帡:ping|帢:qia|帣:juan|帤:ru|帥:shuai|带:dai|帧:zheng|帨:shui|帩:qiao|帪:zhen|師:shi|帬:qun|席:xi|帮:bang|帯:dai|帰:gui|帱:chou|帲:ping|帳:zhang|帴:jian|帵:wan|帶:dai|帷:wei|常:chang|帹:sha|帺:qi|帻:ze|帼:guo|帽:mao|帾:du|帿:hou|幀:zheng|幁:xu|幂:mi|幃:wei|幄:wo|幅:fu|幆:yi|幇:bang|幈:ping|幉:die|幊:gong|幋:pan|幌:huang|幍:tao|幎:mi|幏:jia|幐:teng|幑:hui|幒:zhong|幓:shan|幔:man|幕:mu|幖:biao|幗:guo|幘:ze|幙:mu|幚:bang|幛:zhang|幜:jing|幝:chan|幞:fu|幟:zhi|幠:hu|幡:fan|幢:chuang|幣:bi|幤:bi|幥:zhang|幦:mi|幧:qiao|幨:chan|幩:fen|幪:meng|幫:bang|幬:chou|幭:mie|幮:chu|幯:jie|幰:xian|幱:lan|干:gan|平:ping|年:nian|幵:jian|并:bing|幷:bing|幸:xing|幹:gan|幺:yao|幻:huan|幼:you|幽:you|幾:ji|广:guang|庀:pi|庁:ting|庂:ze|広:guang|庄:zhuang|庅:me|庆:qing|庇:bi|庈:qin|庉:dun|床:chuang|庋:gui|庌:ya|庍:bai|庎:jie|序:xu|庐:lu|庑:wu|庒:zhuang|库:ku|应:ying|底:di|庖:pao|店:dian|庘:ya|庙:miao|庚:geng|庛:ci|府:fu|庝:tong|庞:pang|废:fei|庠:xiang|庡:yi|庢:zhi|庣:tiao|庤:zhi|庥:xiu|度:du|座:zuo|庨:xiao|庩:tu|庪:gui|庫:ku|庬:meng|庭:ting|庮:you|庯:bu|庰:bing|庱:cheng|庲:lai|庳:bi|庴:ji|庵:an|庶:shu|康:kang|庸:yong|庹:tuo|庺:song|庻:shu|庼:qing|庽:yu|庾:yu|庿:miao|廀:sou|廁:ce|廂:xiang|廃:fei|廄:jiu|廅:e|廆:wei|廇:liu|廈:xia|廉:lian|廊:lang|廋:sou|廌:zhi|廍:bu|廎:qing|廏:jiu|廐:jiu|廑:jin|廒:ao|廓:kuo|廔:lou|廕:yin|廖:liao|廗:dai|廘:lu|廙:yi|廚:chu|廛:chan|廜:tu|廝:si|廞:xin|廟:miao|廠:chang|廡:wu|廢:fei|廣:guang|廤:kou|廥:kuai|廦:bi|廧:qiang|廨:xie|廩:lin|廪:lin|廫:liao|廬:lu|廭:ji|廮:ying|廯:xian|廰:ting|廱:yong|廲:li|廳:ting|廴:yin|廵:xun|延:yan|廷:ting|廸:di|廹:po|建:jian|廻:hui|廼:nai|廽:hui|廾:gong|廿:nian|开:kai|弁:bian|异:yi|弃:qi|弄:nong|弅:fen|弆:ju|弇:yan|弈:yi|弉:zang|弊:bi|弋:yi|弌:yi|弍:er|弎:san|式:shi|弐:er|弑:shi|弒:shi|弓:gong|弔:diao|引:yin|弖:hu|弗:fu|弘:hong|弙:wu|弚:di|弛:chi|弜:jiang|弝:ba|弞:shen|弟:di|张:zhang|弡:zhang|弢:tao|弣:fu|弤:di|弥:mi|弦:xian|弧:hu|弨:chao|弩:nu|弪:jing|弫:zhen|弬:yi|弭:mi|弮:juan|弯:wan|弰:shao|弱:ruo|弲:yuan|弳:jing|弴:diao|張:zhang|弶:jiang|強:qiang|弸:peng|弹:dan|强:qiang|弻:bi|弼:bi|弽:she|弾:tan|弿:jian|彀:gou|彁:ge|彂:fa|彃:bi|彄:kou|彅:jian|彆:bie|彇:xiao|彈:dan|彉:guo|彊:jiang|彋:hong|彌:mi|彍:guo|彎:wan|彏:jue|彐:ji|彑:ji|归:gui|当:dang|彔:lu|录:lu|彖:tuan|彗:hui|彘:zhi|彙:hui|彚:hui|彛:yi|彜:yi|彝:yi|彞:yi|彟:yue|彠:yue|彡:shan|形:xing|彣:wen|彤:tong|彥:yan|彦:yan|彧:yu|彨:chi|彩:cai|彪:biao|彫:diao|彬:bin|彭:peng|彮:yong|彯:piao|彰:zhang|影:ying|彲:chi|彳:chi|彴:zhuo|彵:tuo|彶:ji|彷:fang|彸:zhong|役:yi|彺:wang|彻:che|彼:bi|彽:di|彾:ling|彿:fu|往:wang|征:zheng|徂:cu|徃:wang|径:jing|待:dai|徆:xi|徇:xun|很:hen|徉:yang|徊:huai|律:lv|後:hou|徍:wa|徎:cheng|徏:zhi|徐:xu|徑:jing|徒:tu|従:cong|徔:zhi|徕:lai|徖:cong|得:de|徘:pai|徙:xi|徚:xi|徛:ji|徜:chang|徝:zhi|從:cong|徟:zhou|徠:lai|御:yu|徢:xie|徣:jie|徤:jian|徥:shi|徦:jia|徧:bian|徨:huang|復:fu|循:xun|徫:wei|徬:bang|徭:yao|微:wei|徯:xi|徰:zheng|徱:piao|徲:ti|徳:de|徴:zheng|徵:zheng|徶:bie|德:de|徸:chong|徹:che|徺:yao|徻:hui|徼:jiao|徽:hui|徾:mei|徿:long|忀:xiang|忁:bao|忂:qu|心:xin|忄:xin|必:bi|忆:yi|忇:le|忈:ren|忉:dao|忊:ding|忋:gai|忌:ji|忍:ren|忎:ren|忏:chan|忐:tan|忑:te|忒:te|忓:gan|忔:yi|忕:shi|忖:cun|志:zhi|忘:wang|忙:mang|忚:xi|忛:fan|応:ying|忝:tian|忞:min|忟:wen|忠:zhong|忡:chong|忢:wu|忣:ji|忤:wu|忥:xi|忦:jie|忧:you|忨:wan|忩:cong|忪:song|快:kuai|忬:yu|忭:bian|忮:zhi|忯:qi|忰:cui|忱:chen|忲:tai|忳:tun|忴:qian|念:nian|忶:hun|忷:xiong|忸:niu|忹:wang|忺:xian|忻:xin|忼:kang|忽:hu|忾:kai|忿:fen|怀:huai|态:tai|怂:song|怃:wu|怄:ou|怅:chang|怆:chuang|怇:ju|怈:yi|怉:bao|怊:chao|怋:min|怌:pei|怍:zuo|怎:zen|怏:yang|怐:kou|怑:ban|怒:nu|怓:nao|怔:zheng|怕:pa|怖:bu|怗:tie|怘:gu|怙:hu|怚:ju|怛:da|怜:lian|思:si|怞:you|怟:di|怠:dai|怡:yi|怢:tu|怣:you|怤:fu|急:ji|怦:peng|性:xing|怨:yuan|怩:ni|怪:guai|怫:fu|怬:xi|怭:bi|怮:you|怯:qie|怰:xuan|怱:cong|怲:bing|怳:huang|怴:xue|怵:chu|怶:pi|怷:shu|怸:xi|怹:tan|怺:yong|总:zong|怼:dui|怽:mo|怾:keng|怿:yi|恀:shi|恁:ren|恂:xun|恃:shi|恄:xi|恅:lao|恆:heng|恇:kuang|恈:mou|恉:zhi|恊:xie|恋:lian|恌:tiao|恍:huang|恎:die|恏:hao|恐:kong|恑:gui|恒:heng|恓:qi|恔:xiao|恕:shu|恖:si|恗:hu|恘:qiu|恙:yang|恚:hui|恛:hui|恜:chi|恝:jia|恞:yi|恟:xiong|恠:guai|恡:lin|恢:hui|恣:zi|恤:xu|恥:chi|恦:shang|恧:nv|恨:hen|恩:en|恪:ke|恫:dong|恬:tian|恭:gong|恮:quan|息:xi|恰:qia|恱:yue|恲:peng|恳:ken|恴:de|恵:hui|恶:e|恷:xiao|恸:tong|恹:yan|恺:kai|恻:ce|恼:nao|恽:yun|恾:mang|恿:yong|悀:yong|悁:yuan|悂:pi|悃:kun|悄:qiao|悅:yue|悆:yu|悇:tu|悈:jie|悉:xi|悊:zhe|悋:lin|悌:ti|悍:han|悎:hao|悏:qie|悐:ti|悑:bu|悒:yi|悓:qian|悔:hui|悕:xi|悖:bei|悗:man|悘:yi|悙:heng|悚:song|悛:quan|悜:cheng|悝:kui|悞:wu|悟:wu|悠:you|悡:li|悢:lang|患:huan|悤:cong|悥:yi|悦:yue|悧:li|您:nin|悩:nao|悪:e|悫:que|悬:xuan|悭:qian|悮:wu|悯:min|悰:cong|悱:fei|悲:bei|悳:de|悴:cui|悵:chang|悶:men|悷:li|悸:ji|悹:guan|悺:guan|悻:xing|悼:dao|悽:qi|悾:kong|悿:tian|惀:lun|惁:xi|惂:kan|惃:gun|惄:ni|情:qing|惆:chou|惇:dun|惈:guo|惉:zhan|惊:jing|惋:wan|惌:yuan|惍:jin|惎:ji|惏:lin|惐:yu|惑:huo|惒:he|惓:quan|惔:tan|惕:ti|惖:ti|惗:nie|惘:wang|惙:chuo|惚:hu|惛:hun|惜:xi|惝:chang|惞:xin|惟:wei|惠:hui|惡:e|惢:suo|惣:zong|惤:jian|惥:yong|惦:dian|惧:ju|惨:can|惩:cheng|惪:de|惫:bei|惬:qie|惭:can|惮:dan|惯:guan|惰:duo|惱:nao|惲:yun|想:xiang|惴:zhui|惵:die|惶:huang|惷:chun|惸:qiong|惹:re|惺:xing|惻:ce|惼:bian|惽:hun|惾:zong|惿:ti|愀:qiao|愁:chou|愂:bei|愃:xuan|愄:wei|愅:ge|愆:qian|愇:wei|愈:yu|愉:yu|愊:bi|愋:xuan|愌:huan|愍:min|愎:bi|意:yi|愐:mian|愑:yong|愒:qi|愓:shang|愔:yin|愕:e|愖:chen|愗:mao|愘:qia|愙:ke|愚:yu|愛:ai|愜:qie|愝:yan|愞:ruan|感:gan|愠:yun|愡:zong|愢:sai|愣:leng|愤:fen|愥:ying|愦:kui|愧:kui|愨:que|愩:gong|愪:yun|愫:su|愬:su|愭:qi|愮:yao|愯:song|愰:huang|愱:ji|愲:gu|愳:ju|愴:chuang|愵:ni|愶:xie|愷:kai|愸:zheng|愹:yong|愺:cao|愻:xun|愼:shen|愽:bo|愾:kai|愿:yuan|慀:xi|慁:hun|慂:yong|慃:yang|慄:li|慅:sao|慆:tao|慇:yin|慈:ci|慉:xu|慊:qian|態:tai|慌:huang|慍:yun|慎:shen|慏:ming|慐:gong|慑:she|慒:cong|慓:piao|慔:mu|慕:mu|慖:guo|慗:chi|慘:can|慙:can|慚:can|慛:cui|慜:min|慝:te|慞:zhang|慟:tong|慠:ao|慡:shuang|慢:man|慣:guan|慤:que|慥:zao|慦:jiu|慧:hui|慨:kai|慩:lian|慪:ou|慫:song|慬:jin|慭:yin|慮:lv|慯:shang|慰:wei|慱:tuan|慲:man|慳:qian|慴:she|慵:yong|慶:qing|慷:kang|慸:di|慹:zhi|慺:lou|慻:juan|慼:qi|慽:qi|慾:yu|慿:ping|憀:liao|憁:cong|憂:you|憃:chong|憄:zhi|憅:tong|憆:cheng|憇:qi|憈:qu|憉:peng|憊:bei|憋:bie|憌:qiong|憍:jiao|憎:zeng|憏:chi|憐:lian|憑:ping|憒:kui|憓:hui|憔:qiao|憕:cheng|憖:yin|憗:yin|憘:xi|憙:xi|憚:dan|憛:tan|憜:duo|憝:dui|憞:dun|憟:su|憠:jue|憡:ce|憢:xiao|憣:fan|憤:fen|憥:lao|憦:lao|憧:chong|憨:han|憩:qi|憪:xian|憫:min|憬:jing|憭:liao|憮:wu|憯:can|憰:jue|憱:cu|憲:xian|憳:tan|憴:sheng|憵:pi|憶:yi|憷:chu|憸:xian|憹:nong|憺:dan|憻:tan|憼:jing|憽:song|憾:han|憿:ji|懀:wei|懁:huan|懂:dong|懃:qin|懄:qin|懅:ju|懆:cao|懇:ken|懈:xie|應:ying|懊:ao|懋:mao|懌:yi|懍:lin|懎:se|懏:jun|懐:huai|懑:men|懒:lan|懓:ai|懔:lin|懕:yan|懖:kuo|懗:xia|懘:chi|懙:yu|懚:yin|懛:dai|懜:meng|懝:ai|懞:meng|懟:dui|懠:qi|懡:mo|懢:lan|懣:men|懤:chou|懥:zhi|懦:nuo|懧:nuo|懨:chu|懩:yang|懪:bo|懫:zhi|懬:kuang|懭:kuang|懮:you|懯:fu|懰:liu|懱:mie|懲:cheng|懳:hui|懴:chan|懵:meng|懶:lan|懷:huai|懸:xuan|懹:rang|懺:chan|懻:ji|懼:ju|懽:huan|懾:she|懿:yi|戀:lian|戁:nan|戂:mi|戃:tang|戄:jue|戅:zhuang|戆:gang|戇:gang|戈:ge|戉:yue|戊:wu|戋:jian|戌:xu|戍:shu|戎:rong|戏:xi|成:cheng|我:wo|戒:jie|戓:ge|戔:jian|戕:qiang|或:huo|戗:qiang|战:zhan|戙:dong|戚:qi|戛:jia|戜:die|戝:cai|戞:jia|戟:ji|戠:zhi|戡:kan|戢:ji|戣:kui|戤:gai|戥:deng|戦:zhan|戧:qiang|戨:ge|戩:jian|截:jie|戫:yu|戬:jian|戭:yan|戮:lu|戯:xi|戰:zhan|戱:xi|戲:xi|戳:chuo|戴:dai|戵:qu|戶:hu|户:hu|戸:hu|戹:e|戺:shi|戻:ti|戼:mao|戽:hu|戾:li|房:fang|所:suo|扁:bian|扂:dian|扃:jiong|扄:shang|扅:yi|扆:yi|扇:shan|扈:hu|扉:fei|扊:yan|手:shou|扌:shou|才:cai|扎:zha|扏:qiu|扐:le|扑:pu|扒:ba|打:da|扔:reng|払:fan|扖:ru|扗:zai|托:tuo|扙:zhang|扚:diao|扛:kang|扜:yu|扝:ku|扞:han|扟:shen|扠:cha|扡:tuo|扢:gu|扣:kou|扤:wu|扥:den|扦:qian|执:zhi|扨:ren|扩:kuo|扪:men|扫:sao|扬:yang|扭:niu|扮:ban|扯:che|扰:rao|扱:cha|扲:qian|扳:ban|扴:jia|扵:yu|扶:fu|扷:ao|扸:xi|批:pi|扺:zhi|扻:zhi|扼:e|扽:den|找:zhao|承:cheng|技:ji|抁:yan|抂:kuang|抃:bian|抄:chao|抅:ju|抆:wen|抇:hu|抈:yue|抉:jue|把:ba|抋:qin|抌:shen|抍:zheng|抎:yun|抏:wan|抐:ne|抑:yi|抒:shu|抓:zhua|抔:pou|投:tou|抖:dou|抗:kang|折:zhe|抙:pou|抚:fu|抛:pao|抜:ba|抝:ao|択:ze|抟:tuan|抠:kou|抡:lun|抢:qiang|抣:yun|护:hu|报:bao|抦:bing|抧:zhi|抨:peng|抩:nan|抪:pu|披:pi|抬:tai|抭:yao|抮:zhen|抯:zha|抰:yang|抱:bao|抲:he|抳:ni|抴:ye|抵:di|抶:chi|抷:pi|抸:jia|抹:mo|抺:mei|抻:chen|押:ya|抽:chou|抾:qu|抿:min|拀:chu|拁:jia|拂:fu|拃:zhan|拄:zhu|担:dan|拆:chai|拇:mu|拈:nian|拉:la|拊:fu|拋:pao|拌:ban|拍:pai|拎:lin|拏:na|拐:guai|拑:qian|拒:ju|拓:tuo|拔:ba|拕:tuo|拖:tuo|拗:ao|拘:ju|拙:zhuo|拚:bian|招:zhao|拜:bai|拝:bai|拞:di|拟:ni|拠:ju|拡:kuo|拢:long|拣:jian|拤:qia|拥:yong|拦:lan|拧:ning|拨:bo|择:ze|拪:qian|拫:hen|括:kuo|拭:shi|拮:jie|拯:zheng|拰:nin|拱:gong|拲:gong|拳:quan|拴:shuan|拵:cun|拶:za|拷:kao|拸:chi|拹:xie|拺:ce|拻:hui|拼:pin|拽:zhuai|拾:shi|拿:na|挀:bai|持:chi|挂:gua|挃:zhi|挄:kuo|挅:duo|挆:duo|指:zhi|挈:qie|按:an|挊:nong|挋:zhen|挌:ge|挍:jiao|挎:kua|挏:dong|挐:na|挑:tiao|挒:lie|挓:zha|挔:lu|挕:die|挖:wa|挗:jue|挘:lie|挙:ju|挚:zhi|挛:luan|挜:ya|挝:wo|挞:ta|挟:xie|挠:nao|挡:dang|挢:jiao|挣:zheng|挤:ji|挥:hui|挦:xian|挧:yu|挨:ai|挩:tuo|挪:nuo|挫:cuo|挬:bo|挭:geng|挮:ti|振:zhen|挰:cheng|挱:sha|挲:suo|挳:keng|挴:mei|挵:nong|挶:ju|挷:peng|挸:jian|挹:yi|挺:ting|挻:shan|挼:ruo|挽:wan|挾:xie|挿:cha|捀:feng|捁:jiao|捂:wu|捃:jun|捄:ju|捅:tong|捆:kun|捇:huo|捈:tu|捉:zhuo|捊:pou|捋:luo|捌:ba|捍:han|捎:shao|捏:nie|捐:juan|捑:ze|捒:song|捓:ye|捔:jue|捕:bo|捖:wan|捗:bu|捘:zun|捙:ye|捚:zhai|捛:lu|捜:sou|捝:tuo|捞:lao|损:sun|捠:bang|捡:jian|换:huan|捣:dao|捤:wei|捥:wan|捦:qin|捧:peng|捨:she|捩:lie|捪:min|捫:men|捬:fu|捭:bai|据:ju|捯:dao|捰:wo|捱:ai|捲:juan|捳:yue|捴:zong|捵:tian|捶:chui|捷:jie|捸:tu|捹:ben|捺:na|捻:nian|捼:ruo|捽:zuo|捾:wo|捿:qi|掀:xian|掁:cheng|掂:dian|掃:sao|掄:lun|掅:qing|掆:gang|掇:duo|授:shou|掉:diao|掊:pou|掋:di|掌:zhang|掍:hun|掎:ji|掏:tao|掐:qia|掑:qi|排:pai|掓:shu|掔:qian|掕:ling|掖:ye|掗:ya|掘:jue|掙:zheng|掚:liang|掛:gua|掜:ni|掝:huo|掞:yan|掟:zheng|掠:lue|採:cai|探:tan|掣:che|掤:bing|接:jie|掦:ti|控:kong|推:tui|掩:yan|措:cuo|掫:zou|掬:ju|掭:tian|掮:qian|掯:ken|掰:bai|掱:pa|掲:jie|掳:lu|掴:guo|掵:ming|掶:geng|掷:zhi|掸:dan|掹:meng|掺:chan|掻:sao|掼:guan|掽:peng|掾:yuan|掿:nuo|揀:jian|揁:zheng|揂:jiu|揃:jian|揄:yu|揅:yan|揆:kui|揇:nan|揈:hong|揉:rou|揊:pi|揋:wei|揌:sai|揍:zou|揎:xuan|描:miao|提:ti|揑:nie|插:cha|揓:shi|揔:zong|揕:zhen|揖:yi|揗:xun|揘:huang|揙:bian|揚:yang|換:huan|揜:yan|揝:zuan|揞:an|揟:xu|揠:ya|握:wo|揢:ke|揣:chuai|揤:ji|揥:ti|揦:la|揧:la|揨:chen|揩:kai|揪:jiu|揫:jiu|揬:tu|揭:jie|揮:hui|揯:gen|揰:chong|揱:xiao|揲:die|揳:xie|援:yuan|揵:qian|揶:ye|揷:cha|揸:zha|揹:bei|揺:yao|揻:wei|揼:den|揽:lan|揾:wen|揿:qin|搀:chan|搁:ge|搂:lou|搃:zong|搄:gen|搅:jiao|搆:gou|搇:qin|搈:rong|搉:que|搊:chou|搋:chu|搌:zhan|損:sun|搎:sun|搏:bo|搐:chu|搑:rong|搒:peng|搓:cuo|搔:sao|搕:ke|搖:yao|搗:dao|搘:zhi|搙:nu|搚:la|搛:jian|搜:sou|搝:qiu|搞:gao|搟:xian|搠:shuo|搡:sang|搢:jin|搣:mie|搤:e|搥:chui|搦:nuo|搧:shan|搨:ta|搩:zha|搪:tang|搫:pan|搬:ban|搭:da|搮:li|搯:tao|搰:hu|搱:zhi|搲:wa|搳:xia|搴:qian|搵:wen|搶:qiang|搷:tian|搸:zhen|搹:e|携:xie|搻:nuo|搼:quan|搽:cha|搾:zha|搿:ge|摀:wu|摁:en|摂:she|摃:kang|摄:she|摅:shu|摆:bai|摇:yao|摈:bin|摉:rong|摊:tan|摋:sa|摌:chan|摍:suo|摎:jiu|摏:chong|摐:chuang|摑:guo|摒:bing|摓:feng|摔:shuai|摕:di|摖:qi|摗:sou|摘:zhai|摙:lian|摚:cheng|摛:chi|摜:guan|摝:lu|摞:luo|摟:lou|摠:zong|摡:gai|摢:hu|摣:zha|摤:qiang|摥:tang|摦:hua|摧:cui|摨:nai|摩:mo|摪:jiang|摫:gui|摬:ying|摭:zhi|摮:ao|摯:zhi|摰:nie|摱:man|摲:chan|摳:kou|摴:chu|摵:she|摶:tuan|摷:jiao|摸:mo|摹:mo|摺:zhe|摻:chan|摼:qian|摽:biao|摾:jiang|摿:yao|撀:gou|撁:qian|撂:liao|撃:ji|撄:ying|撅:jue|撆:pie|撇:pie|撈:lao|撉:dun|撊:xian|撋:ruan|撌:gui|撍:zan|撎:yi|撏:xian|撐:cheng|撑:cheng|撒:sa|撓:nao|撔:hong|撕:si|撖:han|撗:guang|撘:da|撙:zun|撚:nian|撛:lin|撜:zheng|撝:hui|撞:zhuang|撟:jiao|撠:ji|撡:cao|撢:tan|撣:dan|撤:che|撥:bo|撦:che|撧:jue|撨:xiao|撩:liao|撪:ben|撫:fu|撬:qiao|播:bo|撮:cuo|撯:zhuo|撰:zhuan|撱:tuo|撲:pu|撳:qin|撴:dun|撵:nian|撶:hua|撷:xie|撸:lu|撹:jiao|撺:cuan|撻:ta|撼:han|撽:qiao|撾:wo|撿:jian|擀:gan|擁:yong|擂:lei|擃:nang|擄:lu|擅:shan|擆:zhuo|擇:ze|擈:pu|擉:chuo|擊:ji|擋:dang|擌:se|操:cao|擎:qing|擏:qing|擐:huan|擑:jie|擒:qin|擓:kuai|擔:dan|擕:xie|擖:ye|擗:pi|擘:bo|擙:ao|據:ju|擛:ye|擜:e|擝:meng|擞:sou|擟:mi|擠:ji|擡:tai|擢:zhuo|擣:dao|擤:xing|擥:lan|擦:ca|擧:ju|擨:ye|擩:ru|擪:ye|擫:ye|擬:ni|擭:huo|擮:jie|擯:bin|擰:ning|擱:ge|擲:zhi|擳:jie|擴:kuo|擵:mo|擶:jian|擷:xie|擸:lie|擹:tan|擺:bai|擻:sou|擼:lu|擽:lve|擾:rao|擿:zhi|攀:pan|攁:yang|攂:lei|攃:ca|攄:shu|攅:cuan|攆:nian|攇:xian|攈:jun|攉:huo|攊:li|攋:la|攌:huan|攍:ying|攎:lu|攏:long|攐:qian|攑:qian|攒:zan|攓:qian|攔:lan|攕:xian|攖:ying|攗:mei|攘:rang|攙:chan|攚:ying|攛:cuan|攜:xie|攝:she|攞:luo|攟:mei|攠:mi|攡:chi|攢:zan|攣:luan|攤:tan|攥:zuan|攦:li|攧:dian|攨:wa|攩:dang|攪:jiao|攫:jue|攬:lan|攭:li|攮:nang|支:zhi|攰:gui|攱:gui|攲:ji|攳:xun|攴:pu|攵:pu|收:shou|攷:kao|攸:you|改:gai|攺:yi|攻:gong|攼:gan|攽:ban|放:fang|政:zheng|敀:po|敁:dian|敂:kou|敃:min|敄:wu|故:gu|敆:he|敇:ce|效:xiao|敉:mi|敊:chu|敋:ge|敌:di|敍:xu|敎:jiao|敏:min|敐:chen|救:jiu|敒:zhen|敓:duo|敔:yu|敕:chi|敖:ao|敗:bai|敘:xu|教:jiao|敚:duo|敛:lian|敜:nie|敝:bi|敞:chang|敟:dian|敠:duo|敡:yi|敢:gan|散:san|敤:ke|敥:yan|敦:dun|敧:ji|敨:tou|敩:xiao|敪:duo|敫:jiao|敬:jing|敭:yang|敮:xia|敯:min|数:shu|敱:ai|敲:qiao|敳:ai|整:zheng|敵:di|敶:zhen|敷:fu|數:shu|敹:liao|敺:qu|敻:xiong|敼:yi|敽:jiao|敾:shan|敿:jiao|斀:zhuo|斁:yi|斂:lian|斃:bi|斄:li|斅:xiao|斆:xiao|文:wen|斈:xue|斉:qi|斊:qi|斋:zhai|斌:bin|斍:jue|斎:zhai|斏:zhai|斐:fei|斑:ban|斒:ban|斓:lan|斔:yu|斕:lan|斖:wei|斗:dou|斘:sheng|料:liao|斚:jia|斛:hu|斜:xie|斝:jia|斞:yu|斟:zhen|斠:jiao|斡:wo|斢:tou|斣:dou|斤:jin|斥:chi|斦:yin|斧:fu|斨:qiang|斩:zhan|斪:qu|斫:zhuo|斬:zhan|断:duan|斮:zhuo|斯:si|新:xin|斱:zhuo|斲:zhuo|斳:qin|斴:lin|斵:zhuo|斶:chu|斷:duan|斸:zhu|方:fang|斺:jie|斻:hang|於:yu|施:shi|斾:pei|斿:you|旀:mu|旁:pang|旂:qi|旃:zhan|旄:mao|旅:lv|旆:pei|旇:pi|旈:liu|旉:fu|旊:fang|旋:xuan|旌:jing|旍:jing|旎:ni|族:zu|旐:zhao|旑:yi|旒:liu|旓:shao|旔:jian|旕:er|旖:yi|旗:qi|旘:zhi|旙:fan|旚:piao|旛:fan|旜:zhan|旝:kuai|旞:sui|旟:yu|无:wu|旡:ji|既:ji|旣:ji|旤:huo|日:ri|旦:dan|旧:jiu|旨:zhi|早:zao|旪:xie|旫:tiao|旬:xun|旭:xu|旮:ga|旯:la|旰:gan|旱:han|旲:tai|旳:di|旴:xu|旵:chan|时:shi|旷:kuang|旸:yang|旹:shi|旺:wang|旻:min|旼:min|旽:tun|旾:chun|旿:wu|昀:yun|昁:bei|昂:ang|昃:ze|昄:ban|昅:jie|昆:kun|昇:sheng|昈:hu|昉:fang|昊:hao|昋:gui|昌:chang|昍:xuan|明:ming|昏:hun|昐:fen|昑:qin|昒:hu|易:yi|昔:xi|昕:xin|昖:yan|昗:ze|昘:fang|昙:tan|昚:shen|昛:ju|昜:yang|昝:zan|昞:bing|星:xing|映:ying|昡:xuan|昢:po|昣:zhen|昤:ling|春:chun|昦:hao|昧:mei|昨:zuo|昩:mo|昪:bian|昫:xu|昬:hun|昭:zhao|昮:zong|是:shi|昰:shi|昱:yu|昲:fei|昳:die|昴:mao|昵:ni|昶:chang|昷:wei|昸:dong|昹:ai|昺:bing|昻:ang|昼:zhou|昽:long|显:xian|昿:kuang|晀:tiao|晁:chao|時:shi|晃:huang|晄:huang|晅:xuan|晆:kui|晇:kua|晈:jiao|晉:jin|晊:zhi|晋:jin|晌:shang|晍:tong|晎:hong|晏:yan|晐:gai|晑:xiang|晒:shai|晓:xiao|晔:ye|晕:yun|晖:hui|晗:han|晘:han|晙:jun|晚:wan|晛:xian|晜:kun|晝:zhou|晞:xi|晟:sheng|晠:sheng|晡:bu|晢:zhe|晣:zhe|晤:wu|晥:han|晦:hui|晧:hao|晨:chen|晩:wan|晪:tian|晫:zhuo|晬:zui|晭:zhou|普:pu|景:jing|晰:xi|晱:shan|晲:ni|晳:xi|晴:qing|晵:qi|晶:jing|晷:gui|晸:zheng|晹:yi|智:zhi|晻:an|晼:wan|晽:lin|晾:liang|晿:chang|暀:wang|暁:xiao|暂:zan|暃:fei|暄:xuan|暅:xuan|暆:yi|暇:xia|暈:yun|暉:hui|暊:xu|暋:min|暌:kui|暍:ye|暎:ying|暏:shu|暐:wei|暑:shu|暒:qing|暓:mao|暔:nan|暕:jian|暖:nuan|暗:an|暘:yang|暙:chun|暚:yao|暛:suo|暜:pu|暝:ming|暞:jiao|暟:kai|暠:gao|暡:weng|暢:chang|暣:qi|暤:hao|暥:yan|暦:li|暧:ai|暨:ji|暩:ji|暪:men|暫:zan|暬:xie|暭:hao|暮:mu|暯:mo|暰:cong|暱:ni|暲:zhang|暳:hui|暴:bao|暵:han|暶:xuan|暷:chuan|暸:liao|暹:xian|暺:tan|暻:jing|暼:pie|暽:lin|暾:tun|暿:xi|曀:yi|曁:ji|曂:huang|曃:dai|曄:ye|曅:ye|曆:li|曇:tan|曈:tong|曉:xiao|曊:fei|曋:shen|曌:zhao|曍:hao|曎:yi|曏:xiang|曐:xing|曑:shan|曒:jiao|曓:bao|曔:jing|曕:yan|曖:ai|曗:ye|曘:ru|曙:shu|曚:meng|曛:xun|曜:yao|曝:pu|曞:li|曟:chen|曠:kuang|曡:die|曢:die|曣:yao|曤:huo|曥:lv|曦:xi|曧:rong|曨:long|曩:nang|曪:luo|曫:luan|曬:shai|曭:tang|曮:yan|曯:zhu|曰:yue|曱:yue|曲:qu|曳:ye|更:geng|曵:yi|曶:hu|曷:he|書:shu|曹:cao|曺:cao|曻:sheng|曼:man|曽:zeng|曾:zeng|替:ti|最:zui|朁:can|朂:xu|會:hui|朄:yin|朅:qie|朆:fen|朇:pi|月:yue|有:you|朊:ruan|朋:peng|朌:ban|服:fu|朎:ling|朏:fei|朐:qu|朑:qu|朒:nv|朓:tiao|朔:shuo|朕:zhen|朖:lang|朗:lang|朘:zui|朙:ming|朚:huang|望:wang|朜:tun|朝:chao|朞:qi|期:qi|朠:ying|朡:zong|朢:wang|朣:tong|朤:lang|朥:lao|朦:meng|朧:long|木:mu|朩:pin|未:wei|末:mo|本:ben|札:zha|朮:shu|术:shu|朰:teng|朱:zhu|朲:ren|朳:ba|朴:pu|朵:duo|朶:duo|朷:dao|朸:li|朹:qiu|机:ji|朻:jiu|朼:bi|朽:xiu|朾:cheng|朿:ci|杀:sha|杁:ru|杂:za|权:quan|杄:qian|杅:yu|杆:gan|杇:wu|杈:cha|杉:shan|杊:xun|杋:fan|杌:wu|杍:zi|李:li|杏:xing|材:cai|村:cun|杒:ren|杓:biao|杔:tuo|杕:di|杖:zhang|杗:mang|杘:chi|杙:yi|杚:gai|杛:gong|杜:du|杝:yi|杞:qi|束:shu|杠:gang|条:tiao|杢:jiang|杣:shan|杤:wan|来:lai|杦:jiu|杧:mang|杨:yang|杩:ma|杪:miao|杫:xi|杬:yuan|杭:hang|杮:fei|杯:bei|杰:jie|東:dong|杲:gao|杳:yao|杴:xian|杵:chu|杶:chun|杷:pa|杸:shu|杹:hua|杺:xin|杻:niu|杼:zhu|杽:chou|松:song|板:ban|枀:song|极:ji|枂:yue|枃:jin|构:gou|枅:ji|枆:mao|枇:pi|枈:bi|枉:wang|枊:ang|枋:fang|枌:fen|枍:yi|枎:fu|枏:nan|析:xi|枑:hu|枒:ya|枓:dou|枔:xin|枕:zhen|枖:yao|林:lin|枘:rui|枙:e|枚:mei|枛:zhao|果:guo|枝:zhi|枞:cong|枟:yun|枠:zui|枡:sheng|枢:shu|枣:zao|枤:di|枥:li|枦:lu|枧:jian|枨:cheng|枩:song|枪:qiang|枫:feng|枬:nan|枭:xiao|枮:xian|枯:ku|枰:ping|枱:si|枲:xi|枳:zhi|枴:guai|枵:xiao|架:jia|枷:jia|枸:gou|枹:fu|枺:mo|枻:yi|枼:ye|枽:ye|枾:shi|枿:nie|柀:bi|柁:tuo|柂:yi|柃:ling|柄:bing|柅:ni|柆:la|柇:he|柈:pan|柉:fan|柊:zhong|柋:dai|柌:ci|柍:yang|柎:fu|柏:bai|某:mou|柑:gan|柒:qi|染:ran|柔:rou|柕:shu|柖:shao|柗:song|柘:zhe|柙:xia|柚:you|柛:shen|柜:gui|柝:tuo|柞:zuo|柟:nan|柠:ning|柡:yong|柢:di|柣:zhi|柤:zha|查:cha|柦:dan|柧:gu|柨:pu|柩:jiu|柪:ao|柫:fu|柬:jian|柭:bo|柮:duo|柯:ke|柰:nai|柱:zhu|柲:bi|柳:liu|柴:chai|柵:zha|柶:si|柷:zhu|柸:pei|柹:shi|柺:guai|査:cha|柼:yao|柽:cheng|柾:jiu|柿:shi|栀:zhi|栁:liu|栂:mei|栃:li|栄:rong|栅:zha|栆:zao|标:biao|栈:zhan|栉:zhi|栊:long|栋:dong|栌:lu|栍:sa|栎:li|栏:lan|栐:yong|树:shu|栒:xun|栓:shuan|栔:qi|栕:zhen|栖:qi|栗:li|栘:yi|栙:xiang|栚:zhen|栛:li|栜:se|栝:kuo|栞:kan|栟:bing|栠:ren|校:xiao|栢:bai|栣:ren|栤:bing|栥:zi|栦:chou|栧:yi|栨:ci|栩:xu|株:zhu|栫:jian|栬:zui|栭:er|栮:er|栯:yu|栰:fa|栱:gong|栲:kao|栳:lao|栴:zhan|栵:li|栶:yin|样:yang|核:he|根:gen|栺:yi|栻:shi|格:ge|栽:zai|栾:luan|栿:fu|桀:jie|桁:heng|桂:gui|桃:tao|桄:guang|桅:wei|框:kuang|桇:ru|案:an|桉:an|桊:juan|桋:yi|桌:zhuo|桍:ku|桎:zhi|桏:qiong|桐:tong|桑:sang|桒:sang|桓:huan|桔:jie|桕:jiu|桖:xue|桗:duo|桘:chui|桙:mou|桚:za|桛:nuo|桜:ying|桝:jie|桞:liu|桟:zhan|桠:ya|桡:rao|桢:zhen|档:dang|桤:qi|桥:qiao|桦:hua|桧:hui|桨:jiang|桩:zhuang|桪:xun|桫:suo|桬:sa|桭:zhen|桮:bei|桯:ting|桰:kuo|桱:jing|桲:bo|桳:ben|桴:fu|桵:rui|桶:tong|桷:jue|桸:xi|桹:lang|桺:liu|桻:feng|桼:qi|桽:wen|桾:jun|桿:gan|梀:su|梁:liang|梂:qiu|梃:ting|梄:you|梅:mei|梆:bang|梇:long|梈:peng|梉:zhuang|梊:di|梋:xuan|梌:tu|梍:zao|梎:you|梏:gu|梐:bi|梑:di|梒:han|梓:zi|梔:zhi|梕:ren|梖:bei|梗:geng|梘:jian|梙:huan|梚:wan|梛:nuo|梜:jia|條:tiao|梞:ji|梟:xiao|梠:lv|梡:kuan|梢:shao|梣:cen|梤:fen|梥:song|梦:meng|梧:wu|梨:li|梩:si|梪:dou|梫:qin|梬:ying|梭:suo|梮:ju|梯:ti|械:xie|梱:kun|梲:zhuo|梳:shu|梴:chan|梵:fan|梶:wei|梷:jing|梸:li|梹:bing|梺:xia|梻:fo|梼:tao|梽:zhi|梾:lai|梿:lian|检:jian|棁:zhuo|棂:ling|棃:li|棄:qi|棅:bing|棆:lun|棇:cong|棈:qian|棉:mian|棊:qi|棋:qi|棌:cai|棍:gun|棎:chan|棏:zhe|棐:fei|棑:pai|棒:bang|棓:bang|棔:hun|棕:zong|棖:cheng|棗:zao|棘:ji|棙:li|棚:peng|棛:yu|棜:yu|棝:gu|棞:gun|棟:dong|棠:tang|棡:gang|棢:wang|棣:di|棤:cuo|棥:fan|棦:cheng|棧:zhan|棨:qi|棩:yuan|棪:yan|棫:yu|棬:quan|棭:yi|森:sen|棯:ren|棰:chui|棱:leng|棲:qi|棳:zhuo|棴:fu|棵:ke|棶:lai|棷:zou|棸:zou|棹:zhao|棺:guan|棻:fen|棼:fen|棽:chen|棾:qing|棿:ni|椀:wan|椁:guo|椂:lu|椃:hao|椄:jie|椅:yi|椆:chou|椇:ju|椈:ju|椉:cheng|椊:zuo|椋:liang|椌:qiang|植:zhi|椎:zhui|椏:ya|椐:ju|椑:pi|椒:jiao|椓:zhuo|椔:zi|椕:bin|椖:peng|椗:ding|椘:chu|椙:chang|椚:men|椛:hua|検:jian|椝:gui|椞:xi|椟:du|椠:qian|椡:dao|椢:gui|椣:dian|椤:luo|椥:zhi|椦:quan|椧:mu|椨:fu|椩:geng|椪:peng|椫:shan|椬:yi|椭:tuo|椮:shen|椯:duo|椰:ye|椱:fu|椲:wei|椳:wei|椴:duan|椵:jia|椶:zong|椷:jian|椸:yi|椹:shen|椺:xi|椻:yan|椼:yan|椽:chuan|椾:jian|椿:chun|楀:yu|楁:he|楂:zha|楃:wo|楄:pian|楅:bi|楆:yao|楇:guo|楈:xu|楉:ruo|楊:yang|楋:la|楌:yan|楍:ben|楎:hui|楏:kui|楐:jie|楑:kui|楒:si|楓:feng|楔:xie|楕:tuo|楖:ji|楗:jian|楘:mu|楙:mao|楚:chu|楛:ku|楜:hu|楝:lian|楞:leng|楟:ting|楠:nan|楡:yu|楢:you|楣:mei|楤:cong|楥:xuan|楦:xuan|楧:yang|楨:zhen|楩:pian|楪:ye|楫:ji|楬:jie|業:ye|楮:chu|楯:shui|楰:yu|楱:cou|楲:wei|楳:mei|楴:ti|極:ji|楶:jie|楷:kai|楸:qiu|楹:ying|楺:rou|楻:huang|楼:lou|楽:le|楾:quan|楿:xiang|榀:pin|榁:shi|概:gai|榃:tan|榄:lan|榅:wen|榆:yu|榇:chen|榈:lv|榉:ju|榊:shen|榋:chu|榌:pi|榍:xie|榎:jia|榏:yi|榐:zhan|榑:fu|榒:nuo|榓:mi|榔:lang|榕:rong|榖:gu|榗:jian|榘:ju|榙:ta|榚:yao|榛:zhen|榜:bang|榝:sha|榞:yuan|榟:zi|榠:ming|榡:su|榢:jia|榣:yao|榤:jie|榥:huang|榦:gan|榧:fei|榨:zha|榩:qian|榪:ma|榫:sun|榬:yuan|榭:xie|榮:rong|榯:shi|榰:zhi|榱:cui|榲:wen|榳:ting|榴:liu|榵:rong|榶:tang|榷:que|榸:zhai|榹:si|榺:sheng|榻:ta|榼:ke|榽:xi|榾:gu|榿:qi|槀:gao|槁:gao|槂:sun|槃:pan|槄:tao|槅:ge|槆:chun|槇:zhen|槈:nou|槉:ji|槊:shuo|構:gou|槌:chui|槍:qiang|槎:cha|槏:qian|槐:huai|槑:mei|槒:xu|槓:gang|槔:gao|槕:zhuo|槖:tuo|槗:qiao|様:yang|槙:dian|槚:jia|槛:jian|槜:zhi|槝:dao|槞:long|槟:bin|槠:zhu|槡:sang|槢:xi|槣:ji|槤:lian|槥:hui|槦:yong|槧:qian|槨:guo|槩:gai|槪:gai|槫:tuan|槬:hua|槭:qi|槮:se|槯:cui|槰:peng|槱:you|槲:hu|槳:jiang|槴:hu|槵:huan|槶:gui|槷:nie|槸:yi|槹:gao|槺:kang|槻:gui|槼:gui|槽:cao|槾:man|槿:jin|樀:zhe|樁:zhuang|樂:le|樃:lang|樄:chen|樅:cong|樆:li|樇:xiu|樈:qing|樉:shuang|樊:fan|樋:tong|樌:guan|樍:ze|樎:su|樏:lei|樐:lu|樑:liang|樒:mi|樓:lou|樔:chao|樕:su|樖:ke|樗:chu|樘:tang|標:biao|樚:lu|樛:jiu|樜:zhe|樝:zha|樞:shu|樟:zhang|樠:man|模:mo|樢:mu|樣:yang|樤:tiao|樥:peng|樦:zhu|樧:sha|樨:xi|権:quan|横:heng|樫:jian|樬:cong|樭:ji|樮:yan|樯:qiang|樰:xue|樱:ying|樲:er|樳:xun|樴:zhi|樵:qiao|樶:zui|樷:cong|樸:pu|樹:shu|樺:hua|樻:kui|樼:zhen|樽:zun|樾:yue|樿:shan|橀:xi|橁:chun|橂:dian|橃:fa|橄:gan|橅:mo|橆:wu|橇:qiao|橈:rao|橉:lin|橊:liu|橋:qiao|橌:xian|橍:run|橎:fan|橏:zhan|橐:tuo|橑:lao|橒:yun|橓:shui|橔:dun|橕:cheng|橖:tang|橗:meng|橘:ju|橙:cheng|橚:su|橛:jue|橜:jue|橝:tan|橞:hui|機:ji|橠:nuo|橡:xiang|橢:tuo|橣:ning|橤:rui|橥:zhu|橦:tong|橧:zeng|橨:fen|橩:qiong|橪:ran|橫:heng|橬:qian|橭:gu|橮:liu|橯:lao|橰:gao|橱:chu|橲:xi|橳:sheng|橴:zi|橵:san|橶:ji|橷:dou|橸:jing|橹:lu|橺:jian|橻:chu|橼:yuan|橽:da|橾:shu|橿:jiang|檀:tan|檁:lin|檂:nao|檃:yin|檄:xi|檅:hui|檆:shan|檇:zui|檈:xuan|檉:cheng|檊:gan|檋:ju|檌:zui|檍:yi|檎:qin|檏:pu|檐:yan|檑:lei|檒:feng|檓:hui|檔:dang|檕:ji|檖:sui|檗:bo|檘:ping|檙:cheng|檚:chu|檛:zhua|檜:hui|檝:ji|檞:jie|檟:jia|檠:qing|檡:shi|檢:jian|檣:qiang|檤:dao|檥:yi|檦:biao|檧:song|檨:she|檩:lin|檪:li|檫:cha|檬:meng|檭:yin|檮:tao|檯:tai|檰:mian|檱:qi|檲:tuan|檳:bing|檴:huo|檵:ji|檶:qian|檷:ni|檸:ning|檹:yi|檺:gao|檻:jian|檼:yin|檽:nou|檾:qing|檿:yan|櫀:qi|櫁:mi|櫂:zhao|櫃:gui|櫄:chun|櫅:ji|櫆:kui|櫇:po|櫈:deng|櫉:chu|櫊:ge|櫋:mian|櫌:you|櫍:zhi|櫎:huang|櫏:qian|櫐:lei|櫑:lei|櫒:sa|櫓:lu|櫔:li|櫕:cuan|櫖:lv|櫗:mie|櫘:hui|櫙:ou|櫚:lv|櫛:zhi|櫜:gao|櫝:du|櫞:yuan|櫟:li|櫠:fei|櫡:zhuo|櫢:sou|櫣:lian|櫤:jiang|櫥:chu|櫦:qing|櫧:zhu|櫨:lu|櫩:yan|櫪:li|櫫:zhu|櫬:chen|櫭:jie|櫮:e|櫯:su|櫰:huai|櫱:nie|櫲:yu|櫳:long|櫴:la|櫵:jiao|櫶:xian|櫷:gui|櫸:ju|櫹:xiao|櫺:ling|櫻:ying|櫼:jian|櫽:yin|櫾:you|櫿:ying|欀:xiang|欁:nong|欂:bo|欃:chan|欄:lan|欅:ju|欆:shuang|欇:she|欈:zui|欉:cong|權:quan|欋:qu|欌:cang|欍:jiu|欎:yu|欏:luo|欐:li|欑:cuan|欒:luan|欓:dang|欔:jue|欕:yan|欖:lan|欗:lan|欘:zhu|欙:lei|欚:li|欛:ba|欜:nang|欝:yu|欞:ling|欟:guang|欠:qian|次:ci|欢:huan|欣:xin|欤:yu|欥:huan|欦:qian|欧:ou|欨:xu|欩:chao|欪:chu|欫:qi|欬:kai|欭:yi|欮:jue|欯:xi|欰:xu|欱:he|欲:yu|欳:kui|欴:lang|欵:kuan|欶:shuo|欷:xi|欸:ai|欹:yi|欺:qi|欻:xu|欼:chi|欽:qin|款:kuan|欿:kan|歀:kuan|歁:kan|歂:chuan|歃:sha|歄:gua|歅:yin|歆:xin|歇:xie|歈:yu|歉:qian|歊:xiao|歋:ye|歌:ge|歍:wu|歎:tan|歏:jin|歐:ou|歑:hu|歒:ti|歓:huan|歔:xu|歕:pen|歖:xi|歗:xiao|歘:xu|歙:xi|歚:xi|歛:lian|歜:chu|歝:yi|歞:e|歟:yu|歠:chuo|歡:huan|止:zhi|正:zheng|此:ci|步:bu|武:wu|歧:qi|歨:bu|歩:bu|歪:wai|歫:ju|歬:qian|歭:chi|歮:se|歯:chi|歰:se|歱:zhong|歲:sui|歳:sui|歴:li|歵:ze|歶:yu|歷:li|歸:gui|歹:dai|歺:e|死:si|歼:jian|歽:zhe|歾:mo|歿:mo|殀:yao|殁:mo|殂:cu|殃:yang|殄:tian|殅:sheng|殆:dai|殇:shang|殈:xu|殉:xun|殊:shu|残:can|殌:jue|殍:piao|殎:qia|殏:qiu|殐:su|殑:qing|殒:yun|殓:lian|殔:yi|殕:tou|殖:zhi|殗:ye|殘:can|殙:hun|殚:dan|殛:ji|殜:die|殝:zhen|殞:yun|殟:wen|殠:chou|殡:bin|殢:ti|殣:jin|殤:shang|殥:yin|殦:diao|殧:jiu|殨:kui|殩:cuan|殪:yi|殫:dan|殬:du|殭:jiang|殮:lian|殯:bin|殰:du|殱:jian|殲:jian|殳:shu|殴:ou|段:duan|殶:zhu|殷:yin|殸:qing|殹:yi|殺:sha|殻:ke|殼:qiao|殽:yao|殾:xun|殿:dian|毀:hui|毁:hui|毂:gu|毃:que|毄:ji|毅:yi|毆:ou|毇:hui|毈:duan|毉:yi|毊:xiao|毋:wu|毌:guan|母:mu|毎:mei|每:mei|毐:ai|毑:jie|毒:du|毓:yu|比:bi|毕:bi|毖:bi|毗:pi|毘:pi|毙:bi|毚:chan|毛:mao|毜:mao|毝:mao|毞:pi|毟:lie|毠:jia|毡:zhan|毢:sai|毣:mu|毤:tuo|毥:xun|毦:er|毧:rong|毨:xian|毩:ju|毪:mu|毫:hao|毬:qiu|毭:dou|毮:ou|毯:tan|毰:pei|毱:ju|毲:duo|毳:cui|毴:bi|毵:san|毶:san|毷:mao|毸:sai|毹:shu|毺:shu|毻:tuo|毼:he|毽:jian|毾:ta|毿:san|氀:lv|氁:mu|氂:li|氃:tong|氄:rong|氅:chang|氆:pu|氇:lu|氈:zhan|氉:sao|氊:zhan|氋:meng|氌:lu|氍:qu|氎:die|氏:shi|氐:di|民:min|氒:jue|氓:mang|气:qi|氕:pie|氖:nai|気:qi|氘:dao|氙:xian|氚:chuan|氛:fen|氜:yang|氝:nei|氞:bin|氟:fu|氠:shen|氡:dong|氢:qing|氣:qi|氤:yin|氥:xi|氦:hai|氧:yang|氨:an|氩:ya|氪:ke|氫:qing|氬:ya|氭:dong|氮:dan|氯:lv|氰:qing|氱:yang|氲:yun|氳:yun|水:shui|氵:shui|氶:zheng|氷:bing|永:yong|氹:dang|氺:shui|氻:le|氼:ni|氽:tun|氾:fan|氿:gui|汀:ting|汁:zhi|求:qiu|汃:bin|汄:ze|汅:mian|汆:cuan|汇:hui|汈:diao|汉:han|汊:cha|汋:yue|汌:chuan|汍:wan|汎:fan|汏:dai|汐:xi|汑:tuo|汒:mang|汓:qiu|汔:qi|汕:shan|汖:pin|汗:han|汘:qian|汙:wu|汚:wu|汛:xun|汜:si|汝:ru|汞:gong|江:jiang|池:chi|污:wu|汢:tu|汣:jiu|汤:tang|汥:zhi|汦:zhi|汧:qian|汨:mi|汩:gu|汪:wang|汫:jing|汬:jing|汭:rui|汮:jun|汯:hong|汰:tai|汱:quan|汲:ji|汳:bian|汴:bian|汵:gan|汶:wen|汷:zhong|汸:pang|汹:xiong|決:jue|汻:hu|汼:niu|汽:qi|汾:fen|汿:xu|沀:xu|沁:qin|沂:yi|沃:wo|沄:yun|沅:yuan|沆:hang|沇:yan|沈:shen|沉:chen|沊:dan|沋:you|沌:dun|沍:hu|沎:huo|沏:qi|沐:mu|沑:niu|沒:mei|沓:da|沔:mian|沕:mi|沖:chong|沗:pang|沘:bi|沙:sha|沚:zhi|沛:pei|沜:pan|沝:zhui|沞:za|沟:gou|沠:liu|没:mei|沢:ze|沣:feng|沤:ou|沥:li|沦:lun|沧:cang|沨:feng|沩:wei|沪:hu|沫:mo|沬:mei|沭:shu|沮:ju|沯:za|沰:tuo|沱:tuo|沲:duo|河:he|沴:li|沵:mi|沶:yi|沷:fa|沸:fei|油:you|沺:tian|治:zhi|沼:zhao|沽:gu|沾:zhan|沿:yan|泀:si|況:kuang|泂:jiong|泃:ju|泄:xie|泅:qiu|泆:yi|泇:jia|泈:zhong|泉:quan|泊:bo|泋:hui|泌:mi|泍:ben|泎:ze|泏:zhu|泐:le|泑:you|泒:gu|泓:hong|泔:gan|法:fa|泖:mao|泗:si|泘:hu|泙:peng|泚:ci|泛:fan|泜:chi|泝:su|泞:ning|泟:cheng|泠:ling|泡:pao|波:bo|泣:qi|泤:si|泥:ni|泦:ju|泧:xue|注:zhu|泩:sheng|泪:lei|泫:xuan|泬:jue|泭:fu|泮:pan|泯:min|泰:tai|泱:yang|泲:ji|泳:yong|泴:guan|泵:beng|泶:xue|泷:long|泸:lu|泹:dan|泺:luo|泻:xie|泼:po|泽:ze|泾:jing|泿:yin|洀:zhou|洁:jie|洂:ye|洃:hui|洄:hui|洅:zai|洆:cheng|洇:yin|洈:wei|洉:hou|洊:jian|洋:yang|洌:lie|洍:si|洎:ji|洏:er|洐:xing|洑:fu|洒:sa|洓:se|洔:zhi|洕:yin|洖:wu|洗:xi|洘:kao|洙:zhu|洚:jiang|洛:luo|洜:luo|洝:an|洞:dong|洟:ti|洠:mou|洡:lei|洢:yi|洣:mi|洤:quan|津:jin|洦:po|洧:wei|洨:xiao|洩:xie|洪:hong|洫:xu|洬:su|洭:kuang|洮:tao|洯:qie|洰:ju|洱:er|洲:zhou|洳:ru|洴:ping|洵:xun|洶:xiong|洷:zhi|洸:guang|洹:huan|洺:ming|活:huo|洼:wa|洽:qia|派:pai|洿:wu|浀:qu|流:liu|浂:yi|浃:jia|浄:jing|浅:qian|浆:jiang|浇:jiao|浈:zhen|浉:shi|浊:zhuo|测:ce|浌:peng|浍:hui|济:ji|浏:liu|浐:chan|浑:hun|浒:hu|浓:nong|浔:xun|浕:jin|浖:lie|浗:qiu|浘:wei|浙:zhe|浚:jun|浛:han|浜:bang|浝:mang|浞:zhuo|浟:you|浠:xi|浡:bo|浢:dou|浣:huan|浤:hong|浥:yi|浦:pu|浧:ying|浨:lan|浩:hao|浪:lang|浫:han|浬:li|浭:geng|浮:fu|浯:wu|浰:lian|浱:chun|浲:feng|浳:yi|浴:yu|浵:tong|浶:lao|海:hai|浸:jin|浹:jia|浺:chong|浻:jiong|浼:mei|浽:sui|浾:cheng|浿:pei|涀:xian|涁:shen|涂:tu|涃:kun|涄:ping|涅:nie|涆:han|涇:jing|消:xiao|涉:she|涊:nian|涋:tu|涌:yong|涍:xiao|涎:xian|涏:ting|涐:e|涑:su|涒:tun|涓:juan|涔:cen|涕:ti|涖:li|涗:shui|涘:si|涙:lei|涚:shui|涛:tao|涜:du|涝:lao|涞:lai|涟:lian|涠:wei|涡:wo|涢:yun|涣:huan|涤:di|涥:heng|润:run|涧:jian|涨:zhang|涩:se|涪:fu|涫:guan|涬:xing|涭:shou|涮:shuan|涯:ya|涰:chuo|涱:zhang|液:ye|涳:kong|涴:wan|涵:han|涶:tuo|涷:dong|涸:he|涹:wo|涺:ju|涻:she|涼:liang|涽:hun|涾:ta|涿:zhuo|淀:dian|淁:qie|淂:de|淃:juan|淄:zi|淅:xi|淆:xiao|淇:qi|淈:gu|淉:guo|淊:han|淋:lin|淌:tang|淍:zhou|淎:peng|淏:hao|淐:chang|淑:shu|淒:qi|淓:fang|淔:chi|淕:lu|淖:nao|淗:ju|淘:tao|淙:cong|淚:lei|淛:zhe|淜:ping|淝:fei|淞:song|淟:tian|淠:pi|淡:dan|淢:yu|淣:ni|淤:yu|淥:lu|淦:gan|淧:mi|淨:jing|淩:ling|淪:lun|淫:yin|淬:cui|淭:qu|淮:huai|淯:yu|淰:nian|深:shen|淲:biao|淳:chun|淴:hu|淵:yuan|淶:lai|混:hun|淸:qing|淹:yan|淺:qian|添:tian|淼:miao|淽:zhi|淾:yin|淿:bo|渀:ben|渁:yuan|渂:wen|渃:ruo|渄:fei|清:qing|渆:yuan|渇:ke|済:ji|渉:she|渊:yuan|渋:se|渌:lu|渍:zi|渎:du|渏:qi|渐:jian|渑:sheng|渒:pi|渓:xi|渔:yu|渕:yuan|渖:shen|渗:shen|渘:rou|渙:huan|渚:zhu|減:jian|渜:nuan|渝:yu|渞:qiu|渟:ting|渠:qu|渡:du|渢:feng|渣:zha|渤:bo|渥:wo|渦:wo|渧:ti|渨:wei|温:wen|渪:ru|渫:xie|測:ce|渭:wei|渮:he|港:gang|渰:yan|渱:hong|渲:xuan|渳:mi|渴:ke|渵:mao|渶:ying|渷:yan|游:you|渹:hong|渺:miao|渻:sheng|渼:mei|渽:zai|渾:hun|渿:nai|湀:gui|湁:chi|湂:e|湃:pai|湄:mei|湅:lian|湆:qi|湇:qi|湈:mei|湉:tian|湊:cou|湋:wei|湌:can|湍:tuan|湎:mian|湏:hui|湐:bo|湑:xu|湒:ji|湓:pen|湔:jian|湕:jian|湖:hu|湗:feng|湘:xiang|湙:yi|湚:yin|湛:zhan|湜:shi|湝:jie|湞:zhen|湟:huang|湠:tan|湡:yu|湢:bi|湣:min|湤:shi|湥:tu|湦:sheng|湧:yong|湨:ju|湩:dong|湪:tuan|湫:jiao|湬:jiao|湭:qiu|湮:yan|湯:tang|湰:long|湱:huo|湲:yuan|湳:nan|湴:ban|湵:you|湶:quan|湷:zhuang|湸:liang|湹:chan|湺:xian|湻:chun|湼:he|湽:zi|湾:wan|湿:shi|満:man|溁:ying|溂:la|溃:kui|溄:feng|溅:jian|溆:xu|溇:lou|溈:wei|溉:gai|溊:xia|溋:ying|溌:po|溍:jin|溎:gui|溏:tang|源:yuan|溑:suo|溒:yuan|溓:lian|溔:yao|溕:meng|準:zhun|溗:cheng|溘:ke|溙:tai|溚:da|溛:wa|溜:liu|溝:gou|溞:sao|溟:ming|溠:zha|溡:shi|溢:yi|溣:lun|溤:ma|溥:pu|溦:wei|溧:li|溨:zai|溩:wu|溪:xi|溫:wen|溬:qiang|溭:ze|溮:shi|溯:su|溰:ai|溱:qin|溲:sou|溳:yun|溴:xiu|溵:yin|溶:rong|溷:hun|溸:su|溹:suo|溺:ni|溻:ta|溼:shi|溽:ru|溾:ai|溿:pan|滀:chu|滁:chu|滂:pang|滃:weng|滄:cang|滅:mie|滆:ge|滇:dian|滈:hao|滉:huang|滊:xi|滋:zi|滌:di|滍:zhi|滎:ying|滏:fu|滐:jie|滑:hua|滒:ge|滓:zi|滔:tao|滕:teng|滖:sui|滗:bi|滘:jiao|滙:hui|滚:gun|滛:yin|滜:gao|滝:shuang|滞:zhi|滟:yan|滠:she|满:man|滢:ying|滣:chun|滤:lv|滥:lan|滦:luan|滧:xiao|滨:bin|滩:tan|滪:yu|滫:xiu|滬:hu|滭:bi|滮:biao|滯:zhi|滰:jiang|滱:kou|滲:shen|滳:shang|滴:di|滵:mi|滶:ao|滷:lu|滸:hu|滹:hu|滺:you|滻:chan|滼:fan|滽:yong|滾:gun|滿:man|漀:qing|漁:yu|漂:piao|漃:ji|漄:ya|漅:chao|漆:qi|漇:xi|漈:ji|漉:lu|漊:lou|漋:long|漌:jin|漍:guo|漎:cong|漏:lou|漐:zhi|漑:gai|漒:qiang|漓:li|演:yan|漕:cao|漖:jiao|漗:cong|漘:chun|漙:tuan|漚:ou|漛:teng|漜:ye|漝:xi|漞:mi|漟:tang|漠:mo|漡:shang|漢:han|漣:lian|漤:lan|漥:wa|漦:chi|漧:gan|漨:peng|漩:xuan|漪:yi|漫:man|漬:zi|漭:mang|漮:kang|漯:luo|漰:peng|漱:shu|漲:zhang|漳:zhang|漴:chong|漵:xu|漶:huan|漷:huo|漸:jian|漹:yan|漺:chuang|漻:liao|漼:cui|漽:ti|漾:yang|漿:jiang|潀:cong|潁:ying|潂:hong|潃:xin|潄:shu|潅:guan|潆:ying|潇:xiao|潈:cong|潉:kun|潊:xu|潋:lian|潌:zhi|潍:wei|潎:pi|潏:jue|潐:jiao|潑:po|潒:dang|潓:hui|潔:jie|潕:wu|潖:pa|潗:ji|潘:pan|潙:wei|潚:su|潛:qian|潜:qian|潝:xi|潞:lu|潟:xi|潠:sun|潡:dun|潢:huang|潣:min|潤:run|潥:su|潦:liao|潧:zhen|潨:cong|潩:yi|潪:zhi|潫:wan|潬:tan|潭:tan|潮:chao|潯:xun|潰:kui|潱:ye|潲:shao|潳:tu|潴:zhu|潵:sa|潶:hei|潷:bi|潸:shan|潹:chan|潺:chan|潻:shu|潼:tong|潽:pu|潾:lin|潿:wei|澀:se|澁:se|澂:cheng|澃:jiong|澄:cheng|澅:hua|澆:jiao|澇:lao|澈:che|澉:gan|澊:cun|澋:hong|澌:si|澍:shu|澎:peng|澏:han|澐:yun|澑:liu|澒:hong|澓:fu|澔:hao|澕:he|澖:xian|澗:jian|澘:shan|澙:xi|澚:ao|澛:lu|澜:lan|澝:ning|澞:yu|澟:lin|澠:sheng|澡:zao|澢:dang|澣:huan|澤:ze|澥:xie|澦:yu|澧:li|澨:shi|澩:xue|澪:ling|澫:man|澬:zi|澭:yong|澮:hui|澯:can|澰:lian|澱:dian|澲:ye|澳:ao|澴:huan|澵:zhen|澶:zhan|澷:man|澸:dan|澹:dan|澺:yi|澻:sui|澼:pi|澽:ju|澾:ta|澿:qin|激:ji|濁:zhuo|濂:lian|濃:nong|濄:guo|濅:jin|濆:pen|濇:se|濈:ji|濉:sui|濊:wei|濋:chu|濌:ta|濍:song|濎:ting|濏:se|濐:zhu|濑:lai|濒:bin|濓:lian|濔:mi|濕:shi|濖:shu|濗:mi|濘:ning|濙:ying|濚:ying|濛:meng|濜:jin|濝:qi|濞:bi|濟:ji|濠:hao|濡:ru|濢:zui|濣:wo|濤:tao|濥:yin|濦:yin|濧:dui|濨:ci|濩:huo|濪:qing|濫:lan|濬:jun|濭:ai|濮:pu|濯:zhuo|濰:wei|濱:bin|濲:gu|濳:qian|濴:ying|濵:bin|濶:kuo|濷:fei|濸:cang|濹:me|濺:jian|濻:wei|濼:luo|濽:zan|濾:lu|濿:li|瀀:you|瀁:yang|瀂:lu|瀃:si|瀄:zhi|瀅:ying|瀆:du|瀇:wang|瀈:hui|瀉:xie|瀊:pan|瀋:shen|瀌:biao|瀍:chan|瀎:mie|瀏:liu|瀐:jian|瀑:pu|瀒:se|瀓:cheng|瀔:gu|瀕:bin|瀖:huo|瀗:xian|瀘:lu|瀙:qin|瀚:han|瀛:ying|瀜:rong|瀝:li|瀞:jing|瀟:xiao|瀠:ying|瀡:sui|瀢:wei|瀣:xie|瀤:huai|瀥:xue|瀦:zhu|瀧:long|瀨:lai|瀩:dui|瀪:fan|瀫:hu|瀬:lai|瀭:shu|瀮:ling|瀯:ying|瀰:mi|瀱:ji|瀲:lian|瀳:jian|瀴:ying|瀵:fen|瀶:lin|瀷:yi|瀸:jian|瀹:yue|瀺:chan|瀻:dai|瀼:rang|瀽:jian|瀾:lan|瀿:fan|灀:shuang|灁:yuan|灂:zhuo|灃:feng|灄:she|灅:lei|灆:lan|灇:cong|灈:qu|灉:yong|灊:qian|灋:fa|灌:guan|灍:jue|灎:yan|灏:hao|灐:ying|灑:sa|灒:zan|灓:luan|灔:yan|灕:li|灖:mi|灗:shan|灘:tan|灙:dang|灚:jiao|灛:chan|灜:ying|灝:hao|灞:ba|灟:zhu|灠:lan|灡:lan|灢:nang|灣:wan|灤:luan|灥:xun|灦:xian|灧:yan|灨:gan|灩:yan|灪:yu|火:huo|灬:biao|灭:mie|灮:guang|灯:deng|灰:hui|灱:xiao|灲:xiao|灳:hui|灴:hong|灵:ling|灶:zao|灷:zhuan|灸:jiu|灹:zha|灺:xie|灻:chi|灼:zhuo|災:zai|灾:zai|灿:can|炀:yang|炁:qi|炂:zhong|炃:fen|炄:niu|炅:gui|炆:wen|炇:pu|炈:yi|炉:lu|炊:chui|炋:pi|炌:kai|炍:pan|炎:yan|炏:kai|炐:pang|炑:mu|炒:chao|炓:liao|炔:que|炕:kang|炖:dun|炗:guang|炘:xin|炙:zhi|炚:guang|炛:guang|炜:wei|炝:qiang|炞:bian|炟:da|炠:xia|炡:zheng|炢:zhu|炣:ke|炤:zhao|炥:fu|炦:ba|炧:xie|炨:xie|炩:ling|炪:zhuo|炫:xuan|炬:ju|炭:tan|炮:pao|炯:jiong|炰:pao|炱:tai|炲:tai|炳:bing|炴:yang|炵:tong|炶:shan|炷:zhu|炸:zha|点:dian|為:wei|炻:shi|炼:lian|炽:chi|炾:huang|炿:zhou|烀:hu|烁:shuo|烂:lan|烃:ting|烄:jiao|烅:xu|烆:heng|烇:quan|烈:lie|烉:huan|烊:yang|烋:xiu|烌:xiu|烍:xian|烎:yin|烏:wu|烐:zhou|烑:yao|烒:shi|烓:wei|烔:tong|烕:xue|烖:zai|烗:kai|烘:hong|烙:lao|烚:xia|烛:zhu|烜:xuan|烝:zheng|烞:po|烟:yan|烠:hui|烡:guang|烢:che|烣:hui|烤:kao|烥:chen|烦:fan|烧:shao|烨:ye|烩:hui|烪:hui|烫:tang|烬:jin|热:re|烮:lie|烯:xi|烰:fu|烱:jiong|烲:che|烳:pu|烴:ting|烵:zhuo|烶:ting|烷:wan|烸:hai|烹:peng|烺:lang|烻:shan|烼:xu|烽:feng|烾:chi|烿:rong|焀:hu|焁:xi|焂:shu|焃:he|焄:xun|焅:ku|焆:jue|焇:xiao|焈:xi|焉:yan|焊:han|焋:zhuang|焌:jun|焍:di|焎:xie|焏:ji|焐:wu|焑:wu|焒:lv|焓:han|焔:yan|焕:huan|焖:men|焗:ju|焘:dao|焙:bei|焚:fen|焛:lin|焜:kun|焝:hun|焞:tun|焟:xi|焠:cui|無:wu|焢:hong|焣:ju|焤:fu|焥:wo|焦:jiao|焧:cong|焨:feng|焩:ping|焪:qiong|焫:ruo|焬:xi|焭:qiong|焮:xin|焯:chao|焰:yan|焱:yan|焲:yi|焳:jiao|焴:yu|焵:gang|然:ran|焷:pi|焸:gu|焹:wang|焺:sheng|焻:gua|焼:shao|焽:shao|焾:nei|焿:geng|煀:wei|煁:chen|煂:he|煃:kui|煄:zhong|煅:duan|煆:xia|煇:hui|煈:feng|煉:lian|煊:xuan|煋:xing|煌:huang|煍:jiao|煎:jian|煏:bi|煐:ying|煑:zhu|煒:wei|煓:tuan|煔:shan|煕:xi|煖:nuan|煗:nuan|煘:chan|煙:yan|煚:jiong|煛:jiong|煜:yu|煝:mei|煞:sha|煟:wei|煠:zha|煡:jin|煢:qiong|煣:rou|煤:mei|煥:huan|煦:xu|照:zhao|煨:wei|煩:fan|煪:qiu|煫:sui|煬:yang|煭:lie|煮:zhu|煯:jie|煰:zao|煱:gua|煲:bao|煳:hu|煴:yun|煵:nan|煶:shi|煷:liang|煸:bian|煹:gou|煺:tui|煻:tang|煼:chao|煽:shan|煾:en|煿:bo|熀:huang|熁:xie|熂:xi|熃:wu|熄:xi|熅:yun|熆:he|熇:he|熈:xi|熉:yun|熊:xiong|熋:nai|熌:shan|熍:qiong|熎:yao|熏:xun|熐:mi|熑:lian|熒:ying|熓:wu|熔:rong|熕:gong|熖:yan|熗:qiang|熘:liu|熙:xi|熚:bi|熛:biao|熜:cong|熝:lu|熞:jian|熟:shu|熠:yi|熡:lou|熢:peng|熣:sui|熤:yi|熥:tong|熦:jue|熧:zong|熨:yun|熩:hu|熪:yi|熫:zhi|熬:ao|熭:wei|熮:liu|熯:han|熰:ou|熱:re|熲:jiong|熳:man|熴:kun|熵:shang|熶:cuan|熷:zeng|熸:jian|熹:xi|熺:xi|熻:xi|熼:yi|熽:xiao|熾:chi|熿:huang|燀:chan|燁:ye|燂:xun|燃:ran|燄:yan|燅:xun|燆:qiao|燇:jun|燈:deng|燉:dun|燊:shen|燋:jiao|燌:fen|燍:si|燎:liao|燏:yu|燐:lin|燑:jiong|燒:shao|燓:fen|燔:fan|燕:yan|燖:xun|燗:lan|燘:mei|燙:tang|燚:yi|燛:jiong|燜:men|燝:jing|燞:jing|營:ying|燠:yu|燡:yi|燢:xue|燣:lan|燤:tai|燥:zao|燦:can|燧:sui|燨:xi|燩:que|燪:zong|燫:lian|燬:hui|燭:zhu|燮:xie|燯:ling|燰:wei|燱:yi|燲:xie|燳:zhao|燴:hui|燵:da|燶:nuo|燷:bing|燸:ru|燹:xian|燺:he|燻:xun|燼:jin|燽:chou|燾:dao|燿:yao|爀:he|爁:lan|爂:biao|爃:rong|爄:li|爅:mo|爆:bao|爇:ruo|爈:lv|爉:la|爊:ao|爋:xun|爌:kuang|爍:shuo|爎:liao|爏:li|爐:lu|爑:jue|爒:liao|爓:yan|爔:xi|爕:xie|爖:long|爗:ye|爘:can|爙:rang|爚:yue|爛:lan|爜:cong|爝:jue|爞:chong|爟:guan|爠:ju|爡:che|爢:mi|爣:tang|爤:lan|爥:zhu|爦:lan|爧:ling|爨:cuan|爩:yu|爪:zhua|爫:zhao|爬:pa|爭:zheng|爮:pao|爯:cheng|爰:yuan|爱:ai|爲:wei|爳:han|爴:jue|爵:jue|父:fu|爷:ye|爸:ba|爹:die|爺:ye|爻:yao|爼:zu|爽:shuang|爾:er|爿:pan|牀:chuang|牁:ke|牂:zang|牃:die|牄:qiang|牅:yong|牆:qiang|片:pian|版:ban|牉:pan|牊:chao|牋:jian|牌:pai|牍:du|牎:chuang|牏:yu|牐:zha|牑:bian|牒:die|牓:bang|牔:bo|牕:chuang|牖:you|牗:you|牘:du|牙:ya|牚:cheng|牛:niu|牜:niu|牝:pin|牞:jiu|牟:mou|牠:ta|牡:mu|牢:lao|牣:ren|牤:mang|牥:fang|牦:mao|牧:mu|牨:gang|物:wu|牪:yan|牫:ge|牬:bei|牭:si|牮:jian|牯:gu|牰:you|牱:ge|牲:sheng|牳:mu|牴:di|牵:qian|牶:quan|牷:quan|牸:zi|特:te|牺:xi|牻:mang|牼:keng|牽:qian|牾:wu|牿:gu|犀:xi|犁:li|犂:li|犃:pou|犄:ji|犅:gang|犆:te|犇:ben|犈:quan|犉:chun|犊:du|犋:ju|犌:jia|犍:jian|犎:feng|犏:pian|犐:ke|犑:ju|犒:kao|犓:chu|犔:xi|犕:bei|犖:luo|犗:jie|犘:ma|犙:san|犚:wei|犛:li|犜:dun|犝:tong|犞:qiao|犟:jiang|犠:xi|犡:li|犢:du|犣:lie|犤:pai|犥:piao|犦:bo|犧:xi|犨:chou|犩:wei|犪:rao|犫:chou|犬:quan|犭:quan|犮:ba|犯:fan|犰:qiu|犱:ji|犲:chai|犳:zhuo|犴:an|犵:ge|状:zhuang|犷:guang|犸:ma|犹:you|犺:kang|犻:bo|犼:hou|犽:ya|犾:yin|犿:fan|狀:zhuang|狁:yun|狂:kuang|狃:niu|狄:di|狅:kuang|狆:zhong|狇:mu|狈:bei|狉:pi|狊:ju|狋:yi|狌:xing|狍:pao|狎:xia|狏:tuo|狐:hu|狑:ling|狒:fei|狓:pi|狔:ni|狕:yao|狖:you|狗:gou|狘:xue|狙:ju|狚:dan|狛:bo|狜:ku|狝:xian|狞:ning|狟:huan|狠:hen|狡:jiao|狢:he|狣:zhao|狤:ji|狥:xun|狦:shan|狧:ta|狨:rong|狩:shou|狪:tong|狫:lao|独:du|狭:xia|狮:shi|狯:kuai|狰:zheng|狱:yu|狲:sun|狳:yu|狴:bi|狵:mang|狶:xi|狷:juan|狸:li|狹:xia|狺:yin|狻:suan|狼:lang|狽:bei|狾:zhi|狿:yan|猀:sha|猁:li|猂:han|猃:xian|猄:jing|猅:pai|猆:fei|猇:xiao|猈:bai|猉:qi|猊:ni|猋:biao|猌:yin|猍:lai|猎:lie|猏:jian|猐:qiang|猑:kun|猒:yan|猓:guo|猔:zong|猕:mi|猖:chang|猗:yi|猘:zhi|猙:zheng|猚:ya|猛:meng|猜:cai|猝:cu|猞:she|猟:lie|猠:ceng|猡:luo|猢:hu|猣:zong|猤:fu|猥:wei|猦:feng|猧:wo|猨:yuan|猩:xing|猪:zhu|猫:mao|猬:wei|猭:chuan|献:xian|猯:tuan|猰:ya|猱:nao|猲:xie|猳:jia|猴:hou|猵:bian|猶:you|猷:you|猸:mei|猹:cha|猺:yao|猻:sun|猼:bo|猽:ming|猾:hua|猿:yuan|獀:sou|獁:ma|獂:yuan|獃:dai|獄:yu|獅:shi|獆:hao|獇:qiang|獈:yi|獉:zhen|獊:cang|獋:hao|獌:man|獍:jing|獎:jiang|獏:mu|獐:zhang|獑:chan|獒:ao|獓:ao|獔:gao|獕:cui|獖:ben|獗:jue|獘:bi|獙:bi|獚:huang|獛:bu|獜:lin|獝:xu|獞:tong|獟:yao|獠:liao|獡:shuo|獢:xiao|獣:shou|獤:dun|獥:jiao|獦:ge|獧:juan|獨:du|獩:hui|獪:kuai|獫:xian|獬:xie|獭:ta|獮:xian|獯:xun|獰:ning|獱:bian|獲:huo|獳:nou|獴:meng|獵:lie|獶:nao|獷:guang|獸:shou|獹:lu|獺:ta|獻:xian|獼:mi|獽:rang|獾:huan|獿:nao|玀:luo|玁:xian|玂:qi|玃:jue|玄:xuan|玅:miao|玆:zi|率:lv|玈:lu|玉:yu|玊:su|王:wang|玌:qiu|玍:ga|玎:ding|玏:le|玐:ba|玑:ji|玒:hong|玓:di|玔:chuan|玕:gan|玖:jiu|玗:yu|玘:qi|玙:yu|玚:chang|玛:ma|玜:hong|玝:wu|玞:fu|玟:min|玠:jie|玡:ya|玢:fen|玣:men|玤:bang|玥:yue|玦:jue|玧:yun|玨:jue|玩:wan|玪:jian|玫:mei|玬:dan|玭:pin|玮:wei|环:huan|现:xian|玱:qiang|玲:ling|玳:dai|玴:yi|玵:an|玶:ping|玷:dian|玸:fu|玹:xuan|玺:xi|玻:bo|玼:ci|玽:gou|玾:jia|玿:shao|珀:po|珁:ci|珂:ke|珃:ran|珄:sheng|珅:shen|珆:tai|珇:zu|珈:jia|珉:min|珊:shan|珋:liu|珌:bi|珍:zhen|珎:zhen|珏:jue|珐:fa|珑:long|珒:jin|珓:jiao|珔:jian|珕:li|珖:guang|珗:xian|珘:zhou|珙:gong|珚:yan|珛:xiu|珜:yang|珝:xu|珞:luo|珟:su|珠:zhu|珡:qin|珢:yin|珣:xun|珤:bao|珥:er|珦:xiang|珧:yao|珨:xia|珩:heng|珪:gui|珫:chong|珬:xu|班:ban|珮:pei|珯:lao|珰:dang|珱:ying|珲:hui|珳:wen|珴:e|珵:cheng|珶:ti|珷:wu|珸:wu|珹:cheng|珺:jun|珻:mei|珼:bei|珽:ting|現:xian|珿:chu|琀:han|琁:xuan|琂:yan|球:qiu|琄:xuan|琅:lang|理:li|琇:xiu|琈:fu|琉:liu|琊:ya|琋:xi|琌:ling|琍:li|琎:jin|琏:lian|琐:suo|琑:suo|琒:feng|琓:wan|琔:dian|琕:pin|琖:zhan|琗:se|琘:min|琙:yu|琚:ju|琛:chen|琜:lai|琝:min|琞:sheng|琟:wei|琠:tian|琡:chu|琢:zhuo|琣:beng|琤:cheng|琥:hu|琦:qi|琧:e|琨:kun|琩:chang|琪:qi|琫:beng|琬:wan|琭:lu|琮:cong|琯:guan|琰:yan|琱:diao|琲:bei|琳:lin|琴:qin|琵:pi|琶:pa|琷:qiang|琸:zhuo|琹:qin|琺:fa|琻:jin|琼:qiong|琽:du|琾:jie|琿:hui|瑀:yu|瑁:mao|瑂:mei|瑃:chun|瑄:xuan|瑅:ti|瑆:xing|瑇:dai|瑈:rou|瑉:min|瑊:jian|瑋:wei|瑌:ruan|瑍:huan|瑎:xie|瑏:chuan|瑐:jian|瑑:zhuan|瑒:chang|瑓:lian|瑔:quan|瑕:xia|瑖:duan|瑗:yuan|瑘:ya|瑙:nao|瑚:hu|瑛:ying|瑜:yu|瑝:huang|瑞:rui|瑟:se|瑠:liu|瑡:shi|瑢:rong|瑣:suo|瑤:yao|瑥:wen|瑦:wu|瑧:zhen|瑨:jin|瑩:ying|瑪:ma|瑫:tao|瑬:liu|瑭:tang|瑮:li|瑯:lang|瑰:gui|瑱:tian|瑲:qiang|瑳:cuo|瑴:jue|瑵:zhao|瑶:yao|瑷:ai|瑸:bin|瑹:shu|瑺:chang|瑻:kun|瑼:zhuan|瑽:cong|瑾:jin|瑿:yi|璀:cui|璁:cong|璂:qi|璃:li|璄:jing|璅:zao|璆:qiu|璇:xuan|璈:ao|璉:lian|璊:men|璋:zhang|璌:yin|璍:hua|璎:ying|璏:wei|璐:lu|璑:wu|璒:deng|璓:xiu|璔:zeng|璕:xun|璖:qu|璗:dang|璘:lin|璙:liao|璚:qiong|璛:su|璜:huang|璝:gui|璞:pu|璟:jing|璠:fan|璡:jin|璢:liu|璣:ji|璤:hui|璥:jing|璦:ai|璧:bi|璨:can|璩:qu|璪:zao|璫:dang|璬:jiao|璭:gun|璮:tan|璯:hui|環:huan|璱:se|璲:sui|璳:tian|璴:chu|璵:yu|璶:jin|璷:lu|璸:bin|璹:shu|璺:wen|璻:zui|璼:lan|璽:xi|璾:ji|璿:xuan|瓀:ruan|瓁:wo|瓂:gai|瓃:lei|瓄:du|瓅:li|瓆:zhi|瓇:rou|瓈:li|瓉:zan|瓊:qiong|瓋:ti|瓌:gui|瓍:sui|瓎:la|瓏:long|瓐:lu|瓑:li|瓒:zan|瓓:lan|瓔:ying|瓕:mi|瓖:xiang|瓗:qiong|瓘:guan|瓙:dao|瓚:zan|瓛:huan|瓜:gua|瓝:bo|瓞:die|瓟:bo|瓠:hu|瓡:zhi|瓢:piao|瓣:ban|瓤:rang|瓥:li|瓦:wa|瓧:shi|瓨:xiang|瓩:qiang|瓪:ban|瓫:pen|瓬:fang|瓭:dan|瓮:weng|瓯:ou|瓰:feng|瓱:mie|瓲:wa|瓳:hu|瓴:ling|瓵:yi|瓶:ping|瓷:ci|瓸:bai|瓹:juan|瓺:chang|瓻:chi|瓼:liu|瓽:dang|瓾:meng|瓿:bu|甀:zhui|甁:ping|甂:bian|甃:zhou|甄:zhen|甅:liu|甆:ci|甇:ying|甈:qi|甉:xian|甊:lou|甋:di|甌:ou|甍:meng|甎:zhuan|甏:beng|甐:lin|甑:zeng|甒:wu|甓:pi|甔:dan|甕:weng|甖:ying|甗:yan|甘:gan|甙:dai|甚:shen|甛:tian|甜:tian|甝:han|甞:chang|生:sheng|甠:qing|甡:shen|產:chan|産:chan|甤:rui|甥:sheng|甦:su|甧:shen|用:yong|甩:shuai|甪:lu|甫:fu|甬:yong|甭:beng|甮:feng|甯:ning|田:tian|由:you|甲:jia|申:shen|甴:zha|电:dian|甶:fu|男:nan|甸:dian|甹:ping|町:ting|画:hua|甼:ding|甽:quan|甾:zai|甿:meng|畀:bi|畁:bi|畂:jiu|畃:sun|畄:liu|畅:chang|畆:mu|畇:yun|畈:fan|畉:fu|畊:geng|畋:tian|界:jie|畍:jie|畎:quan|畏:wei|畐:fu|畑:tian|畒:mu|畓:tao|畔:pan|畕:jiang|畖:wa|畗:fu|畘:nan|留:liu|畚:ben|畛:zhen|畜:xu|畝:mu|畞:mu|畟:ce|畠:tian|畡:gai|畢:bi|畣:da|畤:zhi|略:lue|畦:qi|畧:lve|畨:pan|畩:yi|番:fan|畫:hua|畬:yu|畭:yu|畮:mu|畯:jun|異:yi|畱:liu|畲:she|畳:die|畴:chou|畵:hua|當:dang|畷:zhui|畸:ji|畹:wan|畺:jiang|畻:cheng|畼:chang|畽:tun|畾:lei|畿:ji|疀:cha|疁:liu|疂:die|疃:tuan|疄:lin|疅:jiang|疆:jiang|疇:chou|疈:pi|疉:die|疊:die|疋:ya|疌:jie|疍:dan|疎:shu|疏:shu|疐:zhi|疑:yi|疒:ne|疓:nai|疔:ding|疕:bi|疖:jie|疗:liao|疘:gang|疙:ge|疚:jiu|疛:zhou|疜:xia|疝:shan|疞:xu|疟:nue|疠:li|疡:yang|疢:chen|疣:you|疤:ba|疥:jie|疦:xue|疧:qi|疨:xia|疩:cui|疪:bi|疫:yi|疬:li|疭:zong|疮:chuang|疯:feng|疰:zhu|疱:pao|疲:pi|疳:gan|疴:ke|疵:ci|疶:xue|疷:zhi|疸:dan|疹:zhen|疺:fa|疻:zhi|疼:teng|疽:ju|疾:ji|疿:fei|痀:ju|痁:shan|痂:jia|痃:xuan|痄:zha|病:bing|痆:ni|症:zheng|痈:yong|痉:jing|痊:quan|痋:teng|痌:tong|痍:yi|痎:jie|痏:wei|痐:hui|痑:tan|痒:yang|痓:chi|痔:zhi|痕:hen|痖:ya|痗:mei|痘:dou|痙:jing|痚:xiao|痛:tong|痜:tu|痝:mang|痞:pi|痟:xiao|痠:suan|痡:pu|痢:li|痣:zhi|痤:cuo|痥:duo|痦:wu|痧:sha|痨:lao|痩:shou|痪:huan|痫:xian|痬:yi|痭:beng|痮:zhang|痯:guan|痰:tan|痱:fei|痲:ma|痳:lin|痴:chi|痵:ji|痶:tian|痷:an|痸:chi|痹:bi|痺:bi|痻:min|痼:gu|痽:dui|痾:ke|痿:wei|瘀:yu|瘁:cui|瘂:ya|瘃:zhu|瘄:cu|瘅:dan|瘆:shen|瘇:zhong|瘈:zhi|瘉:yu|瘊:hou|瘋:feng|瘌:la|瘍:yang|瘎:chen|瘏:tu|瘐:yu|瘑:guo|瘒:wen|瘓:huan|瘔:ku|瘕:jia|瘖:yin|瘗:yi|瘘:lou|瘙:sao|瘚:jue|瘛:chi|瘜:xi|瘝:guan|瘞:yi|瘟:wen|瘠:ji|瘡:chuang|瘢:ban|瘣:hui|瘤:liu|瘥:chai|瘦:shou|瘧:nve|瘨:dian|瘩:da|瘪:bie|瘫:tan|瘬:zhang|瘭:biao|瘮:shen|瘯:cu|瘰:luo|瘱:yi|瘲:zong|瘳:chou|瘴:zhang|瘵:zhai|瘶:sou|瘷:se|瘸:que|瘹:diao|瘺:lou|瘻:lou|瘼:mo|瘽:qin|瘾:yin|瘿:ying|癀:huang|癁:fu|療:liao|癃:long|癄:qiao|癅:liu|癆:lao|癇:xian|癈:fei|癉:dan|癊:yin|癋:he|癌:ai|癍:ban|癎:xian|癏:guan|癐:wei|癑:nong|癒:yu|癓:wei|癔:yi|癕:yong|癖:pi|癗:lei|癘:li|癙:shu|癚:dan|癛:lin|癜:dian|癝:lin|癞:lai|癟:bie|癠:ji|癡:chi|癢:yang|癣:xuan|癤:jie|癥:zheng|癦:me|癧:li|癨:huo|癩:lai|癪:ji|癫:dian|癬:xuan|癭:ying|癮:yin|癯:qu|癰:yong|癱:tan|癲:dian|癳:luo|癴:luan|癵:luan|癶:bo|癷:bo|癸:gui|癹:ba|発:fa|登:deng|發:fa|白:bai|百:bai|癿:qie|皀:bi|皁:zao|皂:zao|皃:mao|的:de|皅:pa|皆:jie|皇:huang|皈:gui|皉:ci|皊:ling|皋:gao|皌:mo|皍:ji|皎:jiao|皏:peng|皐:gao|皑:ai|皒:e|皓:hao|皔:han|皕:bi|皖:wan|皗:chou|皘:qian|皙:xi|皚:ai|皛:xiao|皜:hao|皝:huang|皞:hao|皟:ze|皠:cui|皡:hao|皢:xiao|皣:ye|皤:po|皥:hao|皦:jiao|皧:ai|皨:xing|皩:huang|皪:li|皫:piao|皬:he|皭:jiao|皮:pi|皯:gan|皰:pao|皱:zhou|皲:jun|皳:qiu|皴:cun|皵:que|皶:zha|皷:gu|皸:jun|皹:jun|皺:zhou|皻:zha|皼:zha|皽:zhan|皾:du|皿:min|盀:qi|盁:ying|盂:yu|盃:bei|盄:zhao|盅:zhong|盆:pen|盇:he|盈:ying|盉:he|益:yi|盋:bo|盌:wan|盍:he|盎:ang|盏:zhan|盐:yan|监:jian|盒:he|盓:yu|盔:kui|盕:fan|盖:gai|盗:dao|盘:pan|盙:fu|盚:qiu|盛:sheng|盜:dao|盝:lu|盞:zhan|盟:meng|盠:li|盡:jin|盢:xu|監:jian|盤:pan|盥:guan|盦:an|盧:lu|盨:xu|盩:zhou|盪:dang|盫:an|盬:gu|盭:li|目:mu|盯:ding|盰:gan|盱:xu|盲:mang|盳:wang|直:zhi|盵:qi|盶:yuan|盷:tian|相:xiang|盹:dun|盺:xin|盻:xi|盼:pan|盽:feng|盾:dun|盿:min|眀:ming|省:sheng|眂:shi|眃:yun|眄:mian|眅:pan|眆:fang|眇:miao|眈:dan|眉:mei|眊:mao|看:kan|県:xian|眍:kou|眎:shi|眏:yang|眐:zheng|眑:yao|眒:shen|眓:huo|眔:da|眕:zhen|眖:kuang|眗:ju|眘:shen|眙:yi|眚:sheng|眛:mei|眜:mo|眝:zhu|眞:zhen|真:zhen|眠:mian|眡:shi|眢:yuan|眣:die|眤:ni|眥:zi|眦:zi|眧:chao|眨:zha|眩:xuan|眪:bing|眫:mi|眬:long|眭:sui|眮:tong|眯:mi|眰:die|眱:di|眲:ne|眳:ming|眴:xuan|眵:chi|眶:kuang|眷:juan|眸:mou|眹:zhen|眺:tiao|眻:yang|眼:yan|眽:mo|眾:zhong|眿:mo|着:zhuo|睁:zheng|睂:mei|睃:suo|睄:qiao|睅:han|睆:huan|睇:di|睈:cheng|睉:cuo|睊:juan|睋:e|睌:man|睍:xian|睎:xi|睏:kun|睐:lai|睑:jian|睒:shan|睓:tian|睔:gun|睕:wan|睖:leng|睗:shi|睘:qiong|睙:lie|睚:ya|睛:jing|睜:zheng|睝:li|睞:lai|睟:sui|睠:juan|睡:shui|睢:sui|督:du|睤:pi|睥:pi|睦:mu|睧:hun|睨:ni|睩:lu|睪:gao|睫:jie|睬:cai|睭:zhou|睮:yu|睯:hun|睰:ma|睱:xia|睲:xing|睳:hui|睴:gun|睵:zai|睶:chun|睷:jian|睸:mei|睹:du|睺:hou|睻:xuan|睼:tian|睽:kui|睾:gao|睿:rui|瞀:mao|瞁:xu|瞂:fa|瞃:wo|瞄:miao|瞅:chou|瞆:gui|瞇:mi|瞈:weng|瞉:kou|瞊:dang|瞋:chen|瞌:ke|瞍:sou|瞎:xia|瞏:qiong|瞐:mo|瞑:ming|瞒:man|瞓:fen|瞔:ze|瞕:zhang|瞖:yi|瞗:diao|瞘:kou|瞙:mo|瞚:shui|瞛:cong|瞜:lou|瞝:chi|瞞:man|瞟:piao|瞠:cheng|瞡:gui|瞢:meng|瞣:huan|瞤:shui|瞥:pie|瞦:xi|瞧:qiao|瞨:pu|瞩:zhu|瞪:deng|瞫:shen|瞬:shun|瞭:liao|瞮:che|瞯:jian|瞰:kan|瞱:ye|瞲:xu|瞳:tong|瞴:mou|瞵:lin|瞶:gui|瞷:jian|瞸:ye|瞹:ai|瞺:hui|瞻:zhan|瞼:jian|瞽:gu|瞾:zhao|瞿:ju|矀:mei|矁:chou|矂:sao|矃:ning|矄:xun|矅:yao|矆:huo|矇:meng|矈:mian|矉:pin|矊:mian|矋:lei|矌:kuang|矍:jue|矎:xuan|矏:mian|矐:huo|矑:lu|矒:meng|矓:long|矔:guan|矕:man|矖:xi|矗:chu|矘:tang|矙:kan|矚:zhu|矛:mao|矜:jin|矝:qin|矞:yu|矟:shuo|矠:ze|矡:jue|矢:shi|矣:yi|矤:shen|知:zhi|矦:hou|矧:shen|矨:ying|矩:ju|矪:zhou|矫:jiao|矬:cuo|短:duan|矮:ai|矯:jiao|矰:zeng|矱:yue|矲:ba|石:shi|矴:ding|矵:qi|矶:ji|矷:zi|矸:gan|矹:wu|矺:zhe|矻:ku|矼:gang|矽:xi|矾:fan|矿:kuang|砀:dang|码:ma|砂:sha|砃:dan|砄:jue|砅:li|砆:fu|砇:wen|砈:e|砉:xu|砊:kang|砋:zhi|砌:qi|砍:kan|砎:jie|砏:bin|砐:e|砑:ya|砒:pi|砓:zhe|研:yan|砕:sui|砖:zhuan|砗:che|砘:dun|砙:wa|砚:yan|砛:jin|砜:feng|砝:fa|砞:mo|砟:zha|砠:ju|砡:yu|砢:luo|砣:tuo|砤:tuo|砥:di|砦:zhai|砧:zhen|砨:e|砩:fu|砪:mu|砫:zhu|砬:la|砭:bian|砮:nu|砯:ping|砰:peng|砱:ling|砲:pao|砳:le|破:po|砵:bo|砶:po|砷:shen|砸:za|砹:ai|砺:li|砻:long|砼:tong|砽:yong|砾:li|砿:kuang|础:chu|硁:keng|硂:quan|硃:zhu|硄:guang|硅:gui|硆:e|硇:nao|硈:qia|硉:lu|硊:wei|硋:ai|硌:ge|硍:xian|硎:xing|硏:yan|硐:dong|硑:peng|硒:xi|硓:lao|硔:hong|硕:shuo|硖:xia|硗:qiao|硘:qing|硙:wei|硚:qiao|硛:ceng|硜:keng|硝:xiao|硞:que|硟:chan|硠:lang|硡:hong|硢:yu|硣:xiao|硤:xia|硥:bang|硦:long|硧:yong|硨:che|硩:che|硪:wo|硫:liu|硬:ying|硭:mang|确:que|硯:yan|硰:sha|硱:kun|硲:gu|硳:ceng|硴:hua|硵:lu|硶:chen|硷:jian|硸:nuo|硹:song|硺:zhuo|硻:keng|硼:peng|硽:yan|硾:zhui|硿:kong|碀:cheng|碁:qi|碂:zong|碃:qing|碄:lin|碅:jun|碆:bo|碇:ding|碈:min|碉:diao|碊:jian|碋:he|碌:lu|碍:ai|碎:sui|碏:que|碐:leng|碑:bei|碒:yin|碓:dui|碔:wu|碕:qi|碖:lun|碗:wan|碘:dian|碙:nao|碚:bei|碛:qi|碜:chen|碝:ruan|碞:yan|碟:die|碠:ding|碡:zhou|碢:tuo|碣:jie|碤:ying|碥:bian|碦:ke|碧:bi|碨:wei|碩:shuo|碪:zhen|碫:duan|碬:xia|碭:dang|碮:ti|碯:nao|碰:peng|碱:jian|碲:di|碳:tan|碴:cha|碵:tian|碶:qi|碷:dun|碸:feng|碹:xuan|確:que|碻:qiao|碼:ma|碽:gong|碾:nian|碿:su|磀:e|磁:ci|磂:liu|磃:si|磄:tang|磅:bang|磆:hua|磇:pi|磈:kui|磉:sang|磊:lei|磋:cuo|磌:tian|磍:xia|磎:qi|磏:lian|磐:pan|磑:wei|磒:yun|磓:dui|磔:zhe|磕:ke|磖:la|磗:pai|磘:yao|磙:gun|磚:zhuan|磛:chan|磜:qi|磝:ao|磞:peng|磟:liu|磠:lu|磡:kan|磢:chuang|磣:chen|磤:yin|磥:lei|磦:piao|磧:qi|磨:mo|磩:qi|磪:cui|磫:zong|磬:qing|磭:chuo|磮:lun|磯:ji|磰:shan|磱:lao|磲:qu|磳:zeng|磴:deng|磵:jian|磶:xi|磷:lin|磸:ding|磹:dian|磺:huang|磻:bo|磼:za|磽:qiao|磾:di|磿:li|礀:jian|礁:jiao|礂:xi|礃:zhang|礄:qiao|礅:dun|礆:jian|礇:yu|礈:zhui|礉:he|礊:ke|礋:ze|礌:lei|礍:jie|礎:chu|礏:ye|礐:que|礑:dang|礒:yi|礓:jiang|礔:pi|礕:pi|礖:yu|礗:pin|礘:qi|礙:ai|礚:ke|礛:jian|礜:yu|礝:ruan|礞:meng|礟:pao|礠:ci|礡:bo|礢:yang|礣:ma|礤:ca|礥:xian|礦:kuang|礧:lei|礨:lei|礩:zhi|礪:li|礫:li|礬:fan|礭:que|礮:pao|礯:ying|礰:li|礱:long|礲:long|礳:mo|礴:bo|礵:shuang|礶:guan|礷:lan|礸:ca|礹:yan|示:shi|礻:shi|礼:li|礽:reng|社:she|礿:yue|祀:si|祁:qi|祂:ta|祃:ma|祄:xie|祅:yao|祆:xian|祇:qi|祈:qi|祉:zhi|祊:beng|祋:dui|祌:zhong|祍:zhong|祎:yi|祏:shi|祐:you|祑:zhi|祒:tiao|祓:fu|祔:fu|祕:mi|祖:zu|祗:zhi|祘:suan|祙:mei|祚:zuo|祛:qu|祜:hu|祝:zhu|神:shen|祟:sui|祠:ci|祡:chai|祢:mi|祣:lv|祤:yu|祥:xiang|祦:wu|祧:tiao|票:piao|祩:zhu|祪:gui|祫:xia|祬:zhi|祭:ji|祮:gao|祯:zhen|祰:gao|祱:shui|祲:jin|祳:shen|祴:gai|祵:kun|祶:di|祷:dao|祸:huo|祹:tao|祺:qi|祻:gu|祼:guan|祽:zui|祾:ling|祿:lu|禀:bing|禁:jin|禂:dao|禃:zhi|禄:lu|禅:chan|禆:bi|禇:chu|禈:hui|禉:you|禊:xi|禋:yin|禌:zi|禍:huo|禎:zhen|福:fu|禐:yuan|禑:wu|禒:xian|禓:yang|禔:zhi|禕:yi|禖:mei|禗:si|禘:di|禙:bei|禚:zhuo|禛:zhen|禜:yong|禝:ji|禞:gao|禟:tang|禠:si|禡:ma|禢:ta|禣:fu|禤:xuan|禥:qi|禦:yu|禧:xi|禨:ji|禩:si|禪:chan|禫:dan|禬:gui|禭:sui|禮:li|禯:nong|禰:mi|禱:dao|禲:li|禳:rang|禴:yue|禵:zhi|禶:zan|禷:lei|禸:rou|禹:yu|禺:yu|离:li|禼:xie|禽:qin|禾:he|禿:tu|秀:xiu|私:si|秂:ren|秃:tu|秄:zi|秅:cha|秆:gan|秇:yi|秈:xian|秉:bing|秊:nian|秋:qiu|秌:qiu|种:zhong|秎:fen|秏:hao|秐:yun|科:ke|秒:miao|秓:zhi|秔:jing|秕:bi|秖:zhi|秗:yu|秘:mi|秙:ku|秚:ban|秛:pi|秜:ni|秝:li|秞:you|租:zu|秠:pi|秡:bo|秢:ling|秣:mo|秤:cheng|秥:nian|秦:qin|秧:yang|秨:zuo|秩:zhi|秪:zhi|秫:shu|秬:ju|秭:zi|秮:huo|积:ji|称:cheng|秱:tong|秲:zhi|秳:huo|秴:he|秵:yin|秶:zi|秷:zhi|秸:jie|秹:ren|秺:du|移:yi|秼:zhu|秽:hui|秾:nong|秿:fu|稀:xi|稁:gao|稂:lang|稃:fu|稄:ze|稅:shui|稆:lv|稇:kun|稈:gan|稉:jing|稊:ti|程:cheng|稌:tu|稍:shao|税:shui|稏:ya|稐:lun|稑:lu|稒:gu|稓:zuo|稔:ren|稕:zhun|稖:bang|稗:bai|稘:ji|稙:zhi|稚:zhi|稛:kun|稜:leng|稝:peng|稞:ke|稟:lin|稠:chou|稡:zu|稢:yu|稣:su|稤:lve|稥:lve|稦:yi|稧:xi|稨:bian|稩:ji|稪:fu|稫:bi|稬:nuo|稭:jie|種:zhong|稯:zong|稰:xu|稱:chen|稲:dao|稳:wen|稴:lian|稵:zi|稶:yu|稷:ji|稸:xu|稹:zhen|稺:zhi|稻:dao|稼:jia|稽:ji|稾:gao|稿:gao|穀:gu|穁:rong|穂:sui|穃:rong|穄:ji|穅:kang|穆:mu|穇:can|穈:men|穉:zhi|穊:ji|穋:lu|穌:su|積:ji|穎:ying|穏:wen|穐:qiu|穑:se|穒:kuo|穓:yi|穔:huang|穕:qie|穖:ji|穗:sui|穘:xiao|穙:pu|穚:jiao|穛:zhuo|穜:tong|穝:zui|穞:lv|穟:sui|穠:nong|穡:se|穢:hui|穣:rang|穤:nuo|穥:yu|穦:pin|穧:ji|穨:tui|穩:wen|穪:cheng|穫:huo|穬:kuang|穭:lv|穮:biao|穯:se|穰:rang|穱:zhuo|穲:li|穳:cuan|穴:xue|穵:ya|究:jiu|穷:qiong|穸:xi|穹:qiong|空:kong|穻:yu|穼:shen|穽:jing|穾:yao|穿:chuan|窀:zhun|突:tu|窂:lao|窃:qie|窄:zhai|窅:yao|窆:bian|窇:bao|窈:yao|窉:bing|窊:wa|窋:zhu|窌:jiao|窍:qiao|窎:diao|窏:wu|窐:gui|窑:yao|窒:zhi|窓:chuang|窔:yao|窕:tiao|窖:jiao|窗:chuang|窘:jiong|窙:xiao|窚:cheng|窛:kou|窜:cuan|窝:wo|窞:dan|窟:ku|窠:ke|窡:zhuo|窢:xu|窣:su|窤:guan|窥:kui|窦:dou|窧:zhuo|窨:xun|窩:wo|窪:wa|窫:ya|窬:yu|窭:ju|窮:qiong|窯:yao|窰:yao|窱:tiao|窲:chao|窳:yu|窴:tian|窵:diao|窶:ju|窷:liao|窸:xi|窹:wu|窺:kui|窻:chuang|窼:zhao|窽:kuan|窾:kuan|窿:long|竀:cheng|竁:cui|竂:liao|竃:zao|竄:cuan|竅:qiao|竆:qiong|竇:dou|竈:zao|竉:long|竊:qie|立:li|竌:chu|竍:shi|竎:fu|竏:qian|竐:chu|竑:hong|竒:qi|竓:hao|竔:sheng|竕:fen|竖:shu|竗:miao|竘:qu|站:zhan|竚:zhu|竛:ling|竜:long|竝:bing|竞:jing|竟:jing|章:zhang|竡:bai|竢:si|竣:jun|竤:hong|童:tong|竦:song|竧:jing|竨:diao|竩:yi|竪:shu|竫:jing|竬:qu|竭:jie|竮:ping|端:duan|竰:li|竱:zhuan|竲:ceng|竳:deng|竴:cun|竵:wai|競:jing|竷:kan|竸:jing|竹:zhu|竺:zhu|竻:le|竼:peng|竽:yu|竾:chi|竿:gan|笀:mang|笁:zhu|笂:wan|笃:du|笄:ji|笅:jiao|笆:ba|笇:suan|笈:ji|笉:qin|笊:zhao|笋:sun|笌:ya|笍:zhui|笎:yuan|笏:hu|笐:hang|笑:xiao|笒:cen|笓:pi|笔:bi|笕:jian|笖:yi|笗:dong|笘:shan|笙:sheng|笚:xia|笛:di|笜:zhu|笝:na|笞:chi|笟:gu|笠:li|笡:qie|笢:min|笣:bao|笤:tiao|笥:si|符:fu|笧:ce|笨:ben|笩:pei|笪:da|笫:zi|第:di|笭:ling|笮:ze|笯:nu|笰:fu|笱:gou|笲:fan|笳:jia|笴:gan|笵:fan|笶:shi|笷:mao|笸:po|笹:shi|笺:jian|笻:qiong|笼:long|笽:min|笾:bian|笿:luo|筀:gui|筁:qu|筂:chi|筃:yin|筄:yao|筅:xian|筆:bi|筇:qiong|筈:kuo|等:deng|筊:jiao|筋:jin|筌:quan|筍:sun|筎:ru|筏:fa|筐:kuang|筑:zhu|筒:tong|筓:ji|答:da|筕:hang|策:ce|筗:zhong|筘:kou|筙:lai|筚:bi|筛:shai|筜:dang|筝:zheng|筞:ce|筟:fu|筠:jun|筡:tu|筢:pa|筣:li|筤:lang|筥:ju|筦:guan|筧:jian|筨:han|筩:tong|筪:xia|筫:zhi|筬:cheng|筭:suan|筮:shi|筯:zhu|筰:zuo|筱:xiao|筲:shao|筳:ting|筴:ce|筵:yan|筶:gao|筷:kuai|筸:gan|筹:chou|筺:kuang|筻:gang|筼:yun|筽:ou|签:qian|筿:xiao|简:jian|箁:pou|箂:lai|箃:zou|箄:pai|箅:bi|箆:bi|箇:ge|箈:tai|箉:guai|箊:yu|箋:jian|箌:zhao|箍:gu|箎:hu|箏:zheng|箐:qing|箑:sha|箒:zhou|箓:lu|箔:bo|箕:ji|箖:lin|算:suan|箘:jun|箙:fu|箚:zha|箛:gu|箜:kong|箝:qian|箞:qian|箟:jun|箠:chui|管:guan|箢:yuan|箣:ce|箤:zu|箥:bo|箦:ze|箧:qie|箨:tuo|箩:luo|箪:dan|箫:xiao|箬:ruo|箭:jian|箮:xuan|箯:bian|箰:sun|箱:xiang|箲:xian|箳:ping|箴:zhen|箵:xing|箶:hu|箷:shi|箸:zhu|箹:yue|箺:chun|箻:lv|箼:wu|箽:dong|箾:shuo|箿:ji|節:jie|篁:huang|篂:xing|篃:mei|範:fan|篅:chuan|篆:zhuan|篇:pian|篈:feng|築:zhu|篊:hong|篋:qie|篌:hou|篍:qiu|篎:miao|篏:qian|篐:gu|篑:kui|篒:shi|篓:lou|篔:yun|篕:he|篖:tang|篗:yue|篘:chou|篙:gao|篚:fei|篛:ruo|篜:zheng|篝:gou|篞:nie|篟:qian|篠:xiao|篡:cuan|篢:gong|篣:pang|篤:du|篥:li|篦:bi|篧:zhuo|篨:chu|篩:shai|篪:chi|篫:zhu|篬:qiang|篭:long|篮:lan|篯:jian|篰:bu|篱:li|篲:hui|篳:bi|篴:di|篵:cong|篶:yan|篷:peng|篸:se|篹:cuan|篺:pi|篻:piao|篼:dou|篽:yu|篾:mie|篿:tuan|簀:ze|簁:shai|簂:gui|簃:yi|簄:hu|簅:chan|簆:kou|簇:cu|簈:ping|簉:zao|簊:ji|簋:gui|簌:su|簍:lou|簎:ce|簏:lu|簐:nian|簑:suo|簒:cuan|簓:diao|簔:suo|簕:le|簖:duan|簗:liang|簘:xiao|簙:bo|簚:mi|簛:shai|簜:dang|簝:liao|簞:dan|簟:dian|簠:fu|簡:jian|簢:min|簣:kui|簤:dai|簥:jiao|簦:deng|簧:huang|簨:sun|簩:lao|簪:zan|簫:xiao|簬:lu|簭:shi|簮:zan|簯:qi|簰:pai|簱:qi|簲:pi|簳:gan|簴:ju|簵:lu|簶:lu|簷:yan|簸:bo|簹:dang|簺:sai|簻:zhua|簼:gou|簽:qian|簾:lian|簿:bu|籀:zhou|籁:lai|籂:shi|籃:lan|籄:kui|籅:yu|籆:yue|籇:hao|籈:zhen|籉:tai|籊:ti|籋:nie|籌:chou|籍:ji|籎:yi|籏:qi|籐:teng|籑:zhuan|籒:zhou|籓:fan|籔:shu|籕:zhou|籖:qian|籗:zhuo|籘:teng|籙:lu|籚:lu|籛:jian|籜:tuo|籝:ying|籞:yu|籟:lai|籠:long|籡:qie|籢:lian|籣:lan|籤:qian|籥:yue|籦:zhong|籧:qu|籨:lian|籩:bian|籪:duan|籫:zuan|籬:li|籭:shi|籮:luo|籯:ying|籰:yue|籱:zhuo|籲:yu|米:mi|籴:di|籵:fan|籶:shen|籷:zhe|籸:shen|籹:nv|籺:he|类:lei|籼:xian|籽:zi|籾:ni|籿:cun|粀:zhang|粁:qian|粂:zhai|粃:bi|粄:ban|粅:wu|粆:sha|粇:kang|粈:rou|粉:fen|粊:bi|粋:sui|粌:yin|粍:zhe|粎:mi|粏:tai|粐:hu|粑:ba|粒:li|粓:gan|粔:ju|粕:po|粖:mo|粗:cu|粘:zhan|粙:zhou|粚:chi|粛:su|粜:tiao|粝:li|粞:xi|粟:su|粠:hong|粡:tong|粢:zi|粣:ce|粤:yue|粥:zhou|粦:lin|粧:zhuang|粨:bai|粩:lao|粪:fen|粫:er|粬:qu|粭:he|粮:liang|粯:xian|粰:fu|粱:liang|粲:can|粳:jing|粴:li|粵:yue|粶:lu|粷:ju|粸:qi|粹:cui|粺:bai|粻:zhang|粼:lin|粽:zong|精:jing|粿:guo|糀:hua|糁:san|糂:san|糃:tang|糄:bian|糅:rou|糆:mian|糇:hou|糈:xu|糉:zong|糊:hu|糋:jian|糌:zan|糍:ci|糎:li|糏:xie|糐:fu|糑:nuo|糒:bei|糓:gu|糔:xiu|糕:gao|糖:tang|糗:qiu|糘:jia|糙:cao|糚:zhuang|糛:tang|糜:mi|糝:san|糞:fen|糟:zao|糠:kang|糡:jiang|糢:mo|糣:san|糤:san|糥:nuo|糦:chi|糧:liang|糨:jiang|糩:kuai|糪:bo|糫:huan|糬:shu|糭:ji|糮:xian|糯:nuo|糰:tuan|糱:nie|糲:li|糳:zuo|糴:di|糵:nie|糶:tiao|糷:lan|糸:mi|糹:si|糺:jiu|系:xi|糼:gong|糽:zheng|糾:jiu|糿:you|紀:ji|紁:cha|紂:zhou|紃:xun|約:yue|紅:hong|紆:yu|紇:ge|紈:wan|紉:ren|紊:wen|紋:wen|紌:qiu|納:na|紎:zi|紏:tou|紐:niu|紑:fou|紒:ji|紓:shu|純:chun|紕:pi|紖:zhen|紗:sha|紘:hong|紙:zhi|級:ji|紛:fen|紜:yun|紝:ren|紞:dan|紟:jin|素:su|紡:fang|索:suo|紣:cui|紤:jiu|紥:za|紦:ba|紧:jin|紨:fu|紩:zhi|紪:qi|紫:zi|紬:chou|紭:hong|紮:za|累:lei|細:xi|紱:fu|紲:xie|紳:shen|紴:bo|紵:zhu|紶:qu|紷:ling|紸:zhu|紹:shao|紺:gan|紻:yang|紼:fu|紽:tuo|紾:zhen|紿:dai|絀:chu|絁:shi|終:zhong|絃:xian|組:zu|絅:jiong|絆:ban|絇:qu|絈:mo|絉:shu|絊:zui|絋:kuang|経:jing|絍:ren|絎:hang|絏:xie|結:jie|絑:zhu|絒:chou|絓:gua|絔:bai|絕:jue|絖:kuang|絗:hu|絘:ci|絙:huan|絚:geng|絛:tao|絜:xie|絝:ku|絞:jiao|絟:quan|絠:gai|絡:luo|絢:xuan|絣:beng|絤:xian|絥:fu|給:gei|絧:tong|絨:rong|絩:tiao|絪:yin|絫:lei|絬:xie|絭:juan|絮:xu|絯:gai|絰:die|統:tong|絲:si|絳:jiang|絴:xiang|絵:hui|絶:jue|絷:zhi|絸:jian|絹:juan|絺:chi|絻:wen|絼:zhen|絽:lv|絾:cheng|絿:qiu|綀:shu|綁:bang|綂:tong|綃:xiao|綄:huan|綅:qin|綆:geng|綇:xiu|綈:ti|綉:xiu|綊:xie|綋:hong|綌:xi|綍:fu|綎:ting|綏:sui|綐:dui|綑:kun|綒:fu|經:jing|綔:hu|綕:zhi|綖:yan|綗:jiong|綘:feng|継:ji|続:xu|綛:ren|綜:zong|綝:chen|綞:duo|綟:li|綠:lv|綡:liang|綢:chou|綣:quan|綤:shao|綥:qi|綦:qi|綧:zhun|綨:qi|綩:wan|綪:qian|綫:xian|綬:shou|維:wei|綮:qi|綯:tao|綰:wan|綱:gang|網:wang|綳:beng|綴:zhui|綵:cai|綶:guo|綷:cui|綸:lun|綹:liu|綺:qi|綻:zhan|綼:bi|綽:chuo|綾:ling|綿:mian|緀:qi|緁:qie|緂:tian|緃:zong|緄:gun|緅:zou|緆:xi|緇:zi|緈:xing|緉:liang|緊:jin|緋:fei|緌:rui|緍:min|緎:yu|総:zong|緐:fan|緑:lv|緒:xu|緓:ying|緔:shang|緕:qi|緖:xu|緗:xiang|緘:jian|緙:ke|線:xian|緛:ruan|緜:mian|緝:ji|緞:duan|緟:chong|締:di|緡:min|緢:mao|緣:yuan|緤:xie|緥:bao|緦:si|緧:qiu|編:bian|緩:huan|緪:geng|緫:cong|緬:mian|緭:wei|緮:fu|緯:wei|緰:tou|緱:gou|緲:miao|緳:xie|練:lian|緵:zong|緶:bian|緷:gun|緸:yin|緹:ti|緺:gua|緻:zhi|緼:yun|緽:cheng|緾:chan|緿:dai|縀:xia|縁:yuan|縂:zong|縃:xu|縄:sheng|縅:wei|縆:geng|縇:se|縈:ying|縉:jin|縊:yi|縋:zhui|縌:ni|縍:bang|縎:gu|縏:pan|縐:zhou|縑:jian|縒:ci|縓:quan|縔:shuang|縕:yun|縖:xia|縗:cui|縘:ji|縙:rong|縚:tao|縛:fu|縜:yun|縝:zhen|縞:gao|縟:ru|縠:hu|縡:zai|縢:teng|縣:xian|縤:su|縥:zhen|縦:zong|縧:tao|縨:huang|縩:cai|縪:bi|縫:feng|縬:cu|縭:li|縮:suo|縯:yan|縰:xi|縱:zong|縲:lei|縳:zhuan|縴:qian|縵:man|縶:zhi|縷:lv|縸:mo|縹:piao|縺:lian|縻:mi|縼:xuan|總:zong|績:ji|縿:shan|繀:sui|繁:fan|繂:lv|繃:beng|繄:yi|繅:sao|繆:miu|繇:yao|繈:qiang|繉:hun|繊:xian|繋:ji|繌:sha|繍:xiu|繎:ran|繏:xuan|繐:sui|繑:qiao|繒:zeng|繓:zuo|織:zhi|繕:shan|繖:san|繗:lin|繘:yu|繙:fan|繚:liao|繛:chao|繜:zun|繝:jian|繞:rao|繟:chan|繠:rui|繡:xiu|繢:hui|繣:hua|繤:zuan|繥:xi|繦:qiang|繧:yun|繨:da|繩:sheng|繪:hui|繫:ji|繬:se|繭:jian|繮:jiang|繯:huan|繰:qiao|繱:cong|繲:jie|繳:jiao|繴:bi|繵:chan|繶:yi|繷:nong|繸:sui|繹:yi|繺:sha|繻:ru|繼:ji|繽:bin|繾:qian|繿:lan|纀:pu|纁:xun|纂:zuan|纃:qi|纄:peng|纅:yao|纆:mo|纇:lei|纈:xie|纉:zuan|纊:kuang|纋:you|續:xu|纍:lei|纎:xian|纏:chan|纐:jiao|纑:lu|纒:chan|纓:ying|纔:cai|纕:rang|纖:xian|纗:zui|纘:zuan|纙:luo|纚:xi|纛:dao|纜:lan|纝:lei|纞:lian|纟:si|纠:jiu|纡:yu|红:hong|纣:zhou|纤:xian|纥:ge|约:yue|级:ji|纨:wan|纩:kuang|纪:ji|纫:ren|纬:wei|纭:yun|纮:hong|纯:chun|纰:pi|纱:sha|纲:gang|纳:na|纴:ren|纵:zong|纶:lun|纷:fen|纸:zhi|纹:wen|纺:fang|纻:zhu|纼:zhen|纽:niu|纾:shu|线:xian|绀:gan|绁:xie|绂:fu|练:lian|组:zu|绅:shen|细:xi|织:zhi|终:zhong|绉:zhou|绊:ban|绋:fu|绌:chu|绍:shao|绎:yi|经:jing|绐:dai|绑:bang|绒:rong|结:jie|绔:ku|绕:rao|绖:die|绗:hang|绘:hui|给:gei|绚:xuan|绛:jiang|络:luo|绝:jue|绞:jiao|统:tong|绠:geng|绡:xiao|绢:juan|绣:xiu|绤:xi|绥:sui|绦:tao|继:ji|绨:ti|绩:ji|绪:xu|绫:ling|绬:ying|续:xu|绮:qi|绯:fei|绰:chuo|绱:shang|绲:gun|绳:sheng|维:wei|绵:mian|绶:shou|绷:beng|绸:chou|绹:tao|绺:liu|绻:quan|综:zong|绽:zhan|绾:wan|绿:lv|缀:zhui|缁:zi|缂:ke|缃:xiang|缄:jian|缅:mian|缆:lan|缇:ti|缈:miao|缉:ji|缊:yun|缋:hui|缌:si|缍:duo|缎:duan|缏:bian|缐:xian|缑:gou|缒:zhui|缓:huan|缔:di|缕:lv|编:bian|缗:min|缘:yuan|缙:jin|缚:fu|缛:ru|缜:zhen|缝:feng|缞:cui|缟:gao|缠:chan|缡:li|缢:yi|缣:jian|缤:bin|缥:piao|缦:man|缧:lei|缨:ying|缩:suo|缪:miu|缫:sao|缬:xie|缭:liao|缮:shan|缯:zeng|缰:jiang|缱:qian|缲:qiao|缳:huan|缴:jiao|缵:zuan|缶:fou|缷:xie|缸:gang|缹:fou|缺:que|缻:fou|缼:qi|缽:bo|缾:ping|缿:xiang|罀:zhao|罁:gang|罂:ying|罃:ying|罄:qing|罅:xia|罆:guan|罇:zun|罈:tan|罉:cang|罊:qi|罋:weng|罌:ying|罍:lei|罎:tan|罏:lu|罐:guan|网:wang|罒:si|罓:gang|罔:wang|罕:han|罖:ra|罗:luo|罘:fu|罙:shen|罚:fa|罛:gu|罜:zhu|罝:ju|罞:meng|罟:gu|罠:min|罡:gang|罢:ba|罣:gua|罤:ti|罥:juan|罦:fu|罧:shen|罨:yan|罩:zhao|罪:zui|罫:gua|罬:zhuo|罭:yu|置:zhi|罯:an|罰:fa|罱:lan|署:shu|罳:si|罴:pi|罵:ma|罶:liu|罷:ba|罸:fa|罹:li|罺:chao|罻:wei|罼:bi|罽:ji|罾:zeng|罿:chong|羀:liu|羁:ji|羂:juan|羃:mi|羄:zhao|羅:luo|羆:pi|羇:ji|羈:ji|羉:luan|羊:yang|羋:mi|羌:qiang|羍:da|美:mei|羏:yang|羐:you|羑:you|羒:fen|羓:ba|羔:gao|羕:yang|羖:gu|羗:qiang|羘:zang|羙:gao|羚:ling|羛:yi|羜:zhu|羝:di|羞:xiu|羟:qiang|羠:yi|羡:xian|羢:rong|羣:qun|群:qun|羥:qiang|羦:huan|羧:suo|羨:xian|義:yi|羪:yang|羫:qiang|羬:qian|羭:yu|羮:geng|羯:jie|羰:tang|羱:yuan|羲:xi|羳:fan|羴:shan|羵:fen|羶:shan|羷:lian|羸:lei|羹:geng|羺:nou|羻:qiang|羼:chan|羽:yu|羾:gong|羿:yi|翀:chong|翁:weng|翂:fen|翃:hong|翄:chi|翅:chi|翆:cui|翇:fu|翈:xia|翉:pen|翊:yi|翋:la|翌:yi|翍:pi|翎:ling|翏:liu|翐:zhi|翑:qu|習:xi|翓:xie|翔:xiang|翕:xi|翖:xi|翗:ke|翘:qiao|翙:hui|翚:hui|翛:xiao|翜:sha|翝:hong|翞:jiang|翟:di|翠:cui|翡:fei|翢:dao|翣:sha|翤:chi|翥:zhu|翦:jian|翧:xuan|翨:chi|翩:pian|翪:zong|翫:wan|翬:hui|翭:hou|翮:he|翯:he|翰:han|翱:ao|翲:piao|翳:yi|翴:lian|翵:qu|翶:ao|翷:lin|翸:pen|翹:qiao|翺:ao|翻:fan|翼:yi|翽:hui|翾:xuan|翿:dao|耀:yao|老:lao|耂:lao|考:kao|耄:mao|者:zhe|耆:qi|耇:gou|耈:gou|耉:gou|耊:die|耋:die|而:er|耍:shua|耎:ruan|耏:er|耐:nai|耑:duan|耒:lei|耓:ting|耔:zi|耕:geng|耖:chao|耗:hao|耘:yun|耙:ba|耚:pi|耛:chi|耜:si|耝:qu|耞:jia|耟:ju|耠:huo|耡:chu|耢:lao|耣:lun|耤:ji|耥:tang|耦:ou|耧:lou|耨:nou|耩:jiang|耪:pang|耫:ze|耬:lou|耭:ji|耮:lao|耯:huo|耰:you|耱:mo|耲:huai|耳:er|耴:yi|耵:ding|耶:ye|耷:da|耸:song|耹:qin|耺:yun|耻:chi|耼:dan|耽:dan|耾:hong|耿:geng|聀:zhi|聁:zhi|聂:nie|聃:dan|聄:zhen|聅:che|聆:ling|聇:zheng|聈:you|聉:wa|聊:liao|聋:long|职:zhi|聍:ning|聎:tiao|聏:er|聐:ya|聑:tie|聒:guo|聓:se|联:lian|聕:hao|聖:sheng|聗:lie|聘:pin|聙:jing|聚:ju|聛:bi|聜:di|聝:guo|聞:wen|聟:xu|聠:ping|聡:cong|聢:ding|聣:ding|聤:ting|聥:ju|聦:cong|聧:kui|聨:lian|聩:kui|聪:cong|聫:lian|聬:weng|聭:kui|聮:lian|聯:lian|聰:cong|聱:ao|聲:sheng|聳:song|聴:ting|聵:kui|聶:nie|職:zhi|聸:dan|聹:ning|聺:qie|聻:jian|聼:ting|聽:ting|聾:long|聿:yu|肀:nie|肁:zhao|肂:si|肃:su|肄:yi|肅:su|肆:si|肇:zhao|肈:zhao|肉:rou|肊:yi|肋:lei|肌:ji|肍:qiu|肎:ken|肏:cao|肐:ge|肑:di|肒:huan|肓:huang|肔:chi|肕:ren|肖:xiao|肗:ru|肘:zhou|肙:yuan|肚:du|肛:gang|肜:rong|肝:gan|肞:cha|肟:wo|肠:chang|股:gu|肢:zhi|肣:han|肤:fu|肥:fei|肦:fen|肧:pei|肨:pang|肩:jian|肪:fang|肫:zhun|肬:you|肭:na|肮:ang|肯:ken|肰:ran|肱:gong|育:yu|肳:wen|肴:yao|肵:qi|肶:pi|肷:qian|肸:xi|肹:xi|肺:fei|肻:ken|肼:jing|肽:tai|肾:shen|肿:zhong|胀:zhang|胁:xie|胂:shen|胃:wei|胄:zhou|胅:die|胆:dan|胇:bi|胈:ba|胉:bo|胊:qu|胋:tian|背:bei|胍:gua|胎:tai|胏:zi|胐:fei|胑:zhi|胒:ni|胓:peng|胔:zi|胕:fu|胖:pang|胗:zhen|胘:xian|胙:zuo|胚:pei|胛:jia|胜:sheng|胝:zhi|胞:bao|胟:mu|胠:qu|胡:hu|胢:ke|胣:chi|胤:yin|胥:xu|胦:yang|胧:long|胨:dong|胩:ka|胪:lu|胫:jing|胬:nu|胭:yan|胮:pang|胯:kua|胰:yi|胱:guang|胲:hai|胳:ge|胴:dong|胵:chi|胶:jiao|胷:xiong|胸:xiong|胹:er|胺:an|胻:heng|胼:pian|能:neng|胾:zi|胿:gui|脀:cheng|脁:tiao|脂:zhi|脃:cui|脄:mei|脅:xie|脆:cui|脇:xie|脈:mai|脉:mai|脊:ji|脋:xie|脌:nin|脍:kuai|脎:sa|脏:zang|脐:qi|脑:nao|脒:mi|脓:nong|脔:luan|脕:wen|脖:bo|脗:wen|脘:wan|脙:xiu|脚:jiao|脛:jing|脜:you|脝:heng|脞:cuo|脟:lie|脠:shan|脡:ting|脢:mei|脣:chun|脤:shen|脥:jia|脦:te|脧:juan|脨:ji|脩:xiu|脪:xin|脫:tuo|脬:pao|脭:cheng|脮:tui|脯:fu|脰:dou|脱:tuo|脲:niao|脳:nao|脴:pi|脵:gu|脶:luo|脷:lei|脸:lian|脹:zhang|脺:sui|脻:jie|脼:liang|脽:shui|脾:pi|脿:biao|腀:lun|腁:pian|腂:guo|腃:quan|腄:chui|腅:dan|腆:tian|腇:nei|腈:jing|腉:nai|腊:la|腋:ye|腌:yan|腍:ren|腎:shen|腏:chuo|腐:fu|腑:fu|腒:ju|腓:fei|腔:qiang|腕:wan|腖:dong|腗:pi|腘:guo|腙:zong|腚:ding|腛:wo|腜:mei|腝:ruan|腞:dun|腟:chi|腠:cou|腡:luo|腢:ou|腣:di|腤:an|腥:xing|腦:nao|腧:shu|腨:shuan|腩:nan|腪:yun|腫:zhong|腬:rou|腭:e|腮:sai|腯:tu|腰:yao|腱:jian|腲:wei|腳:jiao|腴:yu|腵:jia|腶:duan|腷:bi|腸:chang|腹:fu|腺:xian|腻:ni|腼:mian|腽:wa|腾:teng|腿:tui|膀:bang|膁:qian|膂:lv|膃:wa|膄:shou|膅:tang|膆:su|膇:zhui|膈:ge|膉:yi|膊:bo|膋:liao|膌:ji|膍:pi|膎:xie|膏:gao|膐:lv|膑:bin|膒:ou|膓:chang|膔:lu|膕:guo|膖:pang|膗:chuai|膘:biao|膙:jiang|膚:fu|膛:tang|膜:mo|膝:xi|膞:zhuan|膟:lu|膠:jiao|膡:ying|膢:lv|膣:zhi|膤:xue|膥:cen|膦:lin|膧:tong|膨:peng|膩:ni|膪:zha|膫:liao|膬:cui|膭:gui|膮:xiao|膯:teng|膰:fan|膱:zhi|膲:jiao|膳:shan|膴:hu|膵:cui|膶:yen|膷:xiang|膸:sui|膹:fen|膺:ying|膻:shan|膼:zhua|膽:dan|膾:kuai|膿:nong|臀:tun|臁:lian|臂:bi|臃:yong|臄:jue|臅:chu|臆:yi|臇:juan|臈:la|臉:lian|臊:sao|臋:tun|臌:gu|臍:qi|臎:cui|臏:bin|臐:xun|臑:nao|臒:wo|臓:zang|臔:xian|臕:biao|臖:xing|臗:kun|臘:la|臙:yan|臚:lu|臛:huo|臜:za|臝:luo|臞:qu|臟:zang|臠:luan|臡:ni|臢:za|臣:chen|臤:qian|臥:wo|臦:guang|臧:zang|臨:lin|臩:guang|自:zi|臫:jiao|臬:nie|臭:chou|臮:ji|臯:gao|臰:chou|臱:mian|臲:nie|至:zhi|致:zhi|臵:ge|臶:jian|臷:die|臸:zhi|臹:xiu|臺:tai|臻:zhen|臼:jiu|臽:xian|臾:yu|臿:cha|舀:yao|舁:yu|舂:chong|舃:que|舄:xi|舅:jiu|舆:yu|與:yu|興:xing|舉:ju|舊:jiu|舋:xin|舌:she|舍:she|舎:she|舏:jiu|舐:shi|舑:tan|舒:shu|舓:shi|舔:tian|舕:tan|舖:pu|舗:pu|舘:guan|舙:hua|舚:tian|舛:chuan|舜:shun|舝:xia|舞:wu|舟:zhou|舠:dao|舡:chuan|舢:shan|舣:yi|舤:fan|舥:pa|舦:tai|舧:fan|舨:ban|舩:chuan|航:hang|舫:fang|般:ban|舭:bi|舮:lu|舯:zhong|舰:jian|舱:cang|舲:ling|舳:zhu|舴:ze|舵:duo|舶:bo|舷:xian|舸:ge|船:chuan|舺:xia|舻:lu|舼:qiong|舽:pang|舾:xi|舿:kua|艀:fu|艁:zao|艂:feng|艃:li|艄:shao|艅:yu|艆:lang|艇:ting|艈:ting|艉:wei|艊:bo|艋:meng|艌:nian|艍:ju|艎:huang|艏:shou|艐:zong|艑:bian|艒:mu|艓:die|艔:dou|艕:bang|艖:cha|艗:yi|艘:sou|艙:cang|艚:cao|艛:lou|艜:dai|艝:xue|艞:yao|艟:chong|艠:deng|艡:dang|艢:qiang|艣:lu|艤:yi|艥:ji|艦:jian|艧:huo|艨:meng|艩:qi|艪:lu|艫:lu|艬:chan|艭:shuang|艮:gen|良:liang|艰:jian|艱:jian|色:se|艳:yan|艴:fu|艵:ping|艶:yan|艷:yan|艸:cao|艹:cao|艺:yi|艻:le|艼:ting|艽:jiao|艾:ai|艿:nai|芀:tiao|芁:jiao|节:jie|芃:peng|芄:wan|芅:yi|芆:cha|芇:mian|芈:mi|芉:gan|芊:qian|芋:yu|芌:yu|芍:shao|芎:xiong|芏:du|芐:hu|芑:qi|芒:mang|芓:zi|芔:hui|芕:sui|芖:zhi|芗:xiang|芘:pi|芙:fu|芚:tun|芛:wei|芜:wu|芝:zhi|芞:qi|芟:shan|芠:wen|芡:qian|芢:ren|芣:fu|芤:kou|芥:jie|芦:lu|芧:zhu|芨:ji|芩:qin|芪:qi|芫:yan|芬:fen|芭:ba|芮:rui|芯:xin|芰:ji|花:hua|芲:hua|芳:fang|芴:wu|芵:jue|芶:ji|芷:zhi|芸:yun|芹:qin|芺:ao|芻:chu|芼:mao|芽:ya|芾:fu|芿:reng|苀:hang|苁:cong|苂:yin|苃:you|苄:bian|苅:yi|苆:qie|苇:wei|苈:li|苉:pi|苊:e|苋:xian|苌:chang|苍:cang|苎:zhu|苏:su|苐:di|苑:yuan|苒:ran|苓:ling|苔:tai|苕:shao|苖:di|苗:miao|苘:qing|苙:li|苚:yong|苛:ke|苜:mu|苝:bei|苞:bao|苟:gou|苠:min|苡:yi|苢:yi|苣:ju|苤:pie|若:ruo|苦:ku|苧:zhu|苨:ni|苩:bo|苪:bing|苫:shan|苬:xiu|苭:yao|苮:xian|苯:ben|苰:hong|英:ying|苲:zha|苳:dong|苴:ju|苵:die|苶:nie|苷:gan|苸:hu|苹:ping|苺:mei|苻:fu|苼:sheng|苽:gua|苾:bi|苿:wei|茀:fu|茁:zhuo|茂:mao|范:fan|茄:qie|茅:mao|茆:mao|茇:ba|茈:ci|茉:mo|茊:zi|茋:zhi|茌:chi|茍:ji|茎:jing|茏:long|茐:cong|茑:niao|茒:niao|茓:xue|茔:ying|茕:qiong|茖:ge|茗:ming|茘:li|茙:rong|茚:yin|茛:gen|茜:qian|茝:chai|茞:chen|茟:yu|茠:hao|茡:zi|茢:lie|茣:wu|茤:duo|茥:gui|茦:ci|茧:jian|茨:ci|茩:gou|茪:guang|茫:mang|茬:cha|茭:jiao|茮:jiao|茯:fu|茰:yu|茱:zhu|茲:zi|茳:jiang|茴:hui|茵:yin|茶:cha|茷:fa|茸:rong|茹:ru|茺:chong|茻:mang|茼:tong|茽:zhong|茾:qian|茿:zhu|荀:xun|荁:huan|荂:fu|荃:quan|荄:gai|荅:da|荆:jing|荇:xing|荈:chuan|草:cao|荊:jing|荋:er|荌:an|荍:qiao|荎:chi|荏:ren|荐:jian|荑:yi|荒:huang|荓:ping|荔:li|荕:jin|荖:lao|荗:shu|荘:zhuang|荙:da|荚:jia|荛:rao|荜:bi|荝:ce|荞:qiao|荟:hui|荠:qi|荡:dang|荢:yu|荣:rong|荤:hun|荥:ying|荦:luo|荧:ying|荨:qian|荩:jin|荪:sun|荫:yin|荬:mai|荭:hong|荮:zhou|药:yao|荰:du|荱:wei|荲:li|荳:dou|荴:fu|荵:ren|荶:yin|荷:he|荸:bi|荹:bu|荺:yun|荻:di|荼:tu|荽:sui|荾:sui|荿:cheng|莀:chen|莁:wu|莂:bie|莃:xi|莄:geng|莅:li|莆:pu|莇:zhu|莈:mo|莉:li|莊:zhuang|莋:zuo|莌:tuo|莍:qiu|莎:sha|莏:suo|莐:chen|莑:peng|莒:ju|莓:mei|莔:meng|莕:xing|莖:jing|莗:che|莘:xin|莙:jun|莚:yan|莛:ting|莜:you|莝:cuo|莞:wan|莟:han|莠:you|莡:cuo|莢:jia|莣:wang|莤:you|莥:niu|莦:shao|莧:xian|莨:lang|莩:fu|莪:e|莫:mo|莬:wen|莭:jie|莮:nan|莯:mu|莰:kan|莱:lai|莲:lian|莳:shi|莴:wo|莵:tu|莶:xian|获:huo|莸:you|莹:ying|莺:ying|莻:n|莼:chun|莽:mang|莾:mang|莿:ci|菀:wan|菁:jing|菂:di|菃:qu|菄:dong|菅:jian|菆:zou|菇:gu|菈:la|菉:lu|菊:ju|菋:wei|菌:jun|菍:nie|菎:kun|菏:he|菐:pu|菑:zai|菒:gao|菓:guo|菔:fu|菕:lun|菖:chang|菗:chou|菘:song|菙:chui|菚:zhan|菛:men|菜:cai|菝:ba|菞:li|菟:tu|菠:bo|菡:han|菢:bao|菣:qin|菤:juan|菥:xi|菦:qin|菧:di|菨:jie|菩:pu|菪:dang|菫:jin|菬:qiao|菭:tai|菮:geng|華:hua|菰:gu|菱:ling|菲:fei|菳:qin|菴:an|菵:wang|菶:beng|菷:zhou|菸:yan|菹:zu|菺:jian|菻:lin|菼:tan|菽:shu|菾:tian|菿:dao|萀:hu|萁:qi|萂:he|萃:cui|萄:tao|萅:chun|萆:bi|萇:chang|萈:huan|萉:fei|萊:lai|萋:qi|萌:meng|萍:ping|萎:wei|萏:dan|萐:sha|萑:huan|萒:yan|萓:yi|萔:shao|萕:ji|萖:guan|萗:ce|萘:nai|萙:zhen|萚:tuo|萛:jiu|萜:tie|萝:luo|萞:bi|萟:yi|萠:meng|萡:bao|萢:pao|萣:ding|萤:ying|营:ying|萦:ying|萧:xiao|萨:sa|萩:qiu|萪:ke|萫:xiang|萬:wan|萭:yu|萮:yu|萯:fu|萰:lian|萱:xuan|萲:xuan|萳:nan|萴:ce|萵:wo|萶:chun|萷:xiao|萸:yu|萹:bian|萺:mu|萻:an|萼:e|落:luo|萾:ying|萿:kuo|葀:kuo|葁:jiang|葂:mian|葃:zuo|葄:zuo|葅:zu|葆:bao|葇:rou|葈:xi|葉:ye|葊:an|葋:qu|葌:jian|葍:fu|葎:lv|葏:jian|葐:pen|葑:feng|葒:hong|葓:hong|葔:hou|葕:yan|葖:tu|著:zhu|葘:zi|葙:xiang|葚:ren|葛:ge|葜:qia|葝:qing|葞:mi|葟:huang|葠:shen|葡:pu|葢:gai|董:dong|葤:zhou|葥:jian|葦:wei|葧:bo|葨:wei|葩:pa|葪:ji|葫:hu|葬:zang|葭:jia|葮:duan|葯:yao|葰:sui|葱:cong|葲:quan|葳:wei|葴:zhen|葵:kui|葶:ting|葷:hun|葸:xi|葹:shi|葺:qi|葻:lan|葼:zong|葽:yao|葾:yuan|葿:mei|蒀:yun|蒁:shu|蒂:di|蒃:zhuan|蒄:guan|蒅:ran|蒆:xue|蒇:chan|蒈:kai|蒉:kui|蒊:kui|蒋:jiang|蒌:lou|蒍:wei|蒎:pai|蒏:you|蒐:sou|蒑:yin|蒒:shi|蒓:chun|蒔:shi|蒕:yun|蒖:zhen|蒗:lang|蒘:ru|蒙:meng|蒚:li|蒛:que|蒜:suan|蒝:yuan|蒞:li|蒟:ju|蒠:xi|蒡:bang|蒢:chu|蒣:xu|蒤:tu|蒥:liu|蒦:huo|蒧:dian|蒨:qian|蒩:zu|蒪:po|蒫:cuo|蒬:yuan|蒭:chu|蒮:yu|蒯:kuai|蒰:pan|蒱:pu|蒲:pu|蒳:na|蒴:shuo|蒵:xi|蒶:fen|蒷:yun|蒸:zheng|蒹:jian|蒺:ji|蒻:ruo|蒼:cang|蒽:en|蒾:mi|蒿:hao|蓀:sun|蓁:zhen|蓂:ming|蓃:sou|蓄:xu|蓅:liu|蓆:xi|蓇:gu|蓈:lang|蓉:rong|蓊:weng|蓋:gai|蓌:cuo|蓍:shi|蓎:tang|蓏:luo|蓐:ru|蓑:suo|蓒:xuan|蓓:bei|蓔:yao|蓕:gui|蓖:bi|蓗:zong|蓘:gun|蓙:zuo|蓚:tiao|蓛:ce|蓜:pei|蓝:lan|蓞:lan|蓟:ji|蓠:li|蓡:shen|蓢:lang|蓣:yu|蓤:ling|蓥:ying|蓦:mo|蓧:diao|蓨:tiao|蓩:mao|蓪:tong|蓫:zhu|蓬:peng|蓭:an|蓮:lian|蓯:cong|蓰:xi|蓱:ping|蓲:qiu|蓳:jin|蓴:chun|蓵:jie|蓶:wei|蓷:tui|蓸:cao|蓹:yu|蓺:yi|蓻:ju|蓼:liao|蓽:bi|蓾:lu|蓿:xu|蔀:bu|蔁:zhang|蔂:lei|蔃:qiang|蔄:man|蔅:yan|蔆:ling|蔇:ji|蔈:biao|蔉:gun|蔊:han|蔋:di|蔌:su|蔍:lu|蔎:she|蔏:shang|蔐:di|蔑:mie|蔒:xun|蔓:man|蔔:bo|蔕:di|蔖:cuo|蔗:zhe|蔘:shen|蔙:xuan|蔚:wei|蔛:hu|蔜:ao|蔝:mi|蔞:lou|蔟:cu|蔠:zhong|蔡:cai|蔢:po|蔣:jiang|蔤:mi|蔥:cong|蔦:niao|蔧:hui|蔨:juan|蔩:yin|蔪:shan|蔫:nian|蔬:shu|蔭:yin|蔮:guo|蔯:chen|蔰:hu|蔱:sha|蔲:kou|蔳:qian|蔴:ma|蔵:cang|蔶:ze|蔷:qiang|蔸:dou|蔹:lian|蔺:lin|蔻:kou|蔼:ai|蔽:bi|蔾:li|蔿:wei|蕀:ji|蕁:qian|蕂:sheng|蕃:fan|蕄:meng|蕅:ou|蕆:chan|蕇:dian|蕈:xun|蕉:jiao|蕊:rui|蕋:rui|蕌:lei|蕍:yu|蕎:qiao|蕏:chu|蕐:hua|蕑:jian|蕒:mai|蕓:yun|蕔:bao|蕕:you|蕖:qu|蕗:lu|蕘:rao|蕙:hui|蕚:e|蕛:ti|蕜:fei|蕝:jue|蕞:zui|蕟:fei|蕠:ru|蕡:fen|蕢:kui|蕣:shui|蕤:rui|蕥:ya|蕦:xu|蕧:fu|蕨:jue|蕩:dang|蕪:wu|蕫:dong|蕬:si|蕭:xiao|蕮:xi|蕯:long|蕰:yun|蕱:shao|蕲:qi|蕳:jian|蕴:yun|蕵:sun|蕶:ling|蕷:yu|蕸:xia|蕹:weng|蕺:ji|蕻:hong|蕼:si|蕽:nong|蕾:lei|蕿:xuan|薀:yun|薁:yu|薂:xi|薃:hao|薄:bao|薅:hao|薆:ai|薇:wei|薈:hui|薉:hui|薊:ji|薋:ci|薌:xiang|薍:wan|薎:mie|薏:yi|薐:leng|薑:jiang|薒:can|薓:shen|薔:qiang|薕:lian|薖:ke|薗:yuan|薘:da|薙:ti|薚:tang|薛:xue|薜:bi|薝:zhan|薞:sun|薟:xian|薠:fan|薡:ding|薢:xie|薣:gu|薤:xie|薥:shu|薦:jian|薧:hao|薨:hong|薩:sa|薪:xin|薫:xun|薬:yao|薭:bai|薮:sou|薯:shu|薰:xun|薱:dui|薲:pin|薳:wei|薴:ning|薵:chou|薶:mai|薷:ru|薸:piao|薹:tai|薺:qi|薻:zao|薼:chen|薽:zhen|薾:er|薿:ni|藀:ying|藁:gao|藂:cong|藃:xiao|藄:qi|藅:fa|藆:jian|藇:xu|藈:kui|藉:jie|藊:bian|藋:diao|藌:mi|藍:lan|藎:jin|藏:cang|藐:miao|藑:qiong|藒:qie|藓:xian|藔:xian|藕:ou|藖:xian|藗:su|藘:lv|藙:yi|藚:xu|藛:xie|藜:li|藝:yi|藞:la|藟:lei|藠:jiao|藡:di|藢:zhi|藣:bei|藤:teng|藥:yao|藦:mo|藧:huan|藨:biao|藩:fan|藪:sou|藫:tan|藬:tui|藭:qiong|藮:qiao|藯:wei|藰:liu|藱:hui|藲:ou|藳:gao|藴:yun|藵:bao|藶:li|藷:shu|藸:zhu|藹:ai|藺:lin|藻:zao|藼:xuan|藽:qin|藾:lai|藿:huo|蘀:tuo|蘁:wu|蘂:rui|蘃:rui|蘄:qi|蘅:heng|蘆:lu|蘇:su|蘈:tui|蘉:mang|蘊:yun|蘋:ping|蘌:yu|蘍:xun|蘎:ji|蘏:jiong|蘐:xuan|蘑:mo|蘒:qiu|蘓:su|蘔:jiong|蘕:feng|蘖:nie|蘗:bo|蘘:rang|蘙:yi|蘚:xian|蘛:yu|蘜:ju|蘝:lian|蘞:lian|蘟:yin|蘠:qiang|蘡:ying|蘢:long|蘣:tou|蘤:hua|蘥:yue|蘦:ling|蘧:qu|蘨:yao|蘩:fan|蘪:mei|蘫:han|蘬:kui|蘭:lan|蘮:ji|蘯:dang|蘰:man|蘱:lei|蘲:lei|蘳:hua|蘴:feng|蘵:zhi|蘶:wei|蘷:kui|蘸:zhan|蘹:huai|蘺:li|蘻:ji|蘼:mi|蘽:lei|蘾:huai|蘿:luo|虀:ji|虁:kui|虂:lu|虃:jian|虄:sai|虅:teng|虆:lei|虇:quan|虈:xiao|虉:yi|虊:luan|虋:men|虌:bie|虍:hu|虎:hu|虏:lu|虐:nue|虑:lv|虒:si|虓:xiao|虔:qian|處:chu|虖:hu|虗:xu|虘:cuo|虙:fu|虚:xu|虛:xu|虜:lu|虝:hu|虞:yu|號:hao|虠:jiao|虡:ju|虢:guo|虣:bao|虤:yan|虥:zhan|虦:zhan|虧:kui|虨:bin|虩:xi|虪:shu|虫:chong|虬:qiu|虭:diao|虮:ji|虯:qiu|虰:cheng|虱:shi|虲:shi|虳:jue|虴:zhe|虵:she|虶:yu|虷:han|虸:zi|虹:hong|虺:hui|虻:meng|虼:ge|虽:sui|虾:xia|虿:chai|蚀:shi|蚁:yi|蚂:ma|蚃:xiang|蚄:fang|蚅:e|蚆:ba|蚇:chi|蚈:qian|蚉:wen|蚊:wen|蚋:rui|蚌:bang|蚍:pi|蚎:yue|蚏:yue|蚐:jun|蚑:qi|蚒:tong|蚓:yin|蚔:qi|蚕:can|蚖:yuan|蚗:jue|蚘:hui|蚙:qin|蚚:qi|蚛:zhong|蚜:ya|蚝:hao|蚞:mu|蚟:wang|蚠:fen|蚡:fen|蚢:hang|蚣:gong|蚤:zao|蚥:fu|蚦:ran|蚧:jie|蚨:fu|蚩:chi|蚪:dou|蚫:pao|蚬:xian|蚭:ni|蚮:dai|蚯:qiu|蚰:you|蚱:zha|蚲:ping|蚳:chi|蚴:you|蚵:he|蚶:han|蚷:ju|蚸:li|蚹:fu|蚺:ran|蚻:zha|蚼:gou|蚽:pi|蚾:bo|蚿:xian|蛀:zhu|蛁:diao|蛂:bie|蛃:bing|蛄:gu|蛅:zhan|蛆:qu|蛇:she|蛈:tie|蛉:ling|蛊:gu|蛋:dan|蛌:gu|蛍:ying|蛎:li|蛏:cheng|蛐:qu|蛑:mao|蛒:ge|蛓:ci|蛔:hui|蛕:hui|蛖:mang|蛗:fu|蛘:yang|蛙:wa|蛚:lie|蛛:zhu|蛜:yi|蛝:xian|蛞:kuo|蛟:jiao|蛠:li|蛡:yi|蛢:ping|蛣:jie|蛤:ge|蛥:she|蛦:yi|蛧:wang|蛨:mo|蛩:qiong|蛪:qie|蛫:gui|蛬:qiong|蛭:zhi|蛮:man|蛯:lao|蛰:zhe|蛱:jia|蛲:nao|蛳:si|蛴:qi|蛵:xing|蛶:jie|蛷:qiu|蛸:shao|蛹:yong|蛺:jia|蛻:tui|蛼:che|蛽:bei|蛾:e|蛿:han|蜀:shu|蜁:xuan|蜂:feng|蜃:shen|蜄:zhen|蜅:fu|蜆:xian|蜇:zhe|蜈:wu|蜉:fu|蜊:li|蜋:lang|蜌:bi|蜍:chu|蜎:yuan|蜏:you|蜐:jie|蜑:dan|蜒:yan|蜓:ting|蜔:dian|蜕:tui|蜖:hui|蜗:wo|蜘:zhi|蜙:song|蜚:fei|蜛:ju|蜜:mi|蜝:qi|蜞:qi|蜟:yu|蜠:jun|蜡:la|蜢:meng|蜣:qiang|蜤:si|蜥:xi|蜦:lun|蜧:li|蜨:die|蜩:tiao|蜪:tao|蜫:kun|蜬:han|蜭:han|蜮:yu|蜯:bang|蜰:fei|蜱:pi|蜲:wei|蜳:dun|蜴:yi|蜵:yuan|蜶:suo|蜷:quan|蜸:qian|蜹:rui|蜺:ni|蜻:qing|蜼:wei|蜽:liang|蜾:guo|蜿:wan|蝀:dong|蝁:e|蝂:ban|蝃:di|蝄:wang|蝅:can|蝆:mi|蝇:ying|蝈:guo|蝉:chan|蝊:chan|蝋:la|蝌:ke|蝍:ji|蝎:xie|蝏:ting|蝐:mao|蝑:xu|蝒:mian|蝓:yu|蝔:jie|蝕:shi|蝖:xuan|蝗:huang|蝘:yan|蝙:bian|蝚:rou|蝛:wei|蝜:fu|蝝:yuan|蝞:mei|蝟:wei|蝠:fu|蝡:ruan|蝢:xie|蝣:you|蝤:qiu|蝥:mao|蝦:xia|蝧:ying|蝨:shi|蝩:chong|蝪:tang|蝫:zhu|蝬:zong|蝭:ti|蝮:fu|蝯:yuan|蝰:kui|蝱:meng|蝲:la|蝳:du|蝴:hu|蝵:qiu|蝶:die|蝷:xi|蝸:wo|蝹:yun|蝺:qu|蝻:nan|蝼:lou|蝽:chun|蝾:rong|蝿:ying|螀:jiang|螁:ban|螂:lang|螃:pang|螄:si|螅:xi|螆:ci|螇:xi|螈:yuan|螉:weng|螊:lian|螋:sou|螌:ban|融:rong|螎:rong|螏:ji|螐:wu|螑:xiu|螒:han|螓:qin|螔:yi|螕:bi|螖:hua|螗:tang|螘:yi|螙:du|螚:nai|螛:he|螜:hu|螝:hui|螞:ma|螟:ming|螠:yi|螡:wen|螢:ying|螣:teng|螤:zhong|螥:cang|螦:si|螧:qi|螨:man|螩:tiao|螪:shang|螫:shi|螬:cao|螭:chi|螮:di|螯:ao|螰:lu|螱:wei|螲:zhi|螳:tang|螴:chen|螵:piao|螶:qu|螷:pi|螸:yu|螹:jian|螺:luo|螻:lou|螼:qin|螽:zhong|螾:yin|螿:jiang|蟀:shuai|蟁:wen|蟂:xiao|蟃:man|蟄:zhe|蟅:zhe|蟆:ma|蟇:ma|蟈:guo|蟉:liu|蟊:mao|蟋:xi|蟌:cong|蟍:li|蟎:man|蟏:xiao|蟐:chang|蟑:zhang|蟒:mang|蟓:xiang|蟔:mo|蟕:zui|蟖:si|蟗:qiu|蟘:te|蟙:zhi|蟚:peng|蟛:peng|蟜:jiao|蟝:qu|蟞:bie|蟟:liao|蟠:pan|蟡:gui|蟢:xi|蟣:ji|蟤:zhuan|蟥:huang|蟦:fei|蟧:lao|蟨:jue|蟩:jue|蟪:hui|蟫:yin|蟬:chan|蟭:jiao|蟮:shan|蟯:nao|蟰:xiao|蟱:mou|蟲:chong|蟳:xun|蟴:si|蟵:chu|蟶:cheng|蟷:dang|蟸:li|蟹:xie|蟺:shan|蟻:yi|蟼:jing|蟽:da|蟾:chan|蟿:qi|蠀:ci|蠁:xiang|蠂:she|蠃:luo|蠄:qin|蠅:ying|蠆:chai|蠇:li|蠈:zei|蠉:xuan|蠊:lian|蠋:zhu|蠌:ze|蠍:xie|蠎:mang|蠏:xie|蠐:qi|蠑:rong|蠒:jian|蠓:meng|蠔:hao|蠕:ru|蠖:huo|蠗:zhuo|蠘:jie|蠙:bin|蠚:he|蠛:mie|蠜:fan|蠝:lei|蠞:jie|蠟:la|蠠:min|蠡:li|蠢:chun|蠣:li|蠤:qiu|蠥:nie|蠦:lu|蠧:du|蠨:xiao|蠩:zhu|蠪:long|蠫:li|蠬:long|蠭:pang|蠮:ye|蠯:pi|蠰:nang|蠱:gu|蠲:juan|蠳:ying|蠴:shu|蠵:xi|蠶:can|蠷:qu|蠸:quan|蠹:du|蠺:can|蠻:man|蠼:qu|蠽:jie|蠾:zhu|蠿:zhuo|血:xue|衁:huang|衂:nv|衃:pei|衄:nv|衅:xin|衆:zhong|衇:mai|衈:er|衉:kai|衊:mie|衋:xi|行:xing|衍:yan|衎:kan|衏:yuan|衐:qu|衑:ling|衒:xuan|術:shu|衔:xian|衕:tong|衖:long|街:jie|衘:xian|衙:ya|衚:hu|衛:wei|衜:dao|衝:chong|衞:wei|衟:dao|衠:zhun|衡:heng|衢:qu|衣:yi|衤:yi|补:bu|衦:gan|衧:yu|表:biao|衩:cha|衪:yi|衫:shan|衬:chen|衭:fu|衮:gun|衯:fen|衰:shuai|衱:jie|衲:na|衳:zhong|衴:dan|衵:yi|衶:zhong|衷:zhong|衸:jie|衹:zhi|衺:xie|衻:ran|衼:zhi|衽:ren|衾:qin|衿:jin|袀:jun|袁:yuan|袂:mei|袃:chai|袄:ao|袅:niao|袆:hui|袇:ran|袈:jia|袉:tuo|袊:ling|袋:dai|袌:bao|袍:pao|袎:yao|袏:zuo|袐:bi|袑:shao|袒:tan|袓:ju|袔:he|袕:xue|袖:xiu|袗:zhen|袘:yi|袙:pa|袚:bo|袛:di|袜:wa|袝:fu|袞:gun|袟:zhi|袠:zhi|袡:ran|袢:pan|袣:yi|袤:mao|袥:tuo|袦:na|袧:gou|袨:xuan|袩:zhe|袪:qu|被:bei|袬:yu|袭:xi|袮:mi|袯:bo|袰:bo|袱:fu|袲:chi|袳:chi|袴:ku|袵:ren|袶:jiang|袷:jia|袸:jian|袹:mo|袺:jie|袻:er|袼:ge|袽:ru|袾:zhu|袿:gui|裀:yin|裁:cai|裂:lie|裃:ka|裄:xing|装:zhuang|裆:dang|裇:se|裈:kun|裉:ken|裊:niao|裋:shu|裌:jia|裍:kun|裎:cheng|裏:li|裐:juan|裑:shen|裒:pou|裓:ge|裔:yi|裕:yu|裖:zhen|裗:liu|裘:qiu|裙:qun|裚:ji|裛:yi|補:bu|裝:zhuang|裞:shui|裟:sha|裠:qun|裡:li|裢:lian|裣:lian|裤:ku|裥:jian|裦:xiu|裧:chan|裨:bi|裩:kun|裪:tao|裫:yuan|裬:ling|裭:chi|裮:chang|裯:chou|裰:duo|裱:biao|裲:liang|裳:shang|裴:pei|裵:pei|裶:fei|裷:gun|裸:luo|裹:guo|裺:yan|裻:du|裼:ti|製:zhi|裾:ju|裿:qi|褀:qi|褁:guo|褂:gua|褃:ken|褄:qi|褅:ti|褆:shi|複:fu|褈:chong|褉:xie|褊:bian|褋:die|褌:kun|褍:duan|褎:xiu|褏:xiu|褐:he|褑:yuan|褒:bao|褓:bao|褔:fu|褕:yu|褖:tuan|褗:yan|褘:hui|褙:bei|褚:chu|褛:lv|褜:pao|褝:dan|褞:yun|褟:ta|褠:gou|褡:da|褢:huai|褣:rong|褤:yuan|褥:ru|褦:nai|褧:jiong|褨:cha|褩:ban|褪:tui|褫:chi|褬:sang|褭:niao|褮:ying|褯:jie|褰:qian|褱:huai|褲:ku|褳:lian|褴:lan|褵:li|褶:zhe|褷:shi|褸:lv|褹:yi|褺:die|褻:xie|褼:xian|褽:wei|褾:biao|褿:cao|襀:ji|襁:qiang|襂:se|襃:bao|襄:xiang|襅:bi|襆:fu|襇:jian|襈:zhuan|襉:jian|襊:cui|襋:ji|襌:dan|襍:za|襎:fan|襏:bo|襐:xiang|襑:xun|襒:bie|襓:rao|襔:man|襕:lan|襖:ao|襗:ze|襘:gui|襙:cao|襚:sui|襛:nong|襜:chan|襝:lian|襞:bi|襟:jin|襠:dang|襡:shu|襢:tan|襣:bi|襤:lan|襥:fu|襦:ru|襧:zhi|襨:ta|襩:shu|襪:wa|襫:shi|襬:bai|襭:xie|襮:bo|襯:chen|襰:lai|襱:long|襲:xi|襳:xian|襴:lan|襵:zhe|襶:dai|襷:ju|襸:zan|襹:shi|襺:jian|襻:pan|襼:yi|襽:lan|襾:ya|西:xi|覀:xi|要:yao|覂:feng|覃:tan|覄:fu|覅:fiao|覆:fu|覇:ba|覈:he|覉:ji|覊:ji|見:jian|覌:guan|覍:bian|覎:yan|規:gui|覐:jue|覑:pian|覒:mao|覓:mi|覔:mi|覕:mie|視:shi|覗:si|覘:chan|覙:zhen|覚:jue|覛:mi|覜:tiao|覝:lian|覞:yao|覟:zhi|覠:jun|覡:xi|覢:shan|覣:wei|覤:xi|覥:tian|覦:yu|覧:lan|覨:e|覩:du|親:qin|覫:pang|覬:ji|覭:ming|覮:ying|覯:gou|覰:qu|覱:zhan|覲:jin|観:guan|覴:deng|覵:jian|覶:luo|覷:qu|覸:jian|覹:wei|覺:jue|覻:qu|覼:luo|覽:lan|覾:shen|覿:di|觀:guan|见:jian|观:guan|觃:yan|规:gui|觅:mi|视:shi|觇:chan|览:lan|觉:jue|觊:ji|觋:xi|觌:di|觍:tian|觎:yu|觏:gou|觐:jin|觑:qu|角:jiao|觓:qiu|觔:jin|觕:cu|觖:jue|觗:zhi|觘:chao|觙:ji|觚:gu|觛:dan|觜:zi|觝:di|觞:shang|觟:hua|觠:quan|觡:ge|觢:shi|解:jie|觤:gui|觥:gong|触:chu|觧:jie|觨:hun|觩:qiu|觪:xing|觫:su|觬:ni|觭:ji|觮:lu|觯:zhi|觰:zha|觱:bi|觲:xing|觳:hu|觴:shang|觵:gong|觶:zhi|觷:xue|觸:chu|觹:xi|觺:yi|觻:lu|觼:jue|觽:xi|觾:yan|觿:xi|言:yan|訁:yan|訂:ding|訃:fu|訄:qiu|訅:qiu|訆:jiao|訇:hong|計:ji|訉:fan|訊:xun|訋:diao|訌:hong|訍:cha|討:tao|訏:xu|訐:jie|訑:dan|訒:ren|訓:xun|訔:yin|訕:shan|訖:qi|託:tuo|記:ji|訙:xun|訚:yin|訛:e|訜:fen|訝:ya|訞:yao|訟:song|訠:shen|訡:yin|訢:xin|訣:jue|訤:xiao|訥:ne|訦:chen|訧:you|訨:zhi|訩:xiong|訪:fang|訫:xin|訬:chao|設:she|訮:yan|訯:sa|訰:zhun|許:xu|訲:yi|訳:yi|訴:su|訵:chi|訶:he|訷:shen|訸:he|訹:xu|診:zhen|註:zhu|証:zheng|訽:gou|訾:zi|訿:zi|詀:zhan|詁:gu|詂:fu|詃:jian|詄:die|詅:ling|詆:di|詇:yang|詈:li|詉:nao|詊:pan|詋:zhou|詌:gan|詍:yi|詎:ju|詏:yao|詐:zha|詑:tuo|詒:yi|詓:qu|詔:zhao|評:ping|詖:bi|詗:xiong|詘:qu|詙:ba|詚:da|詛:zu|詜:tao|詝:zhu|詞:ci|詟:zhe|詠:yong|詡:xu|詢:xun|詣:yi|詤:huang|詥:he|試:shi|詧:cha|詨:xiao|詩:shi|詪:hen|詫:cha|詬:gou|詭:gui|詮:quan|詯:hui|詰:jie|話:hua|該:gai|詳:xiang|詴:wei|詵:shen|詶:chou|詷:dong|詸:mi|詹:zhan|詺:ming|詻:e|詼:hui|詽:yan|詾:xiong|詿:gua|誀:er|誁:bing|誂:tiao|誃:yi|誄:lei|誅:zhu|誆:kuang|誇:kua|誈:wu|誉:yu|誊:teng|誋:ji|誌:zhi|認:ren|誎:cu|誏:lang|誐:e|誑:kuang|誒:xi|誓:shi|誔:ting|誕:dan|誖:bei|誗:chan|誘:you|誙:keng|誚:qiao|誛:qin|誜:shua|誝:an|語:yu|誟:xiao|誠:cheng|誡:jie|誢:xian|誣:wu|誤:wu|誥:gao|誦:song|誧:bu|誨:hui|誩:jing|說:shui|誫:zhen|説:shui|読:du|誮:hua|誯:chang|誰:shui|誱:jie|課:ke|誳:qu|誴:cong|誵:xiao|誶:sui|誷:wang|誸:xian|誹:fei|誺:chi|誻:ta|誼:yi|誽:na|誾:yin|調:diao|諀:pi|諁:zhuo|諂:chan|諃:chen|諄:zhun|諅:ji|諆:qi|談:tan|諈:zhui|諉:wei|諊:ju|請:qing|諌:dong|諍:zheng|諎:ze|諏:zou|諐:qian|諑:zhuo|諒:liang|諓:jian|諔:chu|諕:xia|論:lun|諗:shen|諘:biao|諙:hua|諚:pian|諛:yu|諜:die|諝:xu|諞:pian|諟:shi|諠:xuan|諡:shi|諢:hun|諣:hua|諤:e|諥:zhong|諦:di|諧:xie|諨:fu|諩:pu|諪:ting|諫:jian|諬:qi|諭:yu|諮:zi|諯:zhuan|諰:xi|諱:hui|諲:yin|諳:an|諴:xian|諵:nan|諶:chen|諷:feng|諸:zhu|諹:yang|諺:yan|諻:huang|諼:xuan|諽:ge|諾:nuo|諿:qi|謀:mou|謁:ye|謂:wei|謃:xing|謄:teng|謅:zhou|謆:shan|謇:jian|謈:po|謉:kui|謊:huang|謋:huo|謌:ge|謍:ying|謎:mi|謏:xiao|謐:mi|謑:xi|謒:qiang|謓:chen|謔:xue|謕:ti|謖:su|謗:bang|謘:chi|謙:qian|謚:shi|講:jiang|謜:yuan|謝:xie|謞:xiao|謟:tao|謠:yao|謡:yao|謢:zhi|謣:yu|謤:biao|謥:cong|謦:qing|謧:li|謨:mo|謩:mo|謪:shang|謫:zhe|謬:miu|謭:jian|謮:ze|謯:zu|謰:lian|謱:lou|謲:can|謳:ou|謴:gun|謵:xi|謶:zhuo|謷:ao|謸:ao|謹:jin|謺:zhe|謻:yi|謼:hu|謽:jiang|謾:man|謿:chao|譀:han|譁:hua|譂:chan|譃:xu|譄:zeng|譅:se|譆:xi|譇:zha|譈:dui|證:zheng|譊:nao|譋:lan|譌:e|譍:ying|譎:jue|譏:ji|譐:zun|譑:jiao|譒:bo|譓:hui|譔:zhuan|譕:mo|譖:zen|譗:zha|識:shi|譙:qiao|譚:tan|譛:zen|譜:pu|譝:sheng|譞:xuan|譟:zao|譠:tan|譡:dang|譢:sui|譣:xian|譤:ji|譥:jiao|警:jing|譧:lian|譨:nou|譩:yi|譪:ai|譫:zhan|譬:pi|譭:hui|譮:hui|譯:yi|議:yi|譱:shan|譲:rang|譳:nou|譴:qian|譵:dui|譶:ta|護:hu|譸:zhou|譹:hao|譺:ai|譻:ying|譼:jian|譽:yu|譾:jian|譿:hui|讀:du|讁:zhe|讂:xuan|讃:zan|讄:lei|讅:shen|讆:wei|讇:chan|讈:li|讉:yi|變:bian|讋:zhe|讌:yan|讍:e|讎:chou|讏:wei|讐:chou|讑:yao|讒:chan|讓:rang|讔:yin|讕:lan|讖:chen|讗:xie|讘:nie|讙:huan|讚:zan|讛:yi|讜:dang|讝:zhan|讞:yan|讟:du|讠:yan|计:ji|订:ding|讣:fu|认:ren|讥:ji|讦:jie|讧:hong|讨:tao|让:rang|讪:shan|讫:qi|讬:tuo|训:xun|议:yi|讯:xun|记:ji|讱:ren|讲:jiang|讳:hui|讴:ou|讵:ju|讶:ya|讷:ne|许:xu|讹:e|论:lun|讻:xiong|讼:song|讽:feng|设:she|访:fang|诀:jue|证:zheng|诂:gu|诃:he|评:ping|诅:zu|识:shi|诇:xiong|诈:zha|诉:su|诊:zhen|诋:di|诌:zhou|词:ci|诎:qu|诏:zhao|诐:bi|译:yi|诒:yi|诓:kuang|诔:lei|试:shi|诖:gua|诗:shi|诘:jie|诙:hui|诚:cheng|诛:zhu|诜:shen|话:hua|诞:dan|诟:gou|诠:quan|诡:gui|询:xun|诣:yi|诤:zheng|该:gai|详:xiang|诧:cha|诨:hun|诩:xu|诪:zhou|诫:jie|诬:wu|语:yu|诮:qiao|误:wu|诰:gao|诱:you|诲:hui|诳:kuang|说:shuo|诵:song|诶:xi|请:qing|诸:zhu|诹:zou|诺:nuo|读:du|诼:zhuo|诽:fei|课:ke|诿:wei|谀:yu|谁:shui|谂:shen|调:diao|谄:chan|谅:liang|谆:zhun|谇:sui|谈:tan|谉:shen|谊:yi|谋:mou|谌:chen|谍:die|谎:huang|谏:jian|谐:xie|谑:xue|谒:ye|谓:wei|谔:e|谕:yu|谖:xuan|谗:chan|谘:zi|谙:an|谚:yan|谛:di|谜:mi|谝:pian|谞:xu|谟:mo|谠:dang|谡:su|谢:xie|谣:yao|谤:bang|谥:shi|谦:qian|谧:mi|谨:jin|谩:man|谪:zhe|谫:jian|谬:miu|谭:tan|谮:zen|谯:qiao|谰:lan|谱:pu|谲:jue|谳:yan|谴:qian|谵:zhan|谶:chen|谷:gu|谸:qian|谹:hong|谺:xia|谻:ji|谼:hong|谽:han|谾:hong|谿:xi|豀:xi|豁:huo|豂:liao|豃:han|豄:du|豅:long|豆:dou|豇:jiang|豈:qi|豉:chi|豊:li|豋:deng|豌:wan|豍:bi|豎:shu|豏:xian|豐:feng|豑:zhi|豒:zhi|豓:yan|豔:yan|豕:shi|豖:chu|豗:hui|豘:tun|豙:yi|豚:tun|豛:yi|豜:jian|豝:ba|豞:hou|豟:e|豠:chu|象:xiang|豢:huan|豣:jian|豤:ken|豥:gai|豦:ju|豧:fu|豨:xi|豩:bin|豪:hao|豫:yu|豬:zhu|豭:jia|豮:fen|豯:xi|豰:bo|豱:wen|豲:huan|豳:bin|豴:di|豵:zong|豶:fen|豷:yi|豸:zhi|豹:bao|豺:chai|豻:an|豼:pi|豽:na|豾:pi|豿:gou|貀:na|貁:you|貂:diao|貃:mo|貄:si|貅:xiu|貆:huan|貇:ken|貈:he|貉:he|貊:mo|貋:an|貌:mao|貍:li|貎:ni|貏:bi|貐:yu|貑:jia|貒:tuan|貓:mao|貔:pi|貕:xi|貖:yi|貗:ju|貘:mo|貙:chu|貚:tan|貛:huan|貜:jue|貝:bei|貞:zhen|貟:yuan|負:fu|財:cai|貢:gong|貣:te|貤:yi|貥:hang|貦:wan|貧:pin|貨:huo|販:fan|貪:tan|貫:guan|責:ze|貭:zhi|貮:er|貯:zhu|貰:shi|貱:bi|貲:zi|貳:er|貴:gui|貵:pian|貶:bian|買:mai|貸:dai|貹:sheng|貺:kuang|費:fei|貼:tie|貽:yi|貾:chi|貿:mao|賀:he|賁:ben|賂:lu|賃:lin|賄:hui|賅:gai|賆:pian|資:zi|賈:jia|賉:xu|賊:zei|賋:jiao|賌:gai|賍:zang|賎:jian|賏:ying|賐:xun|賑:zhen|賒:she|賓:bin|賔:bin|賕:qiu|賖:she|賗:chuan|賘:zang|賙:zhou|賚:lai|賛:zan|賜:ci|賝:chen|賞:shang|賟:tian|賠:pei|賡:geng|賢:xian|賣:mai|賤:jian|賥:sui|賦:fu|賧:tan|賨:cong|賩:cong|質:zhi|賫:ji|賬:zhang|賭:du|賮:jin|賯:min|賰:chun|賱:yun|賲:bao|賳:zai|賴:lai|賵:feng|賶:cang|賷:ji|賸:sheng|賹:ai|賺:zhuan|賻:fu|購:gou|賽:sai|賾:ze|賿:liao|贀:yi|贁:bai|贂:chen|贃:wan|贄:zhi|贅:zhui|贆:biao|贇:yun|贈:zeng|贉:dan|贊:zan|贋:yan|贌:pu|贍:shan|贎:wan|贏:ying|贐:jin|贑:gong|贒:xian|贓:zang|贔:bi|贕:du|贖:shu|贗:yan|贘:yan|贙:xuan|贚:long|贛:gan|贜:zang|贝:bei|贞:zhen|负:fu|贠:yuan|贡:gong|财:cai|责:ze|贤:xian|败:bai|账:zhang|货:huo|质:zhi|贩:fan|贪:tan|贫:pin|贬:bian|购:gou|贮:zhu|贯:guan|贰:er|贱:jian|贲:ben|贳:shi|贴:tie|贵:gui|贶:kuang|贷:dai|贸:mao|费:fei|贺:he|贻:yi|贼:zei|贽:zhi|贾:jia|贿:hui|赀:zi|赁:lin|赂:lu|赃:zang|资:zi|赅:gai|赆:jin|赇:qiu|赈:zhen|赉:lai|赊:she|赋:fu|赌:du|赍:ji|赎:shu|赏:shang|赐:ci|赑:bi|赒:zhou|赓:geng|赔:pei|赕:tan|赖:lai|赗:feng|赘:zhui|赙:fu|赚:zhuan|赛:sai|赜:ze|赝:yan|赞:zan|赟:yun|赠:zeng|赡:shan|赢:ying|赣:gan|赤:chi|赥:xi|赦:she|赧:nan|赨:tong|赩:xi|赪:cheng|赫:he|赬:cheng|赭:zhe|赮:xia|赯:tang|走:zou|赱:zou|赲:li|赳:jiu|赴:fu|赵:zhao|赶:gan|起:qi|赸:shan|赹:qiong|赺:qin|赻:xian|赼:zi|赽:jue|赾:qin|赿:chi|趀:ci|趁:chen|趂:chen|趃:die|趄:ju|超:chao|趆:di|趇:xi|趈:zhan|趉:jue|越:yue|趋:qu|趌:ji|趍:chi|趎:chu|趏:gua|趐:xue|趑:zi|趒:tiao|趓:duo|趔:lie|趕:gan|趖:suo|趗:cu|趘:xi|趙:zhao|趚:su|趛:yin|趜:ju|趝:jian|趞:que|趟:tang|趠:chuo|趡:cui|趢:lu|趣:qu|趤:dang|趥:qiu|趦:zi|趧:ti|趨:qu|趩:chi|趪:huang|趫:qiao|趬:qiao|趭:jiao|趮:zao|趯:ti|趰:er|趱:zan|趲:zan|足:zu|趴:pa|趵:bao|趶:wu|趷:ke|趸:dun|趹:jue|趺:fu|趻:chen|趼:jian|趽:fang|趾:zhi|趿:ta|跀:yue|跁:ba|跂:qi|跃:yue|跄:qiang|跅:tuo|跆:tai|跇:yi|跈:jian|跉:ling|跊:mei|跋:ba|跌:die|跍:ku|跎:tuo|跏:jia|跐:ci|跑:pao|跒:qia|跓:zhu|跔:ju|跕:tie|跖:zhi|跗:fu|跘:pan|跙:ju|跚:shan|跛:bo|跜:ni|距:ju|跞:li|跟:gen|跠:yi|跡:ji|跢:duo|跣:xian|跤:jiao|跥:duo|跦:zhu|跧:quan|跨:kua|跩:zhuai|跪:gui|跫:qiong|跬:kui|跭:xiang|跮:chi|路:lu|跰:beng|跱:zhi|跲:jia|跳:tiao|跴:cai|践:jian|跶:da|跷:qiao|跸:bi|跹:xian|跺:duo|跻:ji|跼:ju|跽:ji|跾:shu|跿:tu|踀:chu|踁:jing|踂:nie|踃:xiao|踄:bu|踅:xue|踆:cun|踇:mu|踈:shu|踉:liang|踊:yong|踋:jiao|踌:chou|踍:qiao|踎:mi|踏:ta|踐:jian|踑:qi|踒:wo|踓:wei|踔:chuo|踕:jie|踖:ji|踗:nie|踘:ju|踙:nie|踚:lun|踛:lu|踜:leng|踝:huai|踞:ju|踟:chi|踠:wan|踡:quan|踢:ti|踣:bo|踤:zu|踥:qie|踦:qi|踧:cu|踨:zong|踩:cai|踪:zong|踫:peng|踬:zhi|踭:zheng|踮:dian|踯:zhi|踰:yu|踱:duo|踲:dun|踳:chun|踴:yong|踵:zhong|踶:di|踷:zha|踸:chen|踹:chuai|踺:jian|踻:tuo|踼:tang|踽:ju|踾:fu|踿:zu|蹀:die|蹁:pian|蹂:rou|蹃:nuo|蹄:ti|蹅:cha|蹆:tui|蹇:jian|蹈:dao|蹉:cuo|蹊:qi|蹋:ta|蹌:qiang|蹍:zhan|蹎:dian|蹏:ti|蹐:ji|蹑:nie|蹒:pan|蹓:liu|蹔:zan|蹕:bi|蹖:chong|蹗:lu|蹘:liao|蹙:cu|蹚:tang|蹛:dai|蹜:su|蹝:xi|蹞:kui|蹟:ji|蹠:zhi|蹡:qiang|蹢:zhi|蹣:pan|蹤:zong|蹥:lian|蹦:beng|蹧:zao|蹨:nian|蹩:bie|蹪:tui|蹫:ju|蹬:deng|蹭:ceng|蹮:xian|蹯:fan|蹰:chu|蹱:zhong|蹲:dun|蹳:bo|蹴:cu|蹵:cu|蹶:jue|蹷:jue|蹸:lin|蹹:ta|蹺:qiao|蹻:qiao|蹼:pu|蹽:liao|蹾:dun|蹿:cuan|躀:guan|躁:zao|躂:da|躃:bi|躄:bi|躅:zhu|躆:ju|躇:chu|躈:qiao|躉:dun|躊:chou|躋:ji|躌:wu|躍:yue|躎:nian|躏:lin|躐:lie|躑:zhi|躒:li|躓:zhi|躔:chan|躕:chu|躖:duan|躗:wei|躘:long|躙:lin|躚:xian|躛:wei|躜:zuan|躝:lan|躞:xie|躟:rang|躠:xie|躡:nie|躢:ta|躣:qu|躤:ji|躥:cuan|躦:zuan|躧:xi|躨:kui|躩:jue|躪:lin|身:shen|躬:gong|躭:dan|躮:fen|躯:qu|躰:ti|躱:duo|躲:duo|躳:gong|躴:lang|躵:ren|躶:luo|躷:ai|躸:ji|躹:ju|躺:tang|躻:kong|躼:kong|躽:yan|躾:mei|躿:kang|軀:qu|軁:lou|軂:lao|軃:duo|軄:zhi|軅:yan|軆:ti|軇:dao|軈:ying|軉:yu|車:che|軋:zha|軌:gui|軍:jun|軎:wei|軏:yue|軐:xin|軑:dai|軒:xuan|軓:fan|軔:ren|軕:shan|軖:kuang|軗:shu|軘:tun|軙:chen|軚:dai|軛:e|軜:na|軝:qi|軞:mao|軟:ruan|軠:kuang|軡:qian|転:zhuan|軣:hong|軤:hu|軥:qu|軦:kuang|軧:di|軨:ling|軩:dai|軪:ao|軫:zhen|軬:fan|軭:kuang|軮:yang|軯:peng|軰:bei|軱:gu|軲:gu|軳:pao|軴:zhu|軵:rong|軶:e|軷:ba|軸:zhou|軹:zhi|軺:yao|軻:ke|軼:yi|軽:qing|軾:shi|軿:ping|輀:er|輁:gong|輂:ju|較:jiao|輄:guang|輅:lu|輆:kai|輇:quan|輈:zhou|載:zai|輊:zhi|輋:she|輌:liang|輍:yu|輎:shao|輏:you|輐:huan|輑:qun|輒:zhe|輓:wan|輔:fu|輕:qing|輖:zhou|輗:ni|輘:ling|輙:zhe|輚:zhan|輛:liang|輜:zi|輝:hui|輞:wang|輟:chuo|輠:guo|輡:kan|輢:yi|輣:peng|輤:qian|輥:gun|輦:nian|輧:ping|輨:guan|輩:bei|輪:lun|輫:pai|輬:liang|輭:ruan|輮:rou|輯:ji|輰:yang|輱:xian|輲:chuan|輳:cou|輴:chun|輵:ge|輶:you|輷:hong|輸:shu|輹:fu|輺:zi|輻:fu|輼:wen|輽:ben|輾:zhan|輿:yu|轀:wen|轁:kan|轂:gu|轃:zhen|轄:xia|轅:yuan|轆:lu|轇:jiao|轈:chao|轉:zhuan|轊:wei|轋:hun|轌:xue|轍:zhe|轎:jiao|轏:zhan|轐:bu|轑:lao|轒:fen|轓:fan|轔:lin|轕:ge|轖:se|轗:kan|轘:huan|轙:yi|轚:ji|轛:zhui|轜:er|轝:yu|轞:jian|轟:hong|轠:lei|轡:pei|轢:li|轣:li|轤:lu|轥:lin|车:che|轧:zha|轨:gui|轩:xuan|轪:dai|轫:ren|转:zhuan|轭:e|轮:lun|软:ruan|轰:hong|轱:gu|轲:ke|轳:lu|轴:zhou|轵:zhi|轶:yi|轷:hu|轸:zhen|轹:li|轺:yao|轻:qing|轼:shi|载:zai|轾:zhi|轿:jiao|辀:zhou|辁:quan|辂:lu|较:jiao|辄:zhe|辅:fu|辆:liang|辇:nian|辈:bei|辉:hui|辊:gun|辋:wang|辌:liang|辍:chuo|辎:zi|辏:cou|辐:fu|辑:ji|辒:wen|输:shu|辔:pei|辕:yuan|辖:xia|辗:zhan|辘:lu|辙:zhe|辚:lin|辛:xin|辜:gu|辝:ci|辞:ci|辟:bi|辠:zui|辡:bian|辢:la|辣:la|辤:ci|辥:xue|辦:ban|辧:bian|辨:bian|辩:bian|辪:bian|辫:bian|辬:ban|辭:ci|辮:bian|辯:bian|辰:chen|辱:ru|農:nong|辳:nong|辴:zhen|辵:chuo|辶:chuo|辷:yi|辸:reng|边:bian|辺:fan|辻:shi|込:ru|辽:liao|达:da|辿:chan|迀:gan|迁:qian|迂:you|迃:yu|迄:qi|迅:xun|迆:yi|过:guo|迈:mai|迉:qi|迊:zha|迋:wang|迌:tu|迍:zhun|迎:ying|迏:da|运:yun|近:jin|迒:hang|迓:ya|返:fan|迕:wu|迖:da|迗:e|还:huan|这:zhe|迚:zhong|进:jin|远:yuan|违:wei|连:lian|迟:chi|迠:che|迡:ni|迢:tiao|迣:chi|迤:yi|迥:jiong|迦:jia|迧:chen|迨:dai|迩:er|迪:di|迫:po|迬:zhu|迭:die|迮:ze|迯:tao|述:shu|迱:tuo|迲:qu|迳:jing|迴:hui|迵:dong|迶:you|迷:mi|迸:beng|迹:ji|迺:nai|迻:yi|迼:jie|追:zhui|迾:lie|迿:xun|退:tui|送:song|适:shi|逃:tao|逄:pang|逅:hou|逆:ni|逇:dun|逈:jiong|选:xuan|逊:xun|逋:bu|逌:you|逍:xiao|逎:qiu|透:tou|逐:zhu|逑:qiu|递:di|逓:di|途:tu|逕:jing|逖:ti|逗:dou|逘:yi|這:zhe|通:tong|逛:guang|逜:wu|逝:shi|逞:cheng|速:su|造:zao|逡:qun|逢:feng|連:lian|逤:suo|逥:hui|逦:li|逧:gu|逨:lai|逩:ben|逪:cuo|逫:jue|逬:beng|逭:huan|逮:dai|逯:lu|逰:you|週:zhou|進:jin|逳:yu|逴:chuo|逵:kui|逶:wei|逷:ti|逸:yi|逹:da|逺:yuan|逻:luo|逼:bi|逽:nuo|逾:yu|逿:dang|遀:sui|遁:dun|遂:sui|遃:yan|遄:chuan|遅:chi|遆:ti|遇:yu|遈:shi|遉:zhen|遊:you|運:yun|遌:e|遍:bian|過:guo|遏:e|遐:xia|遑:huang|遒:qiu|道:dao|達:da|違:wei|遖:nan|遗:yi|遘:gou|遙:yao|遚:chou|遛:liu|遜:xun|遝:ta|遞:di|遟:chi|遠:yuan|遡:su|遢:ta|遣:qian|遤:ma|遥:yao|遦:guan|遧:zhang|遨:ao|適:shi|遪:ca|遫:chi|遬:su|遭:zao|遮:zhe|遯:dun|遰:di|遱:lou|遲:chi|遳:cuo|遴:lin|遵:zun|遶:rao|遷:qian|選:xuan|遹:yu|遺:yi|遻:e|遼:liao|遽:ju|遾:shi|避:bi|邀:yao|邁:mai|邂:xie|邃:sui|還:huan|邅:zhan|邆:teng|邇:er|邈:miao|邉:bian|邊:bian|邋:la|邌:li|邍:yuan|邎:you|邏:luo|邐:li|邑:yi|邒:ting|邓:deng|邔:qi|邕:yong|邖:shan|邗:han|邘:yu|邙:mang|邚:ru|邛:qiong|邜:wan|邝:kuang|邞:fu|邟:kang|邠:bin|邡:fang|邢:xing|那:na|邤:xin|邥:shen|邦:bang|邧:yuan|邨:cun|邩:huo|邪:xie|邫:bang|邬:wu|邭:ju|邮:you|邯:han|邰:tai|邱:qiu|邲:bi|邳:pi|邴:bing|邵:shao|邶:bei|邷:wa|邸:di|邹:zou|邺:ye|邻:lin|邼:kuang|邽:gui|邾:zhu|邿:shi|郀:ku|郁:yu|郂:gai|郃:he|郄:xi|郅:zhi|郆:ji|郇:huan|郈:hou|郉:xing|郊:jiao|郋:xi|郌:gui|郍:nuo|郎:lang|郏:jia|郐:kuai|郑:zheng|郒:lang|郓:yun|郔:yan|郕:cheng|郖:dou|郗:xi|郘:lv|郙:fu|郚:wu|郛:fu|郜:gao|郝:hao|郞:lang|郟:jia|郠:geng|郡:jun|郢:ying|郣:bo|郤:xi|郥:qu|郦:li|郧:yun|部:bu|郩:xiao|郪:qi|郫:pi|郬:qing|郭:guo|郮:zhou|郯:tan|郰:zou|郱:ping|郲:lai|郳:ni|郴:chen|郵:you|郶:bu|郷:xiang|郸:dan|郹:ju|郺:yong|郻:qiao|郼:yi|都:du|郾:yan|郿:mei|鄀:ruo|鄁:bei|鄂:e|鄃:shu|鄄:juan|鄅:yu|鄆:yun|鄇:hou|鄈:kui|鄉:xiang|鄊:xiang|鄋:sou|鄌:tang|鄍:ming|鄎:xi|鄏:ru|鄐:chu|鄑:zi|鄒:zou|鄓:ye|鄔:wu|鄕:xiang|鄖:yun|鄗:qiao|鄘:yong|鄙:bi|鄚:mao|鄛:chao|鄜:fu|鄝:liao|鄞:yin|鄟:zhuan|鄠:hu|鄡:qiao|鄢:yan|鄣:zhang|鄤:man|鄥:qiao|鄦:xu|鄧:deng|鄨:bi|鄩:xun|鄪:bi|鄫:zeng|鄬:wei|鄭:zheng|鄮:mao|鄯:shan|鄰:lin|鄱:po|鄲:dan|鄳:meng|鄴:ye|鄵:cao|鄶:kuai|鄷:feng|鄸:meng|鄹:zou|鄺:kuang|鄻:lian|鄼:zan|鄽:chan|鄾:you|鄿:ji|酀:yan|酁:chan|酂:zan|酃:ling|酄:huan|酅:xi|酆:feng|酇:zan|酈:li|酉:you|酊:ding|酋:qiu|酌:zhuo|配:pei|酎:zhou|酏:yi|酐:gan|酑:yu|酒:jiu|酓:yan|酔:zui|酕:mao|酖:dan|酗:xu|酘:dou|酙:zhen|酚:fen|酛:yuan|酜:fu|酝:yun|酞:tai|酟:tian|酠:qia|酡:tuo|酢:cu|酣:han|酤:gu|酥:su|酦:po|酧:chou|酨:zai|酩:ming|酪:lao|酫:chuo|酬:chou|酭:you|酮:tong|酯:zhi|酰:xian|酱:jiang|酲:cheng|酳:yin|酴:tu|酵:jiao|酶:mei|酷:ku|酸:suan|酹:lei|酺:pu|酻:zui|酼:hai|酽:yan|酾:shi|酿:niang|醀:wei|醁:lu|醂:lan|醃:yan|醄:tao|醅:pei|醆:zhan|醇:chun|醈:tan|醉:zui|醊:zhui|醋:cu|醌:kun|醍:ti|醎:xian|醏:du|醐:hu|醑:xu|醒:xing|醓:tan|醔:qiu|醕:chun|醖:yun|醗:fa|醘:ke|醙:sou|醚:mi|醛:quan|醜:chou|醝:cuo|醞:yun|醟:yong|醠:ang|醡:zha|醢:hai|醣:tang|醤:jiang|醥:piao|醦:chan|醧:yu|醨:li|醩:zao|醪:lao|醫:yi|醬:jiang|醭:bu|醮:jiao|醯:xi|醰:tan|醱:po|醲:nong|醳:yi|醴:li|醵:ju|醶:yan|醷:yi|醸:niang|醹:ru|醺:xun|醻:chou|醼:yan|醽:ling|醾:mi|醿:mi|釀:niang|釁:xin|釂:jiao|釃:shi|釄:mi|釅:yan|釆:bian|采:cai|釈:shi|釉:you|释:shi|釋:shi|里:li|重:zhong|野:ye|量:liang|釐:li|金:jin|釒:jin|釓:ga|釔:yi|釕:liao|釖:dao|釗:zhao|釘:ding|釙:po|釚:qiu|釛:ba|釜:fu|針:zhen|釞:zhi|釟:ba|釠:luan|釡:fu|釢:nai|釣:diao|釤:shan|釥:qiao|釦:kou|釧:chuan|釨:zi|釩:fan|釪:yu|釫:wu|釬:han|釭:gang|釮:qi|釯:mang|釰:ri|釱:di|釲:si|釳:xi|釴:yi|釵:cha|釶:shi|釷:tu|釸:xi|釹:nv|釺:qian|釻:qiu|釼:jian|釽:pi|釾:ye|釿:yin|鈀:ba|鈁:fang|鈂:chen|鈃:xing|鈄:dou|鈅:yue|鈆:qian|鈇:fu|鈈:bu|鈉:na|鈊:xin|鈋:e|鈌:jue|鈍:dun|鈎:gou|鈏:yin|鈐:qian|鈑:ban|鈒:sa|鈓:ren|鈔:chao|鈕:niu|鈖:fen|鈗:yun|鈘:yi|鈙:qin|鈚:pi|鈛:guo|鈜:hong|鈝:yin|鈞:jun|鈟:diao|鈠:yi|鈡:zhong|鈢:xi|鈣:gai|鈤:ri|鈥:huo|鈦:tai|鈧:kang|鈨:yuan|鈩:lu|鈪:n|鈫:wen|鈬:duo|鈭:zi|鈮:ni|鈯:tu|鈰:shi|鈱:min|鈲:gu|鈳:ke|鈴:ling|鈵:bing|鈶:ci|鈷:gu|鈸:bo|鈹:pi|鈺:yu|鈻:si|鈼:zuo|鈽:bu|鈾:you|鈿:dian|鉀:jia|鉁:zhen|鉂:shi|鉃:shi|鉄:tie|鉅:ju|鉆:qian|鉇:shi|鉈:ta|鉉:xuan|鉊:zhao|鉋:bao|鉌:he|鉍:bi|鉎:sheng|鉏:chu|鉐:shi|鉑:bo|鉒:zhu|鉓:chi|鉔:za|鉕:po|鉖:tong|鉗:qian|鉘:fu|鉙:zhai|鉚:mao|鉛:qian|鉜:fu|鉝:li|鉞:yue|鉟:pi|鉠:yang|鉡:ban|鉢:bo|鉣:jie|鉤:gou|鉥:shu|鉦:zheng|鉧:mu|鉨:ni|鉩:nie|鉪:di|鉫:jia|鉬:mu|鉭:tan|鉮:shen|鉯:yi|鉰:si|鉱:kuang|鉲:ka|鉳:bei|鉴:jian|鉵:tong|鉶:xing|鉷:hong|鉸:jiao|鉹:chi|鉺:er|鉻:ge|鉼:bing|鉽:shi|鉾:mou|鉿:ha|銀:yin|銁:jun|銂:zhou|銃:chong|銄:xiang|銅:tong|銆:mo|銇:lei|銈:ji|銉:yu|銊:xu|銋:ren|銌:zun|銍:zhi|銎:qiong|銏:shan|銐:chi|銑:xi|銒:xing|銓:quan|銔:pi|銕:tie|銖:zhu|銗:hou|銘:ming|銙:kua|銚:yao|銛:xian|銜:xian|銝:xiu|銞:jun|銟:cha|銠:lao|銡:ji|銢:pi|銣:ru|銤:mi|銥:yi|銦:yin|銧:guang|銨:an|銩:diu|銪:you|銫:se|銬:kao|銭:qian|銮:luan|銯:si|銰:n|銱:diao|銲:han|銳:rui|銴:shi|銵:keng|銶:qiu|銷:xiao|銸:zhe|銹:xiu|銺:zang|銻:ti|銼:cuo|銽:gua|銾:hong|銿:yong|鋀:dou|鋁:lv|鋂:mei|鋃:lang|鋄:wan|鋅:xin|鋆:yun|鋇:bei|鋈:wu|鋉:su|鋊:yu|鋋:chan|鋌:ting|鋍:bo|鋎:han|鋏:jia|鋐:hong|鋑:juan|鋒:feng|鋓:chan|鋔:wan|鋕:zhi|鋖:si|鋗:juan|鋘:hua|鋙:yu|鋚:tiao|鋛:kuang|鋜:zhuo|鋝:lve|鋞:xing|鋟:qin|鋠:shen|鋡:han|鋢:lve|鋣:ye|鋤:chu|鋥:zeng|鋦:ju|鋧:xian|鋨:e|鋩:mang|鋪:pu|鋫:li|鋬:pan|鋭:rui|鋮:cheng|鋯:gao|鋰:li|鋱:te|鋲:bing|鋳:zhu|鋴:zhen|鋵:tu|鋶:liu|鋷:zui|鋸:ju|鋹:chang|鋺:yuan|鋻:jian|鋼:gang|鋽:diao|鋾:tao|鋿:chang|錀:lun|錁:ke|錂:ling|錃:pi|錄:lu|錅:li|錆:qiang|錇:pei|錈:juan|錉:min|錊:zui|錋:peng|錌:an|錍:pi|錎:xian|錏:ya|錐:zhui|錑:lei|錒:a|錓:kong|錔:ta|錕:kun|錖:du|錗:wei|錘:chui|錙:zi|錚:zheng|錛:ben|錜:nie|錝:zong|錞:chun|錟:tan|錠:ding|錡:qi|錢:qian|錣:zhui|錤:ji|錥:yu|錦:jin|錧:guan|錨:mao|錩:chang|錪:tian|錫:xi|錬:lian|錭:tao|錮:gu|錯:cuo|錰:shu|錱:zhen|録:lu|錳:meng|錴:lu|錵:hua|錶:biao|錷:ga|錸:lai|錹:ken|錺:fang|錻:wu|錼:nai|錽:wan|錾:zan|錿:hu|鍀:de|鍁:xian|鍂:xian|鍃:huo|鍄:liang|鍅:fa|鍆:men|鍇:kai|鍈:yang|鍉:di|鍊:lian|鍋:guo|鍌:xian|鍍:du|鍎:tu|鍏:wei|鍐:wan|鍑:fu|鍒:rou|鍓:ji|鍔:e|鍕:jun|鍖:zhen|鍗:ti|鍘:zha|鍙:hu|鍚:yang|鍛:duan|鍜:xia|鍝:yu|鍞:keng|鍟:sheng|鍠:huang|鍡:wei|鍢:fu|鍣:zhao|鍤:cha|鍥:qie|鍦:shi|鍧:hong|鍨:kui|鍩:nuo|鍪:mou|鍫:qiao|鍬:qiao|鍭:hou|鍮:tou|鍯:zong|鍰:huan|鍱:ye|鍲:min|鍳:jian|鍴:duan|鍵:jian|鍶:si|鍷:kui|鍸:hu|鍹:xuan|鍺:zhe|鍻:jie|鍼:zhen|鍽:bian|鍾:zhong|鍿:zi|鎀:xiu|鎁:ye|鎂:mei|鎃:pai|鎄:ai|鎅:gai|鎆:qian|鎇:mei|鎈:cha|鎉:da|鎊:bang|鎋:xia|鎌:lian|鎍:suo|鎎:kai|鎏:liu|鎐:yao|鎑:ye|鎒:nou|鎓:weng|鎔:rong|鎕:tang|鎖:suo|鎗:qiang|鎘:ge|鎙:shuo|鎚:chui|鎛:bo|鎜:pan|鎝:da|鎞:bi|鎟:sang|鎠:gang|鎡:zi|鎢:wu|鎣:ying|鎤:huang|鎥:tiao|鎦:liu|鎧:kai|鎨:sun|鎩:sha|鎪:sou|鎫:wan|鎬:gao|鎭:zhen|鎮:zhen|鎯:lang|鎰:yi|鎱:yuan|鎲:tang|鎳:nie|鎴:xi|鎵:jia|鎶:ge|鎷:ma|鎸:juan|鎹:song|鎺:zu|鎻:suo|鎼:suo|鎽:feng|鎾:wen|鎿:na|鏀:lu|鏁:suo|鏂:ou|鏃:zu|鏄:tuan|鏅:xiu|鏆:guan|鏇:xuan|鏈:lian|鏉:shou|鏊:ao|鏋:man|鏌:mo|鏍:luo|鏎:bi|鏏:wei|鏐:liu|鏑:di|鏒:san|鏓:zong|鏔:yi|鏕:lu|鏖:ao|鏗:keng|鏘:qiang|鏙:cui|鏚:qi|鏛:chang|鏜:tang|鏝:man|鏞:yong|鏟:chan|鏠:feng|鏡:jing|鏢:biao|鏣:shu|鏤:lou|鏥:xiu|鏦:cong|鏧:long|鏨:zan|鏩:jian|鏪:cao|鏫:li|鏬:xia|鏭:xi|鏮:kang|鏯:shuang|鏰:beng|鏱:zhang|鏲:qian|鏳:cheng|鏴:lu|鏵:hua|鏶:ji|鏷:pu|鏸:hui|鏹:qiang|鏺:po|鏻:lin|鏼:se|鏽:xiu|鏾:xian|鏿:cheng|鐀:kui|鐁:si|鐂:liu|鐃:nao|鐄:huang|鐅:pie|鐆:sui|鐇:fan|鐈:qiao|鐉:quan|鐊:yang|鐋:tang|鐌:xiang|鐍:jue|鐎:jiao|鐏:zun|鐐:liao|鐑:qie|鐒:lao|鐓:dui|鐔:xin|鐕:zan|鐖:ji|鐗:jian|鐘:zhong|鐙:deng|鐚:ya|鐛:ying|鐜:dui|鐝:jue|鐞:nou|鐟:ti|鐠:pu|鐡:tie|鐢:tie|鐣:zhang|鐤:ding|鐥:shan|鐦:kai|鐧:jian|鐨:fei|鐩:sui|鐪:lu|鐫:juan|鐬:hui|鐭:yu|鐮:lian|鐯:zhuo|鐰:qiao|鐱:qian|鐲:zhuo|鐳:lei|鐴:bi|鐵:tie|鐶:huan|鐷:xie|鐸:duo|鐹:guo|鐺:cheng|鐻:ju|鐼:fen|鐽:da|鐾:bei|鐿:yi|鑀:ai|鑁:zong|鑂:xun|鑃:diao|鑄:zhu|鑅:heng|鑆:zhui|鑇:ji|鑈:ni|鑉:he|鑊:huo|鑋:qing|鑌:bin|鑍:ying|鑎:kui|鑏:ning|鑐:xu|鑑:jian|鑒:jian|鑓:qian|鑔:cha|鑕:zhi|鑖:mie|鑗:li|鑘:lei|鑙:ji|鑚:zuan|鑛:kuang|鑜:shang|鑝:peng|鑞:la|鑟:du|鑠:shuo|鑡:chuo|鑢:lv|鑣:biao|鑤:bao|鑥:lu|鑦:xian|鑧:kuan|鑨:long|鑩:e|鑪:lu|鑫:xin|鑬:jian|鑭:lan|鑮:bo|鑯:jian|鑰:yao|鑱:chan|鑲:xiang|鑳:jian|鑴:xi|鑵:guan|鑶:cang|鑷:nie|鑸:lei|鑹:cuan|鑺:qu|鑻:pan|鑼:luo|鑽:zuan|鑾:luan|鑿:zao|钀:nie|钁:jue|钂:tang|钃:zhu|钄:lan|钅:jin|钆:ga|钇:yi|针:zhen|钉:ding|钊:zhao|钋:po|钌:liao|钍:tu|钎:qian|钏:chuan|钐:shan|钑:sa|钒:fan|钓:diao|钔:men|钕:nv|钖:yang|钗:cha|钘:xing|钙:gai|钚:bu|钛:tai|钜:ju|钝:dun|钞:chao|钟:zhong|钠:na|钡:bei|钢:gang|钣:ban|钤:qian|钥:yue|钦:qin|钧:jun|钨:wu|钩:gou|钪:kang|钫:fang|钬:huo|钭:dou|钮:niu|钯:ba|钰:yu|钱:qian|钲:zheng|钳:qian|钴:gu|钵:bo|钶:ke|钷:po|钸:bu|钹:bo|钺:yue|钻:zuan|钼:mu|钽:tan|钾:jia|钿:dian|铀:you|铁:tie|铂:bo|铃:ling|铄:shuo|铅:qian|铆:mao|铇:bao|铈:shi|铉:xuan|铊:ta|铋:bi|铌:ni|铍:pi|铎:duo|铏:xing|铐:kao|铑:lao|铒:er|铓:mang|铔:ya|铕:you|铖:cheng|铗:jia|铘:ye|铙:nao|铚:zhi|铛:cheng|铜:tong|铝:lv|铞:diao|铟:yin|铠:kai|铡:zha|铢:zhu|铣:xi|铤:ting|铥:diu|铦:xian|铧:hua|铨:quan|铩:sha|铪:ha|铫:tiao|铬:ge|铭:ming|铮:zheng|铯:se|铰:jiao|铱:yi|铲:chan|铳:chong|铴:tang|铵:an|银:yin|铷:ru|铸:zhu|铹:lao|铺:pu|铻:yu|铼:lai|铽:te|链:lian|铿:keng|销:xiao|锁:suo|锂:li|锃:zeng|锄:chu|锅:guo|锆:gao|锇:e|锈:xiu|锉:cuo|锊:lve|锋:feng|锌:xin|锍:liu|锎:kai|锏:jian|锐:rui|锑:ti|锒:lang|锓:qin|锔:ju|锕:a|锖:qiang|锗:zhe|锘:nuo|错:cuo|锚:mao|锛:ben|锜:qi|锝:de|锞:ke|锟:kun|锠:chang|锡:xi|锢:gu|锣:luo|锤:chui|锥:zhui|锦:jin|锧:zhi|锨:xian|锩:juan|锪:huo|锫:pei|锬:tan|锭:ding|键:jian|锯:ju|锰:meng|锱:zi|锲:qie|锳:ying|锴:kai|锵:qiang|锶:si|锷:e|锸:cha|锹:qiao|锺:zhong|锻:duan|锼:sou|锽:huang|锾:huan|锿:ai|镀:du|镁:mei|镂:lou|镃:zi|镄:fei|镅:mei|镆:mo|镇:zhen|镈:bo|镉:ge|镊:nie|镋:tang|镌:juan|镍:nie|镎:na|镏:liu|镐:gao|镑:bang|镒:yi|镓:jia|镔:bin|镕:rong|镖:biao|镗:tang|镘:man|镙:luo|镚:beng|镛:yong|镜:jing|镝:di|镞:zu|镟:xuan|镠:liu|镡:chan|镢:jue|镣:liao|镤:pu|镥:lu|镦:dui|镧:lan|镨:pu|镩:chuan|镪:qiang|镫:deng|镬:huo|镭:lei|镮:huan|镯:zhuo|镰:lian|镱:yi|镲:cha|镳:biao|镴:la|镵:chan|镶:xiang|長:chang|镸:chang|镹:jiu|镺:ao|镻:die|镼:jue|镽:liao|镾:mi|长:chang|門:men|閁:ma|閂:shuan|閃:shan|閄:huo|閅:men|閆:yan|閇:bi|閈:han|閉:bi|閊:shan|開:kai|閌:kang|閍:beng|閎:hong|閏:run|閐:san|閑:xian|閒:jian|間:jian|閔:min|閕:xia|閖:shui|閗:dou|閘:zha|閙:nao|閚:zhan|閛:peng|閜:xia|閝:ling|閞:bian|閟:bi|閠:run|閡:he|関:guan|閣:ge|閤:ge|閥:fa|閦:chu|閧:hong|閨:gui|閩:min|閪:se|閫:kun|閬:lang|閭:lv|閮:ting|閯:sha|閰:ju|閱:yue|閲:yue|閳:chan|閴:qu|閵:lin|閶:chang|閷:sha|閸:kun|閹:yan|閺:wen|閻:yan|閼:e|閽:hun|閾:yu|閿:wen|闀:hong|闁:bao|闂:hong|闃:qu|闄:yao|闅:wen|闆:ban|闇:an|闈:wei|闉:yin|闊:kuo|闋:que|闌:lan|闍:du|闎:quan|闏:peng|闐:tian|闑:nie|闒:ta|闓:kai|闔:he|闕:que|闖:chuang|闗:guan|闘:dou|闙:qi|闚:kui|闛:tang|關:guan|闝:piao|闞:kan|闟:xi|闠:hui|闡:chan|闢:pi|闣:dang|闤:huan|闥:ta|闦:wen|闧:wen|门:men|闩:shuan|闪:shan|闫:yan|闬:han|闭:bi|问:wen|闯:chuang|闰:run|闱:wei|闲:xian|闳:hong|间:jian|闵:min|闶:kang|闷:men|闸:zha|闹:nao|闺:gui|闻:wen|闼:ta|闽:min|闾:lv|闿:kai|阀:fa|阁:ge|阂:he|阃:kun|阄:jiu|阅:yue|阆:lang|阇:du|阈:yu|阉:yan|阊:chang|阋:xi|阌:wen|阍:hun|阎:yan|阏:e|阐:chan|阑:lan|阒:qu|阓:hui|阔:kuo|阕:que|阖:he|阗:tian|阘:ta|阙:que|阚:kan|阛:huan|阜:fu|阝:fu|阞:le|队:dui|阠:xin|阡:qian|阢:wu|阣:yi|阤:zhi|阥:yin|阦:yang|阧:dou|阨:e|阩:sheng|阪:ban|阫:pei|阬:gang|阭:yun|阮:ruan|阯:zhi|阰:pi|阱:jing|防:fang|阳:yang|阴:yin|阵:zhen|阶:jie|阷:cheng|阸:e|阹:qu|阺:di|阻:zu|阼:zuo|阽:dian|阾:ling|阿:a|陀:tuo|陁:tuo|陂:bei|陃:bing|附:fu|际:ji|陆:lu|陇:long|陈:chen|陉:xing|陊:duo|陋:lou|陌:mo|降:jiang|陎:shu|陏:duo|限:xian|陑:er|陒:gui|陓:yu|陔:gai|陕:shan|陖:jun|陗:qiao|陘:xing|陙:chun|陚:fu|陛:bi|陜:xia|陝:shan|陞:sheng|陟:zhi|陠:pu|陡:dou|院:yuan|陣:zhen|除:chu|陥:xian|陦:dao|陧:nie|陨:yun|险:xian|陪:pei|陫:pei|陬:zou|陭:yi|陮:dui|陯:lun|陰:yin|陱:ju|陲:chui|陳:chen|陴:pi|陵:ling|陶:tao|陷:xian|陸:lu|陹:sheng|険:xian|陻:yin|陼:zhu|陽:yang|陾:reng|陿:xia|隀:chong|隁:yan|隂:yin|隃:yu|隄:di|隅:yu|隆:long|隇:wei|隈:wei|隉:nie|隊:dui|隋:sui|隌:an|隍:huang|階:jie|随:sui|隐:yin|隑:gai|隒:yan|隓:hui|隔:ge|隕:yun|隖:wu|隗:wei|隘:ai|隙:xi|隚:tang|際:ji|障:zhang|隝:dao|隞:ao|隟:xi|隠:yin|隡:sa|隢:rao|隣:lin|隤:tui|隥:deng|隦:pi|隧:sui|隨:sui|隩:yu|險:xian|隫:fen|隬:ni|隭:er|隮:ji|隯:dao|隰:xi|隱:yin|隲:zhi|隳:hui|隴:long|隵:xi|隶:li|隷:li|隸:li|隹:zhui|隺:he|隻:zhi|隼:sun|隽:juan|难:nan|隿:yi|雀:que|雁:yan|雂:qin|雃:qian|雄:xiong|雅:ya|集:ji|雇:gu|雈:huan|雉:zhi|雊:gou|雋:juan|雌:ci|雍:yong|雎:ju|雏:chu|雐:hu|雑:za|雒:luo|雓:yu|雔:chou|雕:diao|雖:sui|雗:han|雘:huo|雙:shuang|雚:guan|雛:chu|雜:za|雝:yong|雞:ji|雟:xi|雠:chou|雡:liu|離:li|難:nan|雤:xue|雥:za|雦:ji|雧:ji|雨:yu|雩:yu|雪:xue|雫:na|雬:fou|雭:se|雮:mu|雯:wen|雰:fen|雱:pang|雲:yun|雳:li|雴:chi|雵:yang|零:ling|雷:lei|雸:an|雹:bao|雺:meng|電:dian|雼:dang|雽:hu|雾:wu|雿:diao|需:xu|霁:ji|霂:mu|霃:chen|霄:xiao|霅:zha|霆:ting|震:zhen|霈:pei|霉:mei|霊:ling|霋:qi|霌:zhou|霍:huo|霎:sha|霏:fei|霐:hong|霑:zhan|霒:yin|霓:ni|霔:zhu|霕:tun|霖:lin|霗:ling|霘:dong|霙:ying|霚:wu|霛:ling|霜:shuang|霝:ling|霞:xia|霟:hong|霠:yin|霡:mai|霢:mai|霣:yun|霤:liu|霥:meng|霦:bin|霧:wu|霨:wei|霩:kuo|霪:yin|霫:xi|霬:yi|霭:ai|霮:dan|霯:teng|霰:xian|霱:yu|露:lu|霳:long|霴:dai|霵:ji|霶:pang|霷:yang|霸:ba|霹:pi|霺:wei|霻:wei|霼:xi|霽:ji|霾:mai|霿:meng|靀:meng|靁:lei|靂:li|靃:huo|靄:ai|靅:fei|靆:dai|靇:long|靈:ling|靉:ai|靊:feng|靋:li|靌:bao|靍:he|靎:he|靏:he|靐:bing|靑:qing|青:qing|靓:jing|靔:tian|靕:zhen|靖:jing|靗:cheng|靘:qing|静:jing|靚:jing|靛:dian|靜:jing|靝:tian|非:fei|靟:fei|靠:kao|靡:mi|面:mian|靣:mian|靤:bao|靥:ye|靦:tian|靧:hui|靨:ye|革:ge|靪:ding|靫:cha|靬:jian|靭:ren|靮:di|靯:du|靰:wu|靱:ren|靲:qin|靳:jin|靴:xue|靵:niu|靶:ba|靷:yin|靸:sa|靹:na|靺:mo|靻:zu|靼:da|靽:ban|靾:yi|靿:yao|鞀:tao|鞁:bai|鞂:jie|鞃:hong|鞄:pao|鞅:yang|鞆:bing|鞇:yin|鞈:ge|鞉:tao|鞊:ji|鞋:xie|鞌:an|鞍:an|鞎:hen|鞏:gong|鞐:qia|鞑:da|鞒:qiao|鞓:ting|鞔:man|鞕:ying|鞖:sui|鞗:tiao|鞘:qiao|鞙:xuan|鞚:kong|鞛:beng|鞜:ta|鞝:zhang|鞞:bi|鞟:kuo|鞠:ju|鞡:la|鞢:xie|鞣:rou|鞤:bang|鞥:eng|鞦:qiu|鞧:qiu|鞨:he|鞩:qiao|鞪:mu|鞫:ju|鞬:jian|鞭:bian|鞮:di|鞯:jian|鞰:ou|鞱:tao|鞲:gou|鞳:ta|鞴:bei|鞵:xie|鞶:pan|鞷:ge|鞸:bi|鞹:kuo|鞺:tang|鞻:lou|鞼:gui|鞽:qiao|鞾:xue|鞿:ji|韀:jian|韁:jiang|韂:chan|韃:da|韄:hu|韅:xian|韆:qian|韇:du|韈:wa|韉:jian|韊:lan|韋:wei|韌:ren|韍:fu|韎:mei|韏:quan|韐:ge|韑:wei|韒:qiao|韓:han|韔:chang|韕:kuo|韖:rou|韗:yun|韘:she|韙:wei|韚:ge|韛:bai|韜:tao|韝:gou|韞:yun|韟:gao|韠:bi|韡:wei|韢:hui|韣:du|韤:wa|韥:du|韦:wei|韧:ren|韨:fu|韩:han|韪:wei|韫:yun|韬:tao|韭:jiu|韮:jiu|韯:xian|韰:xie|韱:xian|韲:ji|音:yin|韴:za|韵:yun|韶:shao|韷:le|韸:peng|韹:huang|韺:ying|韻:yun|韼:peng|韽:an|韾:yin|響:xiang|頀:hu|頁:ye|頂:ding|頃:qing|頄:kui|項:xiang|順:shui|頇:han|須:xu|頉:yi|頊:xu|頋:gu|頌:song|頍:kui|頎:qi|頏:hang|預:yu|頑:wan|頒:ban|頓:dun|頔:di|頕:dan|頖:pan|頗:po|領:ling|頙:che|頚:jing|頛:lei|頜:he|頝:qiao|頞:e|頟:e|頠:wei|頡:jie|頢:kuo|頣:shen|頤:yi|頥:yi|頦:ke|頧:dui|頨:bian|頩:ping|頪:lei|頫:tiao|頬:jia|頭:tou|頮:hui|頯:kui|頰:jia|頱:luo|頲:ting|頳:cheng|頴:ying|頵:yun|頶:hu|頷:han|頸:jing|頹:tui|頺:tui|頻:pin|頼:lai|頽:tui|頾:zi|頿:zi|顀:chui|顁:ding|顂:lai|顃:tan|顄:han|顅:qian|顆:ke|顇:cui|顈:jiong|顉:qin|顊:yi|顋:sai|題:ti|額:e|顎:e|顏:yan|顐:wen|顑:kan|顒:yong|顓:zhuan|顔:yan|顕:xian|顖:xin|顗:yi|願:yuan|顙:sang|顚:dian|顛:dian|顜:jiang|顝:kui|類:lei|顟:lao|顠:piao|顡:wai|顢:man|顣:cu|顤:yao|顥:hao|顦:qiao|顧:gu|顨:xun|顩:yan|顪:hui|顫:chan|顬:ru|顭:meng|顮:bin|顯:xian|顰:pin|顱:lu|顲:lin|顳:nie|顴:quan|页:ye|顶:ding|顷:qing|顸:han|项:xiang|顺:shun|须:xu|顼:xu|顽:wan|顾:gu|顿:dun|颀:qi|颁:ban|颂:song|颃:hang|预:yu|颅:lu|领:ling|颇:po|颈:jing|颉:jie|颊:jia|颋:ting|颌:he|颍:ying|颎:jiong|颏:ke|颐:yi|频:pin|颒:pou|颓:tui|颔:han|颕:ying|颖:ying|颗:ke|题:ti|颙:yong|颚:e|颛:zhuan|颜:yan|额:e|颞:nie|颟:man|颠:dian|颡:sang|颢:hao|颣:lei|颤:chan|颥:ru|颦:pin|颧:quan|風:feng|颩:diu|颪:gua|颫:fu|颬:xia|颭:zhan|颮:biao|颯:sa|颰:ba|颱:tai|颲:lie|颳:gua|颴:xuan|颵:shao|颶:ju|颷:biao|颸:si|颹:wei|颺:yang|颻:yao|颼:sou|颽:kai|颾:sao|颿:fan|飀:liu|飁:xi|飂:liu|飃:piao|飄:piao|飅:liu|飆:biao|飇:biao|飈:biao|飉:liao|飊:biao|飋:se|飌:feng|飍:xiu|风:feng|飏:yang|飐:zhan|飑:biao|飒:sa|飓:ju|飔:si|飕:sou|飖:yao|飗:liu|飘:piao|飙:biao|飚:biao|飛:fei|飜:fan|飝:fei|飞:fei|食:shi|飠:shi|飡:can|飢:ji|飣:ding|飤:si|飥:tuo|飦:zhan|飧:sun|飨:xiang|飩:tun|飪:ren|飫:yu|飬:juan|飭:chi|飮:yin|飯:fan|飰:fan|飱:sun|飲:yin|飳:zhu|飴:yi|飵:zuo|飶:bi|飷:jie|飸:tao|飹:bao|飺:ci|飻:tie|飼:si|飽:bao|飾:shi|飿:duo|餀:hai|餁:ren|餂:tian|餃:jiao|餄:jia|餅:bing|餆:yao|餇:tong|餈:ci|餉:xiang|養:yang|餋:juan|餌:er|餍:yan|餎:le|餏:xi|餐:can|餑:bo|餒:nei|餓:e|餔:bu|餕:jun|餖:dou|餗:su|餘:yu|餙:xi|餚:yao|餛:hun|餜:guo|餝:shi|餞:jian|餟:zhui|餠:bing|餡:xian|餢:bu|餣:ye|餤:dan|餥:fei|餦:zhang|餧:wei|館:guan|餩:e|餪:nuan|餫:yun|餬:hu|餭:huang|餮:tie|餯:hui|餰:jian|餱:hou|餲:ai|餳:tang|餴:fen|餵:wei|餶:gu|餷:cha|餸:song|餹:tang|餺:bo|餻:gao|餼:xi|餽:kui|餾:liu|餿:sou|饀:tao|饁:ye|饂:wen|饃:mo|饄:tang|饅:man|饆:bi|饇:yu|饈:xiu|饉:jin|饊:san|饋:kui|饌:zhuan|饍:shan|饎:chi|饏:dan|饐:yi|饑:ji|饒:rao|饓:cheng|饔:yong|饕:tao|饖:wei|饗:xiang|饘:zhan|饙:fen|饚:hai|饛:meng|饜:yan|饝:mo|饞:chan|饟:xiang|饠:luo|饡:zan|饢:nang|饣:shi|饤:ding|饥:ji|饦:tuo|饧:tang|饨:tun|饩:xi|饪:ren|饫:yu|饬:chi|饭:fan|饮:yin|饯:jian|饰:shi|饱:bao|饲:si|饳:duo|饴:yi|饵:er|饶:rao|饷:xiang|饸:he|饹:le|饺:jiao|饻:xi|饼:bing|饽:bo|饾:dou|饿:e|馀:yu|馁:nei|馂:jun|馃:guo|馄:hun|馅:xian|馆:guan|馇:cha|馈:kui|馉:gu|馊:sou|馋:chan|馌:ye|馍:mo|馎:bo|馏:liu|馐:xiu|馑:jin|馒:man|馓:san|馔:zhuan|馕:nang|首:shou|馗:kui|馘:guo|香:xiang|馚:fen|馛:bo|馜:ni|馝:bi|馞:bo|馟:tu|馠:han|馡:fei|馢:jian|馣:an|馤:ai|馥:fu|馦:xian|馧:yun|馨:xin|馩:fen|馪:pin|馫:xin|馬:ma|馭:yu|馮:feng|馯:han|馰:di|馱:tuo|馲:tuo|馳:chi|馴:xun|馵:zhu|馶:zhi|馷:pei|馸:xin|馹:ri|馺:sa|馻:yun|馼:wen|馽:zhi|馾:dan|馿:lv|駀:you|駁:bo|駂:bao|駃:jue|駄:tuo|駅:yi|駆:qu|駇:pu|駈:qu|駉:jiong|駊:po|駋:zhao|駌:yuan|駍:peng|駎:zhou|駏:ju|駐:zhu|駑:nu|駒:ju|駓:pi|駔:zang|駕:jia|駖:ling|駗:zhen|駘:tai|駙:fu|駚:yang|駛:shi|駜:bi|駝:tuo|駞:tuo|駟:si|駠:liu|駡:ma|駢:pian|駣:tao|駤:zhi|駥:rong|駦:teng|駧:dong|駨:xun|駩:quan|駪:shen|駫:jiong|駬:er|駭:hai|駮:bo|駯:zhu|駰:yin|駱:luo|駲:zhou|駳:dan|駴:hai|駵:liu|駶:ju|駷:song|駸:qin|駹:mang|駺:lang|駻:han|駼:tu|駽:xuan|駾:tui|駿:jun|騀:e|騁:cheng|騂:xing|騃:si|騄:lu|騅:zhui|騆:zhou|騇:she|騈:pian|騉:kun|騊:tao|騋:lai|騌:zong|騍:ke|騎:qi|騏:qi|騐:yan|騑:fei|騒:sao|験:yan|騔:ge|騕:yao|騖:wu|騗:pian|騘:cong|騙:pian|騚:qian|騛:fei|騜:huang|騝:qian|騞:huo|騟:yu|騠:ti|騡:quan|騢:xia|騣:zong|騤:kui|騥:rou|騦:si|騧:gua|騨:tuo|騩:gui|騪:sou|騫:qian|騬:cheng|騭:zhi|騮:liu|騯:peng|騰:teng|騱:xi|騲:cao|騳:du|騴:yan|騵:yuan|騶:zou|騷:sao|騸:shan|騹:qi|騺:zhi|騻:shuang|騼:lu|騽:xi|騾:luo|騿:zhang|驀:mo|驁:ao|驂:can|驃:biao|驄:cong|驅:qu|驆:bi|驇:zhi|驈:yu|驉:xu|驊:hua|驋:bo|驌:su|驍:xiao|驎:lin|驏:zhan|驐:dun|驑:liu|驒:tuo|驓:ceng|驔:dian|驕:jiao|驖:tie|驗:yan|驘:luo|驙:zhan|驚:jing|驛:yi|驜:ye|驝:tuo|驞:pin|驟:zhou|驠:yan|驡:long|驢:lv|驣:teng|驤:xiang|驥:ji|驦:shuang|驧:ju|驨:xi|驩:huan|驪:li|驫:biao|马:ma|驭:yu|驮:tuo|驯:xun|驰:chi|驱:qu|驲:ri|驳:bo|驴:lv|驵:zang|驶:shi|驷:si|驸:fu|驹:ju|驺:zou|驻:zhu|驼:tuo|驽:nu|驾:jia|驿:yi|骀:tai|骁:xiao|骂:ma|骃:yin|骄:jiao|骅:hua|骆:luo|骇:hai|骈:pian|骉:biao|骊:li|骋:cheng|验:yan|骍:xing|骎:qin|骏:jun|骐:qi|骑:qi|骒:ke|骓:zhui|骔:zong|骕:su|骖:can|骗:pian|骘:zhi|骙:kui|骚:sao|骛:wu|骜:ao|骝:liu|骞:qian|骟:shan|骠:biao|骡:luo|骢:cong|骣:zhan|骤:zhou|骥:ji|骦:shuang|骧:xiang|骨:gu|骩:wei|骪:wei|骫:wei|骬:yu|骭:gan|骮:yi|骯:ang|骰:tou|骱:jie|骲:bao|骳:bei|骴:ci|骵:ti|骶:di|骷:ku|骸:hai|骹:qiao|骺:hou|骻:kua|骼:ge|骽:tui|骾:geng|骿:pian|髀:bi|髁:ke|髂:qia|髃:yu|髄:sui|髅:lou|髆:bo|髇:xiao|髈:pang|髉:bo|髊:cuo|髋:kuan|髌:bin|髍:mo|髎:liao|髏:lou|髐:xiao|髑:du|髒:zang|髓:sui|體:ti|髕:bin|髖:kuan|髗:lu|高:gao|髙:gao|髚:qiao|髛:kao|髜:qiao|髝:lao|髞:sao|髟:biao|髠:kun|髡:kun|髢:di|髣:fang|髤:xiu|髥:ran|髦:mao|髧:dan|髨:kun|髩:bin|髪:fa|髫:tiao|髬:pi|髭:zi|髮:fa|髯:ran|髰:ti|髱:bao|髲:bi|髳:mao|髴:fu|髵:er|髶:rong|髷:qu|髸:gong|髹:xiu|髺:kuo|髻:ji|髼:peng|髽:zhua|髾:shao|髿:suo|鬀:ti|鬁:li|鬂:bin|鬃:zong|鬄:ti|鬅:peng|鬆:song|鬇:zheng|鬈:quan|鬉:zong|鬊:shui|鬋:jian|鬌:duo|鬍:hu|鬎:la|鬏:jiu|鬐:qi|鬑:lian|鬒:zhen|鬓:bin|鬔:peng|鬕:ma|鬖:san|鬗:man|鬘:man|鬙:se|鬚:xu|鬛:lie|鬜:qian|鬝:qian|鬞:nang|鬟:huan|鬠:kuo|鬡:ning|鬢:bin|鬣:lie|鬤:rang|鬥:dou|鬦:dou|鬧:nao|鬨:hong|鬩:xi|鬪:dou|鬫:han|鬬:dou|鬭:dou|鬮:jiu|鬯:chang|鬰:yu|鬱:yu|鬲:ge|鬳:yan|鬴:fu|鬵:xin|鬶:gui|鬷:zong|鬸:liu|鬹:gui|鬺:shang|鬻:yu|鬼:gui|鬽:mei|鬾:qi|鬿:qi|魀:ga|魁:kui|魂:hun|魃:ba|魄:po|魅:mei|魆:xu|魇:yan|魈:xiao|魉:liang|魊:yu|魋:tui|魌:qi|魍:wang|魎:liang|魏:wei|魐:gan|魑:chi|魒:piao|魓:bi|魔:mo|魕:qi|魖:xu|魗:chou|魘:yan|魙:zhan|魚:yu|魛:dao|魜:ren|魝:jie|魞:ba|魟:hong|魠:tuo|魡:di|魢:ji|魣:yu|魤:e|魥:qie|魦:sha|魧:hang|魨:tun|魩:mo|魪:jie|魫:shen|魬:ban|魭:yuan|魮:pi|魯:lu|魰:wen|魱:hu|魲:lu|魳:za|魴:fang|魵:fen|魶:na|魷:you|魸:pian|魹:mo|魺:he|魻:xia|魼:qu|魽:han|魾:pi|魿:ling|鮀:tuo|鮁:ba|鮂:qiu|鮃:ping|鮄:fu|鮅:bi|鮆:ji|鮇:wei|鮈:ju|鮉:diao|鮊:bo|鮋:you|鮌:gun|鮍:pi|鮎:nian|鮏:xing|鮐:tai|鮑:bao|鮒:fu|鮓:zha|鮔:ju|鮕:gu|鮖:shi|鮗:dong|鮘:dai|鮙:ta|鮚:jie|鮛:shu|鮜:hou|鮝:xiang|鮞:er|鮟:an|鮠:wei|鮡:zhao|鮢:zhu|鮣:yin|鮤:lie|鮥:luo|鮦:tong|鮧:yi|鮨:yi|鮩:bing|鮪:wei|鮫:jiao|鮬:ku|鮭:gui|鮮:xian|鮯:ge|鮰:hui|鮱:lao|鮲:fu|鮳:kao|鮴:xiu|鮵:duo|鮶:jun|鮷:ti|鮸:mian|鮹:shao|鮺:zha|鮻:suo|鮼:qin|鮽:yu|鮾:nei|鮿:zhe|鯀:gun|鯁:geng|鯂:su|鯃:wu|鯄:qiu|鯅:shan|鯆:pu|鯇:huan|鯈:tiao|鯉:li|鯊:sha|鯋:sha|鯌:kao|鯍:meng|鯎:cheng|鯏:li|鯐:zou|鯑:xi|鯒:yong|鯓:shen|鯔:zi|鯕:qi|鯖:qing|鯗:xiang|鯘:nei|鯙:chun|鯚:ji|鯛:diao|鯜:qie|鯝:gu|鯞:zhou|鯟:dong|鯠:lai|鯡:fei|鯢:ni|鯣:yi|鯤:kun|鯥:lu|鯦:jiu|鯧:chang|鯨:jing|鯩:lun|鯪:ling|鯫:zou|鯬:li|鯭:meng|鯮:zong|鯯:zhi|鯰:nian|鯱:hu|鯲:yu|鯳:di|鯴:shi|鯵:shen|鯶:huan|鯷:ti|鯸:hou|鯹:xing|鯺:zhu|鯻:la|鯼:zong|鯽:ji|鯾:bian|鯿:bian|鰀:huan|鰁:quan|鰂:zei|鰃:wei|鰄:wei|鰅:yu|鰆:chun|鰇:rou|鰈:die|鰉:huang|鰊:lian|鰋:yan|鰌:qiu|鰍:qiu|鰎:jian|鰏:bi|鰐:e|鰑:yang|鰒:fu|鰓:sai|鰔:jian|鰕:xia|鰖:tuo|鰗:hu|鰘:shi|鰙:ruo|鰚:xuan|鰛:wen|鰜:jian|鰝:hao|鰞:wu|鰟:pang|鰠:sao|鰡:liu|鰢:ma|鰣:shi|鰤:shi|鰥:guan|鰦:zi|鰧:teng|鰨:ta|鰩:yao|鰪:ge|鰫:yong|鰬:qian|鰭:qi|鰮:wen|鰯:ruo|鰰:shen|鰱:lian|鰲:ao|鰳:le|鰴:hui|鰵:min|鰶:ji|鰷:tiao|鰸:qu|鰹:jian|鰺:shen|鰻:man|鰼:xi|鰽:qiu|鰾:biao|鰿:ji|鱀:ji|鱁:zhu|鱂:jiang|鱃:qiu|鱄:zhuan|鱅:yong|鱆:zhang|鱇:kang|鱈:xue|鱉:bie|鱊:yu|鱋:qu|鱌:xiang|鱍:bo|鱎:jiao|鱏:xun|鱐:su|鱑:huang|鱒:zun|鱓:shan|鱔:shan|鱕:fan|鱖:gui|鱗:lin|鱘:xun|鱙:miao|鱚:xi|鱛:zeng|鱜:xiang|鱝:fen|鱞:guan|鱟:hou|鱠:kuai|鱡:zei|鱢:sao|鱣:zhan|鱤:gan|鱥:gui|鱦:sheng|鱧:li|鱨:chang|鱩:lei|鱪:shu|鱫:ai|鱬:ru|鱭:ji|鱮:xu|鱯:hu|鱰:shu|鱱:li|鱲:lie|鱳:luo|鱴:mie|鱵:zhen|鱶:xiang|鱷:e|鱸:lu|鱹:guan|鱺:li|鱻:xian|鱼:yu|鱽:dao|鱾:ji|鱿:you|鲀:tun|鲁:lu|鲂:fang|鲃:ba|鲄:he|鲅:ba|鲆:ping|鲇:nian|鲈:lu|鲉:you|鲊:zha|鲋:fu|鲌:bo|鲍:bao|鲎:hou|鲏:pi|鲐:tai|鲑:gui|鲒:jie|鲓:kao|鲔:wei|鲕:er|鲖:tong|鲗:zei|鲘:hou|鲙:kuai|鲚:ji|鲛:jiao|鲜:xian|鲝:zha|鲞:xiang|鲟:xun|鲠:geng|鲡:li|鲢:lian|鲣:jian|鲤:li|鲥:shi|鲦:tiao|鲧:gun|鲨:sha|鲩:huan|鲪:jun|鲫:ji|鲬:yong|鲭:qing|鲮:ling|鲯:qi|鲰:zou|鲱:fei|鲲:kun|鲳:chang|鲴:gu|鲵:ni|鲶:nian|鲷:diao|鲸:jing|鲹:shen|鲺:shi|鲻:zi|鲼:fen|鲽:die|鲾:bi|鲿:chang|鳀:ti|鳁:wen|鳂:wei|鳃:sai|鳄:e|鳅:qiu|鳆:fu|鳇:huang|鳈:quan|鳉:jiang|鳊:bian|鳋:sao|鳌:ao|鳍:qi|鳎:ta|鳏:guan|鳐:yao|鳑:pang|鳒:jian|鳓:le|鳔:biao|鳕:xue|鳖:bie|鳗:man|鳘:min|鳙:yong|鳚:wei|鳛:xi|鳜:gui|鳝:shan|鳞:lin|鳟:zun|鳠:hu|鳡:gan|鳢:li|鳣:zhan|鳤:guan|鳥:niao|鳦:yi|鳧:fu|鳨:li|鳩:jiu|鳪:bu|鳫:yan|鳬:fu|鳭:diao|鳮:ji|鳯:feng|鳰:ru|鳱:gan|鳲:shi|鳳:feng|鳴:ming|鳵:bao|鳶:yuan|鳷:zhi|鳸:hu|鳹:qin|鳺:fu|鳻:fen|鳼:wen|鳽:jian|鳾:shi|鳿:yu|鴀:fou|鴁:yao|鴂:jue|鴃:jue|鴄:pi|鴅:huan|鴆:zhen|鴇:bao|鴈:yan|鴉:ya|鴊:zheng|鴋:fang|鴌:feng|鴍:wen|鴎:ou|鴏:dai|鴐:ge|鴑:ru|鴒:ling|鴓:mie|鴔:fu|鴕:tuo|鴖:min|鴗:li|鴘:bian|鴙:zhi|鴚:ge|鴛:yuan|鴜:ci|鴝:qu|鴞:xiao|鴟:chi|鴠:dan|鴡:ju|鴢:yao|鴣:gu|鴤:zhong|鴥:yu|鴦:yang|鴧:yu|鴨:ya|鴩:tie|鴪:yu|鴫:tian|鴬:ying|鴭:dui|鴮:wu|鴯:er|鴰:gua|鴱:ai|鴲:zhi|鴳:yan|鴴:heng|鴵:xiao|鴶:jia|鴷:lie|鴸:zhu|鴹:yang|鴺:ti|鴻:hong|鴼:lu|鴽:ru|鴾:mou|鴿:ge|鵀:ren|鵁:jiao|鵂:xiu|鵃:zhou|鵄:chi|鵅:luo|鵆:heng|鵇:nian|鵈:e|鵉:luan|鵊:jia|鵋:ji|鵌:tu|鵍:huan|鵎:tuo|鵏:bu|鵐:wu|鵑:juan|鵒:yu|鵓:bo|鵔:jun|鵕:jun|鵖:bi|鵗:xi|鵘:jun|鵙:ju|鵚:tu|鵛:jing|鵜:ti|鵝:e|鵞:e|鵟:kuang|鵠:gu|鵡:wu|鵢:shen|鵣:lai|鵤:jiao|鵥:pan|鵦:lu|鵧:pi|鵨:shu|鵩:fu|鵪:an|鵫:zhuo|鵬:peng|鵭:qiu|鵮:qian|鵯:bei|鵰:diao|鵱:lu|鵲:que|鵳:jian|鵴:ju|鵵:tu|鵶:ya|鵷:yuan|鵸:qi|鵹:li|鵺:ye|鵻:zhui|鵼:kong|鵽:duo|鵾:kun|鵿:sheng|鶀:qi|鶁:jing|鶂:yi|鶃:yi|鶄:jing|鶅:zi|鶆:lai|鶇:dong|鶈:qi|鶉:chun|鶊:geng|鶋:ju|鶌:jue|鶍:yi|鶎:zun|鶏:ji|鶐:shu|鶑:shu|鶒:chi|鶓:miao|鶔:rou|鶕:an|鶖:qiu|鶗:ti|鶘:hu|鶙:ti|鶚:e|鶛:jie|鶜:mao|鶝:fu|鶞:chun|鶟:tu|鶠:yan|鶡:he|鶢:yuan|鶣:pian|鶤:kun|鶥:mei|鶦:hu|鶧:ying|鶨:chuan|鶩:wu|鶪:ju|鶫:dong|鶬:cang|鶭:fang|鶮:hu|鶯:ying|鶰:yuan|鶱:xian|鶲:weng|鶳:shi|鶴:he|鶵:chu|鶶:tang|鶷:xia|鶸:ruo|鶹:liu|鶺:ji|鶻:gu|鶼:jian|鶽:sun|鶾:han|鶿:ci|鷀:ci|鷁:yi|鷂:yao|鷃:yan|鷄:ji|鷅:li|鷆:tian|鷇:kou|鷈:ti|鷉:ti|鷊:yi|鷋:tu|鷌:ma|鷍:xiao|鷎:gao|鷏:tian|鷐:chen|鷑:ji|鷒:tuan|鷓:zhe|鷔:ao|鷕:yao|鷖:yi|鷗:ou|鷘:chi|鷙:zhi|鷚:liu|鷛:yong|鷜:lv|鷝:bi|鷞:shuang|鷟:zhuo|鷠:yu|鷡:wu|鷢:jue|鷣:yin|鷤:tan|鷥:si|鷦:jiao|鷧:yi|鷨:hua|鷩:bi|鷪:ying|鷫:su|鷬:huang|鷭:fan|鷮:jiao|鷯:liao|鷰:yan|鷱:gao|鷲:jiu|鷳:xian|鷴:xian|鷵:tu|鷶:mai|鷷:zun|鷸:yu|鷹:ying|鷺:lu|鷻:tuan|鷼:xian|鷽:xue|鷾:yi|鷿:pi|鸀:shu|鸁:luo|鸂:xi|鸃:yi|鸄:ji|鸅:ze|鸆:yu|鸇:zhan|鸈:ye|鸉:yang|鸊:pi|鸋:ning|鸌:hu|鸍:mi|鸎:ying|鸏:meng|鸐:di|鸑:yue|鸒:yu|鸓:lei|鸔:bu|鸕:lu|鸖:he|鸗:long|鸘:shuang|鸙:yue|鸚:ying|鸛:guan|鸜:qu|鸝:li|鸞:luan|鸟:niao|鸠:jiu|鸡:ji|鸢:yuan|鸣:ming|鸤:shi|鸥:ou|鸦:ya|鸧:cang|鸨:bao|鸩:zhen|鸪:gu|鸫:dong|鸬:lu|鸭:ya|鸮:xiao|鸯:yang|鸰:ling|鸱:chi|鸲:qu|鸳:yuan|鸴:xue|鸵:tuo|鸶:si|鸷:zhi|鸸:er|鸹:gua|鸺:xiu|鸻:heng|鸼:zhou|鸽:ge|鸾:luan|鸿:hong|鹀:wu|鹁:bo|鹂:li|鹃:juan|鹄:gu|鹅:e|鹆:yu|鹇:xian|鹈:ti|鹉:wu|鹊:que|鹋:miao|鹌:an|鹍:kun|鹎:bei|鹏:peng|鹐:qian|鹑:chun|鹒:geng|鹓:yuan|鹔:su|鹕:hu|鹖:he|鹗:e|鹘:gu|鹙:qiu|鹚:ci|鹛:mei|鹜:wu|鹝:yi|鹞:yao|鹟:weng|鹠:liu|鹡:ji|鹢:yi|鹣:jian|鹤:he|鹥:yi|鹦:ying|鹧:zhe|鹨:liu|鹩:liao|鹪:jiao|鹫:jiu|鹬:yu|鹭:lu|鹮:huan|鹯:zhan|鹰:ying|鹱:hu|鹲:meng|鹳:guan|鹴:shuang|鹵:lu|鹶:jin|鹷:ling|鹸:jian|鹹:xian|鹺:cuo|鹻:jian|鹼:jian|鹽:yan|鹾:cuo|鹿:lu|麀:you|麁:cu|麂:ji|麃:biao|麄:cu|麅:pao|麆:zhu|麇:jun|麈:zhu|麉:jian|麊:mi|麋:mi|麌:yu|麍:liu|麎:chen|麏:jun|麐:lin|麑:ni|麒:qi|麓:lu|麔:jiu|麕:jun|麖:jing|麗:li|麘:xiang|麙:yan|麚:jia|麛:mi|麜:li|麝:she|麞:zhang|麟:lin|麠:jing|麡:qi|麢:ling|麣:yan|麤:cu|麥:mai|麦:mai|麧:he|麨:chao|麩:fu|麪:mian|麫:mian|麬:fu|麭:pao|麮:qu|麯:qu|麰:mou|麱:fu|麲:xian|麳:lai|麴:qu|麵:mian|麶:chi|麷:feng|麸:fu|麹:qu|麺:mian|麻:ma|麼:mo|麽:mo|麾:hui|麿:mo|黀:zou|黁:nuo|黂:fen|黃:huang|黄:huang|黅:jin|黆:guang|黇:tian|黈:tou|黉:hong|黊:hua|黋:kuang|黌:hong|黍:shu|黎:li|黏:nian|黐:chi|黑:hei|黒:hei|黓:yi|黔:qian|黕:dan|黖:xi|黗:tun|默:mo|黙:mo|黚:qian|黛:dai|黜:chu|黝:you|點:dian|黟:yi|黠:xia|黡:yan|黢:qu|黣:mei|黤:yan|黥:qing|黦:yue|黧:li|黨:dang|黩:du|黪:can|黫:yan|黬:yan|黭:yan|黮:zhen|黯:an|黰:zhen|黱:dai|黲:can|黳:yi|黴:mei|黵:zhan|黶:yan|黷:du|黸:lu|黹:zhi|黺:fen|黻:fu|黼:fu|黽:min|黾:mian|黿:yuan|鼀:cu|鼁:qu|鼂:chao|鼃:wa|鼄:zhu|鼅:zhi|鼆:meng|鼇:ao|鼈:bie|鼉:tuo|鼊:bi|鼋:yuan|鼌:chao|鼍:tuo|鼎:ding|鼏:mi|鼐:nai|鼑:ding|鼒:zi|鼓:gu|鼔:gu|鼕:dong|鼖:fen|鼗:tao|鼘:yuan|鼙:pi|鼚:chang|鼛:gao|鼜:cao|鼝:yuan|鼞:tang|鼟:teng|鼠:shu|鼡:shu|鼢:fen|鼣:fei|鼤:wen|鼥:ba|鼦:diao|鼧:tuo|鼨:zhong|鼩:qu|鼪:sheng|鼫:shi|鼬:you|鼭:shi|鼮:ting|鼯:wu|鼰:ju|鼱:jing|鼲:hun|鼳:ju|鼴:yan|鼵:tu|鼶:si|鼷:xi|鼸:xian|鼹:yan|鼺:lei|鼻:bi|鼼:yao|鼽:qiu|鼾:han|鼿:wu|齀:wu|齁:hou|齂:xie|齃:he|齄:zha|齅:xiu|齆:weng|齇:zha|齈:nong|齉:nang|齊:qi|齋:zhai|齌:ji|齍:zi|齎:ji|齏:ji|齐:qi|齑:ji|齒:chi|齓:chen|齔:chen|齕:he|齖:ya|齗:yin|齘:xie|齙:bao|齚:ze|齛:shi|齜:zi|齝:chi|齞:yan|齟:ju|齠:tiao|齡:ling|齢:ling|齣:chu|齤:quan|齥:xie|齦:yin|齧:nie|齨:jiu|齩:yao|齪:chuo|齫:yun|齬:yu|齭:chu|齮:yi|齯:ni|齰:ze|齱:chuo|齲:qu|齳:yun|齴:yan|齵:ou|齶:e|齷:wo|齸:yi|齹:cuo|齺:zou|齻:dian|齼:chu|齽:jin|齾:ya|齿:chi|龀:chen|龁:he|龂:yin|龃:ju|龄:ling|龅:bao|龆:tiao|龇:zi|龈:yin|龉:yu|龊:chuo|龋:qu|龌:wo|龍:long|龎:pang|龏:gong|龐:pang|龑:yan|龒:long|龓:long|龔:gong|龕:kan|龖:da|龗:ling|龘:da|龙:long|龚:gong|龛:kan|龜:gui|龝:qiu|龞:bie|龟:gui|龠:yue|龡:chui|龢:he|龣:jue|龤:xie|龥:yue|㐀:qiu|㐁:tian|㐄:kua|㐅:wu|㐆:yin|㐌:si|㐖:ye|㐜:chou|㐡:nuo|㐤:qiu|㐨:xu|㐩:xing|㐫:xiong|㐬:liu|㐭:lin|㐮:xiang|㐯:yong|㐰:xin|㐱:zhen|㐲:dai|㐳:wu|㐴:pan|㐷:ma|㐸:qian|㐹:yi|㐺:zhong|㐻:n|㐼:cheng|㑁:zhuo|㑂:fang|㑃:ao|㑄:wu|㑅:zuo|㑇:zhou|㑈:dong|㑉:su|㑊:yi|㑋:jiong|㑌:wang|㑍:lei|㑎:nao|㑏:zhu|㑔:xu|㑘:jie|㑙:die|㑚:nuo|㑛:su|㑜:yi|㑝:long|㑞:ying|㑟:beng|㑣:lan|㑤:miao|㑥:yi|㑦:li|㑧:ji|㑨:yu|㑩:luo|㑪:chai|㑮:hun|㑯:xu|㑰:hui|㑱:rao|㑳:zhou|㑵:han|㑶:xi|㑷:tai|㑸:ai|㑹:hui|㑺:jun|㑻:ma|㑼:lve|㑽:tang|㑾:xiao|㑿:tiao|㒀:zha|㒁:yu|㒂:ku|㒃:er|㒄:nang|㒅:qi|㒆:chi|㒇:mu|㒈:han|㒉:tang|㒊:se|㒌:qiong|㒍:lei|㒎:sa|㒑:hui|㒒:pu|㒓:ta|㒔:shu|㒖:ou|㒗:tai|㒙:mian|㒚:wen|㒛:diao|㒜:yu|㒝:mie|㒞:jun|㒟:niao|㒠:xie|㒡:you|㒤:she|㒦:lei|㒧:li|㒩:luo|㒫:ji|㒰:quan|㒲:cai|㒳:liang|㒴:gu|㒵:mao|㒷:gua|㒸:sui|㒻:mao|㒼:man|㒾:shi|㒿:li|㓁:wang|㓂:kou|㓃:chui|㓄:zhen|㓈:bing|㓉:huan|㓊:dong|㓋:gong|㓎:lian|㓏:jiong|㓐:lu|㓑:xing|㓓:nan|㓔:xie|㓖:bi|㓗:jie|㓘:su|㓜:you|㓝:xing|㓞:qi|㓠:dian|㓡:fu|㓢:luo|㓣:qia|㓤:jie|㓧:yan|㓨:ci|㓪:lang|㓭:he|㓯:li|㓰:hua|㓱:tou|㓲:pian|㓴:jun|㓵:e|㓶:qie|㓷:yi|㓸:jue|㓹:rui|㓺:jian|㓼:chi|㓽:chong|㓾:chi|㔀:lve|㔂:lin|㔃:jue|㔄:su|㔅:xiao|㔆:chan|㔉:zhu|㔊:dan|㔋:jian|㔌:zhou|㔍:duo|㔎:xie|㔏:li|㔑:chi|㔒:xi|㔓:jian|㔕:ji|㔗:fei|㔘:chu|㔙:bang|㔚:kou|㔜:ba|㔝:liang|㔞:kuai|㔠:he|㔢:jue|㔣:lei|㔤:shen|㔥:pi|㔦:yang|㔧:lv|㔨:bei|㔩:e|㔪:lu|㔭:che|㔮:nuo|㔯:suan|㔰:heng|㔱:yu|㔳:gui|㔴:yi|㔵:xian|㔶:gong|㔷:lou|㔹:le|㔺:shi|㔼:sun|㔽:yao|㔾:jie|㔿:zou|㕁:que|㕂:yin|㕄:zhi|㕅:jia|㕆:hu|㕇:la|㕈:hou|㕉:ke|㕋:jing|㕌:ai|㕎:e|㕏:chu|㕐:xie|㕑:chu|㕒:wei|㕕:huan|㕖:su|㕗:you|㕙:jun|㕚:zhao|㕛:xu|㕜:shi|㕟:kui|㕡:he|㕢:gai|㕣:yan|㕤:qiu|㕥:yi|㕦:hua|㕨:fan|㕩:zhang|㕪:dan|㕫:fang|㕬:song|㕭:ao|㕮:fu|㕯:nei|㕰:he|㕱:you|㕲:hua|㕴:chen|㕵:guo|㕶:ng|㕷:hua|㕸:li|㕹:fa|㕺:hao|㕻:pou|㕽:si|㖀:le|㖁:lin|㖂:yi|㖃:hou|㖅:xu|㖆:qu|㖇:er|㖏:nei|㖐:wei|㖑:xie|㖒:ti|㖓:hong|㖔:tun|㖕:bo|㖖:nie|㖗:yin|㖞:wai|㖟:shou|㖠:ba|㖡:ye|㖢:ji|㖣:tou|㖤:han|㖥:jiong|㖦:dong|㖧:wen|㖨:lu|㖩:sou|㖪:guo|㖫:ling|㖭:tian|㖮:lun|㖶:ye|㖷:shi|㖸:xue|㖹:fen|㖺:chun|㖻:rou|㖼:duo|㖽:ze|㖾:e|㖿:xie|㗁:e|㗂:sheng|㗃:wen|㗄:man|㗅:hu|㗆:ge|㗇:xia|㗈:man|㗉:bi|㗊:ji|㗋:hou|㗌:zhi|㗑:bai|㗒:ai|㗕:gou|㗖:dan|㗗:bai|㗘:bo|㗙:na|㗚:li|㗛:xiao|㗜:xiu|㗢:dong|㗣:ti|㗤:cu|㗥:kuo|㗦:lao|㗧:zhi|㗨:ai|㗩:xi|㗫:qie|㗰:chu|㗱:ji|㗲:huo|㗳:ta|㗴:yan|㗵:xu|㗷:sai|㗼:ye|㗽:xiang|㗿:xia|㘀:zuo|㘁:yi|㘂:ci|㘅:xian|㘆:tai|㘇:rong|㘈:yi|㘉:zhi|㘊:yi|㘋:xian|㘌:ju|㘍:ji|㘎:han|㘐:pao|㘑:li|㘓:lan|㘔:can|㘕:han|㘖:yan|㘙:yan|㘚:han|㘜:chi|㘝:nian|㘞:huo|㘠:bi|㘡:xia|㘢:weng|㘣:xuan|㘥:you|㘦:yi|㘧:xu|㘨:nei|㘩:bi|㘪:hao|㘫:jing|㘬:ao|㘭:ao|㘲:ju|㘴:zuo|㘵:bu|㘶:jie|㘷:ai|㘸:zang|㘹:ci|㘺:fa|㘿:nie|㙀:liu|㙁:mang|㙂:dui|㙄:bi|㙅:bao|㙇:chu|㙈:han|㙉:tian|㙊:chang|㙏:fu|㙐:duo|㙑:yu|㙒:ye|㙓:kui|㙔:han|㙕:kuai|㙗:kuai|㙙:long|㙛:bu|㙜:chi|㙝:xie|㙞:nie|㙟:lang|㙠:yi|㙢:man|㙣:zhang|㙤:xia|㙥:gun|㙨:ji|㙩:liao|㙪:ye|㙫:ji|㙬:yin|㙮:da|㙯:yi|㙰:xie|㙱:hao|㙲:yong|㙳:han|㙴:chan|㙵:tai|㙶:tang|㙷:zhi|㙸:bao|㙹:meng|㙺:gui|㙻:chan|㙼:lei|㙾:xi|㚁:qiao|㚂:rang|㚃:yun|㚅:long|㚆:fu|㚉:gu|㚌:hua|㚍:guo|㚏:gao|㚐:tao|㚒:shan|㚓:lai|㚔:nie|㚕:fu|㚖:gao|㚗:qie|㚘:ban|㚛:xi|㚜:xu|㚝:kui|㚞:meng|㚟:chuo|㚡:ji|㚢:nu|㚣:xiao|㚤:yi|㚥:yu|㚦:yi|㚧:yan|㚩:ran|㚪:hao|㚫:sha|㚭:you|㚯:xin|㚰:bi|㚲:dian|㚴:bu|㚶:si|㚷:er|㚹:mao|㚺:yun|㚽:qiao|㚿:pao|㛂:nuo|㛃:jie|㛅:er|㛆:duo|㛊:duo|㛍:qie|㛏:ou|㛐:sou|㛑:can|㛒:dou|㛔:peng|㛕:yi|㛗:zuo|㛘:po|㛙:qie|㛚:tong|㛛:xin|㛜:you|㛝:bei|㛞:long|㛥:ta|㛦:lan|㛧:man|㛨:qiang|㛩:zhou|㛪:yan|㛬:lu|㛮:sao|㛯:mian|㛱:rui|㛲:fa|㛳:cha|㛴:nao|㛶:chou|㛸:shu|㛹:pian|㛻:kui|㛼:sha|㛾:xian|㛿:zhi|㜃:lian|㜄:xun|㜅:xu|㜆:mi|㜇:hui|㜈:mu|㜊:pang|㜋:yi|㜌:gou|㜍:tang|㜎:qi|㜏:yun|㜐:shu|㜑:fu|㜒:yi|㜓:da|㜕:lian|㜖:cao|㜗:can|㜘:ju|㜙:lu|㜚:su|㜛:nen|㜜:ao|㜝:an|㜞:qian|㜣:ran|㜤:shen|㜥:mai|㜦:han|㜧:yue|㜨:er|㜩:ao|㜪:xian|㜫:ma|㜮:lan|㜰:yue|㜱:dong|㜲:weng|㜳:huai|㜴:meng|㜵:niao|㜶:wan|㜷:mi|㜸:nie|㜹:qu|㜺:zan|㜻:lian|㜼:zhi|㜽:zi|㜾:hai|㜿:xu|㝀:hao|㝁:xun|㝂:zhi|㝃:fan|㝄:chun|㝅:gou|㝇:chun|㝈:luan|㝉:zhu|㝊:shou|㝋:liao|㝌:jie|㝍:xie|㝎:ding|㝏:jie|㝐:rong|㝑:mang|㝓:ge|㝔:yao|㝕:ning|㝖:yi|㝗:lang|㝘:yong|㝙:yin|㝛:su|㝝:lin|㝞:ya|㝟:mao|㝠:ming|㝡:zui|㝢:yu|㝣:ye|㝤:gou|㝥:mi|㝦:jun|㝧:wen|㝪:dian|㝫:long|㝭:xing|㝮:cui|㝯:qiao|㝰:mian|㝱:meng|㝲:qin|㝴:wan|㝵:de|㝶:ai|㝸:bian|㝹:nou|㝺:lian|㝻:jin|㝽:chui|㝾:zuo|㝿:bo|㞁:yao|㞂:tui|㞃:ji|㞅:guo|㞆:ji|㞇:wei|㞊:xu|㞋:nian|㞌:yun|㞎:ba|㞏:zhe|㞐:ju|㞑:wei|㞒:xi|㞓:qi|㞔:yi|㞕:xie|㞖:ci|㞗:qiu|㞘:tun|㞙:niao|㞚:qi|㞛:ji|㞟:dian|㞠:lao|㞡:zhan|㞤:yin|㞥:cen|㞦:ji|㞧:hui|㞨:zai|㞩:lan|㞪:nao|㞫:ju|㞬:qin|㞭:dai|㞯:jie|㞰:xu|㞲:yong|㞳:dou|㞴:chi|㞶:min|㞷:huang|㞸:sui|㞹:ke|㞺:zu|㞻:hao|㞼:cheng|㞽:xue|㞾:ni|㞿:chi|㟀:lian|㟁:an|㟂:chi|㟄:xiang|㟅:yang|㟆:hua|㟇:cuo|㟈:qiu|㟉:lao|㟊:fu|㟋:dui|㟌:mang|㟍:lang|㟎:tuo|㟏:han|㟐:mang|㟑:bo|㟓:qi|㟔:han|㟖:long|㟘:tiao|㟙:lao|㟚:qi|㟛:zan|㟜:mi|㟝:pei|㟞:zhan|㟟:xiang|㟠:gang|㟢:qi|㟤:lu|㟦:yun|㟧:e|㟨:quan|㟩:min|㟪:wei|㟫:quan|㟬:shu|㟭:min|㟰:ming|㟱:yao|㟲:jue|㟳:li|㟴:kuai|㟵:gang|㟶:yuan|㟷:da|㟹:lao|㟺:lou|㟻:qian|㟼:ao|㟽:biao|㟿:mang|㠀:dao|㠂:ao|㠄:xi|㠅:fu|㠇:jiu|㠈:run|㠉:tong|㠊:qu|㠋:e|㠍:ji|㠎:ji|㠏:hua|㠐:jiao|㠑:zui|㠒:biao|㠓:meng|㠔:bai|㠕:wei|㠖:ji|㠗:ao|㠘:yu|㠙:hao|㠚:dui|㠛:wo|㠜:ni|㠝:cuan|㠟:li|㠠:lu|㠡:niao|㠢:hua|㠣:lai|㠥:lv|㠧:mi|㠨:yu|㠪:ju|㠭:zhan|㠯:yi|㠱:ji|㠲:bi|㠴:ren|㠶:fan|㠷:ge|㠸:ku|㠹:jie|㠺:miao|㠽:tong|㠿:ci|㡀:bi|㡁:kai|㡂:li|㡄:sun|㡅:nuo|㡇:ji|㡈:men|㡉:xian|㡊:qia|㡋:e|㡌:mao|㡏:tou|㡑:qiao|㡔:wu|㡖:chuang|㡗:ti|㡘:lian|㡙:bi|㡛:mang|㡜:xue|㡝:feng|㡞:lei|㡠:zheng|㡡:chu|㡢:man|㡣:long|㡥:yin|㡧:zheng|㡨:qian|㡩:luan|㡪:nie|㡫:yi|㡭:ji|㡮:ji|㡯:zhai|㡰:yu|㡱:jiu|㡲:huan|㡳:di|㡵:ling|㡶:ji|㡷:ben|㡸:zha|㡹:ci|㡺:dan|㡻:liao|㡼:yi|㡽:zhao|㡾:xian|㡿:chi|㢀:ci|㢁:chi|㢂:yan|㢃:lang|㢄:dou|㢅:long|㢆:chan|㢈:tui|㢉:cha|㢊:ai|㢋:chi|㢍:ying|㢎:cha|㢏:tou|㢑:tui|㢒:cha|㢓:yao|㢔:zong|㢗:qiao|㢘:lian|㢙:qin|㢚:lu|㢛:yan|㢞:yi|㢟:chan|㢠:jiong|㢡:jiang|㢣:jing|㢥:dong|㢧:juan|㢨:han|㢩:di|㢬:hong|㢮:chi|㢯:min|㢰:bi|㢲:xun|㢳:lu|㢵:she|㢶:bi|㢸:bi|㢺:xian|㢻:wei|㢼:bie|㢽:er|㢾:juan|㣀:zhen|㣁:bei|㣂:yi|㣃:yu|㣄:qu|㣅:zan|㣆:mi|㣇:ni|㣈:si|㣌:shan|㣍:tai|㣎:mu|㣏:jing|㣐:bian|㣑:rong|㣒:ceng|㣓:can|㣙:di|㣚:tong|㣛:ta|㣜:xing|㣞:duo|㣟:xi|㣠:tong|㣢:ti|㣣:shan|㣤:jian|㣥:zhi|㣧:yin|㣪:huan|㣫:zhong|㣬:qi|㣯:xie|㣰:xie|㣱:ze|㣲:wei|㣵:ta|㣶:zhan|㣷:ning|㣻:yi|㣼:ren|㣽:shu|㣾:cha|㣿:zhuo|㤁:mian|㤂:ji|㤃:fang|㤄:pei|㤅:ai|㤆:fan|㤇:ao|㤈:qin|㤉:qia|㤊:xiao|㤍:qiao|㤏:tong|㤑:you|㤓:ben|㤔:fu|㤕:chu|㤖:zhu|㤘:chu|㤚:hang|㤛:nin|㤜:jue|㤞:cha|㤟:kong|㤠:lie|㤡:li|㤢:xu|㤤:yu|㤥:hai|㤦:li|㤧:hou|㤨:gong|㤩:ke|㤪:yuan|㤫:de|㤬:hui|㤮:kuang|㤯:jiong|㤰:zan|㤱:fu|㤲:qie|㤳:bei|㤴:xi|㤵:ci|㤶:pang|㤸:xi|㤹:qiu|㤺:huang|㤽:chou|㤾:san|㥀:de|㥁:de|㥂:te|㥃:men|㥄:ling|㥅:shou|㥆:dian|㥇:can|㥈:die|㥉:che|㥊:peng|㥌:ju|㥍:ji|㥎:lai|㥏:tian|㥐:yuan|㥒:cai|㥓:qi|㥔:yu|㥕:lian|㥚:yu|㥛:ji|㥜:wei|㥝:mi|㥞:cui|㥟:xie|㥠:xu|㥡:xi|㥢:qiu|㥣:hui|㥥:yu|㥦:qie|㥧:shun|㥨:chui|㥩:duo|㥪:lou|㥬:pang|㥭:tai|㥮:zhou|㥯:yin|㥱:fei|㥲:shen|㥳:yuan|㥴:yi|㥵:hun|㥶:se|㥷:ye|㥸:min|㥹:fen|㥺:he|㥼:yin|㥽:ce|㥾:ni|㥿:ao|㦀:feng|㦁:lian|㦂:chang|㦃:chan|㦄:ma|㦅:di|㦇:lu|㦉:yi|㦊:hua|㦌:tui|㦍:e|㦎:hua|㦏:sun|㦐:ni|㦑:lian|㦒:li|㦓:xian|㦔:yan|㦕:long|㦖:men|㦗:jian|㦚:bian|㦛:yu|㦜:huo|㦝:miao|㦞:chou|㦟:hai|㦡:le|㦢:jie|㦣:wei|㦤:yi|㦥:huan|㦦:he|㦧:can|㦨:lan|㦩:yin|㦪:xie|㦬:luo|㦭:ling|㦮:qian|㦯:huo|㦱:wo|㦴:ge|㦶:die|㦷:yong|㦸:ji|㦹:ang|㦺:ru|㦻:xi|㦼:shuang|㦽:xu|㦾:yi|㦿:hu|㧀:ji|㧁:qu|㧂:tian|㧄:qian|㧅:mu|㧇:mao|㧈:yin|㧉:gai|㧊:ba|㧋:xian|㧌:mao|㧍:fang|㧎:ya|㧐:song|㧑:wei|㧒:xue|㧔:guai|㧕:jiu|㧖:e|㧗:zi|㧘:cui|㧙:bi|㧚:wa|㧜:lie|㧟:kuai|㧡:hai|㧣:zhu|㧤:chong|㧥:xian|㧦:xuan|㧨:qiu|㧩:pei|㧪:gui|㧫:er|㧬:gong|㧭:qiong|㧯:lao|㧰:li|㧱:chen|㧲:san|㧳:bo|㧴:wo|㧵:pou|㧷:duo|㧹:te|㧺:ta|㧻:zhi|㧼:biao|㧽:gu|㨀:bing|㨁:zhi|㨂:dong|㨃:cheng|㨄:zhao|㨅:nei|㨆:lin|㨇:po|㨈:ji|㨉:min|㨊:wei|㨋:che|㨌:gou|㨎:ru|㨐:bu|㨒:kui|㨓:lao|㨔:han|㨕:ying|㨖:zhi|㨗:jie|㨘:xing|㨙:xie|㨚:xun|㨛:shan|㨜:qian|㨝:xie|㨞:su|㨟:hai|㨠:mi|㨡:hun|㨤:hui|㨥:na|㨦:song|㨧:ben|㨨:liu|㨩:jie|㨪:huang|㨫:lan|㨭:hu|㨮:dou|㨯:huo|㨰:ge|㨱:yao|㨲:ce|㨳:gui|㨴:jian|㨵:jian|㨶:chou|㨷:jin|㨸:ma|㨹:hui|㨺:men|㨻:can|㨼:lve|㨽:pi|㨾:yang|㨿:ju|㩀:ju|㩁:que|㩄:shai|㩆:jiu|㩇:hua|㩈:xian|㩉:xie|㩋:su|㩌:fei|㩍:ce|㩎:ye|㩒:qin|㩓:hui|㩔:tun|㩖:qiang|㩗:xi|㩘:yi|㩚:meng|㩛:tuan|㩜:lan|㩝:hao|㩞:ci|㩟:zhai|㩠:piao|㩡:luo|㩢:mi|㩦:xie|㩧:bo|㩨:hui|㩩:qi|㩪:xie|㩭:bo|㩮:qian|㩯:ban|㩰:jiao|㩱:jue|㩲:kun|㩳:song|㩴:ju|㩵:e|㩶:nie|㩸:die|㩹:die|㩻:gui|㩽:qi|㩾:chui|㪀:yu|㪁:qin|㪃:ke|㪄:fu|㪆:di|㪇:xian|㪈:gui|㪉:he|㪊:qun|㪋:han|㪌:tong|㪍:bo|㪎:shan|㪏:bi|㪐:lu|㪑:ye|㪒:ni|㪓:chuai|㪔:san|㪕:diao|㪖:lu|㪗:tou|㪘:lian|㪙:ke|㪚:san|㪛:zhen|㪜:chuai|㪝:lian|㪞:mao|㪠:ji|㪡:ke|㪢:shao|㪣:qiao|㪤:bi|㪦:yin|㪨:shan|㪩:su|㪪:sa|㪫:rui|㪬:zhuo|㪭:lu|㪮:ling|㪯:cha|㪱:huan|㪴:jia|㪵:ban|㪶:hu|㪷:dou|㪹:lou|㪻:juan|㪼:ke|㪽:suo|㪾:ge|㪿:zhe|㫀:ding|㫁:duan|㫂:zhu|㫃:yan|㫄:pang|㫅:cha|㫊:yi|㫍:you|㫎:gun|㫏:yao|㫐:yao|㫑:shi|㫒:gong|㫓:qi|㫔:gen|㫗:hou|㫘:mi|㫙:fu|㫚:hu|㫛:guang|㫜:dan|㫟:yan|㫢:qu|㫤:chang|㫥:ming|㫧:bao|㫫:xian|㫯:mao|㫰:lang|㫱:nan|㫲:pei|㫳:chen|㫶:cou|㫸:qie|㫹:dai|㫻:kun|㫼:die|㫽:lu|㬂:yu|㬃:tai|㬄:chan|㬅:man|㬆:mian|㬇:huan|㬉:nuan|㬊:huan|㬋:hou|㬌:jing|㬍:bo|㬎:xian|㬏:li|㬐:jin|㬒:mang|㬓:piao|㬔:hao|㬕:yang|㬗:xian|㬘:su|㬙:wei|㬚:che|㬜:jin|㬝:ceng|㬞:he|㬠:shai|㬡:ling|㬣:dui|㬥:pu|㬦:yue|㬧:bo|㬩:hui|㬪:die|㬫:yan|㬬:ju|㬭:jiao|㬮:kuai|㬯:lie|㬰:yu|㬱:ti|㬳:wu|㬴:hong|㬵:xiao|㬶:hao|㬻:huang|㬼:fu|㬿:dun|㭁:reng|㭂:jiao|㭄:xin|㭇:yuan|㭈:jue|㭉:hua|㭋:bang|㭌:mou|㭏:wei|㭑:mei|㭒:si|㭓:bian|㭔:lu|㭘:he|㭙:she|㭚:lv|㭛:pai|㭜:rong|㭝:qiu|㭞:lie|㭟:gong|㭠:xian|㭡:xi|㭤:niao|㭨:xie|㭩:lei|㭫:cuan|㭬:zhuo|㭭:fei|㭮:zuo|㭯:die|㭰:ji|㭱:he|㭲:ji|㭸:tu|㭹:xian|㭺:yan|㭻:tang|㭼:ta|㭽:di|㭾:jue|㭿:ang|㮀:han|㮁:yao|㮂:ju|㮃:rui|㮄:bang|㮆:nie|㮇:tian|㮈:nai|㮋:you|㮌:mian|㮏:nai|㮐:xing|㮑:qi|㮓:gen|㮔:tong|㮕:er|㮖:jia|㮗:qin|㮘:mao|㮙:e|㮚:li|㮛:chi|㮝:he|㮞:jie|㮟:ji|㮡:guan|㮢:hou|㮣:gai|㮥:fen|㮦:se|㮨:ji|㮪:qiong|㮫:he|㮭:xian|㮮:jie|㮯:hua|㮰:bi|㮳:zhen|㮶:shi|㮸:song|㮹:zhi|㮺:ben|㮾:lang|㮿:bi|㯀:xian|㯁:bang|㯂:dai|㯅:pi|㯆:chan|㯇:bi|㯈:su|㯉:huo|㯊:hen|㯋:ying|㯌:chuan|㯍:jiang|㯎:nen|㯏:gu|㯐:fang|㯓:ta|㯔:cui|㯖:de|㯗:ran|㯘:kuan|㯙:che|㯚:da|㯛:hu|㯜:cui|㯝:lu|㯞:juan|㯟:lu|㯠:qian|㯡:pao|㯢:zhen|㯤:li|㯥:cao|㯦:qi|㯩:ti|㯪:ling|㯫:qu|㯬:lian|㯭:lu|㯮:shu|㯯:gong|㯰:zhe|㯱:biao|㯲:jin|㯳:qing|㯶:zong|㯷:pu|㯸:jin|㯹:biao|㯺:jian|㯻:gun|㯿:lie|㰀:li|㰁:luo|㰂:shen|㰃:mian|㰄:jian|㰅:di|㰆:bei|㰈:lian|㰊:xun|㰋:pin|㰌:que|㰍:long|㰎:zui|㰐:jue|㰒:she|㰔:xie|㰖:lan|㰗:cu|㰘:yi|㰙:nuo|㰚:li|㰛:yue|㰝:yi|㰟:ji|㰠:kang|㰡:xie|㰣:zi|㰤:ke|㰥:hui|㰦:qu|㰪:wa|㰬:xun|㰮:shen|㰯:kou|㰰:qie|㰱:sha|㰲:xu|㰳:ya|㰴:po|㰵:zu|㰶:you|㰷:zi|㰸:lian|㰹:jin|㰺:xia|㰻:yi|㰼:qie|㰽:mi|㰾:jiao|㱀:chi|㱁:shi|㱃:yin|㱄:mo|㱅:yi|㱇:se|㱈:jin|㱉:ye|㱋:que|㱌:che|㱍:luan|㱏:zheng|㱖:cui|㱘:an|㱙:xiu|㱚:can|㱛:chuan|㱜:zha|㱞:ji|㱟:bo|㱢:lang|㱣:tui|㱥:ling|㱦:e|㱧:wo|㱨:lian|㱩:du|㱪:men|㱫:lan|㱬:wei|㱭:duan|㱮:kuai|㱯:ai|㱰:zai|㱱:hui|㱲:yi|㱳:mo|㱴:zi|㱵:ben|㱶:beng|㱸:bi|㱹:li|㱺:lu|㱻:luo|㱽:dan|㱿:que|㲀:chen|㲂:cheng|㲃:jiu|㲄:kou|㲅:ji|㲆:ling|㲈:shao|㲉:kai|㲊:rui|㲋:chuo|㲌:neng|㲎:lou|㲏:bao|㲒:bao|㲓:rong|㲕:lei|㲘:qu|㲛:zhi|㲜:tan|㲝:rong|㲞:zu|㲟:ying|㲠:mao|㲡:nai|㲢:bian|㲥:tang|㲦:han|㲧:zao|㲨:rong|㲫:pu|㲭:tan|㲯:ran|㲰:ning|㲱:lie|㲲:die|㲳:die|㲴:zhong|㲶:lv|㲷:dan|㲹:gui|㲺:ji|㲻:ni|㲼:yi|㲽:nian|㲾:yu|㲿:wang|㳀:guo|㳁:ze|㳂:yan|㳃:cui|㳄:xian|㳅:jiao|㳆:shu|㳇:fu|㳈:pei|㳍:bu|㳎:bian|㳏:chi|㳐:sa|㳑:yi|㳒:bian|㳔:dui|㳕:lan|㳗:chai|㳙:xuan|㳚:yu|㳛:yu|㳠:ta|㳥:ju|㳦:xie|㳧:xi|㳨:jian|㳪:pan|㳫:ta|㳬:xuan|㳭:xian|㳮:niao|㳴:mi|㳵:ji|㳶:gou|㳷:wen|㳹:wang|㳺:you|㳻:ze|㳼:bi|㳽:mi|㳿:xie|㴀:fan|㴁:yi|㴃:lei|㴄:ying|㴆:jin|㴇:she|㴈:yin|㴉:ji|㴋:su|㴏:wang|㴐:mian|㴑:su|㴒:yi|㴓:zai|㴔:se|㴕:ji|㴖:luo|㴘:mao|㴙:zha|㴚:sui|㴛:zhi|㴜:bian|㴝:li|㴥:qiao|㴦:guan|㴨:zhen|㴪:nie|㴫:jun|㴬:xie|㴭:yao|㴮:xie|㴰:neng|㴳:long|㴴:chen|㴵:mi|㴶:que|㴸:na|㴼:su|㴽:xie|㴾:bo|㴿:ding|㵀:cuan|㵂:chuang|㵃:che|㵄:han|㵅:dan|㵆:hao|㵊:shen|㵋:mi|㵌:chan|㵍:men|㵎:han|㵏:cui|㵐:jue|㵑:he|㵒:fei|㵓:shi|㵔:che|㵕:shen|㵖:nv|㵗:fu|㵘:man|㵝:yi|㵞:chou|㵡:bao|㵢:lei|㵣:ke|㵤:dian|㵥:bi|㵦:sui|㵧:ge|㵨:bi|㵩:yi|㵪:xian|㵫:ni|㵬:ying|㵭:zhu|㵮:chun|㵯:feng|㵰:xu|㵱:piao|㵲:wu|㵳:liao|㵴:cang|㵵:zou|㵷:bian|㵸:yao|㵹:huan|㵺:pai|㵻:sou|㵽:dui|㵾:jing|㵿:xi|㶁:guo|㶄:yan|㶅:xue|㶆:chu|㶇:heng|㶈:ying|㶌:lian|㶍:xian|㶎:huan|㶑:lian|㶒:shan|㶓:cang|㶔:bei|㶕:jian|㶖:shu|㶗:fan|㶘:dian|㶚:ba|㶛:yu|㶞:nang|㶟:lei|㶠:yi|㶡:dai|㶣:chan|㶤:chao|㶦:jin|㶧:nen|㶫:liao|㶬:mei|㶭:jiu|㶯:liu|㶰:han|㶲:yong|㶳:jin|㶴:chi|㶵:ren|㶶:nong|㶹:hong|㶺:tian|㶿:bo|㷀:qiong|㷂:shu|㷃:cui|㷄:hui|㷅:chao|㷆:dou|㷇:guai|㷈:e|㷉:wei|㷊:fen|㷋:tan|㷍:lun|㷎:he|㷏:yong|㷐:hui|㷒:yu|㷓:zong|㷔:yan|㷕:qiu|㷖:zhao|㷗:jiong|㷘:tai|㷟:tui|㷠:lin|㷡:jiong|㷢:zha|㷤:he|㷦:xu|㷪:cui|㷫:qing|㷬:mo|㷯:beng|㷰:li|㷳:yan|㷴:ge|㷵:mo|㷶:bei|㷷:juan|㷸:die|㷹:shao|㷻:wu|㷼:yan|㷾:jue|㸀:tai|㸁:han|㸃:dian|㸄:ji|㸅:jie|㸉:xie|㸊:la|㸋:fan|㸌:huo|㸍:xi|㸎:nie|㸏:mi|㸐:ran|㸑:cuan|㸒:yin|㸓:mi|㸕:jue|㸗:tong|㸘:wan|㸚:li|㸛:shao|㸜:kong|㸝:kan|㸞:ban|㸠:tiao|㸢:bei|㸣:ye|㸤:pian|㸥:chan|㸦:hu|㸧:ken|㸩:an|㸪:chun|㸫:qian|㸬:bei|㸮:fen|㸰:tuo|㸱:tuo|㸲:zuo|㸳:ling|㸵:gui|㸷:shi|㸸:hou|㸹:lie|㸻:si|㸽:bei|㸾:ren|㸿:du|㹀:bo|㹁:liang|㹂:ci|㹃:bi|㹄:ji|㹅:zong|㹇:he|㹈:li|㹉:yuan|㹊:yue|㹌:chan|㹍:di|㹎:lei|㹏:jin|㹐:chong|㹑:si|㹒:pu|㹓:yi|㹖:huan|㹗:tao|㹘:ru|㹙:ying|㹚:ying|㹛:rao|㹜:yin|㹝:shi|㹞:yin|㹟:jue|㹠:tun|㹡:xuan|㹤:qie|㹥:zhu|㹨:you|㹫:xi|㹬:shi|㹭:yi|㹮:mo|㹱:hu|㹲:xiao|㹳:wu|㹵:jing|㹶:ting|㹷:shi|㹸:ni|㹺:ta|㹼:chu|㹽:chan|㹾:piao|㹿:diao|㺀:nao|㺁:nao|㺂:gan|㺃:gou|㺄:yu|㺅:hou|㺉:hu|㺊:yang|㺌:xian|㺎:rong|㺏:lou|㺐:zhao|㺑:can|㺒:liao|㺓:piao|㺔:hai|㺕:fan|㺖:han|㺗:dan|㺘:zhan|㺚:ta|㺛:zhu|㺜:ban|㺝:jian|㺞:yu|㺟:zhuo|㺠:you|㺡:li|㺥:chan|㺦:lian|㺩:jiu|㺪:pu|㺫:qiu|㺬:gong|㺭:zi|㺮:yu|㺱:reng|㺲:niu|㺳:mei|㺵:jiu|㺷:xu|㺸:ping|㺹:bian|㺺:mao|㺿:yi|㻀:you|㻂:ping|㻄:bao|㻅:hui|㻉:bu|㻊:mang|㻋:la|㻌:tu|㻍:wu|㻎:li|㻏:ling|㻑:ji|㻒:jun|㻔:duo|㻕:jue|㻖:dai|㻗:bei|㻝:la|㻞:bian|㻟:sui|㻠:tu|㻡:die|㻧:duo|㻪:sui|㻫:bi|㻬:tu|㻭:se|㻮:can|㻯:tu|㻰:mian|㻲:lv|㻵:zhan|㻶:bi|㻷:ji|㻸:cen|㻺:li|㻽:sui|㻿:shu|㼂:e|㼇:qiong|㼈:luo|㼉:yin|㼊:tun|㼋:gu|㼌:yu|㼍:lei|㼎:bei|㼏:nei|㼐:pian|㼑:lian|㼒:qiu|㼓:lian|㼖:li|㼗:ding|㼘:wa|㼙:zhou|㼛:xing|㼜:ang|㼝:fan|㼞:peng|㼟:bai|㼠:tuo|㼢:e|㼣:bai|㼤:qi|㼥:chu|㼦:gong|㼧:tong|㼨:han|㼩:cheng|㼪:jia|㼫:huan|㼬:xing|㼭:dian|㼮:mai|㼯:dong|㼰:e|㼱:ruan|㼲:lie|㼳:sheng|㼴:ou|㼵:di|㼶:yu|㼷:chuan|㼸:rong|㼺:tang|㼻:cong|㼼:piao|㼽:shuang|㼾:lu|㼿:tong|㽀:zheng|㽁:li|㽂:sa|㽇:guai|㽈:yi|㽉:han|㽊:xie|㽋:luo|㽌:liu|㽎:dan|㽑:tan|㽕:you|㽖:nan|㽘:gang|㽙:jun|㽚:chi|㽛:kou|㽜:wan|㽝:li|㽞:liu|㽟:lie|㽠:xia|㽢:an|㽣:yu|㽤:ju|㽥:rou|㽦:xun|㽨:cuo|㽩:can|㽪:zeng|㽫:yong|㽬:fu|㽭:ruan|㽯:xi|㽰:shu|㽱:jiao|㽲:jiao|㽳:han|㽴:zhang|㽷:shui|㽸:chen|㽹:fan|㽺:ji|㽽:gu|㽾:wu|㾀:qie|㾁:shu|㾃:tuo|㾄:du|㾅:si|㾆:ran|㾇:mu|㾈:fu|㾉:ling|㾊:ji|㾋:xiu|㾌:xuan|㾍:nai|㾏:jie|㾐:li|㾑:da|㾒:ji|㾔:lv|㾕:shen|㾖:li|㾗:lang|㾘:geng|㾙:yin|㾛:qin|㾜:qie|㾝:che|㾞:you|㾟:bu|㾠:huang|㾡:que|㾢:lai|㾥:xu|㾦:bang|㾧:ke|㾨:qi|㾪:sheng|㾭:zhou|㾮:huang|㾯:tui|㾰:hu|㾱:bei|㾵:ji|㾶:gu|㾸:gao|㾹:chai|㾺:ma|㾻:zhu|㾼:tui|㾽:tui|㾾:lian|㾿:lang|㿃:dai|㿄:ai|㿅:xian|㿇:xi|㿉:tui|㿊:can|㿋:sao|㿍:jie|㿎:fen|㿏:qun|㿑:yao|㿒:dao|㿓:jia|㿔:lei|㿕:yan|㿖:lu|㿗:tui|㿘:ying|㿙:pi|㿚:luo|㿛:li|㿜:bie|㿞:mao|㿟:bai|㿢:yao|㿣:he|㿤:chun|㿥:hu|㿦:ning|㿧:chou|㿨:li|㿩:tang|㿪:huan|㿫:bi|㿭:che|㿮:yang|㿯:da|㿰:ao|㿱:xue|㿵:ran|㿷:zao|㿸:wan|㿹:ta|㿺:bao|㿼:yan|㿾:zhu|㿿:ya|䀀:fan|䀁:you|䀃:tui|䀄:meng|䀅:she|䀆:jin|䀇:gu|䀈:qi|䀉:qiao|䀊:jiao|䀋:yan|䀍:kan|䀎:mian|䀏:xian|䀐:san|䀑:na|䀓:huan|䀔:niu|䀕:cheng|䀗:jue|䀘:xi|䀙:qi|䀚:ang|䀛:mei|䀜:gu|䀟:fan|䀠:qu|䀡:chan|䀢:shun|䀣:bi|䀤:mao|䀥:shuo|䀦:gu|䀧:hong|䀨:huan|䀩:luo|䀪:hang|䀫:jia|䀬:quan|䀮:mang|䀯:bu|䀰:gu|䀲:mu|䀳:ai|䀴:ying|䀵:shun|䀶:lang|䀷:jie|䀸:di|䀹:jie|䀻:pin|䀼:ren|䀽:yan|䀾:du|䀿:di|䁁:lang|䁂:xian|䁄:xing|䁅:bei|䁆:an|䁇:mi|䁈:qi|䁉:qi|䁊:wo|䁋:she|䁌:yu|䁍:jia|䁎:cheng|䁏:yao|䁐:ying|䁑:yang|䁒:ji|䁓:jie|䁔:han|䁕:min|䁖:lou|䁗:kai|䁘:yao|䁙:yan|䁚:sun|䁛:gui|䁜:huang|䁝:ying|䁞:sheng|䁟:cha|䁠:lian|䁢:xuan|䁣:chuan|䁤:che|䁥:ni|䁦:qu|䁧:miao|䁨:huo|䁩:yu|䁪:nan|䁫:hu|䁬:ceng|䁮:qian|䁯:she|䁰:jiang|䁱:ao|䁲:mai|䁳:mang|䁴:zhan|䁵:bian|䁶:jiao|䁷:jue|䁸:nong|䁹:bi|䁺:shi|䁻:li|䁼:mo|䁽:lie|䁾:mie|䁿:mo|䂀:xi|䂁:chan|䂂:qu|䂃:jiao|䂄:huo|䂆:xu|䂇:nang|䂈:tong|䂉:hou|䂊:yu|䂍:bo|䂎:zuan|䂐:chuo|䂒:jie|䂔:xing|䂕:hui|䂖:shi|䂚:yao|䂛:yu|䂜:bang|䂝:jie|䂞:zhe|䂠:she|䂡:di|䂢:dong|䂣:ci|䂤:fu|䂥:min|䂦:zhen|䂧:zhen|䂩:yan|䂪:diao|䂫:hong|䂬:gong|䂮:lve|䂯:guai|䂰:la|䂱:cui|䂲:fa|䂳:cuo|䂴:yan|䂶:jie|䂸:guo|䂹:suo|䂺:wan|䂻:zheng|䂼:nie|䂽:diao|䂾:lai|䂿:ta|䃀:cui|䃂:gun|䃇:mian|䃉:min|䃊:ju|䃋:yu|䃍:zhao|䃎:ze|䃑:pan|䃒:he|䃓:gou|䃔:hong|䃕:lao|䃖:wu|䃗:chuo|䃙:lu|䃚:cu|䃛:lian|䃝:qiao|䃞:shu|䃡:cen|䃣:hui|䃤:su|䃥:chuang|䃧:long|䃩:nao|䃪:tan|䃫:dan|䃬:wei|䃭:gan|䃮:da|䃯:li|䃱:xian|䃲:pan|䃳:la|䃵:niao|䃶:deng|䃷:ying|䃸:xian|䃹:lan|䃺:mo|䃻:ba|䃽:fu|䃾:bi|䄀:huo|䄁:yi|䄂:liu|䄅:juan|䄆:huo|䄇:cheng|䄈:dou|䄉:e|䄋:yan|䄌:zhui|䄍:du|䄎:qi|䄏:yu|䄐:quan|䄑:huo|䄒:nie|䄓:heng|䄔:ju|䄕:she|䄘:peng|䄙:ming|䄚:cao|䄛:lou|䄜:li|䄝:chun|䄟:cui|䄠:shan|䄢:qi|䄤:lai|䄥:ling|䄦:liao|䄧:reng|䄨:yu|䄩:nao|䄪:chuo|䄫:qi|䄬:yi|䄭:nian|䄯:jian|䄰:ya|䄲:chui|䄶:bi|䄷:dan|䄸:po|䄹:nian|䄺:zhi|䄻:chao|䄼:tian|䄽:tian|䄾:rou|䄿:yi|䅀:lie|䅁:an|䅂:he|䅃:qiong|䅄:li|䅆:zi|䅇:su|䅈:yuan|䅉:ya|䅊:du|䅋:wan|䅍:dong|䅎:you|䅏:hui|䅐:jian|䅑:rui|䅒:mang|䅓:ju|䅖:an|䅗:sui|䅘:lai|䅙:hun|䅚:qiang|䅜:duo|䅞:na|䅟:can|䅠:ti|䅡:xu|䅢:jiu|䅣:huang|䅤:qi|䅥:jie|䅦:mao|䅧:yan|䅩:zhi|䅪:tui|䅬:ai|䅭:pang|䅮:cang|䅯:tang|䅰:en|䅱:hun|䅲:qi|䅳:chu|䅴:suo|䅵:zhuo|䅶:nou|䅷:tu|䅸:zu|䅹:lou|䅺:miao|䅻:li|䅼:man|䅽:gu|䅾:cen|䅿:hua|䆀:mei|䆂:lian|䆃:dao|䆄:shan|䆅:ci|䆈:zhi|䆉:ba|䆊:cui|䆋:qiu|䆍:chi|䆏:fei|䆐:guo|䆑:cheng|䆒:jiu|䆓:e|䆕:jue|䆖:hong|䆗:jiao|䆘:cuan|䆙:yao|䆚:tong|䆛:cha|䆜:you|䆝:shu|䆞:yao|䆟:ge|䆠:huan|䆡:lang|䆢:jue|䆣:chen|䆦:shen|䆨:ming|䆩:ming|䆫:chuang|䆬:yun|䆮:jin|䆯:chuo|䆱:tan|䆳:qiong|䆵:cheng|䆷:yu|䆸:cheng|䆹:tong|䆻:qiao|䆽:ju|䆾:lan|䆿:yi|䇀:rong|䇃:si|䇅:fa|䇇:meng|䇈:gui|䇋:hai|䇌:qiao|䇍:chuo|䇎:que|䇏:dui|䇐:li|䇑:ba|䇒:jie|䇔:luo|䇖:yun|䇘:hu|䇙:yin|䇛:zhi|䇜:lian|䇞:gan|䇟:jian|䇠:zhou|䇡:zhu|䇢:ku|䇣:na|䇤:dui|䇥:ze|䇦:yang|䇧:zhu|䇨:gong|䇩:yi|䇬:chuang|䇭:lao|䇮:ren|䇯:rong|䇱:na|䇲:ce|䇵:yi|䇶:jue|䇷:bi|䇸:cheng|䇹:jun|䇺:chou|䇻:hui|䇼:chi|䇽:zhi|䇾:ying|䈁:lun|䈂:bing|䈃:zhao|䈄:han|䈅:yu|䈆:dai|䈇:zhao|䈈:fei|䈉:sha|䈊:ling|䈋:ta|䈍:mang|䈎:ye|䈏:bao|䈐:kui|䈑:gua|䈒:nan|䈓:ge|䈕:chi|䈗:suo|䈘:ci|䈙:zhou|䈚:tai|䈛:kuai|䈜:qin|䈞:du|䈟:ce|䈠:huan|䈢:sai|䈣:zheng|䈤:qian|䈧:wei|䈪:xi|䈫:na|䈬:pu|䈭:huai|䈮:ju|䈲:pan|䈳:ta|䈴:qian|䈶:rong|䈷:luo|䈸:hu|䈹:sou|䈻:pu|䈼:mie|䈾:shuo|䈿:mai|䉀:shu|䉁:ling|䉂:lei|䉃:jiang|䉄:leng|䉅:zhi|䉆:diao|䉈:san|䉉:hu|䉊:fan|䉋:mei|䉌:sui|䉍:jian|䉎:tang|䉏:xie|䉑:mo|䉒:fan|䉓:lei|䉕:ceng|䉖:ling|䉘:cong|䉙:yun|䉚:meng|䉛:yu|䉜:zhi|䉝:qi|䉞:dan|䉟:huo|䉠:wei|䉡:tan|䉢:se|䉣:xie|䉤:sou|䉥:song|䉧:liu|䉨:yi|䉪:lei|䉫:li|䉬:fei|䉭:lie|䉮:lin|䉯:xian|䉰:yao|䉲:bie|䉳:xian|䉴:rang|䉵:zhuan|䉷:dan|䉸:bian|䉹:ling|䉺:hong|䉻:qi|䉼:liao|䉽:ban|䉾:mi|䉿:hu|䊀:hu|䊂:ce|䊃:pei|䊄:qiong|䊅:ming|䊆:jiu|䊇:bu|䊈:mei|䊉:san|䊊:mei|䊍:li|䊎:quan|䊐:en|䊑:xiang|䊓:shi|䊖:lan|䊗:huang|䊘:jiu|䊙:yan|䊛:sa|䊜:tuan|䊝:xie|䊞:zhe|䊟:men|䊠:xi|䊡:man|䊣:huang|䊤:tan|䊥:xiao|䊦:ya|䊧:bi|䊨:luo|䊩:fan|䊪:li|䊫:cui|䊬:cha|䊭:chou|䊮:di|䊯:kuang|䊰:chu|䊲:chan|䊳:mi|䊴:qian|䊵:qiu|䊶:zhen|䊺:gu|䊻:yan|䊼:chi|䊽:guai|䊾:mu|䊿:bo|䋀:kua|䋁:geng|䋂:yao|䋃:mao|䋄:wang|䋈:ru|䋉:jue|䋋:min|䋌:jiang|䋎:zhan|䋏:zuo|䋐:yue|䋑:bing|䋓:zhou|䋔:bi|䋕:ren|䋖:yu|䋘:chuo|䋙:er|䋚:yi|䋛:mi|䋜:qing|䋞:wang|䋟:ji|䋠:bu|䋢:bie|䋣:fan|䋤:yao|䋥:li|䋦:fan|䋧:qu|䋨:fu|䋩:er|䋭:huo|䋮:jin|䋯:qi|䋰:ju|䋱:lai|䋲:che|䋳:bei|䋴:niu|䋵:yi|䋶:xu|䋷:liu|䋸:xun|䋹:fu|䋻:nin|䋼:ting|䋽:beng|䋾:zha|䌂:ou|䌃:shuo|䌄:geng|䌅:tang|䌆:gui|䌇:hui|䌈:ta|䌊:yao|䌌:qi|䌍:han|䌎:lve|䌏:mi|䌐:mi|䌒:lu|䌓:fan|䌔:ou|䌕:mi|䌖:jie|䌗:fu|䌘:mi|䌙:huang|䌚:su|䌛:yao|䌜:nie|䌝:jin|䌞:lian|䌟:bi|䌠:qing|䌡:ti|䌢:ling|䌣:zuan|䌤:zhi|䌥:yin|䌦:dao|䌧:chou|䌨:cai|䌩:mi|䌪:yan|䌫:lan|䌬:chong|䌯:guan|䌰:she|䌱:luo|䌴:luo|䌵:zhu|䌷:chou|䌸:juan|䌹:jiong|䌺:er|䌻:yi|䌼:rui|䌽:cai|䌾:ren|䌿:fu|䍀:lan|䍁:sui|䍂:yu|䍃:yao|䍄:dian|䍅:ling|䍆:zhu|䍇:ta|䍈:ping|䍉:qian|䍊:jue|䍋:chui|䍌:bu|䍍:gu|䍎:cun|䍐:han|䍑:han|䍒:mou|䍓:hu|䍔:hong|䍕:di|䍖:fu|䍗:xuan|䍘:mi|䍙:mei|䍚:lang|䍛:gu|䍜:zhao|䍝:ta|䍞:yu|䍟:zong|䍠:li|䍡:liao|䍢:wu|䍣:lei|䍤:zhang|䍥:lei|䍦:li|䍨:bo|䍩:ang|䍪:kui|䍫:tuo|䍮:zhao|䍯:gui|䍱:xu|䍲:nai|䍳:chuo|䍴:duo|䍶:dong|䍷:gui|䍸:bo|䍺:huan|䍻:xuan|䍼:can|䍽:li|䍾:tui|䍿:huang|䎀:xue|䎁:hu|䎂:bao|䎃:ran|䎄:tiao|䎅:fu|䎆:liao|䎈:yi|䎉:shu|䎊:po|䎋:he|䎌:cu|䎎:na|䎏:an|䎐:chao|䎑:lu|䎒:zhan|䎓:ta|䎗:qiao|䎘:su|䎚:guan|䎝:chu|䎟:er|䎠:er|䎡:nuan|䎢:qi|䎣:si|䎤:chu|䎦:yan|䎧:bang|䎨:an|䎪:ne|䎫:chuang|䎬:ba|䎮:ti|䎯:han|䎰:zuo|䎱:zuan|䎲:zhe|䎳:wa|䎴:sheng|䎵:bi|䎶:er|䎷:zhu|䎸:wu|䎹:wen|䎺:zhi|䎻:zhou|䎼:lu|䎽:wen|䎾:gun|䎿:qiu|䏀:la|䏁:zai|䏂:sou|䏃:mian|䏄:zhi|䏅:qi|䏆:cao|䏇:piao|䏈:lian|䏊:long|䏋:su|䏌:qi|䏍:yuan|䏎:feng|䏐:jue|䏑:di|䏒:pian|䏓:guan|䏔:niu|䏕:ren|䏖:zhen|䏗:gai|䏘:pi|䏙:tan|䏚:chao|䏛:chun|䏝:chun|䏞:mo|䏟:bie|䏠:qi|䏡:shi|䏢:bi|䏣:jue|䏤:si|䏦:hua|䏧:na|䏨:hui|䏪:er|䏬:mou|䏮:xi|䏯:zhi|䏰:ren|䏱:ju|䏲:zhou|䏳:zhe|䏴:shao|䏵:meng|䏶:bi|䏷:han|䏸:yu|䏹:xian|䏻:neng|䏼:can|䏽:bu|䏿:qi|䐀:ji|䐁:niao|䐂:lu|䐃:jiong|䐄:han|䐅:yi|䐆:cai|䐇:chun|䐈:zhi|䐉:zi|䐊:da|䐌:tian|䐍:zhou|䐏:chun|䐑:zhe|䐓:rou|䐔:bin|䐕:ji|䐖:yi|䐗:du|䐘:jue|䐙:ge|䐚:ji|䐝:suo|䐞:ruo|䐟:xiang|䐠:huang|䐡:qi|䐢:zhu|䐣:cuo|䐤:chi|䐥:weng|䐧:kao|䐨:gu|䐩:kai|䐪:fan|䐬:cao|䐭:zhi|䐮:chan|䐯:lei|䐲:zhe|䐳:yu|䐴:gui|䐵:huang|䐶:jin|䐸:guo|䐹:sao|䐺:tan|䐼:xi|䐽:man|䐾:duo|䐿:ao|䑀:pi|䑁:wu|䑂:ai|䑃:meng|䑄:pi|䑅:meng|䑆:yang|䑇:zhi|䑈:bo|䑉:ying|䑊:wei|䑋:nao|䑌:lan|䑍:yan|䑎:chan|䑏:quan|䑐:zhen|䑑:pu|䑓:tai|䑔:fei|䑕:xun|䑗:dang|䑘:cha|䑙:ran|䑚:tian|䑛:chi|䑜:ta|䑝:jia|䑞:shun|䑟:huang|䑠:liao|䑤:jin|䑥:e|䑧:fu|䑨:duo|䑪:e|䑬:yao|䑭:di|䑯:di|䑰:bu|䑱:man|䑲:che|䑳:lun|䑴:qi|䑵:mu|䑶:can|䑻:you|䑽:da|䑿:su|䒀:fu|䒁:ji|䒂:jiang|䒃:cao|䒄:bo|䒅:teng|䒆:che|䒇:fu|䒈:bu|䒉:wu|䒋:yang|䒌:ming|䒍:pang|䒎:mang|䒐:meng|䒑:cao|䒒:tiao|䒓:kai|䒔:bai|䒕:xiao|䒖:xin|䒗:qi|䒚:shao|䒛:heng|䒜:niu|䒝:xiao|䒞:chen|䒠:fan|䒡:yin|䒢:ang|䒣:ran|䒤:ri|䒥:fa|䒦:fan|䒧:qu|䒨:shi|䒩:he|䒪:bian|䒫:dai|䒬:mo|䒭:deng|䒲:cha|䒳:duo|䒴:you|䒵:hao|䒸:xian|䒹:lei|䒺:jin|䒻:qi|䒽:mei|䓂:yan|䓃:yi|䓄:yin|䓅:qi|䓆:zhe|䓇:xi|䓈:yi|䓉:ye|䓊:e|䓌:zhi|䓍:han|䓎:chuo|䓐:chun|䓑:bing|䓒:kuai|䓓:chou|䓕:tuo|䓖:qiong|䓘:jiu|䓚:cu|䓛:fu|䓝:meng|䓞:li|䓟:lie|䓠:ta|䓢:gu|䓣:liang|䓥:la|䓦:dian|䓧:ci|䓫:ji|䓭:cha|䓮:mao|䓯:du|䓱:chai|䓲:rui|䓳:hen|䓴:ruan|䓶:lai|䓷:xing|䓹:yi|䓺:mei|䓼:he|䓽:ji|䓿:han|䔁:li|䔂:zi|䔃:zu|䔄:yao|䔆:li|䔇:qi|䔈:gan|䔉:li|䔎:su|䔏:chou|䔑:xie|䔒:bei|䔓:xu|䔔:jing|䔕:pu|䔖:ling|䔗:xiang|䔘:zuo|䔙:diao|䔚:chun|䔛:qing|䔜:nan|䔞:lv|䔟:chi|䔠:shao|䔡:yu|䔢:hua|䔣:li|䔧:li|䔪:dui|䔬:yi|䔭:ning|䔯:hu|䔰:fu|䔲:cheng|䔳:nan|䔴:ce|䔶:ti|䔷:qin|䔸:biao|䔹:sui|䔺:wei|䔼:se|䔽:ai|䔾:e|䔿:jie|䕀:kuan|䕁:fei|䕃:yin|䕅:sao|䕆:dou|䕇:hui|䕈:xie|䕉:ze|䕊:tan|䕋:chang|䕌:zhi|䕍:yi|䕎:fu|䕏:e|䕑:jun|䕓:cha|䕔:xian|䕕:man|䕗:bi|䕘:ling|䕙:jie|䕚:kui|䕛:jia|䕞:lang|䕠:fei|䕡:lu|䕢:zha|䕣:he|䕥:ni|䕦:ying|䕧:xiao|䕨:teng|䕩:lao|䕪:ze|䕫:kui|䕭:jiang|䕮:ju|䕯:jiang|䕰:ban|䕱:dou|䕲:lin|䕳:mi|䕴:zhuo|䕵:xie|䕶:hu|䕷:mi|䕹:za|䕺:cong|䕻:ge|䕼:nan|䕽:zhu|䕾:yan|䕿:han|䖁:yi|䖂:luan|䖃:yue|䖄:ran|䖅:ling|䖆:niang|䖇:yu|䖈:nve|䖊:yi|䖋:nve|䖌:qin|䖍:qian|䖎:xia|䖏:chu|䖐:jin|䖑:mi|䖓:na|䖔:han|䖕:zu|䖖:xia|䖗:yan|䖘:tu|䖛:suo|䖜:yin|䖝:chong|䖞:zhou|䖟:mang|䖠:yuan|䖡:nv|䖢:miao|䖣:sao|䖤:wan|䖥:li|䖧:na|䖨:shi|䖩:bi|䖪:ci|䖫:bang|䖭:juan|䖮:xiang|䖯:gui|䖰:pai|䖲:xun|䖳:zha|䖴:yao|䖸:e|䖹:yang|䖺:tiao|䖻:you|䖼:jue|䖽:li|䖿:li|䗁:ji|䗂:hu|䗃:zhan|䗄:fu|䗅:chang|䗆:guan|䗇:ju|䗈:meng|䗊:cheng|䗋:mou|䗍:li|䗑:yi|䗒:bing|䗔:hou|䗕:wan|䗖:chi|䗘:ge|䗙:han|䗚:bo|䗜:liu|䗝:can|䗞:can|䗟:yi|䗠:xuan|䗡:yan|䗢:suo|䗣:gao|䗤:yong|䗨:yu|䗪:zhe|䗫:ma|䗮:shuang|䗯:jin|䗰:guan|䗱:pu|䗲:lin|䗴:ting|䗶:la|䗷:yi|䗹:ci|䗺:yan|䗻:jie|䗽:wei|䗾:xian|䗿:ning|䘀:fu|䘁:ge|䘃:mo|䘄:fu|䘅:nai|䘆:xian|䘇:wen|䘈:li|䘉:can|䘊:mie|䘌:ni|䘍:chai|䘏:xu|䘐:nv|䘑:mai|䘓:kan|䘕:hang|䘘:yu|䘙:wei|䘚:zhu|䘝:yi|䘠:fu|䘡:bi|䘢:zhu|䘣:zi|䘤:shu|䘥:xia|䘦:ni|䘨:jiao|䘩:xuan|䘫:nou|䘬:rong|䘭:die|䘮:sa|䘱:yu|䘵:lu|䘶:han|䘸:yi|䘹:zui|䘺:zhan|䘻:su|䘼:wan|䘽:ni|䘾:guan|䘿:jue|䙀:beng|䙁:can|䙃:duo|䙄:qi|䙅:yao|䙆:gui|䙇:nuan|䙈:hou|䙉:xun|䙊:xie|䙌:hui|䙎:xie|䙏:bo|䙐:ke|䙒:xu|䙓:bai|䙕:chu|䙗:ti|䙘:chu|䙙:chi|䙚:niao|䙛:guan|䙜:feng|䙝:xie|䙟:duo|䙠:jue|䙡:hui|䙢:zeng|䙣:sa|䙤:duo|䙥:ling|䙦:meng|䙨:guo|䙩:meng|䙪:long|䙬:ying|䙮:guan|䙯:cu|䙰:li|䙱:du|䙳:e|䙷:de|䙸:de|䙹:jiang|䙺:lian|䙼:shao|䙽:xi|䙿:wei|䚂:he|䚃:you|䚄:lu|䚅:lai|䚆:ou|䚇:sheng|䚈:juan|䚉:qi|䚋:yun|䚍:qi|䚏:leng|䚐:ji|䚑:mai|䚒:chuang|䚓:nian|䚕:li|䚖:ling|䚘:chen|䚚:xian|䚛:hu|䚝:zu|䚞:dai|䚟:dai|䚠:hun|䚢:che|䚣:ti|䚥:nuo|䚦:zhi|䚧:liu|䚨:fei|䚩:jiao|䚫:ao|䚬:lin|䚮:reng|䚯:tao|䚰:pi|䚱:xin|䚲:shan|䚳:xie|䚴:wa|䚵:tao|䚷:xi|䚸:xie|䚹:pi|䚺:yao|䚻:yao|䚼:nv|䚽:hao|䚾:nin|䚿:yin|䛀:fan|䛁:nan|䛂:chi|䛃:wang|䛄:yuan|䛅:xia|䛆:zhou|䛇:yuan|䛈:shi|䛉:mi|䛋:ge|䛌:pao|䛍:fei|䛎:hu|䛏:ni|䛐:ci|䛑:mi|䛒:bian|䛔:na|䛕:yu|䛖:e|䛗:zhi|䛘:nin|䛙:xu|䛚:lve|䛛:hui|䛜:xun|䛝:nao|䛞:han|䛟:jia|䛠:dou|䛡:hua|䛤:cu|䛥:xi|䛦:song|䛧:mi|䛨:xin|䛩:wu|䛪:qiong|䛫:zheng|䛬:chou|䛭:xing|䛮:jiu|䛯:ju|䛰:hun|䛱:ti|䛲:man|䛳:jian|䛴:qi|䛵:shou|䛶:lei|䛷:wan|䛸:che|䛹:can|䛺:jie|䛻:you|䛼:hui|䛽:zha|䛾:su|䛿:ge|䜀:nao|䜁:xi|䜄:chi|䜅:wei|䜆:mo|䜇:gun|䜊:zao|䜋:hui|䜌:luan|䜍:liao|䜎:lao|䜑:qia|䜒:ao|䜓:nie|䜔:sui|䜕:mai|䜖:tan|䜗:xin|䜘:jing|䜙:an|䜚:ta|䜛:chan|䜜:wei|䜝:tuan|䜞:ji|䜟:chen|䜠:che|䜡:xu|䜢:xian|䜣:xin|䜧:nao|䜩:yan|䜪:qiu|䜫:hong|䜬:song|䜭:jun|䜮:liao|䜯:ju|䜱:man|䜲:lie|䜴:chu|䜵:chi|䜶:xiang|䜸:mei|䜹:shu|䜺:ce|䜻:chi|䜼:gu|䜽:yu|䝀:liao|䝁:lao|䝂:shu|䝃:zhe|䝈:e|䝊:sha|䝋:zong|䝌:jue|䝍:jun|䝏:lou|䝐:wei|䝒:zhu|䝓:la|䝕:zhe|䝖:zhao|䝘:yi|䝚:ni|䝝:yi|䝞:hao|䝟:ya|䝠:huan|䝡:man|䝢:man|䝣:qu|䝤:lao|䝥:hao|䝧:men|䝨:xian|䝩:zhen|䝪:shu|䝫:zuo|䝬:zhu|䝭:gou|䝮:xuan|䝯:yi|䝰:ti|䝲:jin|䝳:can|䝵:bu|䝶:liang|䝷:zhi|䝸:ji|䝹:wan|䝺:guan|䝼:qing|䝽:ai|䝾:fu|䝿:gui|䞀:gou|䞁:xian|䞂:ruan|䞃:zhi|䞄:biao|䞅:yi|䞆:suo|䞇:die|䞈:gui|䞉:sheng|䞊:xun|䞋:chen|䞌:she|䞍:qing|䞐:chun|䞑:hong|䞒:dong|䞓:cheng|䞔:wei|䞕:die|䞖:shu|䞘:ji|䞙:za|䞚:qi|䞜:fu|䞝:ao|䞞:fu|䞟:po|䞡:tan|䞢:zha|䞣:che|䞤:qu|䞥:you|䞦:he|䞧:hou|䞨:gui|䞩:e|䞪:jiang|䞫:yun|䞬:tou|䞭:qiu|䞯:fu|䞰:zuo|䞱:hu|䞳:bo|䞵:jue|䞶:di|䞷:jue|䞸:fu|䞹:huang|䞻:yong|䞼:chui|䞽:suo|䞾:chi|䟂:man|䟃:ca|䟄:qi|䟅:jian|䟆:bi|䟈:zhi|䟉:zhu|䟊:qu|䟋:zhan|䟌:ji|䟍:dian|䟏:li|䟐:li|䟑:la|䟒:quan|䟔:fu|䟕:cha|䟖:tang|䟗:shi|䟘:hang|䟙:qie|䟚:qi|䟛:bo|䟜:na|䟝:tou|䟞:chu|䟟:cu|䟠:yue|䟡:di|䟢:chen|䟣:chu|䟤:bi|䟥:mang|䟦:ba|䟧:tian|䟨:min|䟩:lie|䟪:feng|䟬:qiu|䟭:tiao|䟮:fu|䟯:kuo|䟰:jian|䟴:zhen|䟵:qiu|䟶:cuo|䟷:chi|䟸:kui|䟹:lie|䟺:bang|䟻:du|䟼:wu|䟾:jue|䟿:lu|䠀:chang|䠂:chu|䠃:liang|䠄:tian|䠅:kun|䠆:chang|䠇:jue|䠈:tu|䠉:hua|䠊:fei|䠋:bi|䠍:qia|䠎:wo|䠏:ji|䠐:qu|䠑:kui|䠒:hu|䠓:cu|䠔:sui|䠗:qiu|䠘:pi|䠙:bei|䠚:wa|䠛:jiao|䠜:rong|䠞:cu|䠟:die|䠠:chi|䠡:cuo|䠢:meng|䠣:xuan|䠤:duo|䠥:bie|䠦:zhe|䠧:chu|䠨:chan|䠩:gui|䠪:duan|䠫:zou|䠬:deng|䠭:lai|䠮:teng|䠯:yue|䠰:quan|䠱:shu|䠲:ling|䠴:qin|䠵:fu|䠶:she|䠷:tiao|䠹:ai|䠻:qiong|䠼:diao|䠽:hai|䠾:shan|䠿:wai|䡀:zhan|䡁:long|䡂:jiu|䡃:li|䡅:min|䡆:rong|䡇:yue|䡈:jue|䡉:kang|䡊:fan|䡋:qi|䡌:hong|䡍:fu|䡎:lu|䡏:hong|䡐:tuo|䡑:min|䡒:tian|䡓:juan|䡔:qi|䡕:zheng|䡖:jing|䡗:gong|䡘:tian|䡙:lang|䡚:mao|䡛:yin|䡜:lu|䡝:yun|䡞:ju|䡟:pi|䡡:xie|䡢:bian|䡥:rong|䡦:sang|䡧:wu|䡨:cha|䡩:gu|䡪:chan|䡫:peng|䡬:man|䡮:ran|䡯:shuang|䡰:keng|䡱:zhuan|䡲:chan|䡴:chuang|䡵:sui|䡶:bei|䡷:kai|䡹:zhi|䡺:wei|䡻:min|䡼:ling|䡾:nei|䡿:ling|䢀:qi|䢁:yue|䢃:yi|䢄:xi|䢅:chen|䢇:rong|䢈:chen|䢉:nong|䢊:you|䢋:ji|䢌:bo|䢍:fang|䢐:cu|䢑:di|䢓:yu|䢔:ge|䢕:xu|䢖:lv|䢗:he|䢙:bai|䢚:gong|䢛:jiong|䢝:ya|䢞:nu|䢟:you|䢠:song|䢡:xie|䢢:cang|䢣:yao|䢤:shu|䢥:yan|䢦:shuai|䢧:liao|䢩:yu|䢪:lie|䢫:sui|䢭:yan|䢮:lei|䢯:lin|䢰:tai|䢱:du|䢲:yue|䢳:ji|䢵:yun|䢹:ju|䢻:chen|䢽:xiang|䢾:xian|䣀:gui|䣁:yu|䣂:lei|䣄:tu|䣅:chen|䣆:xing|䣇:qiu|䣈:hang|䣊:dang|䣋:cai|䣌:di|䣍:yan|䣑:chan|䣓:li|䣔:suo|䣕:ma|䣖:ma|䣘:tang|䣙:pei|䣚:lou|䣜:cuo|䣝:tu|䣞:e|䣟:can|䣠:jie|䣡:ti|䣢:ji|䣣:dang|䣤:jiao|䣥:bi|䣦:lei|䣧:yi|䣨:chun|䣩:chun|䣪:po|䣫:li|䣬:zai|䣭:tai|䣮:po|䣯:tian|䣰:ju|䣱:xu|䣲:fan|䣴:xu|䣵:er|䣶:huo|䣸:ran|䣹:fa|䣼:liang|䣽:ti|䣾:mi|䤁:cen|䤂:mei|䤃:yin|䤄:mian|䤅:tu|䤆:kui|䤉:mi|䤊:rong|䤋:guo|䤍:mi|䤎:ju|䤏:pi|䤐:jin|䤑:wang|䤒:ji|䤓:meng|䤔:jian|䤕:xue|䤖:bao|䤗:gan|䤘:chan|䤙:li|䤚:li|䤛:qiu|䤜:dun|䤝:ying|䤞:yun|䤟:chen|䤠:ji|䤡:ran|䤣:lve|䤥:gui|䤦:yue|䤧:hui|䤨:pi|䤩:cha|䤪:duo|䤫:chan|䤭:kuan|䤮:she|䤯:xing|䤰:weng|䤱:shi|䤲:chi|䤳:ye|䤴:han|䤵:fei|䤶:ye|䤷:yan|䤸:zuan|䤺:yin|䤻:duo|䤼:xian|䤿:qie|䥀:chan|䥁:han|䥂:meng|䥃:yue|䥄:cu|䥅:qian|䥆:jin|䥇:shan|䥈:mu|䥌:zheng|䥍:zhi|䥎:chun|䥏:yu|䥐:mou|䥑:wan|䥒:chou|䥔:su|䥕:pie|䥖:tian|䥗:kuan|䥘:cu|䥙:sui|䥛:jie|䥜:jian|䥝:ao|䥞:jiao|䥟:ye|䥡:ye|䥢:long|䥣:zao|䥤:bao|䥥:lian|䥧:huan|䥨:lv|䥩:wei|䥪:xian|䥫:tie|䥬:bo|䥭:zheng|䥮:zhu|䥯:ba|䥰:meng|䥱:xie|䥵:xiao|䥶:li|䥷:zha|䥸:mi|䥺:ye|䥾:xie|䦂:shan|䦅:shan|䦆:jue|䦇:ji|䦈:fang|䦊:niao|䦋:ao|䦌:chu|䦍:wu|䦎:guan|䦏:xie|䦐:ting|䦑:xie|䦒:dang|䦔:tan|䦖:xia|䦗:xu|䦘:bi|䦙:si|䦚:huo|䦛:zheng|䦜:wu|䦞:run|䦟:chuai|䦠:shi|䦡:huan|䦢:kuo|䦣:fu|䦤:chuai|䦥:xian|䦦:qin|䦧:qie|䦨:lan|䦪:ya|䦬:que|䦮:chun|䦯:zhi|䦱:kui|䦲:qian|䦳:hang|䦴:yi|䦵:ni|䦶:zheng|䦷:chuai|䦹:shi|䦻:ci|䦼:jue|䦽:xu|䦾:yun|䧁:chu|䧂:dao|䧃:dian|䧄:ge|䧅:ti|䧆:hong|䧇:ni|䧉:li|䧋:xian|䧍:xi|䧎:xuan|䧒:lai|䧔:mu|䧕:cheng|䧖:jian|䧗:bi|䧘:qi|䧙:ling|䧚:cong|䧛:bang|䧜:tang|䧝:di|䧞:fu|䧟:xian|䧠:shuan|䧤:pu|䧥:hui|䧦:wei|䧧:yi|䧨:ye|䧪:che|䧫:hao|䧮:xian|䧯:chan|䧰:hun|䧲:han|䧳:ci|䧵:qi|䧶:kui|䧷:rou|䧺:xiong|䧼:hu|䧽:cui|䧿:que|䨀:di|䨁:che|䨄:yan|䨅:liao|䨆:bi|䨋:nve|䨌:bao|䨍:ying|䨎:hong|䨏:ci|䨐:qia|䨑:ti|䨒:yu|䨓:lei|䨔:bao|䨖:ji|䨗:fu|䨘:xian|䨙:cen|䨛:se|䨞:yu|䨠:ai|䨡:han|䨢:dan|䨣:ge|䨤:di|䨥:hu|䨦:pang|䨩:ling|䨪:mai|䨫:mai|䨬:lian|䨮:xue|䨯:zhen|䨰:po|䨱:fu|䨲:nou|䨳:xi|䨴:dui|䨵:dan|䨶:yun|䨷:xian|䨸:yin|䨺:dui|䨻:beng|䨼:hu|䨽:fei|䨾:fei|䨿:qian|䩀:bei|䩃:shi|䩄:tian|䩅:zhan|䩆:jian|䩈:hui|䩉:fu|䩊:wan|䩋:mo|䩌:qiao|䩍:liao|䩏:mie|䩐:ge|䩑:hong|䩒:yu|䩓:qi|䩔:duo|䩕:ang|䩗:ba|䩘:di|䩙:xuan|䩚:di|䩛:bi|䩜:zhou|䩝:pao|䩞:nian|䩟:yi|䩡:jia|䩢:da|䩣:duo|䩤:xi|䩥:dan|䩦:tiao|䩧:xie|䩨:chang|䩩:yuan|䩪:guan|䩫:liang|䩬:beng|䩮:lu|䩯:ji|䩰:xuan|䩱:shu|䩳:shu|䩴:hu|䩵:yun|䩶:chan|䩸:rong|䩹:e|䩻:ba|䩼:feng|䩾:zhe|䩿:fen|䪀:guan|䪁:bu|䪂:ge|䪄:huang|䪅:du|䪆:ti|䪇:bo|䪈:qian|䪉:la|䪊:long|䪋:wei|䪌:zhan|䪍:lan|䪏:na|䪐:bi|䪑:tuo|䪒:jiao|䪔:bu|䪕:ju|䪖:po|䪗:xia|䪘:wei|䪙:fu|䪚:he|䪛:fan|䪜:chan|䪝:hu|䪞:za|䪤:fan|䪥:die|䪦:hong|䪧:chi|䪨:bao|䪩:yin|䪬:bo|䪭:ruan|䪮:chou|䪯:ying|䪱:gai|䪳:yun|䪴:zhen|䪵:ya|䪷:hou|䪸:min|䪹:pei|䪺:ge|䪻:bian|䪽:hao|䪾:mi|䪿:sheng|䫀:gen|䫁:bi|䫂:duo|䫃:chun|䫄:chua|䫅:san|䫆:cheng|䫇:ran|䫈:zen|䫉:mao|䫊:bo|䫋:tui|䫌:pi|䫍:fu|䫐:lin|䫒:men|䫓:wu|䫔:qi|䫕:zhi|䫖:chen|䫗:xia|䫘:he|䫙:sang|䫛:hou|䫝:fu|䫞:rao|䫟:hun|䫠:pei|䫡:qian|䫣:xi|䫤:ming|䫥:kui|䫦:ge|䫨:ao|䫩:san|䫪:shuang|䫫:lou|䫬:zhen|䫭:hui|䫮:can|䫰:lin|䫱:na|䫲:han|䫳:du|䫴:jin|䫵:mian|䫶:fan|䫷:e|䫸:nao|䫹:hong|䫺:hong|䫻:xue|䫼:xue|䫾:bi|䬀:you|䬁:yi|䬂:xue|䬃:sa|䬄:yu|䬅:li|䬆:li|䬇:yuan|䬈:dui|䬉:hao|䬊:qie|䬋:leng|䬎:guo|䬏:bu|䬐:wei|䬑:wei|䬓:an|䬔:xu|䬕:shang|䬖:heng|䬗:yang|䬙:yao|䬛:lu|䬝:heng|䬞:tao|䬟:liu|䬡:zhu|䬣:qi|䬤:chao|䬥:yi|䬦:dou|䬧:yuan|䬨:cu|䬪:bo|䬫:can|䬬:yang|䬮:yi|䬯:nian|䬰:shao|䬱:ben|䬳:ban|䬴:mo|䬵:ai|䬶:en|䬷:she|䬹:zhi|䬺:yang|䬻:jian|䬼:yuan|䬽:dui|䬾:ti|䬿:wei|䭀:xun|䭁:zhi|䭂:yi|䭃:ren|䭄:shi|䭅:hu|䭆:ne|䭇:yi|䭈:jian|䭉:sui|䭊:ying|䭋:bao|䭌:hu|䭍:hu|䭎:xie|䭐:yang|䭑:lian|䭓:en|䭕:jian|䭖:zhu|䭗:ying|䭘:yan|䭙:jin|䭚:chuang|䭛:dan|䭝:kuai|䭞:yi|䭟:ye|䭠:jian|䭡:en|䭢:ning|䭣:ci|䭤:qian|䭥:xue|䭦:bo|䭧:mi|䭨:shui|䭩:mi|䭪:liang|䭫:qi|䭬:qi|䭭:shou|䭮:bi|䭯:bo|䭰:beng|䭱:bie|䭲:ni|䭳:wei|䭴:huan|䭵:fan|䭶:qi|䭷:liu|䭸:fu|䭹:ang|䭺:ang|䭼:qi|䭽:qun|䭾:tuo|䭿:yi|䮀:bo|䮁:pian|䮂:bo|䮄:xuan|䮇:yu|䮈:chi|䮉:lu|䮊:yi|䮋:li|䮍:niao|䮎:xi|䮏:wu|䮑:lei|䮓:zhao|䮔:zui|䮕:chuo|䮗:an|䮘:er|䮙:yu|䮚:leng|䮛:fu|䮜:sha|䮝:huan|䮞:chu|䮟:sou|䮡:bi|䮢:die|䮤:di|䮥:li|䮧:han|䮨:zai|䮩:gu|䮪:cheng|䮫:lou|䮬:mo|䮭:chan|䮮:mai|䮯:ao|䮰:dan|䮱:zhu|䮲:huang|䮳:fan|䮴:deng|䮵:tong|䮷:du|䮸:hu|䮹:wei|䮺:ji|䮻:chi|䮼:lin|䮾:pang|䮿:jian|䯀:nie|䯁:luo|䯂:ji|䯅:nie|䯆:yi|䯈:wan|䯉:ya|䯊:qia|䯋:bo|䯍:ling|䯎:gan|䯏:huo|䯐:hai|䯒:heng|䯓:kui|䯔:cen|䯖:lang|䯗:bi|䯘:huan|䯙:po|䯚:ou|䯛:jian|䯜:ti|䯝:sui|䯟:dui|䯠:ao|䯡:jian|䯢:mo|䯣:gui|䯤:kuai|䯥:an|䯦:ma|䯧:qing|䯨:fen|䯪:kao|䯫:hao|䯬:duo|䯮:nai|䯰:jie|䯱:fu|䯲:pa|䯴:chang|䯵:nie|䯶:man|䯸:ci|䯺:kuo|䯼:di|䯽:fu|䯾:tiao|䯿:zu|䰀:wo|䰁:fei|䰂:cai|䰃:peng|䰄:shi|䰆:rou|䰇:qi|䰈:cha|䰉:pan|䰊:bo|䰋:man|䰌:zong|䰍:ci|䰎:gui|䰏:ji|䰐:lan|䰒:meng|䰓:mian|䰔:pan|䰕:lu|䰖:cuan|䰘:liu|䰙:yi|䰚:wen|䰛:li|䰜:li|䰝:zeng|䰞:zhu|䰟:hun|䰠:shen|䰡:chi|䰢:xing|䰣:wang|䰥:huo|䰦:pi|䰨:mei|䰩:che|䰪:mei|䰫:chao|䰬:ju|䰭:nou|䰯:ni|䰰:ru|䰱:ling|䰲:ya|䰴:qi|䰷:bang|䰹:ze|䰺:jie|䰻:yu|䰼:xin|䰽:bei|䰾:ba|䰿:tuo|䱁:qiao|䱂:you|䱃:di|䱄:jie|䱅:mo|䱆:sheng|䱇:shan|䱈:qi|䱉:shan|䱊:mi|䱋:dan|䱌:yi|䱍:geng|䱎:geng|䱏:tou|䱑:xue|䱒:yi|䱓:ting|䱔:tiao|䱕:mou|䱖:liu|䱘:li|䱚:lu|䱛:xu|䱜:cuo|䱝:ba|䱞:liu|䱟:ju|䱠:zhan|䱡:ju|䱣:zu|䱤:xian|䱥:zhi|䱨:zhi|䱫:la|䱭:geng|䱮:e|䱯:mu|䱰:zhong|䱱:di|䱲:yan|䱴:geng|䱶:lang|䱷:yu|䱹:na|䱺:hai|䱻:hua|䱼:zhan|䱾:lou|䱿:chan|䲀:die|䲁:wei|䲂:xuan|䲃:zao|䲄:min|䲊:tuo|䲋:cen|䲌:kuan|䲍:teng|䲎:nei|䲏:lao|䲐:lu|䲑:yi|䲒:xie|䲓:yan|䲔:qing|䲕:pu|䲖:chou|䲗:xian|䲘:guan|䲙:jie|䲚:lai|䲛:meng|䲜:ye|䲞:li|䲟:yin|䲢:teng|䲣:yu|䲦:cha|䲧:du|䲨:hong|䲪:xi|䲬:qi|䲮:yuan|䲯:ji|䲰:yun|䲱:fang|䲳:hang|䲴:zhen|䲵:hu|䲸:jie|䲹:pei|䲺:gan|䲻:xuan|䲽:dao|䲾:qiao|䲿:ci|䳀:die|䳁:ba|䳂:tiao|䳃:wan|䳄:ci|䳅:zhi|䳆:bai|䳇:wu|䳈:bao|䳉:dan|䳊:ba|䳋:tong|䳎:jiu|䳏:gui|䳐:ci|䳑:you|䳒:yuan|䳓:lao|䳔:jiu|䳕:fou|䳖:nei|䳗:e|䳘:e|䳙:xing|䳚:he|䳛:yan|䳜:tu|䳝:bu|䳞:beng|䳟:kou|䳠:chui|䳢:qi|䳣:yuan|䳧:hou|䳨:huang|䳪:juan|䳫:kui|䳬:e|䳭:ji|䳮:mo|䳯:chong|䳰:bao|䳱:wu|䳲:zhen|䳳:xu|䳴:da|䳵:chi|䳷:cong|䳸:ma|䳹:kou|䳺:yan|䳻:can|䳽:he|䳿:lan|䴀:tong|䴁:yu|䴂:hang|䴃:nao|䴄:li|䴅:fen|䴆:pu|䴇:ling|䴈:ao|䴉:xuan|䴊:yi|䴋:xuan|䴌:meng|䴎:lei|䴏:yan|䴐:bao|䴑:die|䴒:ling|䴓:shi|䴔:jiao|䴕:lie|䴖:jing|䴗:ju|䴘:ti|䴙:pi|䴚:gang|䴛:jiao|䴜:huai|䴝:bu|䴞:di|䴟:huan|䴠:yao|䴡:li|䴢:mi|䴦:ren|䴩:piao|䴪:lu|䴫:ling|䴬:yi|䴭:cai|䴮:shan|䴰:shu|䴱:tuo|䴲:mo|䴳:he|䴴:tie|䴵:bing|䴶:peng|䴷:hun|䴹:guo|䴺:bu|䴻:li|䴼:chan|䴽:bai|䴾:cuo|䴿:meng|䵀:suo|䵁:qiang|䵂:zhi|䵃:kuang|䵄:bi|䵅:ao|䵆:meng|䵇:xian|䵉:tou|䵋:wei|䵏:lao|䵐:chan|䵑:ni|䵒:ni|䵓:li|䵔:dong|䵕:ju|䵖:jian|䵗:fu|䵘:sha|䵙:zha|䵚:tao|䵛:jian|䵜:nong|䵝:ya|䵞:jing|䵟:gan|䵠:di|䵡:jian|䵢:mei|䵣:da|䵤:jian|䵥:she|䵦:xie|䵧:zai|䵨:mang|䵩:li|䵪:gun|䵫:yu|䵬:ta|䵭:zhe|䵮:yang|䵯:tuan|䵱:he|䵲:diao|䵳:wei|䵴:yun|䵵:zha|䵶:qu|䵺:ting|䵻:gu|䵽:ca|䵾:fu|䵿:tie|䶀:ta|䶁:ta|䶂:zhuo|䶃:han|䶄:ping|䶅:he|䶇:zhou|䶈:bo|䶉:liu|䶊:nv|䶌:pao|䶍:di|䶎:sha|䶏:ti|䶐:kuai|䶑:ti|䶒:qi|䶓:ji|䶔:chi|䶕:pa|䶖:jin|䶗:ke|䶘:li|䶙:ju|䶚:qu|䶛:la|䶜:gu|䶝:qia|䶞:qi|䶟:xian|䶠:jian|䶡:shi|䶢:xian|䶣:ai|䶤:hua|䶥:ju|䶦:ze|䶧:yao|䶩:ji|䶪:cha|䶫:kan|䶮:yan|䶱:tong|䶲:nan|䶳:yue|䶵:chi';
		$a1 = explode('|', $data);
		$pinyins = array();
		foreach($a1 as $v) {
			$a2 = explode(':', $v);
			$pinyins[$a2[0]] = $a2[1];
		}
	}

	$rs = '';
	for($i = 0; $i < $len; $i++) {
		$o = ord($s[$i]);
		if($o < 0x80) {
			if(($o >= 48 && $o <= 57) || ($o >= 97 && $o <= 122)) {
				$rs .= $s[$i]; // 0-9 a-z
			}elseif($o >= 65 && $o <= 90) {
				$rs .= strtolower($s[$i]); // A-Z
			}else{
				$rs .= '_';
			}
		}else{
			$z = $s[$i].$s[++$i].$s[++$i];
			if(isset($pinyins[$z])) {
				$rs .= $isfirst ? $pinyins[$z][0] : $pinyins[$z];
			}else{
				$rs .= '_';
			}
		}
	}
	return $rs;
}

