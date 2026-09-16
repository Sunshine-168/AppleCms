<?php
require_once(dirname(__FILE__)."/config.php");
CheckPurview();
// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rid = intval($_POST['rid']);
    $type = $_POST['type'];
    // 直接获取原始规则，保留空格符
    $rules = $_POST['rules'];

    // 解析规则，保留所有空格符
    $rulePairs = array();
    foreach (explode("\n", $rules) as $line) {
        // 仅去除换行符，保留空格符
        $line = rtrim($line, "\r\n");
        if ($line !== '' && strpos($line, '=') !== false) {
            list($old, $new) = explode('=', $line, 2);
            // 不对 $old 和 $new 使用 trim，保留输入中的空格符
            $rulePairs[$old] = $new;
        }
    }
    // 获取原配置
    $sql = "SELECT replace_config FROM sea_zyk WHERE zid = $rid";
    $row = $dsql->GetOne($sql);
    $config = $row && !empty($row['replace_config']) ? json_decode($row['replace_config'], true) : array();
    
    // 更新配置
    $config[$type] = $rulePairs;
    
    // 保存到数据库
    $newConfig = addslashes(json_encode($config));
    $dsql->ExecuteNoneQuery("UPDATE sea_zyk SET replace_config = '$newConfig' WHERE zid = $rid");
    
    ShowMsg("配置保存成功！", "admin_replace.php?type=$type&rid=$rid");
    exit;
}

$type = isset($_GET['type']) ? $_GET['type'] : 'name';
$rid = intval($_GET['rid']);

// 读取现有配置
$sql = "SELECT replace_config FROM sea_zyk WHERE zid = $rid";
$row = $dsql->GetOne($sql);
$currentRules = '';
if($row && !empty($row['replace_config'])){
    $config = json_decode($row['replace_config'], true);
    if(isset($config[$type])){
        foreach($config[$type] as $old => $new){
            $currentRules .= "$old=$new\n";
        }
    }
}

$typeTitles = array(
    'name' => '视频名称替换（格式：原内容=替换内容 注意:保留空格符）',
    'area' => '视频地区替换（格式：原地区=新地区）',
    'lang' => '视频语言替换（格式：原语言=新语言）',
	'actor' => '视频演员替换（格式：张某/李某=张某,李某）'
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>替换配置</title>
    <link href="img/style.css" rel="stylesheet">
</head>
<body>
<div class="S_info"><?php echo isset($typeTitles[$type]) ? $typeTitles[$type] : '未知配置类型'; ?></div>
<form method="post" style="margin:20px;">
    <input type="hidden" name="rid" value="<?php echo $rid; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    
    <textarea name="rules" 
              style="width:90%;height:200px;padding:10px;"
              placeholder="每行一条替换规则，例如：大陆=中国"><?php echo htmlspecialchars($currentRules); ?></textarea>
    <!-- 每行一条替换规则，例如：\n大陆=中国\n香港=中国香港 -->
    <div style="margin-top:15px;">
        <input type="submit" value="保存配置" class="btn">
        <input type="button" value="返回" class="btn" onclick="history.go(-1)">
    </div>
</form>
</body>
</html>