<?php

namespace App\Services\Admin\System;

/**
 * 库表字段目录：表和字段干什么。SQLite 没有备注，不编 MySQL comment。
 */
class SysDatabaseDictCatalog
{
    /** @return array<string, string> */
    public function groups(): array
    {
        return [
            'catalog' => '片库',
            'member' => '会员',
            'collect' => '采集',
            'site' => '站点',
            'stats' => '访问',
            'plugin' => '插件',
            'system' => '后台',
            'framework' => '框架',
            'other' => '其它',
        ];
    }

    /**
     * @return array<string, array{group:string,label:string,hint:string,url:string}>
     */
    public function tables(): array
    {
        return [
            'videos' => ['group' => 'catalog', 'label' => '影片', 'hint' => '一部片子一行。播放地址不在这张，在播放线路和剧集。', 'url' => '/admin/video'],
            'video_types' => ['group' => 'catalog', 'label' => '分类', 'hint' => '影片、文章、网址导航的栏目树。不是后台字典选项。', 'url' => '/admin/video/types'],
            'video_stats' => ['group' => 'catalog', 'label' => '影片人气', 'hint' => '点击、顶踩、评分。每天凌晨会清今日人气。', 'url' => '/admin/video'],
            'video_sources' => ['group' => 'catalog', 'label' => '播放线路', 'hint' => '一部片子可以有多条线路。每条线路下面才是集。', 'url' => '/admin/video'],
            'video_episodes' => ['group' => 'catalog', 'label' => '剧集地址', 'hint' => '某一条线路下的第几集和播放地址。', 'url' => '/admin/video'],
            'video_players' => ['group' => 'catalog', 'label' => '播放器', 'hint' => '前台用哪种内核播。解析接口填在 parse。', 'url' => '/admin/video/players'],
            'video_tags' => ['group' => 'catalog', 'label' => '标签', 'hint' => '给片子打的词，和分类不是一回事。', 'url' => '/admin/video/tags'],
            'video_tag_rel' => ['group' => 'catalog', 'label' => '影片-标签', 'hint' => '哪部片子打了哪个标签。', 'url' => '/admin/video/tags'],
            'actors' => ['group' => 'catalog', 'label' => '演员', 'hint' => '演员资料。哪部戏演过在关联表。', 'url' => '/admin/video/actors'],
            'video_actor_rel' => ['group' => 'catalog', 'label' => '影片-演员', 'hint' => '片子和演员的对应，可区分主演配角。', 'url' => '/admin/video/actors'],
            'video_roles' => ['group' => 'catalog', 'label' => '角色卡', 'hint' => '片子里的角色，不是后台管理员角色。', 'url' => '/admin/video/roles'],
            'video_topics' => ['group' => 'catalog', 'label' => '专题', 'hint' => '一组片子或文章的合集页。', 'url' => '/admin/video/topics'],
            'video_topic_rel' => ['group' => 'catalog', 'label' => '专题-影片', 'hint' => '专题里有哪些片子。', 'url' => '/admin/video/topics'],
            'video_topic_art_rel' => ['group' => 'catalog', 'label' => '专题-文章', 'hint' => '专题里有哪些文章。', 'url' => '/admin/video/topics'],
            'video_arts' => ['group' => 'catalog', 'label' => '文章', 'hint' => '资讯文章，不是影片简介。', 'url' => '/admin/video/arts'],
            'video_plots' => ['group' => 'catalog', 'label' => '分集剧情', 'hint' => '某一集的剧情文字，挂在影片上。', 'url' => '/admin/video/plots'],
            'video_classes' => ['group' => 'catalog', 'label' => '扩展分类', 'hint' => '分类页「类型」筛选词。栏目树在「分类」，聚合词在「标签」。', 'url' => '/admin/video/classes'],
            'video_synonyms' => ['group' => 'catalog', 'label' => '同义词', 'hint' => '搜索和采集时把原词换成当成的词。不会改库里已有片名。', 'url' => '/admin/video/synonyms'],
            'video_downloaders' => ['group' => 'catalog', 'label' => '下载器', 'hint' => '下载页把剧集地址套进模板。不是后台去下文件，也不是播放器。', 'url' => '/admin/video/downloaders'],
            'video_servers' => ['group' => 'catalog', 'label' => '服务器组', 'hint' => '相对播放地址会拼上此前缀。http(s) 或 // 开头不改。不是下载器，也不是真的去管机器。', 'url' => '/admin/video/servers'],
            'collect_sources' => ['group' => 'collect', 'label' => '采集源', 'hint' => '资源站接口。按规则扒网页请看规则采集，那是占位。', 'url' => '/admin/video/collects'],
            'video_collect_tasks' => ['group' => 'collect', 'label' => '定时采集', 'hint' => '到点去跑哪个采集源。要服务器先装计划任务。', 'url' => '/admin/video/collect_tasks'],
            'video_collect_logs' => ['group' => 'collect', 'label' => '采集日志', 'hint' => '某次采集新建、更新、跳过了多少。', 'url' => '/admin/video/collect_logs'],
            'video_collect_temps' => ['group' => 'collect', 'label' => '待审采集', 'hint' => '采回来还没转正的片子，确认后再入库。', 'url' => '/admin/video/collect_temps'],
            'video_audit_rules' => ['group' => 'collect', 'label' => '入库审核词', 'hint' => '标题里碰到这些词就跳过或待审。', 'url' => '/admin/video/audits'],
            'video_cj_rules' => ['group' => 'collect', 'label' => '规则采集', 'hint' => '占位。请用接口采集入库，不要指望这里能扒站。', 'url' => '/admin/video/collects'],
            'video_unions' => ['group' => 'collect', 'label' => '联盟资源', 'hint' => '对外资源接口登记，不是支付。', 'url' => '/admin/video/unions'],
            'members' => ['group' => 'member', 'label' => '会员', 'hint' => '前台注册用户。后台管理员在 sys_user。', 'url' => '/admin/video/members'],
            'member_groups' => ['group' => 'member', 'label' => '会员组', 'hint' => '按积分门槛分组，可限制试看。', 'url' => '/admin/video/groups'],
            'member_favorites' => ['group' => 'member', 'label' => '收藏', 'hint' => '谁收藏了哪部片子。', 'url' => '/admin/video/favorites'],
            'member_histories' => ['group' => 'member', 'label' => '观看记录', 'hint' => '看到哪条线路哪一集。', 'url' => '/admin/video/members'],
            'member_orders' => ['group' => 'member', 'label' => '订单', 'hint' => '积分订单。不是微信支付宝收款。', 'url' => '/admin/video/orders'],
            'member_withdraws' => ['group' => 'member', 'label' => '提现申请', 'hint' => '会员申请把积分兑出去，要人工处理。', 'url' => '/admin/video/withdraws'],
            'member_pms' => ['group' => 'member', 'label' => '站内信', 'hint' => '会员之间或站发给会员的私信。', 'url' => '/admin/video/pms'],
            'member_point_logs' => ['group' => 'member', 'label' => '积分流水', 'hint' => '积分加减各记一笔。', 'url' => '/admin/video/members'],
            'member_invites' => ['group' => 'member', 'label' => '邀请码', 'hint' => '邀请码和谁用了。', 'url' => '/admin/video/invites'],
            'member_follows' => ['group' => 'member', 'label' => '关注', 'hint' => '会员关注演员等对象。', 'url' => '/admin/video/members'],
            'member_dynamics' => ['group' => 'member', 'label' => '动态', 'hint' => '会员发的动态，可以带一部片子。', 'url' => '/admin/video/members'],
            'member_shares' => ['group' => 'member', 'label' => '分享记录', 'hint' => '谁把哪部片子分享出去了。', 'url' => '/admin/video/members'],
            'member_signs' => ['group' => 'member', 'label' => '签到', 'hint' => '每天签到领积分。', 'url' => '/admin/video/members'],
            'video_cards' => ['group' => 'member', 'label' => '积分卡密', 'hint' => '一卡换积分，用过就作废。', 'url' => '/admin/video/cards'],
            'video_coupons' => ['group' => 'member', 'label' => '优惠码', 'hint' => '兑换积分的码，可设门槛和过期。', 'url' => '/admin/video/cards'],
            'video_comments' => ['group' => 'site', 'label' => '评论', 'hint' => '片子下的评论，可回复。', 'url' => '/admin/video/comments'],
            'video_reports' => ['group' => 'site', 'label' => '报错', 'hint' => '用户反馈片子不能播或有问题。', 'url' => '/admin/video/reports'],
            'video_play_fails' => ['group' => 'site', 'label' => '播放失败', 'hint' => '前台播不了时记下的地址。', 'url' => '/admin/video/playfails'],
            'video_guestbooks' => ['group' => 'site', 'label' => '留言', 'hint' => '站点留言本，不是评论。', 'url' => '/admin/video/guestbooks'],
            'video_notifies' => ['group' => 'site', 'label' => '通知', 'hint' => '发给某个会员的站内通知。', 'url' => '/admin/video/notifies'],
            'video_ads' => ['group' => 'site', 'label' => '广告', 'hint' => '投放位上的广告代码或内容。', 'url' => '/admin/video/ads'],
            'video_slides' => ['group' => 'site', 'label' => '幻灯', 'hint' => '首页等位置的轮播图。', 'url' => '/admin/video/slides'],
            'friend_links' => ['group' => 'site', 'label' => '友情链接', 'hint' => '页脚交换链接，不是网址导航。', 'url' => '/admin/video/links'],
            'video_websites' => ['group' => 'site', 'label' => '网址导航', 'hint' => '顶栏「导航」目录，可挂分类。不是页脚友链。', 'url' => '/admin/video/websites'],
            'video_domains' => ['group' => 'site', 'label' => '绑定域名', 'hint' => '不同域名可以用不同主题。', 'url' => '/admin/video/templates'],
            'video_options' => ['group' => 'site', 'label' => '站点键值', 'hint' => '站点设置存成 k/v，不是影片字段。', 'url' => '/admin/video/settings'],
            'video_search_words' => ['group' => 'site', 'label' => '搜索词', 'hint' => '有人搜过的词和次数。', 'url' => '/admin/video/searchwords'],
            'video_ulogs' => ['group' => 'stats', 'label' => '会员行为', 'hint' => '会员播放等行为流水。', 'url' => '/admin/video/members'],
            'video_visit_days' => ['group' => 'stats', 'label' => '按日访问', 'hint' => '每天的 PV / UV 汇总。', 'url' => '/admin/stats'],
            'video_visit_items' => ['group' => 'stats', 'label' => '按片访问', 'hint' => '某天某部片子或某分类的浏览量。', 'url' => '/admin/stats'],
            'video_access_logs' => ['group' => 'stats', 'label' => '访问流水', 'hint' => '前台 GET 流水。爬虫会标出来，完整图表在统计。', 'url' => '/admin/video/accesslogs'],
            'stat_hits' => ['group' => 'stats', 'label' => '点击明细', 'hint' => '带蜘蛛识别的访问明细，图表用这张。', 'url' => '/admin/stats'],
            'plugin_mangas' => ['group' => 'plugin', 'label' => '漫画', 'hint' => '漫画插件的作品表，不是影片分类。关掉插件后台入口会消失，表还在。', 'url' => '/admin/plugins'],
            'plugin_manga_chapters' => ['group' => 'plugin', 'label' => '漫画章节', 'hint' => '一话对应一组图片地址。属于漫画插件。', 'url' => '/admin/plugins'],
            'plugin_mall_goods' => ['group' => 'plugin', 'label' => '积分商品', 'hint' => '商城插件。积分兑换，不接微信支付宝。', 'url' => '/admin/plugins'],
            'plugin_mall_orders' => ['group' => 'plugin', 'label' => '兑换订单', 'hint' => '谁用积分换了哪个商品。属于商城插件。', 'url' => '/admin/plugins'],
            'plugin_chat_messages' => ['group' => 'plugin', 'label' => '播放页聊天', 'hint' => '聊天室插件，挂在某部片子播放页。', 'url' => '/admin/plugins'],
            'plugin_sms_codes' => ['group' => 'plugin', 'label' => '短信验证码', 'hint' => '短信插件发出去的验证码，有过期时间。', 'url' => '/admin/plugins'],
            'video_danmaku' => ['group' => 'plugin', 'label' => '弹幕', 'hint' => '弹幕插件。一条弹幕一行，挂在片子或某一集。', 'url' => '/admin/plugins'],
            'sys_user' => ['group' => 'system', 'label' => '管理员', 'hint' => '能进后台的人。前台会员不在这里。', 'url' => '/admin/user'],
            'sys_user_log' => ['group' => 'system', 'label' => '登录日志', 'hint' => '管理员登录成功记一笔。', 'url' => '/admin/system/monitor/login-logs'],
            'sys_user_role' => ['group' => 'system', 'label' => '管理员-角色', 'hint' => '哪个管理员挂了哪个角色。', 'url' => '/admin/user'],
            'sys_role' => ['group' => 'system', 'label' => '角色', 'hint' => '后台角色名。影片角色卡不在这里。', 'url' => '/admin/system/roles'],
            'sys_perm' => ['group' => 'system', 'label' => '权限点', 'hint' => '菜单和接口权限。', 'url' => '/admin/system/roles'],
            'sys_role_perm' => ['group' => 'system', 'label' => '角色-权限', 'hint' => '角色拥有哪些权限点。', 'url' => '/admin/system/roles'],
            'sys_schedule' => ['group' => 'system', 'label' => '计划任务', 'hint' => '自己加的定时任务。系统自带的人气日清不写在这。', 'url' => '/admin/system/tools/schedule'],
            'sys_schedule_log' => ['group' => 'system', 'label' => '任务执行记录', 'hint' => '自己加的任务每次跑一行：成败、耗时、输出。系统自带任务不记在这。', 'url' => '/admin/system/tools/schedule'],
            'sys_dict' => ['group' => 'system', 'label' => '字典选项', 'hint' => '下拉框、状态码。影片分类不在这里。', 'url' => '/admin/system/dicts'],
            'sys_file' => ['group' => 'system', 'label' => '附件', 'hint' => '后台传过的文件记录。', 'url' => '/admin/system/attachments'],
            'sys_operate_log' => ['group' => 'system', 'label' => '操作日志', 'hint' => '谁在后台改过什么。', 'url' => '/admin/system/monitor/operate-logs'],
            'sys_system_log' => ['group' => 'system', 'label' => '系统日志', 'hint' => '程序抛错会记一行，不是谁改了数据。', 'url' => '/admin/system/monitor/system-logs'],
            'users' => ['group' => 'framework', 'label' => 'Laravel 用户', 'hint' => '框架自带。本站后台账号在「管理员」，前台在「会员」。这张多半空着。', 'url' => ''],
            'password_reset_tokens' => ['group' => 'framework', 'label' => '重置密码令牌', 'hint' => 'Laravel 邮件重置密码用。会员登录未必走这张。', 'url' => ''],
            'sessions' => ['group' => 'framework', 'label' => '会话', 'hint' => 'SESSION_DRIVER=database 时才写这里。', 'url' => ''],
            'cache' => ['group' => 'framework', 'label' => '数据缓存', 'hint' => 'CACHE_STORE=database 时才写这里。清缓存页在系统工具。', 'url' => '/admin/system/tools/cache'],
            'cache_locks' => ['group' => 'framework', 'label' => '缓存锁', 'hint' => '防并发的锁，一般不用手改。', 'url' => '/admin/system/tools/cache'],
            'jobs' => ['group' => 'framework', 'label' => '队列任务', 'hint' => 'QUEUE_CONNECTION=database 时才有积压。', 'url' => ''],
            'job_batches' => ['group' => 'framework', 'label' => '队列批次', 'hint' => '一批队列任务的进度。', 'url' => ''],
            'failed_jobs' => ['group' => 'framework', 'label' => '失败队列', 'hint' => '队列跑失败后的记录。', 'url' => ''],
            'migrations' => ['group' => 'framework', 'label' => '迁移记录', 'hint' => '哪些数据库迁移已经跑过。不要手改。', 'url' => ''],
        ];
    }

