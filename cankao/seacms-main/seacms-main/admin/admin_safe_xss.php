<?php 
header('Content-Type:text/html;charset=utf-8');
ob_start();
set_time_limit(0);
error_reporting(0);
require_once(dirname(__FILE__)."/config.php");
CheckPurview();
require_once('../data/common.inc.php'); 
$mysqli = new mysqli($cfg_dbhost,$cfg_dbuser,$cfg_dbpwd,$cfg_dbname,$cfg_dbport);
if ($mysqli->connect_error) {die("数据库连接失败" . $mysqli->connect_error);}

/***********
$tables = $mysqli->query("SHOW TABLES");
 while ($table = $tables->fetch_array()) {
    $tableName = $table[0]; 
    // 获取每个表的所有列
    $columns = $mysqli->query("SHOW FULL COLUMNS FROM `$tableName`");    
    while ($column = $columns->fetch_assoc()) {
        $columnName = $column['Field'];
        $type = $column['Type'];
        // 只处理字符串类型的列
        if (preg_match('/^(enum|set|varchar|text|char|mediumtext|longtext|tinytext)/i', $type)) {           
            $allxxxxx[]=$tableName.'#'.$columnName;
        }
    }
} 
print_r($allxxxxx); die;
**********************/
$Tarray=array(sea_admin999name999id,sea_admin999password999id,sea_admin999loginip999id,sea_arcrank999membername999id,sea_arcrank999purviews999id,sea_cck999ckey999id,sea_cck999uname999id,sea_co_cls999clsname999id,sea_co_config999cname999cid,sea_co_data999tname999v_id,sea_co_data999v_name999v_id,sea_co_data999v_pic999v_id,sea_co_data999v_spic999v_id,sea_co_data999v_gpic999v_id,sea_co_data999v_director999v_id,sea_co_data999v_enname999v_id,sea_co_data999v_lang999v_id,sea_co_data999v_actor999v_id,sea_co_data999v_publishyear999v_id,sea_co_data999v_publisharea999v_id,sea_co_data999v_note999v_id,sea_co_data999v_tags999v_id,sea_co_data999v_from999v_id,sea_co_data999v_inbase999v_id,sea_co_data999v_des999v_id,sea_co_data999v_playdata999v_id,sea_co_data999v_downdata999v_id,sea_co_data999v_jq999v_id,sea_co_data999v_longtxt999v_id,sea_co_filters999Name999ID,sea_co_filters999sFind999ID,sea_co_filters999sStart999ID,sea_co_filters999sEnd999ID,sea_co_filters999sReplace999ID,sea_co_news999n_title999n_id,sea_co_news999n_keyword999n_id,sea_co_news999n_pic999n_id,sea_co_news999n_author999n_id,sea_co_news999n_content999n_id,sea_co_news999n_outline999n_id,sea_co_news999tname999n_id,sea_co_news999n_from999n_id,sea_co_news999n_inbase999n_id,sea_co_news999n_entitle999n_id,sea_co_type999tname999tid,sea_co_type999siteurl999tid,sea_co_type999playfrom999tid,sea_co_type999autocls999tid,sea_co_type999coding999tid,sea_co_type999sock999tid,sea_co_type999listconfig999tid,sea_co_type999itemconfig999tid,sea_co_url999url999uid,sea_co_url999pic999uid,sea_co_url999succ999uid,sea_comment999username999id,sea_comment999ip999id,sea_comment999msg999id,sea_comment999pic999id,sea_content999body999v_id,sea_count999userip999id,sea_count999serverurl999id,sea_count999updatetime999id,sea_crons999type999cronid,sea_crons999name999cronid,sea_crons999filename999cronid,sea_crons999minute999cronid,sea_danmaku_ip999ip999ip,sea_danmaku_list999id999cid,sea_danmaku_list999type999cid,sea_danmaku_list999text999cid,sea_danmaku_list999color999cid,sea_danmaku_list999size999cid,sea_danmaku_list999ip999cid,sea_danmaku_list999user999cid,sea_danmaku_report999id999text,sea_danmaku_report999text999text,sea_danmaku_report999type999text,sea_danmaku_report999ip999text,sea_data999v_name999v_id,sea_data999v_pic999v_id,sea_data999v_spic999v_id,sea_data999v_gpic999v_id,sea_data999v_vip999v_id,sea_data999v_actor999v_id,sea_data999v_publisharea999v_id,sea_data999v_note999v_id,sea_data999v_tags999v_id,sea_data999v_director999v_id,sea_data999v_enname999v_id,sea_data999v_lang999v_id,sea_data999v_extratype999v_id,sea_data999v_jq999v_id,sea_data999v_nickname999v_id,sea_data999v_reweek999v_id,sea_data999v_tvs999v_id,sea_data999v_company999v_id,sea_data999v_ver999v_id,sea_data999v_psd999v_id,sea_data999v_longtxt999v_id,sea_erradd999author999id,sea_erradd999ip999id,sea_erradd999errtxt999id,sea_flink999url999id,sea_flink999webname999id,sea_flink999msg999id,sea_flink999email999id,sea_flink999logo999id,sea_guestbook999title999id,sea_guestbook999uname999id,sea_guestbook999ip999id,sea_guestbook999msg999id,sea_hyzbuy999uname999id,sea_ie999ip999id,sea_jqtype999tname999tid,sea_member999username999id,sea_member999nickname999id,sea_member999password999id,sea_member999email999id,sea_member999regip999id,sea_member999vipendtime999id,sea_member999acode999id,sea_member999repswcode999id,sea_member999msgbody999id,sea_member999pic999id,sea_member_group999gname999gid,sea_member_group999gtype999gid,sea_member_group999g_auth999gid,sea_myad999adname999aid,sea_myad999adenname999aid,sea_myad999intro999aid,sea_myad999adsbody999aid,sea_mytag999tagname999aid,sea_mytag999tagdes999aid,sea_mytag999tagcontent999aid,sea_news999n_title999n_id,sea_news999n_pic999n_id,sea_news999n_spic999n_id,sea_news999n_gpic999n_id,sea_news999n_author999n_id,sea_news999n_entitle999n_id,sea_news999n_outline999n_id,sea_news999n_keyword999n_id,sea_news999n_from999n_id,sea_news999n_content999n_id,sea_playdata999body999v_id,sea_playdata999body1999v_id,sea_search_keywords999keyword999aid,sea_search_keywords999spwords999aid,sea_tags999tag999tagid,sea_tags999vids999tagid,sea_temp999v_name999v_id,sea_temp999v_pic999v_id,sea_temp999v_actor999v_id,sea_temp999v_publishyear999v_id,sea_temp999v_publisharea999v_id,sea_temp999v_note999v_id,sea_temp999v_playdata999v_id,sea_temp999v_des999v_id,sea_temp999v_director999v_id,sea_temp999v_enname999v_id,sea_temp999v_lang999v_id,sea_topic999name999id,sea_topic999enname999id,sea_topic999template999id,sea_topic999pic999id,sea_topic999spic999id,sea_topic999gpic999id,sea_topic999des999id,sea_topic999vod999id,sea_topic999news999id,sea_topic999keyword999id,sea_topic999content999id,sea_type999tname999tid,sea_type999tenname999tid,sea_type999templist999tid,sea_type999templist_1999tid,sea_type999templist_2999tid,sea_type999title999tid,sea_type999keyword999tid,sea_type999description999tid,sea_type999unionid999tid,sea_zyk999zname999zid,sea_zyk999zapi999zid,sea_zyk999zinfo999zid)
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
<script type="text/JavaScript">if(parent.$('admincpnav')) parent.$('admincpnav').innerHTML='后台首页&nbsp;&raquo;&nbsp;工具&nbsp;&raquo;&nbsp;挂马清除工具 ';</script>
<div class="r_main">
  <div class="r_content">
    <div class="r_content_1">
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tb_style">
<tbody><tr class="thead">
<td colspan="5" class="td_title">挂马清除工具</td>
</tr>
</tbody></table>	
</div>

