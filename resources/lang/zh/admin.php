<?php

/**
 * 后台管理语言文件
 * 从 maccms10/application/admin/lang/zh-cn.php 迁移
 * 
 * 注意：这是一个基础版本，需要根据实际使用情况补充完整的翻译键值
 */

return [
    // 通用
    'select_type' => '选择分类',
    'select_status' => '选择状态',
    'select_level' => '选择级别',
    'reviewed' => '已审核',
    'reviewed_not' => '未审核',
    'level' => '级别',
    'slide' => '幻灯片',
    'add' => '添加',
    'edit' => '编辑',
    'delete' => '删除',
    'save' => '保存',
    'cancel' => '取消',
    'search' => '搜索',
    'reset' => '重置',
    'submit' => '提交',
    'back' => '返回',
    'confirm' => '确认',
    'operation' => '操作',
    'status' => '状态',
    'time' => '时间',
    'name' => '名称',
    'title' => '标题',
    'content' => '内容',
    'type' => '类型',
    'lock' => '锁定',
    'unlock' => '解锁',
    'enable' => '启用',
    'disable' => '禁用',
    
    // 菜单
    'menu/index' => '首页',
    'menu/system' => '系统',
    'menu/base' => '基础',
    'menu/vod' => '视频',
    'menu/art' => '文章',
    'menu/users' => '用户',
    'menu/templates' => '模板',
    'menu/make' => '生成',
    'menu/cjs' => '采集',
    'menu/db' => '数据库',
    'menu/apps' => '应用',
    'menu/website' => '网址',
    'menu/manga' => '漫画',
    
    // 首页
    'admin/index/index/name' => '后台管理系统',
    'admin/index/index/menu_switch' => '菜单切换',
    'admin/index/index/welcome' => '欢迎',
    'admin/index/index/quickmenu' => '快捷菜单',
    'admin/index/index/menu_index' => '前台首页',
    'admin/index/index/menu_lock' => '锁屏',
    'admin/index/index/menu_cache_clear' => '清理缓存',
    'admin/index/index/menu_logout' => '退出登录',
    'admin/index/index/new_version' => '新版',
    
    // 系统设置
    'admin/system/config/title' => '基本设置',
    'admin/system/configseo/title' => 'SEO设置',
    'admin/system/configuser/title' => '用户设置',
    'admin/system/configcomment/title' => '评论设置',
    'admin/system/configupload/title' => '上传设置',
    'admin/system/configinterface/title' => '接口设置',
    'admin/system/configpay/title' => '支付设置',
    'admin/system/configcollect/title' => '采集设置',
    'admin/system/configapi/title' => 'API设置',
    'admin/system/configconnect/title' => '第三方登录',
    'admin/system/configweixin/title' => '微信设置',
    'admin/system/configemail/title' => '邮件设置',
    'admin/system/configsms/title' => '短信设置',
    'admin/system/configview/title' => '视图设置',
    'admin/system/configpath/title' => '路径设置',
    'admin/system/configrewrite/title' => '重写设置',
    'admin/system/configplay/title' => '播放设置',
    'admin/system/configurl/title' => 'URL设置',
    
    // 视频管理
    'admin/vod/title' => '视频管理',
    'admin/vod/index/title' => '视频列表',
    'admin/vod/info/title' => '视频信息',
    
    // 文章管理
    'admin/art/title' => '文章管理',
    'admin/art/index/title' => '文章列表',
    'admin/art/info/title' => '文章信息',
    
    // 分类管理
    'admin/type/title' => '分类管理',
    'admin/type/index/title' => '分类列表',
    'admin/type/info/title' => '分类信息',
    
    // 用户管理
    'admin/user/title' => '用户管理',
    'admin/user/index/title' => '用户列表',
    'admin/user/info/title' => '用户信息',
    
    // 管理员
    'admin/admin/title' => '管理员管理',
    'admin/admin/index/title' => '管理员列表',
    'admin/admin/info/title' => '管理员信息',
    
    // 数据库
    'admin/database/title' => '数据库管理',
    'admin/database/select_export_table' => '请选择要导出的数据表',
    'admin/database/backup_ok' => '备份成功',
    'admin/database/backup_err' => '备份失败',
    'admin/database/lock_check' => '已有备份任务正在执行',
    'admin/database/select_file' => '请选择要导入的备份文件',
    'admin/database/import_ok' => '导入成功',
    'admin/database/import_err' => '导入失败',
    'admin/database/file_damage' => '备份文件损坏',
    'admin/database/select_optimize_table' => '请选择要优化的数据表',
    'admin/database/optimize_ok' => '优化成功',
    'admin/database/optimize_err' => '优化失败',
    'admin/database/select_repair_table' => '请选择要修复的数据表',
    'admin/database/repair_ok' => '修复成功',
    'admin/database/select_del_file' => '请选择要删除的备份文件',
    
    // 采集
    'admin/cj/title' => '采集节点管理',
    'admin/cj/url/title' => '采集网址',
    'admin/cj/url_list_err' => '采集网址列表为空',
    'admin/cj/url_cj_complete' => '网址采集完成',
    'admin/cj/content/title' => '采集内容',
    'admin/cj/content/tip' => '共 %d 条，分 %d 页，每页 %d 条，当前第 %d 页',
    'admin/cj/content_cj_complete' => '内容采集完成',
    'admin/cj/cj_complete' => '采集完成',
    'admin/cj/publish/title' => '发布管理',
    'admin/cj/content_into/title' => '内容导入',
    'admin/cj/content_into/tip' => '共 %d 条，分 %d 页，每页 %d 条，当前第 %d 页',
    'admin/cj/content_into/complete' => '导入完成',
    
    // 播放器
    'admin/vodplayer/title' => '播放器管理',
    'admin/voddowner/title' => '下载器管理',
    'admin/vodserver/title' => '服务器管理',
    
    // 模板
    'admin/template/title' => '模板管理',
    
    // 定时任务
    'admin/timming/title' => '定时任务管理',
    
    // 其他
    'admin/plog/title' => '积分日志',
    'admin/ulog/title' => '用户日志',
    'admin/visit/title' => '访问统计',
    'admin/manga/title' => '漫画管理',
    'admin/urlsend/title' => 'URL推送',
    'admin/safety/file' => '文件安全',
    'admin/safety/data' => '数据安全',
    'admin/domain/title' => '域名管理',
    'admin/make/title' => '生成管理',
    'admin/images/title' => '图片管理',
    
    // 通用消息
    'save_ok' => '保存成功',
    'save_err' => '保存失败',
    'del_ok' => '删除成功',
    'del_err' => '删除失败',
    'param_err' => '参数错误',
    'permission_denied' => '权限不足',
    'format_err' => '格式错误',
    'run_ok' => '执行成功',
    'obtain_ok' => '获取成功',
    'import_ok' => '导入成功',
    'import_err' => '导入失败',
    'write_err_config' => '配置文件写入失败',
    'write_err_route' => '路由文件写入失败',
    'wirte_err_codefile' => '代码文件写入失败',
    'illegal_request' => '非法请求',
    'test_ok' => '测试成功',
    'test_err' => '测试失败',
    
    // 视频管理详细翻译
    'admin/vod/select_weekday' => '选择星期',
    'admin/vod/select_area' => '选择地区',
    'admin/vod/select_lang' => '选择语言',
    'admin/vod/select_server' => '选择服务器',
    'admin/vod/select_player' => '选择播放器',
    'admin/vod/select_downer' => '选择下载器',
    'admin/vod/select_isend' => '选择状态',
    'admin/vod/select_copyright' => '选择版权',
    'admin/vod/select_plot' => '选择剧情',
    'admin/vod/select_role' => '选择角色',
    'admin/vod/player_empty' => '未设置',
    'admin/vod/downer_empty' => '未设置',
    'admin/vod/no_end' => '连载中',
    'admin/vod/is_end' => '已完结',
    'admin/vod/no' => '无',
    'admin/vod/have' => '有',
    'admin/vod/copyright' => '版权',
    'admin/vod/serialize' => '连载',
    'admin/vod/role' => '角色',
    
    // 播放器管理
    'admin/vodplayer/code_tip' => '播放器标识',
    'admin/vodplayer/sort_tip' => '排序',
    'admin/vodplayer/code_empty' => '播放器标识不能为空',
    'admin/vodserver/url' => '服务器地址',
    
    // 通用表单
    'base_info' => '基本信息',
    'other_info' => '其他信息',
    'param' => '参数',
    'select_lock' => '选择锁定',
    
    // 公共头部
    'admin/public/head/title' => '后台管理',
    
    // 登录页面
    'admin/index/login/title' => '后台登录',
    'admin/index/login/tip_welcome' => '欢迎使用苹果CMS',
    'admin/index/login/tip_sys' => '后台管理系统',
    'admin/index/login/btn_submit' => '登录',
    'admin/index/login/verify_no' => '请输入用户名',
    'admin/index/login/verify_pass' => '请输入密码',
    'admin/index/login/verify_verify' => '请输入验证码',
    'admin/index/login/tip_declare' => '使用声明',
    'admin/index/login/tip_declare_txt' => '本系统仅供学习研究使用，请勿用于商业用途。',
    'account' => '账号',
    'pass' => '密码',
    'verify' => '验证码',
    'wait_submit' => '提交中...',
    'maccms_copyright' => '© 2024 苹果CMS Laravel迁移版',
    'upload_pic' => '上传图片',
    
    // 系统配置
    'admin/system/config/base' => '基本设置',
    'admin/system/config/performance' => '性能设置',
    'admin/system/config/parameters' => '参数设置',
    'admin/system/config/backstage' => '后台设置',
    'admin/system/config/site_name' => '网站名称',
    'admin/system/config/site_url' => '网站地址',
    'admin/system/config/site_url_tip' => '如：https://www.example.com',
    'admin/system/config/site_wapurl' => '手机站地址',
    'admin/system/config/site_wapurl_tip' => '如：https://m.example.com',
    'admin/system/config/site_keywords' => '网站关键词',
    'admin/system/config/site_description' => '网站描述',
    'admin/system/config/site_icp' => 'ICP备案号',
    'admin/system/config/site_qq' => 'QQ号码',
    'admin/system/config/site_email' => '邮箱地址',
    'admin/system/config/install_dir' => '安装目录',
    'admin/system/config/install_dir_tip' => '如果安装在根目录，请留空',
    'admin/system/config/site_logo' => '网站LOGO',
    'admin/system/config/site_logo_tip' => '建议尺寸：200x60',
    'admin/system/config/site_waplogo' => '手机站LOGO',
    'admin/system/config/template_dir' => '模板目录',
    'admin/system/config/html_dir' => '静态页目录',
    'admin/system/config/site_polyfill' => '兼容低版本浏览器',
    'admin/system/config/site_polyfill_tip' => '开启后会自动加载polyfill.js',
    'admin/system/config/mob_status' => '移动端模式',
    'admin/system/config/mob_multiple' => '多模板',
    'admin/system/config/mob_one' => '单模板',
    'admin/system/config/mob_status_tip' => '多模板：PC和移动端使用不同模板；单模板：共用同一模板',
    'admin/system/config/mob_template_dir' => '移动端模板目录',
    'admin/system/config/site_tj' => '统计代码',
    'admin/system/config/site_status' => '网站状态',
    'admin/system/config/site_close_tip' => '关闭提示',
    'admin/system/config/mainland_ip_limit' => '大陆IP限制',
];
