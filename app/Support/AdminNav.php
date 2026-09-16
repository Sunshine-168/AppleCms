<?php

namespace App\Support;

class AdminNav
{
    /**
     * 侧栏：只放日常会点的入口。
     */
    public static function groups(): array
    {
        return [
            [
                'header' => '工作台',
                'items' => [
                    ['url' => '/admin/welcome', 'icon' => 'tachometer-alt', 'label' => '仪表盘'],
                ],
            ],
            [
                'header' => '内容',
                'items' => [
                    ['url' => '/admin/video', 'icon' => 'video', 'label' => '影片'],
                    ['url' => '/admin/video/types', 'icon' => 'sitemap', 'label' => '分类'],
                    ['url' => '/admin/video/collects', 'icon' => 'cloud-download-alt', 'label' => '采集'],
                    ['url' => '/admin/video/comments', 'icon' => 'comments', 'label' => '评论'],
                ],
                'fold' => [
                    'label' => '更多',
                    'items' => [
                        ['url' => '/admin/video/topics', 'icon' => 'layer-group', 'label' => '专题'],
                        ['url' => '/admin/video/actors', 'icon' => 'user-friends', 'label' => '演员'],
                        ['url' => '/admin/video/tags', 'icon' => 'tags', 'label' => '标签'],
                        ['url' => '/admin/video/arts', 'icon' => 'file-alt', 'label' => '文章'],
                        ['url' => '/admin/video/danmaku', 'icon' => 'comment-dots', 'label' => '弹幕'],
                        ['url' => '/admin/video/slides', 'icon' => 'images', 'label' => '幻灯片'],
                        ['url' => '/admin/video/tools/recycle', 'icon' => 'trash-alt', 'label' => '回收站'],
                    ],
                ],
            ],
            [
                'header' => '用户',
                'items' => [
                    ['url' => '/admin/video/members', 'icon' => 'users', 'label' => '会员'],
                    ['url' => '/admin/video/orders', 'icon' => 'file-invoice', 'label' => '订单'],
                ],
                'fold' => [
                    'label' => '更多',
                    'items' => [
                        ['url' => '/admin/video/groups', 'icon' => 'user-tag', 'label' => '会员组'],
                        ['url' => '/admin/video/cards', 'icon' => 'credit-card', 'label' => '积分卡密'],
                        ['url' => '/admin/video/coupons', 'icon' => 'ticket-alt', 'label' => '优惠券'],
                        ['url' => '/admin/video/plogs', 'icon' => 'coins', 'label' => '积分流水'],
                    ],
                ],
            ],
            [
                'header' => '站点',
                'items' => [
                    ['url' => '/admin/video/settings', 'icon' => 'cog', 'label' => '站点设置'],
                    ['url' => '/admin/video/templates', 'icon' => 'palette', 'label' => '模板'],
                    ['url' => '/admin/video/ads', 'icon' => 'bullhorn', 'label' => '广告'],
                ],
                'fold' => [
                    'label' => '更多',
                    'items' => [
                        ['url' => '/admin/video/links', 'icon' => 'link', 'label' => '友情链接'],
                        ['url' => '/admin/video/players', 'icon' => 'play-circle', 'label' => '播放器'],
                        ['url' => '/admin/video/make', 'icon' => 'bolt', 'label' => '静态生成'],
                    ],
                ],
            ],
            [
                'header' => '系统',
                'items' => [
                    ['url' => '/admin/user', 'icon' => 'user-cog', 'label' => '管理员'],
                    ['url' => '/admin/system/roles', 'icon' => 'user-shield', 'label' => '角色'],
                    ['url' => '/admin/system/monitor/login-logs', 'icon' => 'history', 'label' => '日志'],
                    ['url' => '/admin/more', 'icon' => 'th-large', 'label' => '全部功能', 'force' => true],
                ],
            ],
        ];
    }