<div style="margin: 20px 20px 20px 20px;
    padding: 5px;
    font-size: 14px;
    line-height: 26px;
    background-color: #e6f2fb;
    border-radius: 2px;
    width: 800px;">
使用本工具前<font style="color:#FF0000">务必备份</font>网站数据库
<br>本工具理论上可以清除全站任意数据中的恶意跳转代码，类似 &lt;script... &nbsp; &lt;iframe...
<br>超低配服务器，可修改后台/admin_safe_xss.php第82行 $pagesize=10000 减少每次执行数
<br>根据数据库大小不同，本操作可能需要较长时间，请耐心等待
</div>
<script>
function loading() {
document.getElementById('loaddiv1').style.visibility = 'hidden';
location.href='admin_safe_xss.php?action=delXSS&page=1&tkey=0';
}
</script>
<?php 
if($action==""){
?>
<div id="loaddiv1">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" class="btn" value="一键清除网站数据库挂马" onclick="loading();" >
</div>

<?php 
}
if($action=="delXSS"){

$pagesize=10000; //每页处理的数据条数


//$page 页数
$page2=$page+1;
$TTT=explode('999',$Tarray[$tkey]); //print_r($TTT);die;
$tableName = $TTT[0]; //数据表
$columnName = $TTT[1]; //字段名称
$indexName = $TTT[2]; //字段名称
$start= ($page-1)*$pagesize;
//$tkey 字段数据数组键值序号key
$tkey2=$tkey+1;
$Cnum = $start + $pagesize; //当前页数据行号
$COLcount=count($Tarray);   //总字段数

$countR=$mysqli->query("SELECT COUNT(*) AS total FROM $tableName"); 
if($countR === FALSE) {die("查询数据总条数失败: " . $conn->error);}
$count = $countR->fetch_array();
$count = $count['total']; 
$pagecount=ceil(intval($count)/$pagesize); //总页数echo $count.'='.$pagecount;
$pagecount2=$pagecount;
if($pagecount==0){$pagecount2=1;}

echo '<font style="color:#00AA00;font-size:14px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;正在清理&nbsp;【'.$tableName.'】-【'.$columnName.'】&nbsp;&nbsp;页数'.$page.'/'.$pagecount2.'&nbsp;&nbsp;行数'.$start.'-'.$Cnum.'/'.$count.'&nbsp;&nbsp;字段'.$tkey2,'/'.$COLcount.'</font><br>';

$sqlA = "SELECT $indexName,$columnName FROM $tableName limit $start,$pagesize";                                 //echo $sqlA.'<br>';
$result = $mysqli->query($sqlA);                                                                                     //print_r($result);die;
while($row = $result->fetch_assoc()) {                                                                               //echo $row[$columnName] .'<br>';	
	if($row[$columnName] !=''){
		$cleanData = preg_replace('/on\w+=".*?"/i', '', $row[$columnName]);
		$cleanData = preg_replace('/<script.*?>.*?<\/script.*?>/is', '', $cleanData);
		$cleanData = preg_replace('/<script.*?\>/is', '', $cleanData);
		$cleanData = preg_replace('/<script.*?\.js"/is', '', $cleanData);
		$cleanData = preg_replace('/<script.*?\.js/is', '', $cleanData);
		$cleanData = preg_replace('/<iframe.*?>.*?<\/iframe.*?>/is', '', $cleanData);
		$cleanData = preg_replace('/<iframe.*?\>/is', '', $cleanData);
		$cleanData = preg_replace('/<iframe.*?\.js"/is', '', $cleanData);
		$cleanData = preg_replace('/<iframe.*?\.js/is', '', $cleanData);	            //echo $cleanData.'<br>';	
		$sqlB="UPDATE $tableName SET $columnName = '$cleanData' WHERE $indexName = '$row[v_id]'";                          //echo $sqlB."\n\r<br>";
		$mysqli->query($sqlB);
		}
}




// 生成下次执行的url
$nextpage='';
if($page < $pagecount){$nextpage='admin_safe_xss.php?action=delXSS&page='.$page2.'&tkey='.$tkey;}
else{$nextpage='admin_safe_xss.php?action=delXSS&page=1&tkey='.$tkey2;}

//跳转到下一页 or 任务结束 跳出
if($tkey2 < $COLcount){
	echo "<script language=\"javascript\">setTimeout(\"Next();\",1000);function Next(){location.href='".$nextpage."';}</script>";
	}
else{
	echo '<font style="color:#00AA00;font-size:14px;"><strong><br><br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;处理完成</strong></font>';
	}

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