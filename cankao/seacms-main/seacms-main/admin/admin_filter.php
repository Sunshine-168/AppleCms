<?php
require_once(dirname(__FILE__)."/config.php");
CheckPurview();

// 处理表单提交
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $rid = intval($_POST['rid']);
    $type = $_POST['type'];
    $values = trim($_POST['values']);

    // 安全过滤
    $values = str_replace(array("'", "\"", "\\"), "", $values);
    
    // 获取原配置
    $sql = "SELECT filter_config FROM sea_zyk WHERE zid = $rid";
    $row = $dsql->GetOne($sql);
    $config = $row && !empty($row['filter_config']) ? json_decode($row['filter_config'], true) : array();
    
    // 更新配置
    $config[$type] = explode("\n", $values);
    
    // 保存到数据库
    $newConfig = addslashes(json_encode($config));
    $dsql->ExecuteNoneQuery("UPDATE sea_zyk SET filter_config = '$newConfig' WHERE zid = $rid");
    
    ShowMsg("配置保存成功！", "admin_filter.php?type=$type&rid=$rid");
    exit;
}

// 显示配置表单
$type = isset($_GET['type']) ? $_GET['type'] : 'name';
$rid = intval($_GET['rid']);

// 读取现有配置
$sql = "SELECT filter_config FROM sea_zyk WHERE zid = $rid";
$row = $dsql->GetOne($sql);
$currentValues = '';
if($row && !empty($row['filter_config'])){
    $config = json_decode($row['filter_config'], true);
    if(isset($config[$type])){
        $currentValues = implode("\n", $config[$type]);
    }
}

// 配置类型映射
$typeTitles = array(
    'name' => '视频名称过滤（每行一个关键词）',
    'year' => '视频年份过滤（格式：2025，每行一个年份）',
    'area' => '视频地区过滤（每行一个地区）',
    'lang' => '视频语言过滤（比如:英语/法语，过滤/号不采集）'
);
?>
<!DOCTYPE html>
<html>
<head>
    <title>过滤配置</title>
    <link href="img/style.css" rel="stylesheet">
</head>
<body>
<div class="S_info"><?php echo isset($typeTitles[$type]) ? $typeTitles[$type] : '未知配置类型'; ?></div>
<form method="post" style="margin:20px;">
    <input type="hidden" name="rid" value="<?php echo $rid; ?>">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
    
    <textarea name="values" 
              style="width:90%;height:200px;padding:10px;"
              placeholder="<?php echo isset($typeTitles[$type]) ? $typeTitles[$type] : ''; ?>"><?php echo htmlspecialchars($currentValues); ?></textarea>
    
    <div style="margin-top:15px;">
        <input type="submit" value="保存配置" class="btn">
        <input type="button" value="返回" class="btn" onclick="history.go(-1)">
    </div>
</form>
</body>
</html>