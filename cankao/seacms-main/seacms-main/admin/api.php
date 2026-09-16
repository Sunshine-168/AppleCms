<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>资源站一键采集接口</title>
  <script src="js/common.js" type="text/javascript"></script>
  <script src="js/main.js" type="text/javascript"></script>
  <script src="js/drag.js" type="text/javascript"></script>
  <link type="text/css" href="img/alerts.css" rel="stylesheet" media="screen">
  <link href="img/style.css" rel="stylesheet" type="text/css" />
  <style type="text/css">
    .list td {
      background-color: #fff;
      height: 40px;
      line-height: 40px;
      font-size: 12px;
      color: #333;
      border: 1px solid #E7E8EA;
      text-align: center;
    }
    .list .bi {
      text-align: left;
      padding-left: 10px;
    }
    .list .b2 {
      text-align: left;
      padding-left: 10px;
      color: #333;
    }
    .list .der {
      border: 1px solid #E7E8EA;
    }
    
    /* 新增样式 */
    .config-container {
      display: flex;
      gap: 5px; /* 按钮之间的间距 */
      align-items: center;
	  padding-left:5px;
    }
    .config-dropdown {
      position: relative;
      display: inline-block;
    }
    .config-btn {
      cursor: pointer;
      padding: 4px 8px;
      border: 1px solid #ddd;
      border-radius: 3px;
      background: #f8f8f8;
      color: #333;
      font-size: 12px;
    }
    .config-btn:hover {
      background: #e0e0e0;
    }
    .config-menu {
      display: none;
      position: absolute;
      background: white;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
      z-index: 100;
      min-width: 85px;
      left: 0;
      top: 100%;
      border: 1px solid #ddd; /* Ensure dropdown border matches config-btn */
    }
    .config-dropdown:hover .config-menu {
      display: block;
    }
    .config-menu a {
      display: block;
      padding: 5px;
      white-space: nowrap;
      color: #333;
      text-decoration: none;
      border-bottom: 1px solid #eee;
    }
    .config-menu a:hover {
      background: #f5f5f5;
      color: #007bff;
    }

    /* 鼠标移动到行时的高亮样式 */
    .list tr:hover td {
      background-color: #f0f8ff !important; /* 浅蓝色背景 */
      border: 1px 1px solid #007bff !important; /* 蓝色边框 */
    }
  </style>
</head>
<body>
  <!--当前导航-->
  <script type="text/JavaScript">
    if(parent.$('admincpnav')) parent.$('admincpnav').innerHTML='后台首页&nbsp;&raquo;&nbsp;采集&nbsp;&raquo;&nbsp;资源库列表 ';
  </script>
  <?php 
    require_once(dirname(__FILE__)."/config.php");
    require_once(sea_DATA."/mark/inc_photowatermark_config.php");
    CheckPurview();
    if(RWCache('collect_xml'))
      echo "<script>openCollectWin(400,'auto','上次采集未完成，继续采集？','".RWCache('collect_xml')."')</script>";
  ?>
  <div class="S_info">&nbsp;资源库列表</div>
  
  <div style="padding: 5px; border: 0; border-radius:1px; color: #6481a2; font-size: 12px; background-color: #eef5f4; width:98%; margin-left:7px;"> 
    需提前<a href="admin_player.php?action=boardsource">添加对应的播放来源</a>再采集，否则会造成视频无播放器。&nbsp;&nbsp;
    分类绑定冲突会导致采集失败，遇到此问题可尝试<a href="admin_delunionid.php">清除分类绑定</a>操作。
  </div>
  <?php 
    $sqlStr="select * from `sea_zyk` where ztype !=3 order by zid ASC";
    $dsql->SetQuery($sqlStr);
    $dsql->Execute('flink_list'); 
  ?>
  <table width="98%" align="left" cellpadding="0" cellspacing="0" border="0" bgcolor="#fff" style="margin-top:10px; margin-left:10px;" id="list" class="list">
    <tr style="background:#f8f8f8;height:30px;">
      <th width="20">ID</th>
      <th class="bi">资源库名称</th>
      <th width="99">当天采集</th>
      <th width="99">本周采集</th>
      <th width="99">全部采集</th>
      <th width="99">分类绑定</th>
      <th width="180" style="min-width:200px;">过滤/替换配置</th>
    </tr>
    <?php
    while($row=$dsql->GetObject('flink_list'))
    {
      $aid=$row->id;
    ?>
    <tr>
      <td><?php echo $row->zid; ?></td> 
      <td class="bi">
        <img style="height:14px; border-radius:2px; vertical-align:middle; margin-top:-2px;" src="img/<?php echo $row->ztype; ?>.png">
        <a href="admin_reslib.php?ac=list&rid=<?php echo $row->zid; ?>&url=<?php echo $row->zapi; ?>">
          <strong>【<?php echo $row->zname; ?>】</strong><?php echo $row->zinfo; ?>
        </a>
      </td> 
      <td><a href="admin_reslib.php?ac=day&rid=<?php echo $row->zid; ?>&url=<?php echo $row->zapi; ?>">采集当天</a></td>
      <td><a href="admin_reslib.php?ac=week&rid=<?php echo $row->zid; ?>&url=<?php echo $row->zapi; ?>">采集本周</a></td>
      <td><a href="admin_reslib.php?ac=all&rid=<?php echo $row->zid; ?>&url=<?php echo $row->zapi; ?>">采集所有</a></td>
      <td><a href="admin_reslib.php?ac=list&rid=<?php echo $row->zid; ?>&url=<?php echo $row->zapi; ?>">分类绑定</a></td>
      <td>
        <div class="config-container">
          <div class="config-dropdown">
            <button type="button" class="config-btn">⚙️ 过滤配置</button>
            <div class="config-menu">
              <a href="admin_filter.php?type=name&rid=<?=$row->zid?>">名称过滤</a>
              <a href="admin_filter.php?type=year&rid=<?=$row->zid?>">年份过滤</a>
              <a href="admin_filter.php?type=area&rid=<?=$row->zid?>">地区过滤</a>
              <a href="admin_filter.php?type=lang&rid=<?=$row->zid?>">语言过滤</a>
            </div>
          </div>
          <div class="config-dropdown">
            <button type="button" class="config-btn">🔄 替换配置</button>
            <div class="config-menu">
              <a href="admin_replace.php?type=name&rid=<?=$row->zid?>">名称替换</a>
              <a href="admin_replace.php?type=area&rid=<?=$row->zid?>">地区替换</a>
              <a href="admin_replace.php?type=lang&rid=<?=$row->zid?>">语言替换</a>
			  <a href="admin_replace.php?type=actor&rid=<?=$row->zid?>">演员替换</a>
            </div>
          </div>
        </div>
      </td>
    </tr>
    <?php 
    }
    echo '</table>'.$union;
    ?> 
</body>
</html>