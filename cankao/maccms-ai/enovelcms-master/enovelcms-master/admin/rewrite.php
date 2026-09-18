<?php
require_once __DIR__ . '/../includes/config.php';
if (!isAdmin()) {
    redirect(BASE_URL . '/admin/login.php');
}
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
$serverType = 'apache';
if (stripos($serverSoftware, 'nginx') !== false) {
    $serverType = 'nginx';
} elseif (stripos($serverSoftware, 'iis') !== false) {
    $serverType = 'iis';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>伪静态规则</title>
<link rel="stylesheet" href="../assets/css/admin.css">
<link rel="stylesheet" href="/assets/fontawesome/css/all.min.css">
<style>
.rule-box{background:#1e1e2f;color:#f8f8f2;padding:1.5rem;border-radius:12px;overflow-x:auto;font-family:'Fira Code',monospace;font-size:0.85rem;margin:1rem 0}
.rule-box pre{margin:0;white-space:pre-wrap;word-break:break-all}
.server-badge{display:inline-block;background:var(--primary);color:white;padding:0.25rem 1rem;border-radius:20px;font-size:0.8rem}
</style>
</head>
<body>
<div class="admin-container">
<?php include 'sidebar.php'; ?>
<div class="content">
<h1><i class="fas fa-code-branch"></i> 伪静态规则</h1>
<p>当前服务器环境：<span class="server-badge"><?= strtoupper($serverType) ?></span></p>
<div class="rule-box">
<?php if($serverType==='nginx'):?>
<pre>
location / {
    try_files $uri $uri/ /index.php?$args;
}
location /novel/ {
    rewrite ^/novel/(\d+)/?$ /index.php?action=novel&id=$1 last;
}
location /read/ {
    rewrite ^/read/(\d+)/(\d+)/?$ /index.php?action=read&novel_id=$1&chapter_id=$2 last;
}
location /user/ {
    rewrite ^/user/?$ /index.php?user=index last;
    rewrite ^/user/([a-z]+)/?$ /index.php?user=$1 last;
}
</pre>
<?php elseif($serverType==='iis'):?>
<pre>
&lt;?xml version="1.0" encoding="UTF-8"?&gt;
&lt;configuration&gt;
    &lt;system.webServer&gt;
        &lt;rewrite&gt;
            &lt;rules&gt;
                &lt;rule name="Novel" stopProcessing="true"&gt;
                    &lt;match url="^novel/(\d+)/?$" ignoreCase="true" /&gt;
                    &lt;action type="Rewrite" url="index.php?action=novel&id={R:1}" /&gt;
                &lt;/rule&gt;
                &lt;rule name="Read" stopProcessing="true"&gt;
                    &lt;match url="^read/(\d+)/(\d+)/?$" ignoreCase="true" /&gt;
                    &lt;action type="Rewrite" url="index.php?action=read&novel_id={R:1}&chapter_id={R:2}" /&gt;
                &lt;/rule&gt;
                &lt;rule name="UserIndex" stopProcessing="true"&gt;
                    &lt;match url="^user/?$" ignoreCase="true" /&gt;
                    &lt;action type="Rewrite" url="index.php?user=index" /&gt;
                &lt;/rule&gt;
                &lt;rule name="UserAction" stopProcessing="true"&gt;
                    &lt;match url="^user/([a-z]+)/?$" ignoreCase="true" /&gt;
                    &lt;action type="Rewrite" url="index.php?user={R:1}" /&gt;
                &lt;/rule&gt;
                &lt;rule name="Main" stopProcessing="true"&gt;
                    &lt;match url="^(.*)$" ignoreCase="true" /&gt;
                    &lt;conditions logicalGrouping="MatchAll"&gt;
                        &lt;add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" /&gt;
                        &lt;add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" /&gt;
                    &lt;/conditions&gt;
                    &lt;action type="Rewrite" url="index.php?{R:1}" /&gt;
                &lt;/rule&gt;
            &lt;/rules&gt;
        &lt;/rewrite&gt;
    &lt;/system.webServer&gt;
&lt;/configuration&gt;
</pre>
<?php else:?>
<pre>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php?$1 [L,QSA]
RewriteRule ^novel/(\d+)/?$ index.php?action=novel&id=$1 [L]
RewriteRule ^read/(\d+)/(\d+)/?$ index.php?action=read&novel_id=$1&chapter_id=$2 [L]
RewriteRule ^user/?$ index.php?user=index [L]
RewriteRule ^user/([a-z]+)/?$ index.php?user=$1 [L]
</pre>
<?php endif;?>
</div>
<p style="margin-top:1rem;">根据您实际的服务环境，将以上规则添加到对应的配置文件中，并重启服务生效。</p>
</div>
</div>
</body>
</html>