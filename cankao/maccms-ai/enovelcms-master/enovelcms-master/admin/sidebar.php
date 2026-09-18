<style>
.sidebar-footer {
    margin-top: 20px;
    padding: 12px 16px;
    font-size: 14px;
    color: #888;
    border-top: 1px solid #eee;
    text-align: left;
    line-height: 1.6;
}
.sidebar-footer a {
    color: #888;
    text-decoration: none;
}
.sidebar-footer a:hover {
    color: #333;
}
</style>
<div class="sidebar">
    <h2><i class="fas fa-book"></i> 小说管理</h2>
    <ul>
        <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> 控制台</a></li>
        <li><a href="settings.php"><i class="fas fa-cog"></i> 系统设置</a></li>
        <li><a href="themes.php"><i class="fas fa-paint-brush"></i> 主题管理</a></li> <!-- 新增 -->
        <li><a href="novels.php"><i class="fas fa-book"></i> 小说管理</a></li>
        <li><a href="crawl.php"><i class="fas fa-cloud-download-alt"></i> 采集管理</a></li>
        <li><a href="categories.php"><i class="fas fa-tags"></i> 分类管理</a></li>
        <li><a href="users.php"><i class="fas fa-users"></i> 用户管理</a></li>
        <li><a href="ads.php"><i class="fas fa-ad"></i> 广告管理</a></li>
        <li><a href="diy_blocks.php"><i class="fas fa-chart-line"></i> 自定义榜单</a></li>
        <li><a href="seo.php"><i class="fas fa-search"></i> SEO设置</a></li>
        <li><a href="payment_config.php"><i class="fas fa-credit-card"></i> 支付配置</a></li>
        <li><a href="orders.php?type=vip"><i class="fas fa-shopping-cart"></i> 充值订单</a></li>
        <li><a href="language.php"><i class="fas fa-globe"></i> 语言管理</a></li>
        <li><a href="rewrite.php"><i class="fas fa-code-branch"></i> 伪静态规则</a></li>
        <li><a href="sign_logs.php"><i class="fas fa-calendar-check"></i> 签到日志</a></li>
        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> 退出</a></li>
    </ul>
    <!-- 底部版权信息 -->
    <div class="sidebar-footer">
        作者：文煞<br>
        程序：EnovelCms v <?= ENOVELCMS_VERSION ?><br>
        官网：<a href="https://www.enovelcms.cn/" target="_blank">https://www.enovelcms.cn/</a><br>
        技术：<a href="https://www.wslogs.cn/" target="_blank">https://www.wslogs.cn/</a><br>
        QQ群：<a href="https://qm.qq.com/q/EQ7jH2rQ1f" target="_blank">162244086</a>
    </div>
</div>