    public function tableMeta(string $name): array
    {
        $known = $this->tables()[$name] ?? null;
        if ($known === null) {
            $plugin = str_starts_with($name, 'plugin_');

            return [
                'group' => $plugin ? 'plugin' : 'other',
                'group_label' => $plugin ? '插件' : '其它',
                'label' => $name,
                'hint' => $plugin
                    ? '插件建的表，代码目录里还没写这一张的用途。关掉插件后表可能还在。'
                    : '后来加的表，代码目录里没有写死用途。',
                'url' => $plugin ? '/admin/plugins' : '',
                'known' => false,
            ];
        }
        $groups = $this->groups();

        return [
            'group' => $known['group'],
            'group_label' => $groups[$known['group']] ?? $known['group'],
            'label' => $known['label'],
            'hint' => $known['hint'],
            'url' => $known['url'],
            'known' => true,
        ];
    }

    public function fieldPurpose(string $table, string $field): string
    {
        $field = trim($field);
        $specific = $this->tableFields()[$table][$field] ?? null;
        if (is_string($specific) && $specific !== '') {
            return $specific;
        }
        $common = $this->commonFields()[$field] ?? null;
        if (is_string($common) && $common !== '') {
            return $common;
        }

        return $this->guessField($field);
    }

    /** @return array<string, string> */
    public function commonFields(): array
    {
        return [
            'id' => '本表主键',
            'created_at' => '写入时间（多为 Unix 秒）',
            'updated_at' => '最后改动时间（多为 Unix 秒）',
            'create_time' => '写入时间（Unix 秒）',
            'update_time' => '最后改动时间（Unix 秒）',
            'create_at' => '写入时间的可读文本（和 create_time 一起存）',
            'update_at' => '改动时间的可读文本（和 update_time 一起存）',
            'deleted_at' => '进回收站的时间；0 表示还在片库',
            'status' => '开关或状态。不同表取值不一样，看这张表的说明',
            'sort' => '排序，越大越靠前或按后台约定',
            'name' => '名称',
            'title' => '标题',
            'slug' => '网址用的英文标识',
            'cover' => '封面图地址',
            'pic' => '图片地址',
            'url' => '链接或播放地址',
            'content' => '正文或详情',
            'remark' => '备注',
            'remarks' => '备注，如前台角标「更新至 12」',
            'note' => '说明',
            'blurb' => '一句话简介',
            'description' => '简介',
            'ip' => 'IP',
            'login_ip' => '登录 IP',
            'ip_address' => 'IP 归属地（能解析才有）',
            'member_id' => '会员编号，对应 members.id',
            'video_id' => '影片编号，对应 videos.id',
            'type_id' => '分类编号，对应 video_types.id',
            'parent_id' => '上级编号，0 表示没有上级',
            'user_id' => '用户编号',
            'role_id' => '角色编号',
            'hits' => '人气 / 点击次数',
            'password' => '密码哈希，不是明文',
            'email' => '邮箱',
            'username' => '登录名',
            'token' => '令牌',
            'uid' => '管理员编号，对应 sys_user.id',
            'code' => '标识码',
            'label' => '给人看的名字',
            'icon' => '图标名',
            'method' => 'HTTP 方法或调用方式',
            'module' => '模块名',
            'level' => '推荐等级或权重',
            'points' => '积分',
            'amount' => '数量或金额（本站订单是积分，不是人民币收款）',
            'avatar' => '头像地址',
            'logo' => '标志图',
            'banner' => '横幅图',
            'area' => '地区',
            'lang' => '语言',
            'year' => '年代',
            'letter' => '首字母，方便检索',
            'seo_title' => 'SEO 标题',
            'seo_keywords' => 'SEO 关键词',
            'seo_description' => 'SEO 描述',
            'seo_key' => 'SEO 关键词',
            'seo_des' => 'SEO 描述',
            'tpl' => '指定模板文件',
            'parse' => '解析地址或脚本，空则直链播放',
            'engine' => '播放内核：artplayer / dplayer / videojs / iframe',
            'is_read' => '是否已读：1 已读 0 未读',
            'is_bot' => '是否识别成爬虫：1 是 0 不是',
            'is_spider' => '是否识别成爬虫',
            'is_recommend' => '是否推荐',
            'is_hot' => '是否热门',
            'is_regex' => '词表是否按正则匹配',
            'remember_token' => '「记住我」登录令牌',
            'email_verified_at' => '邮箱验证时间；本站会员未必用',
            'payload' => '序列化内容',
            'user_agent' => '浏览器 UA',
            'ua' => '浏览器 UA',
            'referer' => '来源页',
            'locale' => '语言代码',
            'path' => '路径',
            'query' => '查询串',
            'host' => '域名主机名',
            'theme' => '主题目录名',
            'slot' => '投放位置标识',
            'msg' => '结果说明',
            'last_msg' => '上次运行说明',
            'last_error' => '上次失败原因',
            'expire_at' => '过期时间；0 表示不限',
            'used_by' => '谁用过（会员编号）',
            'used_at' => '使用时间；0 表示还没人用',
            'from_id' => '发送人会员编号',
            'to_id' => '接收人会员编号',
            'order_no' => '订单号',
            'trade_no' => '渠道流水号；手工单可空',
            'channel' => '渠道。订单默认 manual，不是微信支付',
            'account' => '收款账号，提现人工打款用',
            'group_id' => '会员组编号',
            'collect_source_id' => '采集源编号',
            'collect_id' => '资源站那边的影片 ID',
            'source_id' => '播放线路编号',
            'episode_id' => '剧集编号',
            'episode_num' => '第几集',
            'episode_name' => '集标题',
            'actor_id' => '演员编号',
            'tag_id' => '标签编号',
            'topic_id' => '专题编号',
            'art_id' => '文章编号',
            'perm_id' => '权限点编号',
            'server_id' => '服务器组编号',
            'type_pid' => '父分类编号，方便列表筛选',
            'hits_day' => '今日人气',
            'hits_week' => '本周人气',
            'hits_month' => '本月人气',
            'score' => '评分',
            'score_all' => '评分总分',
            'score_num' => '评分人数',
            'up' => '顶',
            'down' => '踩',
            'pv' => '浏览次数',
            'uv' => '独立访客大约数',
            'day' => '日期，常用 YYYYMMDD 数字',
            'weekday' => '更新日期，如周一',
            'publish_at' => '定时上架时间；0 表示立刻或已上',
            'duration' => '时长',
            'subtitle' => '副标题',
            'director' => '导演',
            'writer' => '编剧',
            'serial' => '连载信息',
            'total' => '总集数',
            'isend' => '是否完结：1 完结 0 连载',
            'lock' => '是否锁定，锁定后采集不会覆盖',
            'class' => '扩展分类文本',
            'author_name' => '显示名（未登录时手填）',
            'reply' => '管理员回复',
            'phone' => '手机号',
            'qq_openid' => 'QQ 登录标识；没接 OAuth 就空着',
            'wechat_openid' => '微信登录标识；没接 OAuth 就空着',
            'last_login_at' => '上次登录时间',
            'points_min' => '进入该会员组所需最低积分',
            'trysee' => '试看秒数；0 表示按站点默认',
            'day_free' => '每天免费看几部；0 表示不限这套规则',
            'need_login' => '是否必须登录才能看',
            'balance' => '变动后的积分余额',
            'from_word' => '搜索里的原词',
            'to_word' => '当成这个词去搜',
            'words' => '词表，一行一个或按规则拼',
            'scope' => '作用范围，如 title',
            'action' => '命中后怎么处理：跳过、待审等',
            'cron_expression' => '多久跑一次（cron）',
            'pages' => '每次采几页',
            'hours' => '只采最近多少小时的更新',
            'last_run_at' => '上次运行时间',
            'last_collect_at' => '上次采集时间',
            'last_page' => '上次采到第几页',
            'last_created' => '上次新建条数',
            'last_updated' => '上次更新条数',
            'page' => '页码',
            'created_n' => '新建条数',
            'updated_n' => '更新条数',
            'skipped_n' => '跳过条数',
            'ok' => '这次是否成功：1 成功 0 失败',
            'api_url' => '接口地址',
            'api_type' => '接口格式，如 json',
            'mid' => '资源类型：1 影片 2 文章 3 网址',
            'param' => '额外请求参数',
            'bind_json' => '分类绑定（JSON）',
            'list_rule' => '列表页提取规则（占位）',
            'title_rule' => '标题提取规则（占位）',
            'url_rule' => '地址提取规则（占位）',
            'content_rule' => '正文提取规则（占位）',
            'downer' => '下载器代码',
            'player' => '播放器代码',
            'type' => '类型。各表含义不同，看该表说明',
            'k' => '配置键',
            'v' => '配置值',
            'dict_type' => '字典分组',
            'dict_key' => '字典标识',
            'value_type' => '值类型：文本、整数、JSON 等',
            'value_string' => '文本值',
            'value_int' => '整数值',
            'value_float' => '小数值',
            'value_json' => 'JSON 值',
            'value_text' => '长文本值',
            'enum_limit' => '枚举可选范围',
            'size' => '字节数',
            'md5' => '文件指纹',
            'mime' => 'MIME 类型',
            'file' => '出错文件路径',
            'line' => '出错行号',
            'trace' => '堆栈',
            'message' => '日志正文',
            'context' => '上下文 JSON',
            'extra' => '附加信息',
            'exception_class' => '异常类名',
            'exception_message' => '异常说明',
            'request_id' => '请求追踪号',
            'request_data' => '请求体摘要',
            'response_code' => '接口返回码',
            'response_msg' => '接口返回说明',
            'duration_ms' => '耗时毫秒',
            'target_type' => '对象类型',
            'target_id' => '对象编号',
            'permission' => '权限码',
            'route' => '路由名或路径',
            'login_agent' => '登录时的 UA',
            'role' => '旧的角色标记，以 role_id 为准',
            'login_time' => '最近登录时间',
            'ui_locale' => '后台界面语言',
            'pid' => '父权限编号，0 表示顶级',
            'api' => '接口路径',
            'command' => '要跑的命令或网址',
            'params' => '命令参数',
            'timezone' => '时区',
            'without_overlapping' => '上一轮没跑完是否跳过这一轮',
            'on_one_server' => '多机是否只跑一台',
            'run_in_maintenance' => '维护模式是否仍跑',
            'timeout' => '超时秒数；0 表示不限',
            'max_attempts' => '最多尝试几次',
            'last_run_time' => '上次运行时间',
            'last_status' => '上次结果：成功/失败',
            'next_run_time' => '下次计划时间',
            'key' => '缓存键',
            'value' => '缓存内容',
            'expiration' => '过期时间戳',
            'owner' => '锁持有者',
            'queue' => '队列名',
            'attempts' => '已尝试次数',
            'reserved_at' => '被取出执行的时间',
            'available_at' => '最早可执行时间',
            'uuid' => '唯一编号',
            'connection' => '队列连接名',
            'exception' => '失败堆栈',
            'failed_at' => '失败时间',
            'total_jobs' => '这一批共几条',
            'pending_jobs' => '还没跑完几条',
            'failed_jobs' => '失败几条',
            'failed_job_ids' => '失败任务编号列表',
            'options' => '批次选项',
            'cancelled_at' => '取消时间',
            'finished_at' => '完成时间',
            'visitor_hash' => '访客指纹，用来大约算 UV',
            'status_code' => 'HTTP 状态码',
            'spider_name' => '认出的爬虫名',
            'min_points' => '使用门槛积分',
            'day_key' => '签到日期键，如 20260917',
            'days' => '连续签到天数',
            'word' => '搜索词',
            'cover_thumb' => '缩略封面',
            'cover_slide' => '专题幻灯图',
            'sub' => '副标题',
            'color' => '标题颜色',
            'tag' => '专题标签文本',
            'extend' => '扩展 JSON',
            'time_hits' => '人气更新时间',
            'tpl_list' => '列表页模板',
            'tpl_detail' => '详情页模板',
            'tpl_play' => '播放页模板',
            'douban_id' => '豆瓣条目 ID；0 表示没有',
            'douban_score' => '豆瓣分',
            'comment_up' => '评论被顶次数',
            'comment_report' => '评论被举报次数',
            'role_type' => '1 主演 其它为配角等',
            'sex' => '性别',
            'birthday' => '生日',
            'author' => '作者',
            'pics' => '图片地址列表（多为多行或 JSON）',
            'stock' => '库存件数',
            'hint' => '兑换说明',
            'goods_id' => '商品编号',
            'goods_name' => '下单时的商品名（快照）',
            'text' => '文本内容',
            'scene' => '使用场景，如 register',
            'manga_id' => '漫画作品编号',
            'mode' => '弹幕样式',
            'last_activity' => '最后活动时间',
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function tableFields(): array
    {
        return [
            'videos' => [
                'title' => '片名',
                'status' => '1 已发布 0 隐藏；定时上架看 publish_at',
                'type' => '不用这列区分影片类型，分类在 type_id',
            ],
            'video_types' => [
                'mid' => '1 影片栏目 2 文章栏目 3 网址栏目',
                'status' => '1 显示 0 隐藏',
            ],
            'video_sources' => [
                'name' => '线路名，如「高清」',
                'type' => '地址形态，如 m3u8',
                'status' => '1 启用 0 停用这条线路',
            ],
            'video_episodes' => [
                'url' => '这一集的播放地址',
                'duration' => '时长秒数；0 表示未知',
            ],
            'video_players' => [
                'code' => '线路里填写的播放器代码',
                'parse' => '解析接口。带 {url} 或 http 时按 iframe 打开',
            ],
            'video_roles' => [
                'name' => '角色名',
                'video_id' => '出现在哪部片子；0 表示还没挂片',
                'actor_id' => '谁演的；0 表示没绑演员',
            ],
            'video_actor_rel' => [
                'role_type' => '1 主演，其它为配角等',
            ],
            'sys_user' => [
                'role' => '旧字段，权限以 role_id 为准',
                'token' => '后台登录令牌',
                'status' => '1 允许登录 0 停用',
            ],
            'sys_role' => [
                'code' => '角色标识码',
                'status' => '1 可用 0 停用',
            ],
            'sys_perm' => [
                'type' => '权限类型（菜单/接口）',
                'code' => '权限码',
            ],
            'sys_schedule' => [
                'type' => 'artisan / http / shell',
                'status' => '1 开着 0 停用',
                'code' => '可选内部代号',
                'last_duration_ms' => '上次跑完用了多少毫秒',
            ],
            'sys_schedule_log' => [
                'schedule_id' => '对应 sys_schedule.id',
                'status' => '1 成功 2 失败',
                'duration_ms' => '这次跑完用了多少毫秒',
                'output' => '命令输出摘要',
                'error' => '失败原因',
                'create_time' => '这次开始跑的时间',
            ],
            'sys_dict' => [
                'status' => '1 启用 0 停用这条选项',
            ],
            'sys_operate_log' => [
                'title' => '做了什么（给人看的一句）',
                'action' => '动作码，如 save、delete',
                'status' => '这次请求是否成功记下',
            ],
            'sys_system_log' => [
                'level' => 'error / warning / info 等',
                'channel' => '日志通道',
            ],
            'members' => [
                'name' => '会员昵称或登录名',
                'status' => '1 正常 0 禁用',
                'points' => '当前积分',
            ],
            'member_orders' => [
                'status' => '0 待处理 1 已完成等，按订单页为准',
                'amount' => '标价积分，不是人民币',
                'channel' => '来源，默认 manual；没有微信支付',
            ],
            'member_withdraws' => [
                'status' => '0 待审 1 已处理等',
                'amount' => '申请兑出的积分',
            ],
            'member_point_logs' => [
                'points' => '本次加减的积分（可负）',
                'type' => '来源，如 sys、card、sign',
            ],
            'member_pms' => [
                'title' => '私信标题',
            ],
            'video_comments' => [
                'status' => '1 显示 0 隐藏待审',
                'parent_id' => '回复哪条评论；0 表示顶层',
            ],
            'video_reports' => [
                'status' => '0 未处理 1 已处理',
                'content' => '用户填写的问题',
            ],
            'video_play_fails' => [
                'status' => '0 未处理 1 已看过',
                'content' => '失败说明',
                'url' => '播不了的地址',
            ],
            'video_guestbooks' => [
                'status' => '1 显示 0 隐藏',
            ],
            'video_ads' => [
                'content' => '广告 HTML 或代码',
                'type_id' => '只在某分类下展示；0 表示不限',
            ],
            'video_collect_temps' => [
                'status' => '0 待审 1 已转入等',
                'payload' => '采回来的原始数据',
                'title' => '待转入的片名',
            ],
            'video_collect_tasks' => [
                'status' => '1 开着 0 停用这条定时采集',
            ],
            'video_cj_rules' => [
                'status' => '占位规则默认关着。请用接口采集',
                'url' => '要扒的列表页（未接通）',
            ],
            'video_ulogs' => [
                'type' => '行为类型，如 play',
            ],
            'video_options' => [
                'k' => '设置项键名',
                'v' => '设置项内容',
            ],
            'video_access_logs' => [
                'url' => '访问的路径',
            ],
            'stat_hits' => [
                'path' => '访问路径',
                'created_at' => '访问时间（时间戳列）',
            ],
            'sessions' => [
                'id' => '会话 ID',
                'user_id' => '对应 Laravel users，本站会员通常不写这里',
            ],
            'users' => [
                'name' => '框架用户名。后台请看 sys_user',
            ],
            'migrations' => [
                'migration' => '迁移文件名',
                'batch' => '第几批执行的',
            ],
            'video_topics' => [
                'type' => '专题扩展类型文本，不是影片分类 ID',
                'tag' => '专题上的标签词',
            ],
            'member_follows' => [
                'target_type' => '关注对象种类，默认 actor',
            ],
            'member_dynamics' => [
                'type' => '动态形态，如 text',
            ],
            'video_audit_rules' => [
                'status' => '1 启用这条词 0 停用',
            ],
            'video_danmaku' => [
                'text' => '弹幕内容',
                'color' => '弹幕颜色',
                'time' => '出现在播放第几秒',
                'mode' => '滚动/顶部等样式',
                'status' => '1 显示 0 屏蔽',
            ],
            'plugin_manga_chapters' => [
                'name' => '话名，如第 1 话',
                'pics' => '这一话的图片地址，一行一张或按插件约定',
            ],
            'plugin_mangas' => [
                'title' => '漫画名',
                'status' => '1 上架 0 下架',
            ],
            'plugin_mall_goods' => [
                'points' => '兑换所需积分',
                'status' => '1 可兑 0 下架',
            ],
            'plugin_mall_orders' => [
                'status' => '兑换单状态',
                'points' => '扣掉的积分',
            ],
            'plugin_chat_messages' => [
                'text' => '聊天内容',
                'name' => '当时显示的昵称',
                'status' => '1 显示 0 屏蔽',
            ],
            'plugin_sms_codes' => [
                'code' => '验证码',
                'scene' => '发给谁用，如 register 注册',
            ],
        ];
    }

    public function guessField(string $field): string
    {
        if (str_ends_with($field, '_id')) {
            return '关联编号';
        }
        if (str_ends_with($field, '_at') || str_ends_with($field, '_time')) {
            return '时间';
        }
        if (str_starts_with($field, 'is_')) {
            return '是/否';
        }
        if (str_starts_with($field, 'seo_')) {
            return '给搜索引擎看的文字';
        }
        if (str_starts_with($field, 'tpl_')) {
            return '模板文件名';
        }
        if (str_starts_with($field, 'hits_')) {
            return '人气计数';
        }

        return '这一列在库里有，目录按列名推断用途。';
    }
}
