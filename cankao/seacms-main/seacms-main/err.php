<?php 
session_start();
require_once("include/common.php");
require_once(sea_INC."/filter.inc.php");
require_once(sea_INC.'/main.class.php');

if($cfg_feedbackstart=='0'){
	showMsg('对不起，报错功能暂时关闭','index.php');
	exit();
}

$id=$_GET['id'];
$id=intval($id);
$row1 = $dsql->GetOne("SELECT v_name FROM `sea_data` WHERE `v_id` = '$id' ORDER BY `v_id` DESC ");
		if(!is_array($row1)){
			showMsg('请勿恶意提交报错数据','index.php');
			exit();
		}
$errtxt2=$_GET['errtxt'];
$id2=$_GET['id'];
if(empty($action)) $action = '';
if($action=='add')
{
	$ip = GetIP();
	$sendtime = time();
	
	//检查验证码是否正确 
if($cfg_feedback_ck=='1')
{	
	$validate = empty($validate) ? '' : strtolower(trim($validate));
	$svali = $_SESSION['sea_ckstr'];
	if($validate=='' || $validate != $svali)
	{
		ResetVdValue();
		ShowMsg('验证码不正确!','-1');
		exit();
	}
}	
	//检查报错间隔时间；
	if(!empty($cfg_feedback_times))
	{
		$row = $dsql->GetOne("SELECT sendtime FROM `sea_erradd` WHERE `ip` = '$ip' ORDER BY `id` DESC ");
		if($sendtime - $row['sendtime'] < $cfg_feedback_times)
		{
			ShowMsg("提交过快，歇会再来报错吧","-1");
			exit();
		}
	}
	
	
$id=$_POST['vid'];
$id = !empty($id) && is_numeric($id) ? $id : 0;
$ip = GetIP();
$author = HtmlReplace($author);
$errtxt = trimMsg(cn_substr($errtxt,2000),1);
$time = time();
	
if(!preg_match("/[".chr(0xa1)."-".chr(0xff)."]/",$errtxt)){
		showMsg('你必需输入中文才能发表!','-1');
		exit();
	}
if($author=='' || $errtxt=='') {
		showMsg('你的名字和报错内容不能为空!','-1');
		exit();
	}

	$query = "INSERT INTO `sea_erradd`(vid,author,ip,errtxt,sendtime,ischeck)
                  VALUES ('$id','$author','$ip','$errtxt','$time',0); ";
	$dsql->ExecuteNoneQuery($query);
	echo '谢谢您对本网站的支持，我们会尽快处理您的报错！';
	exit();
}
?>

<html>
    <head>
    	<title>影片报错</title>
        <script>
			function checkReportErr(){if (document.getElementById('author').value.length<1){alert('请填写报错者');return false;}; if (document.getElementById('errtxt').value.length<1){alert('请填写报错内容');return false;}<?php if($cfg_feedback_ck=='1'){echo "if (document.getElementById('vdcode').value.length<1){alert('请填写验证码');return false;};";} ?>}
        </script>
		<style>
@charset "utf-8";
*{word-wrap:break-word;outline:none}
html,body{height:100%;font-family:"Microsoft YaHei",微软雅黑,"SimSun",宋体;}
body,td,th,input,textarea,select,button{font-size: 12px;color:#333333;font-family:"Microsoft YaHei",微软雅黑,"SimSun",宋体;}
input{ font:12px;}
ul,li,div,form{ margin:0px; padding:0px;}
h1, h2, h3, h4, h5, h6{ font-size:12px; }
.select{font-size:12px;}
label{cursor:pointer} 
textarea, input, select,button{padding:2px;outline: 0;border:1px solid #ccc; border-radius:2px;vertical-align:middle;margin-top: 1px;}
input[type="radio"]{margin-top:-1px;}
textarea:hover, input:focus,button:focus{border-color:#0099CC;outline: 0;box-shadow: 0px 0px 2px 0px #09c;}
input:hover, input:hover,button:hover{outline: 0;  border: 1px solid #09c;box-shadow: 0px 0px 2px 0px #09c;}
select:hover, select:focus{border-color:#0099CC;outline: 0;box-shadow: 0px 0px 2px 0px #09c;}
a:link{text-decoration:none; font-size:12px; font-style:normal; color:#3F628C;}
a:link{text-decoration:none; font-size:12px; font-style:normal; color:#3F628C;}
a:visited{text-decoration:none; font-size:12px; font-style:normal; color:#3F628C;}
a:hover{text-decoration: underline; font-size:12px; font-style:normal; color:#3F628C;}
a:active{text-decoration:none; font-size:12px; font-style:normal; color:#3F628C;}
td{ background:#FFF; color:#333333;}
img{ border:0px;}
li{ list-style:none;}

		/*定义清除浮动 */
		.clearfloat:after{ content:'.';display:block;clear: both;overflow:hidden;visibility:hidden;font-size:0;line-height:0;width:0;height:0; }
		.clearfloat{zoom:1;}
		h2,p{padding:0; margin:0;}
		h2{font-size:14px;height:25px;color:#027DB9;line-height:25px;background:#B4E5FE;text-align:center;margin-bottom:10px;}
		.err{width:380px;height:200px;background:#F5FBFE;border:1px solid #B0DCF5;margin: 0 auto}
		.err p{margin-left:10px;} 
		</style>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body  style="font-size:12px;background-color:#D7EDFA;height:22px;line-height:22px;">
        <form id="reporterr" action="?id=<?php  echo $id ?>&action=add" method="post" onSubmit="return checkReportErr()">
		<input type="hidden" id="vid" name="vid"  value="<?php  echo $id2 ?>">
            <div class="err">
			<h2>失效影片，我们会在第一时间内修正</h2>
               <p style="padding-bottom:5px;">昵称：<input type="text" readonly id="author" name="author"  value="<?php if($_SESSION['sea_user_name'] !=""){echo $_SESSION['sea_user_name'];}else{echo '匿名';} ?>"  size="15"></p>
			
                <p>详情：<textarea id="errtxt"  name="errtxt" style="width:270px;height:88px" rows=5 cols=30><?php  echo $errtxt2 ?></textarea></p> 
				
				<input type="submit" value="影片报错" style="margin-top:5px;margin-left: 10px;cursor:pointer;color: #fff;width: 80px;cursor:pointer;background-color: #5682b2;height: 28px;border:0; border-radius:2px;float:left;">
				<?php 
				$vcode="<p style='float:left;display:block;margin-top: 6px;'><input name=\"validate\" type=\"text\" id=\"vdcode\" placeholder=\"验证码\"  style=\"width:50px;text-align: center;text-transform:uppercase;float:left;height: 25px;padding: 3px;\" class=\"text\" tabindex=\"3\" onClick=\"document.getElementById('vdimgck').style.display='inline';\"/>&nbsp;<img id=\"vdimgck\" src=\"include/vdimgck.php\" alt=\"看不清？点击更换\"  align=\"absmiddle\"  style=\"cursor:pointer; float:left;height: 25px;display:none;\" onClick=\"this.src=this.src+'?get=' + new Date()\"/><span class=\"red\"></span></p>";
				if($cfg_feedback_ck=='1'){echo $vcode;}
				?>
               
            </div>
        </form>
    </body>
</html>