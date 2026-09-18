// 通用的编辑功能已在各页面内联，这里放一些通用交互
document.addEventListener('DOMContentLoaded', function() {
    // 确认删除提示
    const deleteLinks = document.querySelectorAll('a[href*="delete"]');
    deleteLinks.forEach(link => {
        if (!link.hasAttribute('onclick')) {
            link.addEventListener('click', function(e) {
                if (!confirm('确定要删除吗？此操作不可恢复！')) {
                    e.preventDefault();
                }
            });
        }
    });

    // 侧边栏激活状态
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('.sidebar a');
    sidebarLinks.forEach(link => {
        if (link.getAttribute('href') && currentPath.includes(link.getAttribute('href').replace(/\.php.*/, ''))) {
            link.style.backgroundColor = '#34495e';
            link.style.color = '#fff';
        }
    });
});