    /**
     * 低频入口：放目录页，不进侧栏。
     */
    public static function catalog(): array
    {
        return [
            [
                'title' => '采集与入库',
                'hint' => '资源站、日志、审核',
                'items' => [
                    ['url' => '/admin/video/collect_logs', 'label' => '采集日志'],
                    ['url' => '/admin/video/collect_tasks', 'label' => '定时采集'],
                    ['url' => '/admin/video/unions', 'label' => '推荐资源'],
                    ['url' => '/admin/video/cj', 'label' => '自定义规则'],
                    ['url' => '/admin/video/audits', 'label' => '入库审核'],
                    ['url' => '/admin/video/tools/hub', 'label' => '采集目录'],
                    ['url' => '/admin/video/collect_temps', 'label' => '采集临时表'],
                    ['url' => '/admin/video/config/collect', 'label' => '内容接入'],
                    ['url' => '/admin/video/config/interface', 'label' => '入库接口'],
                ],
            ],
            [
                'title' => '内容补全',
                'hint' => '缺资料请先到影片列表用顶部筛选',
                'items' => [
                    ['url' => '/admin/video/roles', 'label' => '角色库'],
                    ['url' => '/admin/video/plots', 'label' => '分集剧情'],
                    ['url' => '/admin/video/websites', 'label' => '网址导航'],
                    ['url' => '/admin/video/classes', 'label' => '扩展分类'],
                    ['url' => '/admin/video/synonyms', 'label' => '同义词'],
                    ['url' => '/admin/video/searchwords', 'label' => '搜索词'],
                    ['url' => '/admin/video/wizard', 'label' => '标签向导'],
                    ['url' => '/admin/video/downloaders', 'label' => '下载器'],
                    ['url' => '/admin/video/servers', 'label' => '服务器组'],
                    ['url' => '/admin/video/tools/quality', 'label' => '内容质量'],
                    ['url' => '/admin/video/tools/images', 'label' => '远程图片'],
                    ['url' => '/admin/video/tools/players', 'label' => '批量播放器'],
                ],
            ],
            [
                'title' => '互动与客服',
                'items' => [
                    ['url' => '/admin/video/reports', 'label' => '报错'],
                    ['url' => '/admin/video/guestbooks', 'label' => '留言'],
                    ['url' => '/admin/video/playfails', 'label' => '播放失败'],
                    ['url' => '/admin/video/pms', 'label' => '站内信'],
                    ['url' => '/admin/video/notifies', 'label' => '会员通知'],
                ],
            ],
            [
                'title' => '会员运营',
                'items' => [
                    ['url' => '/admin/video/withdraws', 'label' => '提现'],
                    ['url' => '/admin/video/invites', 'label' => '邀请码'],
                    ['url' => '/admin/video/favorites', 'label' => '收藏'],
                    ['url' => '/admin/video/follows', 'label' => '关注'],
                    ['url' => '/admin/video/dynamics', 'label' => '动态'],
                    ['url' => '/admin/video/shares', 'label' => '分享'],
                    ['url' => '/admin/video/signs', 'label' => '签到'],
                    ['url' => '/admin/video/config/user', 'label' => '会员参数'],
                    ['url' => '/admin/video/config/pay', 'label' => '支付参数'],
                ],
            ],
            [
                'title' => '站点参数',
                'items' => [
                    ['url' => '/admin/video/config/seo', 'label' => 'SEO 标题'],
                    ['url' => '/admin/video/config/theme', 'label' => '主题参数'],
                    ['url' => '/admin/video/config/player', 'label' => '播放器参数'],
                    ['url' => '/admin/video/config/url', 'label' => 'URL 规则'],
                    ['url' => '/admin/video/rewrite', 'label' => '伪静态规则'],
                    ['url' => '/admin/video/config/upload', 'label' => '上传限制'],
                    ['url' => '/admin/video/config/comment', 'label' => '评论留言'],
                    ['url' => '/admin/video/config/email', 'label' => '邮件设置'],
                    ['url' => '/admin/video/config/watermark', 'label' => '图片水印'],
                    ['url' => '/admin/video/config/analytics', 'label' => '统计代码'],
                    ['url' => '/admin/video/domains', 'label' => '绑定域名'],
                    ['url' => '/admin/video/push', 'label' => '搜索推送'],
                    ['url' => '/admin/video/visits', 'label' => '访问统计'],
                    ['url' => '/admin/video/ulogs', 'label' => '访问日志'],
                ],
            ],
            [
                'title' => '接入与安全',
                'items' => [
                    ['url' => '/admin/video/config/api', 'label' => '开放 API'],
                    ['url' => '/admin/video/apidoc', 'label' => 'API 文档'],
                    ['url' => '/admin/video/config/weixin', 'label' => '微信公众号'],
                    ['url' => '/admin/video/config/sms', 'label' => '短信网关'],
                    ['url' => '/admin/video/config/connect', 'label' => '第三方登录'],
                    ['url' => '/admin/video/config/ip', 'label' => '后台 IP 白名单'],
                    ['url' => '/admin/video/safety', 'label' => '挂马扫描'],
                    ['url' => '/admin/video/accesslogs', 'label' => '访问风控'],
                    ['url' => '/admin/video/botlogs', 'label' => '爬虫日志'],
                ],
            ],
            [
                'title' => '系统维护',
                'items' => [
                    ['url' => '/admin/system/menus', 'label' => '菜单'],
                    ['url' => '/admin/system/dicts', 'label' => '字典'],
                    ['url' => '/admin/system/attachments', 'label' => '附件'],
                    ['url' => '/admin/video/tools/annex', 'label' => '附件清理'],
                    ['url' => '/admin/system/tools/cache', 'label' => '缓存'],
                    ['url' => '/admin/system/tools/schedule', 'label' => '定时任务'],
                    ['url' => '/admin/system/monitor/operate-logs', 'label' => '操作日志'],
                    ['url' => '/admin/system/monitor/system-logs', 'label' => '系统日志'],
                    ['url' => '/admin/system/database/backup', 'label' => '数据库备份'],
                    ['url' => '/admin/system/database/restore', 'label' => '数据库恢复'],
                    ['url' => '/admin/system/database/dict', 'label' => '数据库字典'],
                    ['url' => '/admin/system/database/sql', 'label' => 'SQL 执行'],
                    ['url' => '/admin/system/database/replace', 'label' => '数据批量替换'],
                    ['url' => '/admin/system/shortcut', 'label' => '系统快捷'],
                ],
            ],
        ];
    }
}
