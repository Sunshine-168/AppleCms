<?php

namespace App\Services\Admin\Video;

use App\Models\Member\Member;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberInvite;
use App\Models\Member\MemberOrder;
use App\Models\Member\MemberPm;
use App\Models\Member\MemberPointLog;
use App\Models\Member\MemberWithdraw;
use App\Models\Video\CollectSourceModel;
use App\Models\Video\FriendLink;
use App\Models\Video\VideoAd;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoCard;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoSlide;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoTopicArtRelModel;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTopicRelModel;
use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoAuditRule;
use App\Models\Video\VideoCollectLog;
use App\Models\Video\VideoCollectTask;
use App\Models\Video\VideoCollectTemp;
use App\Models\Video\VideoSearchWord;
use App\Models\Video\VideoUnion;
use App\Models\Video\VideoEpisodeModel;
use App\Models\Video\VideoGuestbook;
use App\Models\Video\VideoNotify;
use App\Models\Video\VideoPlayFail;
use App\Models\Video\VideoReport;
use App\Support\AdminOpLog;
use App\Support\Utils\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteModuleService
{
    /** @return array<string, mixed> */
    public function config(string $module): array
    {
        $fromPlugin = app(\App\Support\Plugins\PluginHost::class)->findModule($module);
        if ($fromPlugin !== null) {
            return $fromPlugin;
        }

        return match ($module) {
            'topics' => [
                'title' => '专题管理',
                'hint' => '专题是片单。先建「贺岁档」「冷门佳片」这类栏目，再点绑片挂影片，需要资讯时点绑文。',
                'model' => \App\Models\Video\VideoTopicModel::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'slug', 'label' => '别名', 'type' => 'text'],
                    ['name' => 'cover', 'label' => '封面', 'type' => 'text'],
                    ['name' => 'blurb', 'label' => '简介', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'sub', 'label' => '副标', 'type' => 'text'],
                    ['name' => 'letter', 'label' => '首字母', 'type' => 'text'],
                    ['name' => 'color', 'label' => '高亮色', 'type' => 'text'],
                    ['name' => 'level', 'label' => '推荐', 'type' => 'number'],
                    ['name' => 'remarks', 'label' => '备注', 'type' => 'text'],
                    ['name' => 'cover_thumb', 'label' => '缩略图', 'type' => 'text'],
                    ['name' => 'cover_slide', 'label' => '幻灯', 'type' => 'text'],
                    ['name' => 'tpl', 'label' => '模板', 'type' => 'text'],
                    ['name' => 'type', 'label' => '扩展分类', 'type' => 'text'],
                    ['name' => 'tag', 'label' => '标签', 'type' => 'text'],
                    ['name' => 'seo_title', 'label' => 'SEO标题', 'type' => 'text'],
                    ['name' => 'seo_key', 'label' => 'SEO关键字', 'type' => 'text'],
                    ['name' => 'seo_des', 'label' => 'SEO描述', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '上架', '0' => '下架']],
                ],
                'cols' => ['id', 'name', 'slug', 'status', 'sort'],
            ],
            'players' => [
                'title' => '播放器',
                'hint' => '播放器是线路用的内核。直链用 ArtPlayer / DPlayer / Video.js；加密采集地址填解析接口。标识要和线路上的播放器字段一致。',
                'model' => \App\Models\Video\VideoPlayerModel::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'code', 'label' => '标识', 'type' => 'text'],
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'engine', 'label' => '内核', 'type' => 'text'],
                    ['name' => 'parse', 'label' => '解析', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'code', 'name', 'engine', 'status', 'sort'],
            ],
            'links' => [
                'title' => '友情链接',
                'hint' => '友链出现在页脚。默认主题只显示文字；有 Logo 时自定义主题可改成图链。隐藏后前台不再输出。',
                'model' => \App\Models\Video\FriendLink::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'url', 'label' => '网址', 'type' => 'text'],
                    ['name' => 'logo', 'label' => 'Logo', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '隐藏']],
                ],
                'cols' => ['id', 'name', 'url', 'status', 'sort'],
            ],
            'comments' => [
                'title' => '评论管理',
                'hint' => '前台发来的评论在这里审。打开站点设置里的「评论审核」后，新评论会先进入待审。',
                'model' => \App\Models\Video\VideoComment::class,
                'search' => 'content',
                'fields' => [
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                    ['name' => 'author_name', 'label' => '昵称', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '待审 / 隐藏']],
                ],
                'cols' => ['id', 'video_id', 'author_name', 'content', 'comment_report', 'status'],
            ],
            'reports' => [
                'title' => '报错',
                'hint' => '会员在详情页报的无法播放。处理完打标；删掉只去记录，不改播放地址。',
                'model' => \App\Models\Video\VideoReport::class,
                'search' => 'content',
                'fields' => [
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '未处理', '1' => '已处理']],
                ],
                'cols' => ['id', 'video_id', 'content', 'status'],
            ],
            'members' => [
                'title' => '会员',
                'hint' => '会员登录的是网站，不是后台。积分用来点播，分组决定试看和门槛。',
                'model' => \App\Models\Member\Member::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '昵称', 'type' => 'text'],
                    ['name' => 'email', 'label' => '邮箱', 'type' => 'text'],
                    ['name' => 'password', 'label' => '密码', 'type' => 'text'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'group_id', 'label' => '会员组', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '正常', '0' => '停用']],
                ],
                'cols' => ['id', 'name', 'email', 'points', 'group_id', 'status'],
            ],
            'cards' => [
                'title' => '积分卡密',
                'hint' => '批量生成后发给会员，他们在会员中心兑换加积分。每张只能用一次。已兑的不能改、不能删。',
                'model' => VideoCard::class,
                'search' => 'code',
                'fields' => [
                    ['name' => 'code', 'label' => '卡密', 'type' => 'text'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '未用', '0' => '作废']],
                ],
                'cols' => ['id', 'code', 'points', 'status', 'used_by', 'used_at'],
            ],
            'downloaders' => [
                'title' => '下载器',
                'model' => \App\Models\Video\VideoDownloader::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'code', 'label' => '标识', 'type' => 'text'],
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'parse', 'label' => '解析', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'code', 'name', 'status', 'sort'],
            ],
            'servers' => [
                'title' => '服务器组',
                'model' => \App\Models\Video\VideoServer::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'url', 'label' => '地址前缀', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'name', 'url', 'status', 'sort'],
            ],
            'playfails' => [
                'title' => '播放失败',
                'hint' => '播放页点报错记下的坏链。标已处理不会改地址；下线线路才会关掉这条线。',
                'model' => \App\Models\Video\VideoPlayFail::class,
                'search' => 'content',
                'fields' => [
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                    ['name' => 'source_id', 'label' => '线路ID', 'type' => 'number'],
                    ['name' => 'url', 'label' => '地址', 'type' => 'text'],
                    ['name' => 'content', 'label' => '说明', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '未处理', '1' => '已处理']],
                ],
                'cols' => ['id', 'video_id', 'source_id', 'url', 'content', 'status', 'created_at'],
            ],
            'audits' => [
                'title' => '入库审核规则',
                'hint' => '采集时扫标题、简介、演员。命中后可跳过、先下架，或把词抠掉再入库。',
                'model' => VideoAuditRule::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'scope', 'label' => '范围', 'type' => 'select', 'options' => ['title' => '标题', 'content' => '简介', 'actor' => '演员']],
                    ['name' => 'words', 'label' => '关键词', 'type' => 'textarea'],
                    ['name' => 'is_regex', 'label' => '正则', 'type' => 'select', 'options' => ['0' => '普通匹配', '1' => '正则']],
                    ['name' => 'action', 'label' => '动作', 'type' => 'select', 'options' => ['skip' => '跳过不入库', 'review' => '入库并下架', 'replace' => '抠词后再入库']],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                ],
                'cols' => ['id', 'name', 'scope', 'action', 'status'],
            ],
            'collect_tasks' => [
                'title' => '定时采集',
                'hint' => '到点自动采。服务器要跑 Laravel 调度，也可以在列表里立刻检查一遍。',
                'model' => VideoCollectTask::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'collect_source_id', 'label' => '采集源', 'type' => 'number'],
                    ['name' => 'cron_expression', 'label' => '周期', 'type' => 'text'],
                    ['name' => 'pages', 'label' => '页数', 'type' => 'number'],
                    ['name' => 'hours', 'label' => '范围', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'name', 'collect_source_id', 'cron_expression', 'pages', 'hours', 'status', 'last_run_at', 'last_msg'],
            ],
            'ads' => [
                'title' => '广告位',
                'hint' => '广告按位置出现。页头在导航旁，页脚在版权上，播放页在播放器下。过期或停用后前台不再输出。',
                'model' => \App\Models\Video\VideoAd::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'slot', 'label' => '位置', 'type' => 'text'],
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'type_id', 'label' => '分类', 'type' => 'number'],
                    ['name' => 'expire_at', 'label' => '到期', 'type' => 'text'],
                    ['name' => 'content', 'label' => '代码', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'slot', 'name', 'type_id', 'expire_at', 'status', 'sort'],
            ],
            'guestbooks' => [
                'title' => '留言',
                'hint' => '访客在留言板写的。后台回复会出现在那条下面；待审的不会出现在前台。',
                'model' => \App\Models\Video\VideoGuestbook::class,
                'search' => 'content',
                'fields' => [
                    ['name' => 'author_name', 'label' => '昵称', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'reply', 'label' => '回复', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '待审']],
                ],
                'cols' => ['id', 'author_name', 'content', 'reply', 'status'],
            ],
            'groups' => [
                'title' => '会员组',
                'hint' => '会员组是点播档位。积分门槛、试看秒数、每天免费条数在这里。停用后这组权限关掉，人还在名单里。删掉后会员变成未分组。',
                'model' => \App\Models\Member\MemberGroup::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'points_min', 'label' => '积分门槛', 'type' => 'number'],
                    ['name' => 'trysee', 'label' => '试看秒数', 'type' => 'number'],
                    ['name' => 'day_free', 'label' => '每天免费条数', 'type' => 'number'],
                    ['name' => 'need_login', 'label' => '点播需登录', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'name', 'points_min', 'trysee', 'day_free', 'status', 'sort'],
            ],
            'orders' => [
                'title' => '订单',
                'hint' => '订单是充值入账。确认已付会给会员加积分，每笔只加一次。关掉或删除不会扣回去。',
                'model' => \App\Models\Member\MemberOrder::class,
                'search' => 'order_no',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'order_no', 'label' => '单号', 'type' => 'text'],
                    ['name' => 'amount', 'label' => '金额分', 'type' => 'number'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'channel', 'label' => '渠道', 'type' => 'select', 'options' => ['manual' => '人工', 'wechat' => '微信', 'alipay' => '支付宝']],
                    ['name' => 'trade_no', 'label' => '支付流水', 'type' => 'text'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '待付', '1' => '已付', '2' => '关闭']],
                    ['name' => 'remark', 'label' => '备注', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'order_no', 'amount', 'points', 'channel', 'status'],
            ],
            'withdraws' => [
                'title' => '提现',
                'hint' => '积分兑现金申请，待审后打款或拒绝',
                'model' => \App\Models\Member\MemberWithdraw::class,
                'search' => 'account',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'amount', 'label' => '金额分', 'type' => 'number'],
                    ['name' => 'account', 'label' => '账号', 'type' => 'text'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '待审', '1' => '已打款', '2' => '拒绝']],
                    ['name' => 'remark', 'label' => '备注', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'amount', 'account', 'status'],
            ],
            'pms' => [
                'title' => '站内信',
                'hint' => '发给指定会员，会员在站内信里看',
                'model' => \App\Models\Member\MemberPm::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'from_id', 'label' => '发件人ID(0系统)', 'type' => 'number'],
                    ['name' => 'to_id', 'label' => '收件人ID', 'type' => 'number'],
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'is_read', 'label' => '已读', 'type' => 'select', 'options' => ['0' => '未读', '1' => '已读']],
                ],
                'cols' => ['id', 'from_id', 'to_id', 'title', 'is_read', 'created_at'],
            ],
            'collect_logs' => [
                'title' => '采集日志',
                'hint' => '每次采集当天、本周、全部都会记一行。失败看说明；删记录不影响片库。',
                'model' => VideoCollectLog::class,
                'search' => 'msg',
                'fields' => [
                    ['name' => 'collect_source_id', 'label' => '采集源ID', 'type' => 'number'],
                    ['name' => 'page', 'label' => '页码', 'type' => 'number'],
                    ['name' => 'created_n', 'label' => '新建', 'type' => 'number'],
                    ['name' => 'updated_n', 'label' => '更新', 'type' => 'number'],
                    ['name' => 'skipped_n', 'label' => '跳过', 'type' => 'number'],
                    ['name' => 'ok', 'label' => '成功', 'type' => 'select', 'options' => ['1' => '是', '0' => '否']],
                    ['name' => 'msg', 'label' => '说明', 'type' => 'text'],
                ],
                'cols' => ['id', 'collect_source_id', 'page', 'created_n', 'updated_n', 'skipped_n', 'ok', 'msg', 'created_at'],
            ],
            'plogs' => [
                'title' => '积分流水',
                'hint' => '点播、充值、卡密、后台调积分都会记在这里。删记录不会改会员积分。',
                'model' => MemberPointLog::class,
                'search' => 'remark',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'points', 'label' => '变动', 'type' => 'number'],
                    ['name' => 'remark', 'label' => '备注', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'points', 'balance', 'type', 'remark', 'created_at'],
            ],
            'roles' => [
                'title' => '角色库',
                'model' => \App\Models\Video\VideoRole::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                    ['name' => 'actor_id', 'label' => '演员ID', 'type' => 'number'],
                    ['name' => 'slug', 'label' => '别名', 'type' => 'text'],
                    ['name' => 'cover', 'label' => '封面', 'type' => 'text'],
                    ['name' => 'blurb', 'label' => '简介', 'type' => 'text'],
                    ['name' => 'content', 'label' => '详情', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'name', 'video_id', 'actor_id', 'slug', 'status', 'sort'],
            ],
            'websites' => [
                'title' => '网址导航',
                'model' => \App\Models\Video\VideoWebsite::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'type_id', 'label' => '分类ID', 'type' => 'number'],
                    ['name' => 'url', 'label' => '链接', 'type' => 'text'],
                    ['name' => 'logo', 'label' => 'Logo', 'type' => 'text'],
                    ['name' => 'blurb', 'label' => '简介', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '隐藏']],
                ],
                'cols' => ['id', 'type_id', 'name', 'url', 'status', 'sort'],
            ],
            'arts' => [
                'title' => '文章管理',
                'hint' => '文章是资讯，不是影片。栏目在分类里把模型选成「文章」。这里按 LaraCMS 内容列表来写稿、发布。',
                'model' => \App\Models\Video\VideoArt::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'type_id', 'label' => '栏目', 'type' => 'number'],
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'cover', 'label' => '封面', 'type' => 'text'],
                    ['name' => 'content', 'label' => '正文', 'type' => 'textarea'],
                    ['name' => 'hits', 'label' => '点击', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '已发布', '0' => '草稿']],
                ],
                'cols' => ['id', 'type_id', 'title', 'hits', 'status'],
            ],
            'domains' => [
                'title' => '绑定域名',
                'model' => \App\Models\Video\VideoDomain::class,
                'search' => 'host',
                'fields' => [
                    ['name' => 'host', 'label' => '域名', 'type' => 'text'],
                    ['name' => 'theme', 'label' => '主题目录', 'type' => 'text'],
                    ['name' => 'remark', 'label' => '备注', 'type' => 'text'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'host', 'theme', 'status'],
            ],
            'unions' => [
                'title' => '推荐资源',
                'hint' => '收藏别人给的苹果接口。点接入后才会出现在采集源里。',
                'model' => \App\Models\Video\VideoUnion::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'api_url', 'label' => '接口地址', 'type' => 'text'],
                    ['name' => 'note', 'label' => '说明', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '隐藏']],
                ],
                'cols' => ['id', 'name', 'api_url', 'status', 'sort'],
            ],
            'ulogs' => [
                'title' => '访问日志',
                'model' => \App\Models\Video\VideoUlog::class,
                'search' => 'type',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                    ['name' => 'type', 'label' => '类型 play/down/fav', 'type' => 'text'],
                    ['name' => 'ip', 'label' => 'IP', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'video_id', 'type', 'ip', 'created_at'],
            ],
            'plots' => [
                'title' => '分集剧情',
                'model' => \App\Models\Video\VideoPlot::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                    ['name' => 'episode_num', 'label' => '集数', 'type' => 'number'],
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'content', 'label' => '剧情', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                ],
                'cols' => ['id', 'video_id', 'episode_num', 'title', 'sort', 'created_at'],
            ],
            'synonyms' => [
                'title' => '同义词',
                'model' => \App\Models\Video\VideoSynonym::class,
                'search' => 'from_word',
                'fields' => [
                    ['name' => 'from_word', 'label' => '原词', 'type' => 'text'],
                    ['name' => 'to_word', 'label' => '替换为', 'type' => 'text'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'from_word', 'to_word', 'status'],
            ],
            'invites' => [
                'title' => '邀请码',
                'hint' => '注册用的码，可批量生成；站点设置可强制填写',
                'model' => MemberInvite::class,
                'search' => 'code',
                'fields' => [
                    ['name' => 'code', 'label' => '邀请码', 'type' => 'text'],
                    ['name' => 'member_id', 'label' => '邀请人会员ID', 'type' => 'number'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '未用', '0' => '作废']],
                ],
                'cols' => ['id', 'code', 'member_id', 'used_by', 'points', 'status', 'created_at'],
            ],
            'classes' => [
                'title' => '扩展分类',
                'model' => \App\Models\Video\VideoClass::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'name', 'sort', 'status'],
            ],
            'favorites' => [
                'title' => '收藏',
                'model' => \App\Models\Member\MemberFavorite::class,
                'search' => 'member_id',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'video_id', 'label' => '影片ID', 'type' => 'number'],
                ],
                'cols' => ['id', 'member_id', 'video_id', 'created_at'],
            ],
            'accesslogs' => [
                'title' => '访问风控',
                'model' => \App\Models\Video\VideoAccessLog::class,
                'search' => 'ip',
                'fields' => [
                    ['name' => 'ip', 'label' => 'IP', 'type' => 'text'],
                    ['name' => 'url', 'label' => 'URL', 'type' => 'text'],
                    ['name' => 'ua', 'label' => 'UA', 'type' => 'text'],
                    ['name' => 'is_bot', 'label' => '爬虫', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                ],
                'cols' => ['id', 'ip', 'url', 'ua', 'is_bot', 'created_at'],
            ],
            'botlogs' => [
                'title' => '爬虫日志',
                'model' => \App\Models\Video\VideoAccessLog::class,
                'search' => 'ua',
                'where' => ['is_bot' => 1],
                'fields' => [
                    ['name' => 'ip', 'label' => 'IP', 'type' => 'text'],
                    ['name' => 'url', 'label' => 'URL', 'type' => 'text'],
                    ['name' => 'ua', 'label' => 'UA', 'type' => 'text'],
                    ['name' => 'is_bot', 'label' => '爬虫', 'type' => 'select', 'options' => ['1' => '是', '0' => '否']],
                ],
                'cols' => ['id', 'ip', 'url', 'ua', 'is_bot', 'created_at'],
            ],
            'searchwords' => [
                'title' => '搜索词',
                'hint' => '前台搜片会自动记在这里。次数高的就是访客在找什么。',
                'model' => \App\Models\Video\VideoSearchWord::class,
                'search' => 'word',
                'fields' => [
                    ['name' => 'word', 'label' => '关键词', 'type' => 'text'],
                    ['name' => 'hits', 'label' => '次数', 'type' => 'number'],
                ],
                'cols' => ['id', 'word', 'hits', 'updated_at'],
            ],
            'slides' => [
                'title' => '幻灯片',
                'hint' => '幻灯是首页轮播、播放页贴片。先选位置，再上传横图和跳转链接。主题用位置调用，例如 @@vodSlide([\'slot\' => \'home\'])。',
                'model' => \App\Models\Video\VideoSlide::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'pic', 'label' => '图片', 'type' => 'text'],
                    ['name' => 'url', 'label' => '链接', 'type' => 'text'],
                    ['name' => 'slot', 'label' => '位置', 'type' => 'select', 'options' => ['home' => '首页', 'play' => '播放页']],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '隐藏']],
                ],
                'cols' => ['id', 'name', 'slot', 'url', 'status', 'sort'],
            ],
            'notifies' => [
                'title' => '会员通知',
                'hint' => '全站或指定会员的公告，和站内信不同',
                'model' => \App\Models\Video\VideoNotify::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID(0全部)', 'type' => 'number'],
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'is_read', 'label' => '已读', 'type' => 'select', 'options' => ['0' => '未读', '1' => '已读']],
                ],
                'cols' => ['id', 'member_id', 'title', 'is_read', 'created_at'],
            ],
            'collect_temps' => [
                'title' => '待审入库',
                'hint' => '采集开了先入临时表时，新片停在这里。确认后再转入片库。',
                'model' => \App\Models\Video\VideoCollectTemp::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '待转入', '1' => '已入库']],
                    ['name' => 'msg', 'label' => '说明', 'type' => 'text'],
                ],
                'cols' => ['id', 'title', 'status', 'msg', 'created_at'],
            ],
            default => throw new \InvalidArgumentException('未知模块'),
        };
    }

    public static function names(): array
    {
        return ['topics', 'players', 'links', 'comments', 'reports', 'members', 'cards', 'downloaders', 'servers', 'playfails', 'audits', 'collect_tasks', 'ads', 'guestbooks', 'groups', 'orders', 'withdraws', 'pms', 'collect_logs', 'plogs', 'roles', 'websites', 'arts', 'domains', 'unions', 'ulogs', 'plots', 'synonyms', 'invites', 'classes', 'favorites', 'accesslogs', 'botlogs', 'searchwords', 'slides', 'notifies', 'collect_temps'];
    }

    public function lists(string $module, array $params): array
    {
        $cfg = $this->config($module);
        /** @var class-string<Model> $class */
        $class = $cfg['model'];
        try {
            if (! Schema::hasTable((new $class)->getTable())) {
                return Result::fail('请先执行数据库迁移');
            }
        } catch (\Throwable) {
            return Result::fail('请先执行数据库迁移');
        }
        $limit = max(1, (int) ($params['limit'] ?? 10));
        $q = $class::query();
        foreach ($cfg['where'] ?? [] as $col => $val) {
            $q->where($col, $val);
        }
        $kw = trim((string) ($params[$cfg['search']] ?? $params['q'] ?? ''));
        if ($kw !== '') {
            if ($module === 'comments') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('author_name', 'like', '%'.$kw.'%');
                });
            } elseif ($module === 'guestbooks') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('author_name', 'like', '%'.$kw.'%')
                        ->orWhere('reply', 'like', '%'.$kw.'%')
                        ->orWhere('ip', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('member_id', (int) $kw);
                    }
                });
            } elseif ($module === 'arts') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('content', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'members') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('email', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'orders') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('order_no', 'like', '%'.$kw.'%');
                    if (Schema::hasColumn('member_orders', 'trade_no')) {
                        $inner->orWhere('trade_no', 'like', '%'.$kw.'%');
                    }
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('member_id', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('member_id', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'withdraws') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('account', 'like', '%'.$kw.'%')
                        ->orWhere('remark', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('member_id', (int) $kw)->orWhere('amount', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('member_id', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'cards') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('code', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('used_by', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('used_by', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'invites') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('code', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)
                            ->orWhere('member_id', (int) $kw)
                            ->orWhere('used_by', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('member_id', $memberIds)->orWhereIn('used_by', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'plogs') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('remark', 'like', '%'.$kw.'%')
                        ->orWhere('type', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('member_id', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('member_id', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'ads') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('slot', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'links') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('url', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'players') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('code', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'unions') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('api_url', 'like', '%'.$kw.'%')
                        ->orWhere('note', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'collect_logs') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('msg', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('collect_source_id', (int) $kw);
                    }
                    if (Schema::hasTable('collect_sources')) {
                        $sourceIds = CollectSourceModel::query()
                            ->where('name', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($sourceIds !== []) {
                            $inner->orWhereIn('collect_source_id', $sourceIds);
                        }
                    }
                });
            } elseif ($module === 'collect_tasks') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('cron_expression', 'like', '%'.$kw.'%')
                        ->orWhere('last_msg', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('collect_source_id', (int) $kw);
                    }
                    if (Schema::hasTable('collect_sources')) {
                        $sourceIds = CollectSourceModel::query()
                            ->where('name', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($sourceIds !== []) {
                            $inner->orWhereIn('collect_source_id', $sourceIds);
                        }
                    }
                });
            } elseif ($module === 'collect_temps') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('msg', 'like', '%'.$kw.'%')
                        ->orWhere('collect_id', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('collect_source_id', (int) $kw);
                    }
                    if (Schema::hasTable('collect_sources')) {
                        $sourceIds = CollectSourceModel::query()
                            ->where('name', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($sourceIds !== []) {
                            $inner->orWhereIn('collect_source_id', $sourceIds);
                        }
                    }
                });
            } elseif ($module === 'reports') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('ip', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('video_id', (int) $kw)->orWhere('member_id', (int) $kw);
                    }
                    if (Schema::hasTable('videos')) {
                        $videoIds = VideoModel::query()
                            ->where('title', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($videoIds !== []) {
                            $inner->orWhereIn('video_id', $videoIds);
                        }
                    }
                });
            } elseif ($module === 'playfails') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('url', 'like', '%'.$kw.'%')
                        ->orWhere('ip', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('video_id', (int) $kw)->orWhere('source_id', (int) $kw);
                    }
                    if (Schema::hasTable('videos')) {
                        $videoIds = VideoModel::query()
                            ->where('title', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($videoIds !== []) {
                            $inner->orWhereIn('video_id', $videoIds);
                        }
                    }
                    if (Schema::hasTable('video_sources')) {
                        $sourceIds = VideoSourceModel::query()
                            ->where('name', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($sourceIds !== []) {
                            $inner->orWhereIn('source_id', $sourceIds);
                        }
                    }
                });
            } elseif ($module === 'pms') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('content', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('to_id', (int) $kw)->orWhere('from_id', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('to_id', $memberIds)->orWhereIn('from_id', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'notifies') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('content', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('member_id', (int) $kw);
                    }
                    if (Schema::hasTable('members')) {
                        $memberIds = Member::query()
                            ->where(function ($m) use ($kw) {
                                $m->where('name', 'like', '%'.$kw.'%')
                                    ->orWhere('email', 'like', '%'.$kw.'%');
                            })
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($memberIds !== []) {
                            $inner->orWhereIn('member_id', $memberIds);
                        }
                    }
                });
            } elseif ($module === 'audits') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('words', 'like', '%'.$kw.'%')
                        ->orWhere('scope', 'like', '%'.$kw.'%')
                        ->orWhere('action', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } else {
                $q->where($cfg['search'], 'like', '%'.$kw.'%');
            }
        }
        if ($module === 'comments' || $module === 'topics' || $module === 'arts' || $module === 'slides' || $module === 'members' || $module === 'orders' || $module === 'groups' || $module === 'reports' || $module === 'guestbooks' || $module === 'playfails') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ($module === 'comments' && (string) ($params['report'] ?? '') === '1' && Schema::hasColumn('video_comments', 'comment_report')) {
                $q->where('comment_report', '>', 0);
            }
            if ($module === 'arts' && array_key_exists('type_id', $params) && $params['type_id'] !== '' && $params['type_id'] !== null) {
                $q->where('type_id', (int) $params['type_id']);
            }
            if ($module === 'slides' && trim((string) ($params['slot'] ?? '')) !== '') {
                $q->where('slot', trim((string) $params['slot']));
            }
            if ($module === 'members' && array_key_exists('group_id', $params) && $params['group_id'] !== '' && $params['group_id'] !== null) {
                $gid = (int) $params['group_id'];
                if ($gid === 0) {
                    $q->where(function ($inner) {
                        $inner->where('group_id', 0)->orWhereNull('group_id');
                    });
                } else {
                    $q->where('group_id', $gid);
                }
            }
            if ($module === 'orders' && trim((string) ($params['channel'] ?? '')) !== '') {
                $q->where('channel', trim((string) $params['channel']));
            }
        }
        if ($module === 'reports' && (string) ($params['today'] ?? '') === '1') {
            $q->where('created_at', '>=', strtotime('today'));
        }
        if ($module === 'guestbooks') {
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            if ((string) ($params['noreply'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->where('reply', '')->orWhereNull('reply');
                });
            }
        }
        if ($module === 'playfails') {
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            if ((string) ($params['offline'] ?? '') === '1') {
                $q->where('status', 0)->where('source_id', '>', 0);
            }
        }
        if ($module === 'pms') {
            if (array_key_exists('is_read', $params) && $params['is_read'] !== '' && $params['is_read'] !== null) {
                $q->where('is_read', (int) $params['is_read']);
            }
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
        }
        if ($module === 'notifies') {
            if (array_key_exists('is_read', $params) && $params['is_read'] !== '' && $params['is_read'] !== null) {
                $q->where('is_read', (int) $params['is_read']);
            }
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            $audience = trim((string) ($params['audience'] ?? ''));
            if ($audience === 'all') {
                $q->where('member_id', 0);
            } elseif ($audience === 'one') {
                $q->where('member_id', '>', 0);
            }
        }
        if ($module === 'withdraws') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['today'] ?? '') === '1') {
                $probe = new $class;
                $col = $this->hasColumn($probe, 'created_at') ? 'created_at' : 'updated_at';
                if ($this->hasColumn($probe, $col)) {
                    $q->where($col, '>=', strtotime('today'));
                }
            }
        }
        if ($module === 'cards') {
            $queue = trim((string) ($params['queue'] ?? ''));
            if ($queue === 'unused') {
                $q->where('status', 1)->where('used_by', 0);
            } elseif ($queue === 'used') {
                $q->where('used_by', '>', 0);
            } elseif ($queue === 'void') {
                $q->where('status', 0)->where('used_by', 0);
            }
            if (array_key_exists('used_by', $params) && $params['used_by'] !== '' && $params['used_by'] !== null) {
                $q->where('used_by', (int) $params['used_by']);
            }
        }
        if ($module === 'invites') {
            $queue = trim((string) ($params['queue'] ?? ''));
            if ($queue === 'unused') {
                $q->where('status', 1)->where('used_by', 0);
            } elseif ($queue === 'used') {
                $q->where('used_by', '>', 0);
            } elseif ($queue === 'void') {
                $q->where('status', 0)->where('used_by', 0);
            }
            if ((string) ($params['today'] ?? '') === '1' && $this->hasColumn(new $class, 'created_at')) {
                $q->where('created_at', '>=', strtotime('today'));
            }
        }
        if ($module === 'plogs') {
            $dir = trim((string) ($params['dir'] ?? ''));
            if ($dir === 'in') {
                $q->where('points', '>', 0);
            } elseif ($dir === 'out') {
                $q->where('points', '<', 0);
            }
            $type = trim((string) ($params['type'] ?? ''));
            $types = ['play', 'order', 'admin', 'card', 'coupon', 'invite', 'withdraw', 'sys'];
            if (in_array($type, $types, true)) {
                $q->where('type', $type);
            }
            if (array_key_exists('member_id', $params) && $params['member_id'] !== '' && $params['member_id'] !== null) {
                $q->where('member_id', (int) $params['member_id']);
            }
        }
        if ($module === 'ads') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if (trim((string) ($params['slot'] ?? '')) !== '') {
                $q->where('slot', trim((string) $params['slot']));
            }
            if ((string) ($params['expired'] ?? '') === '1' && $this->hasColumn(new $class, 'expire_at')) {
                $now = time();
                $q->where('expire_at', '>', 0)->where('expire_at', '<', $now);
            }
        }
        if ($module === 'links') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['logo'] ?? '') === '1') {
                $q->where('logo', '!=', '')->whereNotNull('logo');
            }
        }
        if ($module === 'players') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            $engine = strtolower(trim((string) ($params['engine'] ?? '')));
            if (in_array($engine, VideoPlayerModel::engines(), true) && $this->hasColumn(new $class, 'engine')) {
                $q->where('engine', $engine);
            }
        }
        if ($module === 'unions') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            $adopted = $params['adopted'] ?? '';
            if ($adopted !== '' && $adopted !== null) {
                $variants = $this->collectApiUrlVariants();
                if ((int) $adopted === 1) {
                    if ($variants === []) {
                        $q->whereRaw('0 = 1');
                    } else {
                        $q->whereIn('api_url', $variants);
                    }
                } elseif ($variants !== []) {
                    $q->whereNotIn('api_url', $variants);
                }
            }
        }
        if ($module === 'collect_logs') {
            if (array_key_exists('ok', $params) && $params['ok'] !== '' && $params['ok'] !== null) {
                $q->where('ok', (int) $params['ok']);
            }
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            if (array_key_exists('collect_source_id', $params) && $params['collect_source_id'] !== '' && $params['collect_source_id'] !== null) {
                $q->where('collect_source_id', (int) $params['collect_source_id']);
            }
        }
        if ($module === 'collect_temps') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['failed'] ?? '') === '1') {
                $q->where('status', 0)->where('msg', '!=', '')->where('msg', '!=', '待转入');
            }
            if (array_key_exists('collect_source_id', $params) && $params['collect_source_id'] !== '' && $params['collect_source_id'] !== null) {
                $q->where('collect_source_id', (int) $params['collect_source_id']);
            }
        }
        if ($module === 'searchwords') {
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('updated_at', '>=', strtotime('today'));
            }
            if ((string) ($params['hot'] ?? '') === '1') {
                $q->where('hits', '>=', 10);
            }
            if ((string) ($params['once'] ?? '') === '1') {
                $q->where('hits', 1);
            }
        }
        if ($module === 'collect_tasks') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['never'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->where('last_run_at', 0)->orWhereNull('last_run_at');
                });
            }
            if ((string) ($params['failed'] ?? '') === '1') {
                $q->where('last_run_at', '>', 0)->where(function ($inner) {
                    $inner->where('last_msg', '')->orWhere('last_msg', 'not like', '入库%');
                });
            }
            if (array_key_exists('collect_source_id', $params) && $params['collect_source_id'] !== '' && $params['collect_source_id'] !== null) {
                $q->where('collect_source_id', (int) $params['collect_source_id']);
            }
        }
        if ($module === 'audits') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            $action = trim((string) ($params['action'] ?? ''));
            if (in_array($action, ['skip', 'review', 'replace'], true)) {
                $q->where('action', $action);
            }
            $scope = trim((string) ($params['scope'] ?? ''));
            if (in_array($scope, ['title', 'content', 'actor'], true)) {
                $q->where('scope', $scope);
            }
        }
        if ($module === 'topics' || $module === 'slides' || $module === 'groups' || $module === 'ads' || $module === 'links' || $module === 'players' || $module === 'unions' || $module === 'audits') {
            $q->orderByDesc('sort')->orderByDesc('id');
        } elseif ($module === 'arts') {
            $q->orderByDesc('updated_at')->orderByDesc('id');
        } elseif ($module === 'collect_temps') {
            $q->orderBy('status')->orderByDesc('id');
        } elseif ($module === 'reports') {
            $q->orderBy('status')->orderByDesc('id');
        } elseif ($module === 'guestbooks') {
            $q->orderBy('status')->orderByDesc('id');
        } elseif ($module === 'playfails') {
            $q->orderBy('status')->orderByDesc('id');
        } elseif ($module === 'pms') {
            $q->orderBy('is_read')->orderByDesc('id');
        } elseif ($module === 'notifies') {
            $q->orderBy('is_read')->orderByDesc('id');
        } elseif ($module === 'withdraws') {
            $q->orderBy('status')->orderByDesc('id');
        } elseif ($module === 'invites') {
            $q->orderByDesc('status')->orderByDesc('id');
        } elseif ($module === 'searchwords') {
            $q->orderByDesc('hits')->orderByDesc('updated_at')->orderByDesc('id');
        } else {
            $q->orderByDesc('id');
        }
        $page = $q->paginate($limit);
        $rows = collect($page->items())->map(function ($row) {
            $arr = $row->toArray();
            unset($arr['password'], $arr['remember_token']);

            return $arr;
        })->all();
        if ($module === 'comments') {
            $rows = $this->decorateComments($rows);
        }
        if ($module === 'reports') {
            $rows = $this->decorateReports($rows);
        }
        if ($module === 'guestbooks') {
            $rows = $this->decorateGuestbooks($rows);
        }
        if ($module === 'playfails') {
            $rows = $this->decoratePlayFails($rows);
        }
        if ($module === 'pms') {
            $rows = $this->decoratePms($rows);
        }
        if ($module === 'notifies') {
            $rows = $this->decorateNotifies($rows);
        }
        if ($module === 'topics') {
            $rows = $this->decorateTopics($rows);
        }
        if ($module === 'arts') {
            $rows = $this->decorateArts($rows);
        }
        if ($module === 'slides') {
            $rows = $this->decorateSlides($rows);
        }
        if ($module === 'members') {
            $rows = $this->decorateMembers($rows);
        }
        if ($module === 'orders') {
            $rows = $this->decorateOrders($rows);
        }
        if ($module === 'withdraws') {
            $rows = $this->decorateWithdraws($rows);
        }
        if ($module === 'groups') {
            $rows = $this->decorateGroups($rows);
        }
        if ($module === 'cards') {
            $rows = $this->decorateCards($rows);
        }
        if ($module === 'invites') {
            $rows = $this->decorateInvites($rows);
        }
        if ($module === 'plogs') {
            $rows = $this->decoratePlogs($rows);
        }
        if ($module === 'ads') {
            $rows = $this->decorateAds($rows);
        }
        if ($module === 'links') {
            $rows = $this->decorateLinks($rows);
        }
        if ($module === 'players') {
            $rows = $this->decoratePlayers($rows);
        }
        if ($module === 'unions') {
            $rows = $this->decorateUnions($rows);
        }
        if ($module === 'collect_logs') {
            $rows = $this->decorateCollectLogs($rows);
        }
        if ($module === 'collect_tasks') {
            $rows = $this->decorateCollectTasks($rows);
        }
        if ($module === 'collect_temps') {
            $rows = $this->decorateCollectTemps($rows);
        }
        if ($module === 'searchwords') {
            $rows = $this->decorateSearchWords($rows);
        }
        if ($module === 'audits') {
            $rows = $this->decorateAudits($rows);
        }

        return Result::success([
            'total' => $page->total(),
            'data' => $rows,
        ]);
    }

    public function save(string $module, array $data, ?int $id = null): array
    {
        if ($module === 'collect_logs') {
            return Result::fail('采集日志由采集任务写入，不能手工改。');
        }
        if ($module === 'collect_temps') {
            return Result::fail('待审记录由采集写入。要进片库请点转入，不要在这里改字段。');
        }
        if ($module === 'guestbooks' && $id === null) {
            return Result::fail('留言由前台提交。后台只回复和审核。');
        }
        if ($module === 'playfails' && $id === null) {
            return Result::fail('播放失败由前台播放页上报。后台只处理和下线线路。');
        }
        if ($module === 'withdraws' && $id === null) {
            return Result::fail('提现由会员申请。后台只审核打款或拒绝。');
        }
        $cfg = $this->config($module);
        /** @var class-string<Model> $class */
        $class = $cfg['model'];
        $probe = new $class;
        $payload = [];
        foreach ($cfg['fields'] as $field) {
            $name = $field['name'];
            if (array_key_exists($name, $data) && $this->hasColumn($probe, $name)) {
                $payload[$name] = $data[$name];
            }
        }
        if ($module === 'members') {
            $pwd = trim((string) ($payload['password'] ?? ''));
            if ($pwd === '') {
                unset($payload['password']);
            } else {
                $payload['password'] = Hash::make($pwd);
            }
            $name = trim((string) ($payload['name'] ?? ''));
            $email = trim((string) ($payload['email'] ?? ''));
            if ($id === null) {
                if ($name === '') {
                    return Result::fail('请填写昵称');
                }
                if ($email === '') {
                    return Result::fail('请填写邮箱');
                }
                if (! isset($payload['password'])) {
                    return Result::fail('请填写密码');
                }
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            if ($email !== '') {
                $payload['email'] = $email;
                $dup = Member::query()->where('email', $email);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个邮箱已经注册过');
                }
            }
            if (array_key_exists('group_id', $payload)) {
                $payload['group_id'] = max(0, (int) $payload['group_id']);
            }
            if (array_key_exists('points', $payload)) {
                $payload['points'] = max(0, (int) $payload['points']);
            }
        }
        if ($module === 'orders' && $id === null && trim((string) ($payload['order_no'] ?? '')) === '') {
            $payload['order_no'] = 'V'.date('YmdHis').Str::upper(Str::random(4));
        }
        if ($module === 'pms') {
            if ($id === null) {
                $toId = (int) ($payload['to_id'] ?? 0);
                if ($toId < 1) {
                    return Result::fail('请填写收件会员 ID');
                }
                if (Schema::hasTable('members') && ! Member::query()->where('id', $toId)->exists()) {
                    return Result::fail('会员不存在');
                }
                $title = mb_substr(trim((string) ($payload['title'] ?? '')), 0, 120);
                $content = trim((string) ($payload['content'] ?? ''));
                if ($title === '') {
                    return Result::fail('请填写标题');
                }
                if ($content === '') {
                    return Result::fail('请填写内容');
                }
                $payload['to_id'] = $toId;
                $payload['title'] = $title;
                $payload['content'] = $content;
                $payload['from_id'] = 0;
                $payload['is_read'] = 0;
                $payload['created_at'] = time();
            } else {
                $allowed = [];
                if (array_key_exists('is_read', $payload)) {
                    $allowed['is_read'] = (int) $payload['is_read'] === 1 ? 1 : 0;
                }
                $payload = $allowed;
            }
        }
        if ($module === 'notifies') {
            if ($id === null) {
                $memberId = max(0, (int) ($payload['member_id'] ?? 0));
                if ($memberId > 0 && Schema::hasTable('members') && ! Member::query()->where('id', $memberId)->exists()) {
                    return Result::fail('会员不存在');
                }
                $title = mb_substr(trim((string) ($payload['title'] ?? '')), 0, 120);
                $content = trim((string) ($payload['content'] ?? ''));
                if ($title === '') {
                    return Result::fail('请填写标题');
                }
                if ($content === '') {
                    return Result::fail('请填写内容');
                }
                $payload['member_id'] = $memberId;
                $payload['title'] = $title;
                $payload['content'] = $content;
                $payload['is_read'] = 0;
                $payload['created_at'] = time();
            } else {
                $allowed = [];
                if (array_key_exists('is_read', $payload)) {
                    $allowed['is_read'] = (int) $payload['is_read'] === 1 ? 1 : 0;
                }
                $payload = $allowed;
            }
        }
        if ($module === 'withdraws') {
            $allowed = [];
            if (array_key_exists('status', $payload)) {
                $status = (int) $payload['status'];
                if (! in_array($status, [0, 1, 2], true)) {
                    return Result::fail('状态无效');
                }
                $allowed['status'] = $status;
            }
            if (array_key_exists('remark', $payload)) {
                $allowed['remark'] = mb_substr(trim((string) $payload['remark']), 0, 255);
            }
            $payload = $allowed;
        }
        if ($module === 'searchwords') {
            $word = mb_substr(trim((string) ($payload['word'] ?? '')), 0, 80);
            if ($word === '') {
                return Result::fail('请填写关键词');
            }
            $dup = VideoSearchWord::query()->where('word', $word);
            if ($id) {
                $dup->where('id', '!=', $id);
            }
            if ($dup->exists()) {
                return Result::fail('已经有这个词了');
            }
            $payload['word'] = $word;
            $payload['hits'] = max(0, (int) ($payload['hits'] ?? 1));
            $payload['updated_at'] = time();
            if ($id === null) {
                $payload['created_at'] = time();
                if (! array_key_exists('hits', $data) || (string) $data['hits'] === '') {
                    $payload['hits'] = 1;
                }
            }
        }
        if ($module === 'arts') {
            if (array_key_exists('title', $payload) || $id === null) {
                $title = trim((string) ($payload['title'] ?? ''));
                if ($title === '') {
                    return Result::fail('请填写标题');
                }
                $payload['title'] = $title;
            }
            if (array_key_exists('type_id', $payload)) {
                $payload['type_id'] = max(0, (int) $payload['type_id']);
            }
            if (array_key_exists('cover', $payload)) {
                $payload['cover'] = trim((string) $payload['cover']);
            }
            if (array_key_exists('hits', $payload)) {
                $payload['hits'] = max(0, (int) $payload['hits']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        if ($module === 'slides') {
            if ($id === null) {
                if (trim((string) ($payload['name'] ?? '')) === '') {
                    return Result::fail('请填写名称');
                }
                if (trim((string) ($payload['pic'] ?? '')) === '') {
                    return Result::fail('请上传图片');
                }
            }
            if (array_key_exists('slot', $payload)) {
                $slot = trim((string) $payload['slot']);
                if ($slot === '') {
                    $payload['slot'] = 'home';
                } elseif (! in_array($slot, ['home', 'play'], true)) {
                    return Result::fail('位置只能是首页或播放页');
                } else {
                    $payload['slot'] = $slot;
                }
            } elseif ($id === null) {
                $payload['slot'] = 'home';
            }
        }
        if ($module === 'orders') {
            if (array_key_exists('amount_yuan', $data) && $this->hasColumn($probe, 'amount')) {
                $payload['amount'] = max(0, (int) round((float) $data['amount_yuan'] * 100));
            }
            if ($id === null && (int) ($payload['member_id'] ?? 0) < 1) {
                return Result::fail('请填写会员 ID');
            }
            if (array_key_exists('member_id', $payload)) {
                $payload['member_id'] = (int) $payload['member_id'];
            }
            if (array_key_exists('points', $payload)) {
                $payload['points'] = max(0, (int) $payload['points']);
            }
            if (array_key_exists('amount', $payload)) {
                $payload['amount'] = max(0, (int) $payload['amount']);
            }
            if (array_key_exists('channel', $payload)) {
                $channel = trim((string) $payload['channel']);
                $payload['channel'] = in_array($channel, ['wechat', 'alipay', 'manual'], true) ? $channel : 'manual';
            } elseif ($id === null && $this->hasColumn($probe, 'channel')) {
                $payload['channel'] = 'manual';
            }
        }
        if ($module === 'groups') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写名称');
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            foreach (['points_min', 'trysee', 'day_free', 'sort', 'need_login', 'status'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
        }
        if ($module === 'cards') {
            if (array_key_exists('code', $payload)) {
                $payload['code'] = strtoupper((string) preg_replace('/\s+/', '', trim((string) $payload['code'])));
            }
            if (array_key_exists('points', $payload)) {
                $payload['points'] = max(1, (int) $payload['points']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if ($id === null) {
                $code = trim((string) ($payload['code'] ?? ''));
                $payload['code'] = $code !== '' ? $code : $this->uniqueCardCode();
                if ((int) ($payload['points'] ?? 0) < 1) {
                    return Result::fail('积分至少为 1');
                }
                $payload['status'] = 1;
                $payload['used_by'] = 0;
                $payload['used_at'] = 0;
            }
            $code = trim((string) ($payload['code'] ?? ''));
            if ($code !== '') {
                $dup = VideoCard::query()->where('code', $code);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个卡密已经存在');
                }
            }
        }
        if ($module === 'invites') {
            if (array_key_exists('code', $payload)) {
                $payload['code'] = strtoupper((string) preg_replace('/\s+/', '', trim((string) $payload['code'])));
            }
            if (array_key_exists('points', $payload)) {
                $payload['points'] = max(0, (int) $payload['points']);
            }
            if (array_key_exists('member_id', $payload)) {
                $payload['member_id'] = max(0, (int) $payload['member_id']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if ($id === null) {
                $code = trim((string) ($payload['code'] ?? ''));
                $payload['code'] = $code !== '' ? $code : $this->uniqueInviteCode();
                $payload['status'] = 1;
                $payload['used_by'] = 0;
                $payload['member_id'] = max(0, (int) ($payload['member_id'] ?? 0));
                $payload['points'] = max(0, (int) ($payload['points'] ?? 0));
            }
            $code = trim((string) ($payload['code'] ?? ''));
            if ($code !== '') {
                $dup = MemberInvite::query()->where('code', $code);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个邀请码已经存在');
                }
            }
        }
        if ($module === 'plogs') {
            if ($id) {
                return Result::fail('流水不能改。删记录也不会改会员积分。');
            }
            $memberId = (int) ($payload['member_id'] ?? 0);
            $delta = (int) ($payload['points'] ?? 0);
            $remark = trim((string) ($payload['remark'] ?? ''));
            if ($memberId < 1) {
                return Result::fail('请填写会员 ID');
            }
            if ($delta === 0) {
                return Result::fail('变动不能为 0');
            }
            if (! Member::query()->find($memberId)) {
                return Result::fail('会员不存在');
            }
            $this->changePoints($memberId, $delta, 'admin', $remark !== '' ? $remark : '后台调积分');

            return Result::success(['member_id' => $memberId]);
        }
        if ($module === 'ads') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写名称');
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            if (array_key_exists('slot', $payload) || $id === null) {
                $slot = strtolower(trim((string) ($payload['slot'] ?? '')));
                if ($slot === '') {
                    $slot = 'header';
                }
                if (! preg_match('/^[a-z][a-z0-9_-]{0,39}$/', $slot)) {
                    return Result::fail('位置用英文字母开头，如 header、footer、play');
                }
                $payload['slot'] = $slot;
            }
            if (array_key_exists('content', $payload)) {
                $payload['content'] = (string) $payload['content'];
            } elseif ($id === null) {
                $payload['content'] = '';
            }
            if (array_key_exists('expire_at', $payload)) {
                $payload['expire_at'] = $this->parseExpireAt($payload['expire_at']);
            }
            foreach (['type_id', 'sort', 'status'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        if ($module === 'links') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写网站名称');
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            if (array_key_exists('url', $payload) || $id === null) {
                $raw = trim((string) ($payload['url'] ?? ''));
                if ($raw === '') {
                    return Result::fail('请填写网址');
                }
                $url = $this->normalizeLinkUrl($raw);
                if ($url === '' || ! filter_var($url, FILTER_VALIDATE_URL)) {
                    return Result::fail('网址格式不对');
                }
                $payload['url'] = $url;
            }
            if (array_key_exists('logo', $payload)) {
                $payload['logo'] = trim((string) $payload['logo']);
            }
            foreach (['sort', 'status'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        if ($module === 'unions') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写名称');
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            if (array_key_exists('api_url', $payload) || $id === null) {
                $url = $this->normalizeUnionUrl((string) ($payload['api_url'] ?? ''));
                if ($url === '') {
                    return Result::fail('请填写接口地址');
                }
                $payload['api_url'] = $url;
            }
            if (array_key_exists('note', $payload)) {
                $payload['note'] = trim((string) $payload['note']);
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        if ($module === 'collect_tasks') {
            $name = trim((string) ($payload['name'] ?? ''));
            $sourceId = (int) ($payload['collect_source_id'] ?? 0);
            $cron = trim((string) ($payload['cron_expression'] ?? ''));
            if ($sourceId < 1) {
                return Result::fail('请选择采集源');
            }
            if (! Schema::hasTable('collect_sources') || ! CollectSourceModel::query()->where('id', $sourceId)->exists()) {
                return Result::fail('采集源不存在，先到采集源里加一个');
            }
            if ($cron === '') {
                $cron = '0 * * * *';
            }
            try {
                new \Cron\CronExpression($cron);
            } catch (\Throwable) {
                return Result::fail('周期表达式不对');
            }
            if ($name === '') {
                $name = (string) (CollectSourceModel::query()->where('id', $sourceId)->value('name') ?? '');
                $name = $name !== '' ? ($name.' 定时') : '定时采集';
            }
            $payload['name'] = $name;
            $payload['collect_source_id'] = $sourceId;
            $payload['cron_expression'] = $cron;
            $payload['pages'] = min(50, max(1, (int) ($payload['pages'] ?? 1)));
            $payload['hours'] = min(8760, max(0, (int) ($payload['hours'] ?? 24)));
            $payload['status'] = (int) ($payload['status'] ?? 1) === 1 ? 1 : 0;
        }
        if ($module === 'audits') {
            $audit = app(\App\Services\Collect\CollectAuditService::class);
            if (array_key_exists('name', $payload)) {
                $name = trim((string) $payload['name']);
                if ($name !== '') {
                    $payload['name'] = mb_substr($name, 0, 80);
                } else {
                    unset($payload['name']);
                }
            }
            if ($id === null || array_key_exists('words', $payload)) {
                $parts = $audit->splitWords((string) ($payload['words'] ?? ''));
                if ($parts === []) {
                    return Result::fail('请填写关键词，一行一个或用逗号分开');
                }
                $isRegex = (int) ($payload['is_regex'] ?? 0) === 1;
                if ($isRegex) {
                    foreach ($parts as $word) {
                        if (@preg_match('/'.$word.'/iu', '') === false) {
                            return Result::fail('正则写得不对：'.$word);
                        }
                    }
                }
                $payload['words'] = implode("\n", $parts);
                if (! isset($payload['name']) || trim((string) $payload['name']) === '') {
                    $payload['name'] = mb_substr($parts[0], 0, 80);
                }
            }
            if ($id === null || array_key_exists('scope', $payload)) {
                $scope = trim((string) ($payload['scope'] ?? 'title'));
                $payload['scope'] = in_array($scope, ['title', 'content', 'actor'], true) ? $scope : 'title';
            }
            if ($id === null || array_key_exists('action', $payload)) {
                $action = trim((string) ($payload['action'] ?? 'skip'));
                $payload['action'] = in_array($action, ['skip', 'review', 'replace'], true) ? $action : 'skip';
            }
            if ($id === null || array_key_exists('status', $payload)) {
                $payload['status'] = (int) ($payload['status'] ?? 1) === 1 ? 1 : 0;
            }
            if ($id === null || array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) ($payload['sort'] ?? 0));
            }
            if ($this->hasColumn($probe, 'is_regex')) {
                if ($id === null || array_key_exists('is_regex', $payload)) {
                    $payload['is_regex'] = (int) ($payload['is_regex'] ?? 0) === 1 ? 1 : 0;
                }
            } else {
                unset($payload['is_regex']);
            }
        }
        if ($module === 'players') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写名称');
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            if (array_key_exists('code', $payload) || $id === null) {
                $code = strtolower(trim((string) ($payload['code'] ?? '')));
                if ($code === '') {
                    return Result::fail('请填写标识，要和线路上的播放器字段一致');
                }
                if (! preg_match('/^[a-z][a-z0-9._-]{0,39}$/', $code)) {
                    return Result::fail('标识用英文字母开头，如 artplayer、dplayer');
                }
                $dup = VideoPlayerModel::query()->where('code', $code);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个标识已经有了');
                }
                $payload['code'] = $code;
            }
            if (array_key_exists('engine', $payload) && $this->hasColumn($probe, 'engine')) {
                $engine = strtolower(trim((string) $payload['engine']));
                $payload['engine'] = in_array($engine, VideoPlayerModel::engines(), true)
                    ? $engine
                    : VideoPlayerModel::inferEngine((string) ($payload['code'] ?? ''), (string) ($payload['parse'] ?? ''));
            } elseif ($id === null && $this->hasColumn($probe, 'engine')) {
                $payload['engine'] = VideoPlayerModel::inferEngine((string) ($payload['code'] ?? ''), (string) ($payload['parse'] ?? ''));
            }
            if (array_key_exists('parse', $payload)) {
                $payload['parse'] = trim((string) $payload['parse']);
            }
            foreach (['sort', 'status'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        $now = time();
        $oldStatus = null;
        $memberPointsTo = null;
        if ($module === 'members' && $id && array_key_exists('points', $payload)) {
            $memberPointsTo = (int) $payload['points'];
            unset($payload['points']);
        }
        if ($id) {
            $row = $class::query()->find($id);
            if (! $row) {
                return Result::fail('数据不存在');
            }
            if ($this->hasColumn($row, 'status')) {
                $oldStatus = (int) $row->status;
            }
            if ($module === 'orders' && $oldStatus === 1 && array_key_exists('status', $payload) && (int) $payload['status'] === 0) {
                return Result::fail('已付订单不能改回待付，只能关闭。关闭不会扣积分。');
            }
            if ($module === 'cards' && (int) ($row->used_by ?? 0) > 0) {
                return Result::fail('已兑换的卡密不能改。');
            }
            if ($module === 'invites' && (int) ($row->used_by ?? 0) > 0) {
                return Result::fail('已用的邀请码不能改。');
            }
            if ($this->hasColumn($row, 'updated_at')) {
                $payload['updated_at'] = $now;
            }
            $row->fill($payload)->save();
            if ($memberPointsTo !== null) {
                $this->changePoints($id, $memberPointsTo - (int) $row->points, 'admin', '后台改积分');
            }
            $this->afterMoneySave($module, $row, $oldStatus);

            return $this->loggedModule($module, 'save', AdminOpLog::moduleSaveSummary($module, true, AdminOpLog::subjectFrom($payload, $row), $id), $id, Result::success(['id' => $id]));
        }
        $model = new $class;
        if ($this->hasColumn($model, 'created_at')) {
            $payload['created_at'] = $now;
        }
        if ($this->hasColumn($model, 'updated_at')) {
            $payload['updated_at'] = $now;
        }
        $row = $class::query()->create($payload);
        $this->afterMoneySave($module, $row, null);
        $newId = (int) $row->id;

        return $this->loggedModule($module, 'save', AdminOpLog::moduleSaveSummary($module, false, AdminOpLog::subjectFrom($payload, $row), $newId), $newId, Result::success(['id' => $newId]));
    }

    private function afterMoneySave(string $module, Model $row, ?int $oldStatus): void
    {
        $status = (int) ($row->status ?? 0);
        if ($module === 'orders' && $status === 1 && $oldStatus !== 1) {
            $this->changePoints((int) $row->member_id, (int) $row->points, 'order', '订单 '.$row->order_no);
        }
        if ($module === 'withdraws' && $status === 1 && $oldStatus !== 1) {
            $this->changePoints((int) $row->member_id, -abs((int) $row->amount), 'withdraw', '提现审核通过');
        }
        if ($module === 'withdraws' && $status === 2 && $oldStatus === 0) {
            // 拒绝不扣分
        }
    }

    private function changePoints(int $memberId, int $delta, string $type, string $remark): void
    {
        if ($memberId < 1 || $delta === 0) {
            return;
        }
        $member = \App\Models\Member\Member::query()->find($memberId);
        if (! $member) {
            return;
        }
        $member->points = max(0, (int) $member->points + $delta);
        $member->save();
        if (! \Illuminate\Support\Facades\Schema::hasTable('member_point_logs')) {
            return;
        }
        \App\Models\Member\MemberPointLog::query()->create([
            'member_id' => $memberId,
            'points' => $delta,
            'balance' => (int) $member->points,
            'type' => $type,
            'remark' => mb_substr($remark, 0, 250),
            'created_at' => time(),
        ]);
    }

    public function delete(string $module, int $id): array
    {
        $cfg = $this->config($module);
        /** @var class-string<Model> $class */
        $class = $cfg['model'];
        $row = $class::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if ($module === 'topics' && Schema::hasTable('video_topic_rel')) {
            VideoTopicRelModel::query()->where('topic_id', $id)->delete();
        }
        if ($module === 'topics' && Schema::hasTable('video_topic_art_rel')) {
            VideoTopicArtRelModel::query()->where('topic_id', $id)->delete();
        }
        if ($module === 'groups' && Schema::hasTable('members') && Schema::hasColumn('members', 'group_id')) {
            Member::query()->where('group_id', $id)->update(['group_id' => 0]);
        }
        if ($module === 'cards' && (int) ($row->used_by ?? 0) > 0) {
            return Result::fail('已兑换的卡密不能删，留着对账。');
        }
        if ($module === 'invites' && (int) ($row->used_by ?? 0) > 0) {
            return Result::fail('已用的邀请码不能删。');
        }
        if ($module === 'players') {
            $code = trim((string) ($row->code ?? ''));
            if ($code !== '' && Schema::hasTable('video_sources') && Schema::hasColumn('video_sources', 'player')) {
                $used = (int) VideoSourceModel::query()->where('player', $code)->count();
                if ($used > 0) {
                    return Result::fail('还有 '.$used.' 条线路在用这个播放器。先到「批量播放器」换掉再删。');
                }
            }
        }
        $subject = AdminOpLog::subjectFrom([], $row);
        $row->delete();

        return $this->loggedModule($module, 'delete', AdminOpLog::moduleDeleteSummary($module, $subject, $id), $id, Result::success());
    }

    /** @return array<string, int> */
    public function commentQueues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'pass' => 0, 'report' => 0];
        try {
            if (! Schema::hasTable('video_comments')) {
                return $zero;
            }

            return [
                'all' => (int) VideoComment::query()->count(),
                'pending' => (int) VideoComment::query()->where('status', 0)->count(),
                'pass' => (int) VideoComment::query()->where('status', 1)->count(),
                'report' => Schema::hasColumn('video_comments', 'comment_report')
                    ? (int) VideoComment::query()->where('comment_report', '>', 0)->count()
                    : 0,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function reportQueues(): array
    {
        $zero = ['all' => 0, 'open' => 0, 'done' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('video_reports')) {
                return $zero;
            }

            return [
                'all' => (int) VideoReport::query()->count(),
                'open' => (int) VideoReport::query()->where('status', 0)->count(),
                'done' => (int) VideoReport::query()->where('status', 1)->count(),
                'today' => (int) VideoReport::query()->where('created_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function guestbookQueues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'shown' => 0, 'noreply' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('video_guestbooks')) {
                return $zero;
            }

            return [
                'all' => (int) VideoGuestbook::query()->count(),
                'pending' => (int) VideoGuestbook::query()->where('status', 0)->count(),
                'shown' => (int) VideoGuestbook::query()->where('status', 1)->count(),
                'noreply' => (int) VideoGuestbook::query()->where(function ($inner) {
                    $inner->where('reply', '')->orWhereNull('reply');
                })->count(),
                'today' => (int) VideoGuestbook::query()->where('created_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    public function guestbookAuditEnabled(): bool
    {
        try {
            return (string) app(\App\Services\Video\VideoSettingService::class)->get('gbook_audit', '0') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<string, int> */
    public function playFailQueues(): array
    {
        $zero = ['all' => 0, 'open' => 0, 'done' => 0, 'offline' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('video_play_fails')) {
                return $zero;
            }

            return [
                'all' => (int) VideoPlayFail::query()->count(),
                'open' => (int) VideoPlayFail::query()->where('status', 0)->count(),
                'done' => (int) VideoPlayFail::query()->where('status', 1)->count(),
                'offline' => (int) VideoPlayFail::query()->where('status', 0)->where('source_id', '>', 0)->count(),
                'today' => (int) VideoPlayFail::query()->where('created_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function pmQueues(): array
    {
        $zero = ['all' => 0, 'unread' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('member_pms')) {
                return $zero;
            }

            return [
                'all' => (int) MemberPm::query()->count(),
                'unread' => (int) MemberPm::query()->where('is_read', 0)->count(),
                'today' => (int) MemberPm::query()->where('created_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function notifyQueues(): array
    {
        $zero = ['all' => 0, 'all_members' => 0, 'one' => 0, 'unread' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('video_notifies')) {
                return $zero;
            }

            return [
                'all' => (int) VideoNotify::query()->count(),
                'all_members' => (int) VideoNotify::query()->where('member_id', 0)->count(),
                'one' => (int) VideoNotify::query()->where('member_id', '>', 0)->count(),
                'unread' => (int) VideoNotify::query()->where('is_read', 0)->count(),
                'today' => (int) VideoNotify::query()->where('created_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /**
     * @param list<int|string> $ids
     */
    public function batch(string $module, array $ids, string $action, mixed $value = ''): array
    {
        if (! in_array($module, ['comments', 'topics', 'arts', 'slides', 'members', 'orders', 'withdraws', 'groups', 'cards', 'invites', 'plogs', 'ads', 'links', 'players', 'collect_logs', 'collect_tasks', 'collect_temps', 'audits', 'searchwords', 'reports', 'guestbooks', 'playfails', 'pms', 'notifies'], true)) {
            return Result::fail('不支持的操作');
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail(match ($module) {
                'topics' => '请先勾选专题',
                'arts' => '请先勾选文章',
                'slides' => '请先勾选幻灯片',
                'members' => '请先勾选会员',
                'orders' => '请先勾选订单',
                'withdraws' => '请先勾选提现',
                'groups' => '请先勾选会员组',
                'cards' => '请先勾选卡密',
                'invites' => '请先勾选邀请码',
                'plogs' => '请先勾选流水',
                'ads' => '请先勾选广告',
                'links' => '请先勾选友链',
                'players' => '请先勾选播放器',
                'collect_logs' => '请先勾选日志',
                'collect_tasks' => '请先勾选任务',
                'collect_temps' => '请先勾选片子',
                'searchwords' => '请先勾选搜索词',
                'reports' => '请先勾选报错',
                'guestbooks' => '请先勾选留言',
                'playfails' => '请先勾选播放失败',
                'pms' => '请先勾选站内信',
                'notifies' => '请先勾选通知',
                'audits' => '请先勾选规则',
                default => '请先勾选评论',
            });
        }
        $ok = 0;
        $fail = 0;
        AdminOpLog::quiet(function () use ($ids, $module, $action, $value, &$ok, &$fail) {
            foreach ($ids as $id) {
                $res = match ($action) {
                    'status' => $this->save($module, ['status' => (int) $value], $id),
                    'type' => $this->save($module, ['type_id' => (int) $value], $id),
                    'slot' => $this->save($module, ['slot' => (string) $value], $id),
                    'group' => $this->save($module, ['group_id' => (int) $value], $id),
                    'engine' => $this->save($module, ['engine' => (string) $value], $id),
                    'points' => $this->adjustMemberPoints($id, (int) $value),
                    'offline' => $module === 'playfails'
                        ? app(\App\Services\Video\SiteOpsService::class)->disablePlayFailSource($id)
                        : Result::fail('不支持的操作'),
                    'read' => ($module === 'pms' || $module === 'notifies')
                        ? $this->save($module, ['is_read' => 1], $id)
                        : Result::fail('不支持的操作'),
                    'delete' => $this->delete($module, $id),
                    default => Result::fail('不支持的操作'),
                };
                if (($res['code'] ?? 1) === 0) {
                    $ok++;
                } else {
                    $fail++;
                }
            }
        });
        if ($ok === 0) {
            return Result::fail('操作失败');
        }

        $msg = $fail > 0 ? ('完成 '.$ok.' 条，'.$fail.' 条未处理') : '操作成功';

        return $this->loggedModule(
            $module,
            'batch',
            AdminOpLog::moduleBatchSummary($module, $action, $value, $ok),
            0,
            Result::success(['ok' => $ok, 'fail' => $fail], $msg),
            ['count' => $ok, 'action' => $action]
        );
    }

    private function adjustMemberPoints(int $id, int $delta): array
    {
        if ($delta === 0) {
            return Result::fail('请填写不为 0 的积分');
        }
        $member = Member::query()->find($id);
        if (! $member) {
            return Result::fail('数据不存在');
        }
        $this->changePoints($id, $delta, 'admin', '后台调整');

        return Result::success(['id' => $id]);
    }

    /** @return list<array<string, mixed>> */
    public function memberGroupOptions(): array
    {
        try {
            if (! Schema::hasTable('member_groups')) {
                return [];
            }
            $counts = [];
            if (Schema::hasTable('members')) {
                $countRows = Member::query()
                    ->selectRaw('group_id, COUNT(*) as c')
                    ->groupBy('group_id')
                    ->get();
                foreach ($countRows as $row) {
                    $counts[(int) $row->group_id] = (int) $row->c;
                }
            }
            $out = [];
            foreach (MemberGroup::query()->orderByDesc('sort')->orderBy('id')->get() as $group) {
                $id = (int) $group->id;
                $out[] = [
                    'id' => $id,
                    'name' => (string) $group->name,
                    'status' => (int) $group->status,
                    'count' => $counts[$id] ?? 0,
                ];
            }

            return $out;
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<string, int> */
    public function memberQueues(): array
    {
        $zero = ['all' => 0, 'off' => 0, 'none' => 0];
        try {
            if (! Schema::hasTable('members')) {
                return $zero;
            }

            return [
                'all' => (int) Member::query()->count(),
                'off' => (int) Member::query()->where('status', 0)->count(),
                'none' => (int) Member::query()->where(function ($inner) {
                    $inner->where('group_id', 0)->orWhereNull('group_id');
                })->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMembers(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $gid = (int) ($row['group_id'] ?? 0);
            if ($gid > 0) {
                $ids[] = $gid;
            }
        }
        $names = [];
        if ($ids !== [] && Schema::hasTable('member_groups')) {
            $names = MemberGroup::query()->whereIn('id', array_values(array_unique($ids)))->pluck('name', 'id')->all();
        }
        foreach ($rows as &$row) {
            $gid = (int) ($row['group_id'] ?? 0);
            $row['group_name'] = $gid > 0 ? (string) ($names[$gid] ?? '未知分组') : '未分组';
            $ts = (int) ($row['created_at'] ?? 0);
            $row['joined_text'] = $ts > 0 ? date('Y-m-d', $ts) : '';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function orderQueues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'paid' => 0, 'closed' => 0];
        try {
            if (! Schema::hasTable('member_orders')) {
                return $zero;
            }

            return [
                'all' => (int) MemberOrder::query()->count(),
                'pending' => (int) MemberOrder::query()->where('status', 0)->count(),
                'paid' => (int) MemberOrder::query()->where('status', 1)->count(),
                'closed' => (int) MemberOrder::query()->where('status', 2)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateOrders(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $mid = (int) ($row['member_id'] ?? 0);
            if ($mid > 0) {
                $ids[] = $mid;
            }
        }
        $members = [];
        if ($ids !== [] && Schema::hasTable('members')) {
            $members = Member::query()->whereIn('id', array_values(array_unique($ids)))->get(['id', 'name', 'email'])->keyBy('id');
        }
        $channels = ['wechat' => '微信', 'alipay' => '支付宝', 'manual' => '人工'];
        $statuses = ['0' => '待付', '1' => '已付', '2' => '关闭'];
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $member = $members[$mid] ?? null;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) $member->email : '';
            $fen = (int) ($row['amount'] ?? 0);
            $row['amount_yuan'] = number_format($fen / 100, 2, '.', '');
            $ch = trim((string) ($row['channel'] ?? ''));
            $row['channel_label'] = $channels[$ch] ?? ($ch !== '' ? $ch : '人工');
            $row['status_label'] = $statuses[(string) ($row['status'] ?? '0')] ?? '待付';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function withdrawQueues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'paid' => 0, 'rejected' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('member_withdraws')) {
                return $zero;
            }
            $timeCol = Schema::hasColumn('member_withdraws', 'created_at') ? 'created_at' : 'updated_at';

            return [
                'all' => (int) MemberWithdraw::query()->count(),
                'pending' => (int) MemberWithdraw::query()->where('status', 0)->count(),
                'paid' => (int) MemberWithdraw::query()->where('status', 1)->count(),
                'rejected' => (int) MemberWithdraw::query()->where('status', 2)->count(),
                'today' => (int) MemberWithdraw::query()->where($timeCol, '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateWithdraws(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $mid = (int) ($row['member_id'] ?? 0);
            if ($mid > 0) {
                $ids[] = $mid;
            }
        }
        $members = [];
        if ($ids !== [] && Schema::hasTable('members')) {
            try {
                $cols = ['id', 'name', 'email'];
                if (Schema::hasColumn('members', 'points')) {
                    $cols[] = 'points';
                }
                $members = Member::query()->whereIn('id', array_values(array_unique($ids)))->get($cols)->keyBy('id');
            } catch (\Throwable) {
                $members = [];
            }
        }
        $statuses = ['0' => '待审', '1' => '已打款', '2' => '拒绝'];
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $member = $members[$mid] ?? null;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) ($member->email ?? '') : '';
            $row['member_points'] = ($member && isset($member->points)) ? (int) $member->points : null;
            $fen = (int) ($row['amount'] ?? 0);
            $row['amount_yuan'] = number_format($fen / 100, 2, '.', '');
            $ts = (int) ($row['created_at'] ?? 0);
            if ($ts <= 0) {
                $ts = (int) ($row['updated_at'] ?? 0);
            }
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $st = (int) ($row['status'] ?? 0);
            $row['status_label'] = $statuses[(string) $st] ?? '待审';
            $row['pending'] = $st === 0 ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function groupQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0];
        try {
            if (! Schema::hasTable('member_groups')) {
                return $zero;
            }

            return [
                'all' => (int) MemberGroup::query()->count(),
                'on' => (int) MemberGroup::query()->where('status', 1)->count(),
                'off' => (int) MemberGroup::query()->where('status', 0)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateGroups(array $rows): array
    {
        $counts = [];
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'group_id')) {
            $countRows = Member::query()
                ->selectRaw('group_id, COUNT(*) as c')
                ->groupBy('group_id')
                ->get();
            foreach ($countRows as $row) {
                $counts[(int) $row->group_id] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $row['member_count'] = $counts[$id] ?? 0;
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function cardQueues(): array
    {
        $zero = ['all' => 0, 'unused' => 0, 'used' => 0, 'void' => 0];
        try {
            if (! Schema::hasTable('video_cards')) {
                return $zero;
            }

            return [
                'all' => (int) VideoCard::query()->count(),
                'unused' => (int) VideoCard::query()->where('status', 1)->where('used_by', 0)->count(),
                'used' => (int) VideoCard::query()->where('used_by', '>', 0)->count(),
                'void' => (int) VideoCard::query()->where('status', 0)->where('used_by', 0)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateCards(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $uid = (int) ($row['used_by'] ?? 0);
            if ($uid > 0) {
                $ids[] = $uid;
            }
        }
        $members = [];
        if ($ids !== [] && Schema::hasTable('members')) {
            $members = Member::query()
                ->whereIn('id', array_values(array_unique($ids)))
                ->get(['id', 'name', 'email'])
                ->keyBy('id')
                ->all();
        }
        foreach ($rows as &$row) {
            $usedBy = (int) ($row['used_by'] ?? 0);
            $status = (int) ($row['status'] ?? 1);
            if ($usedBy > 0) {
                $row['state'] = 'used';
                $row['state_label'] = '已兑';
            } elseif ($status !== 1) {
                $row['state'] = 'void';
                $row['state_label'] = '作废';
            } else {
                $row['state'] = 'unused';
                $row['state_label'] = '未用';
            }
            $member = $members[$usedBy] ?? null;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) $member->email : '';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function inviteQueues(): array
    {
        $zero = ['all' => 0, 'unused' => 0, 'used' => 0, 'void' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('member_invites')) {
                return $zero;
            }

            return [
                'all' => (int) MemberInvite::query()->count(),
                'unused' => (int) MemberInvite::query()->where('status', 1)->where('used_by', 0)->count(),
                'used' => (int) MemberInvite::query()->where('used_by', '>', 0)->count(),
                'void' => (int) MemberInvite::query()->where('status', 0)->where('used_by', 0)->count(),
                'today' => (int) MemberInvite::query()->where('created_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateInvites(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $owner = (int) ($row['member_id'] ?? 0);
            $usedBy = (int) ($row['used_by'] ?? 0);
            if ($owner > 0) {
                $ids[] = $owner;
            }
            if ($usedBy > 0) {
                $ids[] = $usedBy;
            }
        }
        $members = [];
        if ($ids !== [] && Schema::hasTable('members')) {
            try {
                $members = Member::query()
                    ->whereIn('id', array_values(array_unique($ids)))
                    ->get(['id', 'name', 'email'])
                    ->keyBy('id')
                    ->all();
            } catch (\Throwable) {
                $members = [];
            }
        }
        foreach ($rows as &$row) {
            $ownerId = (int) ($row['member_id'] ?? 0);
            $usedBy = (int) ($row['used_by'] ?? 0);
            $status = (int) ($row['status'] ?? 1);
            if ($usedBy > 0) {
                $row['state'] = 'used';
                $row['status_label'] = '已用';
                $row['used'] = 1;
            } elseif ($status !== 1) {
                $row['state'] = 'void';
                $row['status_label'] = '作废';
                $row['used'] = 0;
            } else {
                $row['state'] = 'unused';
                $row['status_label'] = '未用';
                $row['used'] = 0;
            }
            $owner = $members[$ownerId] ?? null;
            $user = $members[$usedBy] ?? null;
            $row['owner_name'] = $ownerId > 0
                ? ($owner ? (string) $owner->name : ('会员 #'.$ownerId))
                : '系统';
            $row['used_name'] = $usedBy > 0
                ? ($user ? (string) $user->name : ('会员 #'.$usedBy))
                : '';
            $ts = (int) ($row['created_at'] ?? 0);
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function plogQueues(): array
    {
        $zero = ['all' => 0, 'in' => 0, 'out' => 0, 'play' => 0, 'order' => 0, 'card' => 0, 'admin' => 0];
        try {
            if (! Schema::hasTable('member_point_logs')) {
                return $zero;
            }

            return [
                'all' => (int) MemberPointLog::query()->count(),
                'in' => (int) MemberPointLog::query()->where('points', '>', 0)->count(),
                'out' => (int) MemberPointLog::query()->where('points', '<', 0)->count(),
                'play' => (int) MemberPointLog::query()->where('type', 'play')->count(),
                'order' => (int) MemberPointLog::query()->where('type', 'order')->count(),
                'card' => (int) MemberPointLog::query()->where('type', 'card')->count(),
                'admin' => (int) MemberPointLog::query()->where('type', 'admin')->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decoratePlogs(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $mid = (int) ($row['member_id'] ?? 0);
            if ($mid > 0) {
                $ids[] = $mid;
            }
        }
        $members = [];
        if ($ids !== [] && Schema::hasTable('members')) {
            $members = Member::query()
                ->whereIn('id', array_values(array_unique($ids)))
                ->get(['id', 'name', 'email'])
                ->keyBy('id')
                ->all();
        }
        $types = [
            'play' => '点播',
            'order' => '订单',
            'admin' => '后台',
            'card' => '卡密',
            'coupon' => '优惠券',
            'invite' => '邀请',
            'withdraw' => '提现',
            'sys' => '系统',
        ];
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $delta = (int) ($row['points'] ?? 0);
            $type = trim((string) ($row['type'] ?? ''));
            $member = $members[$mid] ?? null;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) $member->email : '';
            $row['type_label'] = $types[$type] ?? ($type !== '' ? $type : '系统');
            $row['dir'] = $delta < 0 ? 'out' : 'in';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function collectLogQueues(): array
    {
        $zero = ['all' => 0, 'ok' => 0, 'fail' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('video_collect_logs')) {
                return $zero;
            }
            $today = strtotime('today');

            return [
                'all' => (int) VideoCollectLog::query()->count(),
                'ok' => (int) VideoCollectLog::query()->where('ok', 1)->count(),
                'fail' => (int) VideoCollectLog::query()->where('ok', 0)->count(),
                'today' => (int) VideoCollectLog::query()->where('created_at', '>=', $today)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    public function collectSourceName(int $id): string
    {
        if ($id < 1) {
            return '';
        }
        try {
            if (! Schema::hasTable('collect_sources')) {
                return '';
            }

            return (string) (CollectSourceModel::query()->where('id', $id)->value('name') ?? '');
        } catch (\Throwable) {
            return '';
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateCollectLogs(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['collect_source_id'] ?? 0);
            if ($sid > 0) {
                $ids[] = $sid;
            }
        }
        $sources = [];
        if ($ids !== [] && Schema::hasTable('collect_sources')) {
            $sources = CollectSourceModel::query()
                ->whereIn('id', array_values(array_unique($ids)))
                ->get(['id', 'name'])
                ->keyBy('id')
                ->all();
        }
        foreach ($rows as &$row) {
            $sid = (int) ($row['collect_source_id'] ?? 0);
            $ok = (int) ($row['ok'] ?? 1) === 1;
            $created = (int) ($row['created_n'] ?? 0);
            $updated = (int) ($row['updated_n'] ?? 0);
            $skipped = (int) ($row['skipped_n'] ?? 0);
            $page = (int) ($row['page'] ?? 0);
            $source = $sources[$sid] ?? null;
            $row['source_name'] = $source ? (string) $source->name : ($sid > 0 ? ('采集源 #'.$sid) : '未知采集源');
            $row['ok'] = $ok ? 1 : 0;
            $row['ok_label'] = $ok ? '成功' : '失败';
            $row['created_n'] = $created;
            $row['updated_n'] = $updated;
            $row['skipped_n'] = $skipped;
            $parts = [];
            if ($created > 0) {
                $parts[] = '新建 '.$created;
            }
            if ($updated > 0) {
                $parts[] = '更新 '.$updated;
            }
            if ($skipped > 0) {
                $parts[] = '跳过 '.$skipped;
            }
            $row['stat_text'] = $parts !== [] ? implode(' · ', $parts) : '无入库';
            $row['page_text'] = $page > 0 ? ('第 '.$page.' 页') : '';
            $row['msg'] = trim((string) ($row['msg'] ?? ''));
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function collectTempQueues(): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'failed' => 0, 'done' => 0];
        try {
            if (! Schema::hasTable('video_collect_temps')) {
                return $zero;
            }

            return [
                'all' => (int) VideoCollectTemp::query()->count(),
                'pending' => (int) VideoCollectTemp::query()->where('status', 0)->count(),
                'failed' => (int) VideoCollectTemp::query()
                    ->where('status', 0)
                    ->where('msg', '!=', '')
                    ->where('msg', '!=', '待转入')
                    ->count(),
                'done' => (int) VideoCollectTemp::query()->where('status', 1)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    public function collectToTempEnabled(): bool
    {
        try {
            return (string) app(\App\Services\Video\VideoSettingService::class)->get('collect_to_temp', '0') === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    /** @return array<string, int> */
    public function searchWordQueues(): array
    {
        $zero = ['all' => 0, 'hot' => 0, 'once' => 0, 'today' => 0];
        try {
            if (! Schema::hasTable('video_search_words')) {
                return $zero;
            }

            return [
                'all' => (int) VideoSearchWord::query()->count(),
                'hot' => (int) VideoSearchWord::query()->where('hits', '>=', 10)->count(),
                'once' => (int) VideoSearchWord::query()->where('hits', 1)->count(),
                'today' => (int) VideoSearchWord::query()->where('updated_at', '>=', strtotime('today'))->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateSearchWords(array $rows): array
    {
        foreach ($rows as &$row) {
            $word = trim((string) ($row['word'] ?? ''));
            $hits = max(0, (int) ($row['hits'] ?? 0));
            $row['word'] = $word;
            $row['hits'] = $hits;
            $row['hot'] = $hits >= 10 ? 1 : 0;
            $row['search_url'] = '/search?wd='.rawurlencode($word);
            $row['updated_at_unix'] = (int) ($row['updated_at'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateCollectTemps(array $rows): array
    {
        $sourceIds = [];
        $typeIds = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['collect_source_id'] ?? 0);
            $tid = (int) ($row['type_id'] ?? 0);
            if ($sid > 0) {
                $sourceIds[] = $sid;
            }
            if ($tid > 0) {
                $typeIds[] = $tid;
            }
        }
        $sources = [];
        if ($sourceIds !== [] && Schema::hasTable('collect_sources')) {
            $sources = CollectSourceModel::query()
                ->whereIn('id', array_values(array_unique($sourceIds)))
                ->get(['id', 'name'])
                ->keyBy('id')
                ->all();
        }
        $types = [];
        if ($typeIds !== [] && Schema::hasTable('video_types')) {
            $types = VideoTypeModel::query()
                ->whereIn('id', array_values(array_unique($typeIds)))
                ->get(['id', 'name'])
                ->keyBy('id')
                ->all();
        }
        foreach ($rows as &$row) {
            $sid = (int) ($row['collect_source_id'] ?? 0);
            $tid = (int) ($row['type_id'] ?? 0);
            $status = (int) ($row['status'] ?? 0) === 1 ? 1 : 0;
            $msg = trim((string) ($row['msg'] ?? ''));
            $failed = $status === 0 && $msg !== '' && $msg !== '待转入';
            $source = $sources[$sid] ?? null;
            $type = $types[$tid] ?? null;
            $row['source_name'] = $source ? (string) $source->name : ($sid > 0 ? ('采集源 #'.$sid) : '未知采集源');
            $row['type_name'] = $type ? (string) $type->name : '';
            $row['status'] = $status;
            $row['status_label'] = $status === 1 ? '已入库' : ($failed ? '转入失败' : '待转入');
            $row['failed'] = $failed ? 1 : 0;
            $row['msg'] = $msg;
            $row['cover'] = trim((string) ($row['cover'] ?? ''));
            $row['has_cover'] = $row['cover'] !== '';
            $row['created_at_unix'] = (int) ($row['created_at'] ?? 0);
            unset($row['payload']);
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, string> */
    public function collectCronPresets(): array
    {
        return [
            '0 * * * *' => '每小时',
            '0 */3 * * *' => '每 3 小时',
            '0 */6 * * *' => '每 6 小时',
            '0 2 * * *' => '每天凌晨 2 点',
            '0 6 * * *' => '每天早上 6 点',
        ];
    }

    /** @return array<int, string> */
    public function collectHourPresets(): array
    {
        return [
            24 => '当天更新',
            168 => '近 7 天',
            0 => '全库',
        ];
    }

    /** @return list<array{id:int,name:string,status:int}> */
    public function collectSourceOptions(): array
    {
        try {
            if (! Schema::hasTable('collect_sources')) {
                return [];
            }

            return CollectSourceModel::query()
                ->orderByDesc('status')
                ->orderByDesc('sort')
                ->orderBy('id')
                ->get(['id', 'name', 'status'])
                ->map(fn ($row) => [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'status' => (int) $row->status,
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<string, mixed>|null */
    public function getCollectTask(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        try {
            if (! Schema::hasTable('video_collect_tasks')) {
                return null;
            }
            $row = VideoCollectTask::query()->find($id);
            if (! $row) {
                return null;
            }
            $decorated = $this->decorateCollectTasks([$row->toArray()]);

            return $decorated[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, int> */
    public function collectTaskQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'never' => 0, 'fail' => 0];
        try {
            if (! Schema::hasTable('video_collect_tasks')) {
                return $zero;
            }
            $all = (int) VideoCollectTask::query()->count();
            $on = (int) VideoCollectTask::query()->where('status', 1)->count();
            $never = (int) VideoCollectTask::query()->where(function ($q) {
                $q->where('last_run_at', 0)->orWhereNull('last_run_at');
            })->count();
            $fail = (int) VideoCollectTask::query()
                ->where('last_run_at', '>', 0)
                ->where(function ($q) {
                    $q->where('last_msg', '')->orWhere('last_msg', 'not like', '入库%');
                })
                ->count();

            return [
                'all' => $all,
                'on' => $on,
                'off' => $all - $on,
                'never' => $never,
                'fail' => $fail,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateCollectTasks(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $sid = (int) ($row['collect_source_id'] ?? 0);
            if ($sid > 0) {
                $ids[] = $sid;
            }
        }
        $sources = [];
        if ($ids !== [] && Schema::hasTable('collect_sources')) {
            $sources = CollectSourceModel::query()
                ->whereIn('id', array_values(array_unique($ids)))
                ->get(['id', 'name', 'status'])
                ->keyBy('id')
                ->all();
        }
        $cronLabels = $this->collectCronPresets();
        $hourLabels = $this->collectHourPresets();
        foreach ($rows as &$row) {
            $sid = (int) ($row['collect_source_id'] ?? 0);
            $cron = trim((string) ($row['cron_expression'] ?? ''));
            $hours = (int) ($row['hours'] ?? 0);
            $pages = max(1, (int) ($row['pages'] ?? 1));
            $status = (int) ($row['status'] ?? 0) === 1 ? 1 : 0;
            $lastRun = (int) ($row['last_run_at'] ?? 0);
            $msg = trim((string) ($row['last_msg'] ?? ''));
            $source = $sources[$sid] ?? null;
            $ok = $lastRun > 0 && str_starts_with($msg, '入库');
            $row['source_name'] = $source ? (string) $source->name : ($sid > 0 ? ('采集源 #'.$sid) : '未选采集源');
            $row['source_missing'] = $sid > 0 && $source === null ? 1 : 0;
            $row['source_off'] = $source && (int) $source->status !== 1 ? 1 : 0;
            $row['cron_label'] = $cronLabels[$cron] ?? ($cron !== '' ? ('自定义 '.$cron) : '未设周期');
            $row['hours_label'] = $hourLabels[$hours] ?? ('最近 '.$hours.' 小时');
            $row['pages'] = $pages;
            $row['status'] = $status;
            $row['status_label'] = $status === 1 ? '启用' : '停用';
            $row['last_ok'] = $ok ? 1 : 0;
            $row['last_msg'] = $msg;
            $row['never'] = $lastRun < 1 ? 1 : 0;
            $row['next_run_text'] = $this->collectTaskNextText($cron, $status);
        }
        unset($row);

        return $rows;
    }

    private function collectTaskNextText(string $cron, int $status): string
    {
        if ($status !== 1) {
            return '已停用';
        }
        $cron = trim($cron);
        if ($cron === '') {
            return '未设周期';
        }
        try {
            $next = (new \Cron\CronExpression($cron))->getNextRunDate();
            $ts = $next->getTimestamp();
            $now = time();
            if ($ts <= $now) {
                return '即将执行';
            }
            $diff = $ts - $now;
            if ($diff < 3600) {
                return '约 '.max(1, (int) ceil($diff / 60)).' 分钟后';
            }
            if (date('Y-m-d', $ts) === date('Y-m-d')) {
                return '今天 '.date('H:i', $ts);
            }
            if (date('Y-m-d', $ts) === date('Y-m-d', strtotime('tomorrow'))) {
                return '明天 '.date('H:i', $ts);
            }

            return date('m-d H:i', $ts);
        } catch (\Throwable) {
            return '周期无效';
        }
    }

    /** @return array<string, string> */
    public function auditScopeOptions(): array
    {
        return ['title' => '标题', 'content' => '简介', 'actor' => '演员'];
    }

    /** @return array<string, string> */
    public function auditActionOptions(): array
    {
        return [
            'skip' => '跳过不入库',
            'review' => '入库并下架',
            'replace' => '抠词后再入库',
        ];
    }

    /** @return array<string, mixed>|null */
    public function getAuditRule(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        try {
            if (! Schema::hasTable('video_audit_rules')) {
                return null;
            }
            $row = VideoAuditRule::query()->find($id);
            if (! $row) {
                return null;
            }
            $decorated = $this->decorateAudits([$row->toArray()]);

            return $decorated[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, int> */
    public function auditQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'skip' => 0, 'review' => 0, 'replace' => 0];
        try {
            if (! Schema::hasTable('video_audit_rules')) {
                return $zero;
            }

            return [
                'all' => (int) VideoAuditRule::query()->count(),
                'on' => (int) VideoAuditRule::query()->where('status', 1)->count(),
                'off' => (int) VideoAuditRule::query()->where('status', 0)->count(),
                'skip' => (int) VideoAuditRule::query()->where('action', 'skip')->count(),
                'review' => (int) VideoAuditRule::query()->where('action', 'review')->count(),
                'replace' => (int) VideoAuditRule::query()->where('action', 'replace')->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param  array<string, mixed>  $data */
    public function tryAuditRule(array $data): array
    {
        $sample = trim((string) ($data['sample'] ?? $data['title'] ?? ''));
        if ($sample === '') {
            return Result::fail('请填一句片名或台词试试');
        }
        $scope = trim((string) ($data['scope'] ?? 'title'));
        if (! in_array($scope, ['title', 'content', 'actor'], true)) {
            $scope = 'title';
        }
        $action = trim((string) ($data['action'] ?? 'skip'));
        if (! in_array($action, ['skip', 'review', 'replace'], true)) {
            $action = 'skip';
        }
        $words = (string) ($data['words'] ?? '');
        $isRegex = (int) ($data['is_regex'] ?? 0) === 1;
        $audit = app(\App\Services\Collect\CollectAuditService::class);
        if ($audit->splitWords($words) === []) {
            return Result::fail('请先填写关键词');
        }
        if ($isRegex) {
            foreach ($audit->splitWords($words) as $word) {
                if (@preg_match('/'.$word.'/iu', '') === false) {
                    return Result::fail('正则写得不对：'.$word);
                }
            }
        }
        $hit = $audit->matchText($sample, $words, $isRegex);
        $labels = $this->auditActionOptions();
        if (! $hit) {
            return Result::success(['hit' => 0], '没有命中，会正常入库');
        }

        return Result::success([
            'hit' => 1,
            'action' => $action,
            'scope' => $scope,
        ], '命中了，将'.$labels[$action]);
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateAudits(array $rows): array
    {
        $scopes = $this->auditScopeOptions();
        $actions = $this->auditActionOptions();
        $audit = app(\App\Services\Collect\CollectAuditService::class);
        foreach ($rows as &$row) {
            $scope = trim((string) ($row['scope'] ?? 'title'));
            $action = trim((string) ($row['action'] ?? 'skip'));
            $status = (int) ($row['status'] ?? 0) === 1 ? 1 : 0;
            $words = $audit->splitWords((string) ($row['words'] ?? ''));
            $preview = array_slice($words, 0, 6);
            $row['scope'] = $scope;
            $row['action'] = $action;
            $row['status'] = $status;
            $row['scope_label'] = $scopes[$scope] ?? $scope;
            $row['action_label'] = $actions[$action] ?? $action;
            $row['status_label'] = $status === 1 ? '启用' : '停用';
            $row['is_regex'] = (int) ($row['is_regex'] ?? 0) === 1 ? 1 : 0;
            $row['word_n'] = count($words);
            $row['words_preview'] = implode('、', $preview);
            if (count($words) > 6) {
                $row['words_preview'] .= ' 等'.$row['word_n'].'个';
            }
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateComments(array $rows): array
    {
        $videoIds = [];
        foreach ($rows as $row) {
            $vid = (int) ($row['video_id'] ?? 0);
            if ($vid > 0) {
                $videoIds[] = $vid;
            }
        }
        $titles = [];
        if ($videoIds !== []) {
            $titles = VideoModel::query()->whereIn('id', array_values(array_unique($videoIds)))->pluck('title', 'id')->all();
        }
        foreach ($rows as &$row) {
            $vid = (int) ($row['video_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $row['video_title'] = (string) ($titles[$vid] ?? '');
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['comment_report'] = (int) ($row['comment_report'] ?? 0);
            $row['comment_up'] = (int) ($row['comment_up'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateReports(array $rows): array
    {
        $videoIds = [];
        $memberIds = [];
        foreach ($rows as $row) {
            $vid = (int) ($row['video_id'] ?? 0);
            if ($vid > 0) {
                $videoIds[] = $vid;
            }
            $mid = (int) ($row['member_id'] ?? 0);
            if ($mid > 0) {
                $memberIds[] = $mid;
            }
        }
        $titles = [];
        if ($videoIds !== [] && Schema::hasTable('videos')) {
            try {
                $titles = VideoModel::query()->whereIn('id', array_values(array_unique($videoIds)))->pluck('title', 'id')->all();
            } catch (\Throwable) {
                $titles = [];
            }
        }
        $names = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            try {
                $names = Member::query()->whereIn('id', array_values(array_unique($memberIds)))->pluck('name', 'id')->all();
            } catch (\Throwable) {
                $names = [];
            }
        }
        foreach ($rows as &$row) {
            $vid = (int) ($row['video_id'] ?? 0);
            $mid = (int) ($row['member_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $row['video_title'] = (string) ($titles[$vid] ?? '');
            $row['member_name'] = (string) ($names[$mid] ?? '');
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['open'] = (int) ($row['status'] ?? 0) === 0 ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateGuestbooks(array $rows): array
    {
        $memberIds = [];
        foreach ($rows as $row) {
            $mid = (int) ($row['member_id'] ?? 0);
            if ($mid > 0) {
                $memberIds[] = $mid;
            }
        }
        $names = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            try {
                $names = Member::query()->whereIn('id', array_values(array_unique($memberIds)))->pluck('name', 'id')->all();
            } catch (\Throwable) {
                $names = [];
            }
        }
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $reply = trim((string) ($row['reply'] ?? ''));
            $row['member_name'] = (string) ($names[$mid] ?? '');
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['has_reply'] = $reply !== '' ? 1 : 0;
            $row['pending'] = (int) ($row['status'] ?? 0) === 0 ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decoratePlayFails(array $rows): array
    {
        $videoIds = [];
        $sourceIds = [];
        $episodeIds = [];
        foreach ($rows as $row) {
            $vid = (int) ($row['video_id'] ?? 0);
            if ($vid > 0) {
                $videoIds[] = $vid;
            }
            $sid = (int) ($row['source_id'] ?? 0);
            if ($sid > 0) {
                $sourceIds[] = $sid;
            }
            $eid = (int) ($row['episode_id'] ?? 0);
            if ($eid > 0) {
                $episodeIds[] = $eid;
            }
        }
        $titles = [];
        if ($videoIds !== [] && Schema::hasTable('videos')) {
            try {
                $titles = VideoModel::query()->whereIn('id', array_values(array_unique($videoIds)))->pluck('title', 'id')->all();
            } catch (\Throwable) {
                $titles = [];
            }
        }
        $sources = [];
        if ($sourceIds !== [] && Schema::hasTable('video_sources')) {
            try {
                foreach (VideoSourceModel::query()->whereIn('id', array_values(array_unique($sourceIds)))->get(['id', 'name', 'status', 'player']) as $source) {
                    $sources[(int) $source->id] = $source;
                }
            } catch (\Throwable) {
                $sources = [];
            }
        }
        $episodes = [];
        if ($episodeIds !== [] && Schema::hasTable('video_episodes')) {
            try {
                foreach (VideoEpisodeModel::query()->whereIn('id', array_values(array_unique($episodeIds)))->get() as $episode) {
                    $episodes[(int) $episode->id] = $episode;
                }
            } catch (\Throwable) {
                $episodes = [];
            }
        }
        foreach ($rows as &$row) {
            $vid = (int) ($row['video_id'] ?? 0);
            $sid = (int) ($row['source_id'] ?? 0);
            $eid = (int) ($row['episode_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $source = $sources[$sid] ?? null;
            $episode = $episodes[$eid] ?? null;
            $sourceStatus = $source ? (int) $source->status : -1;
            $row['video_title'] = (string) ($titles[$vid] ?? '');
            $row['source_name'] = $source ? (string) $source->name : '';
            $row['source_player'] = $source ? (string) $source->player : '';
            $row['source_status'] = $sourceStatus;
            $row['episode_label'] = $episode ? (string) $episode->display_name : ($eid > 0 ? ('集 #'.$eid) : '');
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['open'] = (int) ($row['status'] ?? 0) === 0 ? 1 : 0;
            $row['can_offline'] = $sid > 0 && $sourceStatus !== 0 ? 1 : 0;
            $row['play_url'] = $vid > 0
                ? '/play/'.$vid.($sid > 0 ? '/'.$sid : '').($eid > 0 ? '/'.$eid : '')
                : '';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decoratePms(array $rows): array
    {
        $memberIds = [];
        foreach ($rows as $row) {
            $from = (int) ($row['from_id'] ?? 0);
            $to = (int) ($row['to_id'] ?? 0);
            if ($from > 0) {
                $memberIds[] = $from;
            }
            if ($to > 0) {
                $memberIds[] = $to;
            }
        }
        $names = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            try {
                $names = Member::query()->whereIn('id', array_values(array_unique($memberIds)))->pluck('name', 'id')->all();
            } catch (\Throwable) {
                $names = [];
            }
        }
        foreach ($rows as &$row) {
            $from = (int) ($row['from_id'] ?? 0);
            $to = (int) ($row['to_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $row['from_name'] = $from > 0 ? (string) ($names[$from] ?? ('会员 #'.$from)) : '系统';
            $row['to_name'] = $to > 0 ? (string) ($names[$to] ?? ('会员 #'.$to)) : '';
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['unread'] = (int) ($row['is_read'] ?? 0) === 0 ? 1 : 0;
            $row['content_preview'] = mb_substr(trim((string) ($row['content'] ?? '')), 0, 80);
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateNotifies(array $rows): array
    {
        $memberIds = [];
        foreach ($rows as $row) {
            $mid = (int) ($row['member_id'] ?? 0);
            if ($mid > 0) {
                $memberIds[] = $mid;
            }
        }
        $names = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            try {
                $names = Member::query()->whereIn('id', array_values(array_unique($memberIds)))->pluck('name', 'id')->all();
            } catch (\Throwable) {
                $names = [];
            }
        }
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $memberName = $mid > 0 ? (string) ($names[$mid] ?? ('会员 #'.$mid)) : '';
            $row['member_name'] = $memberName;
            $row['audience_label'] = $mid > 0 ? $memberName : '全站';
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['unread'] = (int) ($row['is_read'] ?? 0) === 0 ? 1 : 0;
            $row['content_preview'] = mb_substr(trim((string) ($row['content'] ?? '')), 0, 80);
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateArts(array $rows): array
    {
        $typeIds = [];
        foreach ($rows as $row) {
            $tid = (int) ($row['type_id'] ?? 0);
            if ($tid > 0) {
                $typeIds[] = $tid;
            }
        }
        $names = [];
        if ($typeIds !== [] && Schema::hasTable('video_types')) {
            $names = VideoTypeModel::query()->whereIn('id', array_values(array_unique($typeIds)))->pluck('name', 'id')->all();
        }
        foreach ($rows as &$row) {
            $tid = (int) ($row['type_id'] ?? 0);
            $ts = (int) ($row['updated_at'] ?? ($row['created_at'] ?? 0));
            $row['type_name'] = (string) ($names[$tid] ?? '');
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['updated_at_unix'] = $ts;
            $row['has_cover'] = trim((string) ($row['cover'] ?? '')) !== '';
        }
        unset($row);

        return $rows;
    }

    /** @return list<array{id:int,name:string,parent_id:int}> */
    public function artTypeOptions(): array
    {
        try {
            if (! Schema::hasTable('video_types')) {
                return [];
            }
            $q = VideoTypeModel::query()->orderByDesc('sort')->orderBy('id');
            if (Schema::hasColumn('video_types', 'mid')) {
                $q->where('mid', 2);
            }

            return $q->get(['id', 'name', 'parent_id'])->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'parent_id' => (int) ($row->parent_id ?? 0),
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /** @return array<string, int> */
    public function artQueues(): array
    {
        $zero = ['all' => 0, 'published' => 0, 'draft' => 0];
        try {
            if (! Schema::hasTable('video_arts')) {
                return $zero;
            }

            return [
                'all' => (int) VideoArt::query()->count(),
                'published' => (int) VideoArt::query()->where('status', 1)->count(),
                'draft' => (int) VideoArt::query()->where('status', 0)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateTopics(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $counts = [];
        if ($ids !== [] && Schema::hasTable('video_topic_rel')) {
            $countRows = VideoTopicRelModel::query()
                ->selectRaw('topic_id, COUNT(*) as c')
                ->whereIn('topic_id', $ids)
                ->groupBy('topic_id')
                ->get();
            foreach ($countRows as $row) {
                $counts[(int) $row->topic_id] = (int) $row->c;
            }
        }
        $artCounts = [];
        if ($ids !== [] && Schema::hasTable('video_topic_art_rel')) {
            $artRows = VideoTopicArtRelModel::query()
                ->selectRaw('topic_id, COUNT(*) as c')
                ->whereIn('topic_id', $ids)
                ->groupBy('topic_id')
                ->get();
            foreach ($artRows as $row) {
                $artCounts[(int) $row->topic_id] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $row['video_count'] = $counts[$id] ?? 0;
            $row['art_count'] = $artCounts[$id] ?? 0;
            $row['has_cover'] = trim((string) ($row['cover'] ?? '')) !== '';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateSlides(array $rows): array
    {
        $labels = ['home' => '首页', 'play' => '播放页'];
        foreach ($rows as &$row) {
            $slot = trim((string) ($row['slot'] ?? ''));
            $row['slot_label'] = $labels[$slot] ?? ($slot !== '' ? $slot : '未分区');
            $row['has_pic'] = trim((string) ($row['pic'] ?? '')) !== '';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function slideQueues(): array
    {
        $zero = ['all' => 0, 'home' => 0, 'play' => 0, 'hidden' => 0];
        try {
            if (! Schema::hasTable('video_slides')) {
                return $zero;
            }

            return [
                'all' => (int) VideoSlide::query()->count(),
                'home' => (int) VideoSlide::query()->where('slot', 'home')->count(),
                'play' => (int) VideoSlide::query()->where('slot', 'play')->count(),
                'hidden' => (int) VideoSlide::query()->where('status', 0)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return list<array{id:int,name:string,parent_id:int}> */
    public function vodTypeOptions(): array
    {
        try {
            if (! Schema::hasTable('video_types')) {
                return [];
            }
            $q = VideoTypeModel::query()->orderByDesc('sort')->orderBy('id');
            if (Schema::hasColumn('video_types', 'mid')) {
                $q->where('mid', 1);
            }

            return $q->get(['id', 'name', 'parent_id'])->map(function ($row) {
                return [
                    'id' => (int) $row->id,
                    'name' => (string) $row->name,
                    'parent_id' => (int) ($row->parent_id ?? 0),
                ];
            })->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateAds(array $rows): array
    {
        $labels = ['header' => '页头', 'footer' => '页脚', 'play' => '播放页'];
        $typeIds = [];
        foreach ($rows as $row) {
            $tid = (int) ($row['type_id'] ?? 0);
            if ($tid > 0) {
                $typeIds[] = $tid;
            }
        }
        $typeNames = [];
        if ($typeIds !== [] && Schema::hasTable('video_types')) {
            $typeNames = VideoTypeModel::query()
                ->whereIn('id', array_values(array_unique($typeIds)))
                ->pluck('name', 'id')
                ->all();
        }
        $now = time();
        foreach ($rows as &$row) {
            $slot = trim((string) ($row['slot'] ?? ''));
            $row['slot_label'] = $labels[$slot] ?? ($slot !== '' ? $slot : '未分区');
            $tid = (int) ($row['type_id'] ?? 0);
            $row['type_name'] = $tid > 0 ? (string) ($typeNames[$tid] ?? $typeNames[(string) $tid] ?? '分类#'.$tid) : '全部分类';
            $exp = (int) ($row['expire_at'] ?? 0);
            $row['is_expired'] = $exp > 0 && $exp < $now;
            $row['expire_text'] = $exp < 1 ? '不过期' : date('Y-m-d H:i', $exp);
            $row['expire_local'] = $exp < 1 ? '' : date('Y-m-d\TH:i', $exp);
            $html = (string) ($row['content'] ?? '');
            $img = '';
            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $m)) {
                $img = (string) $m[1];
            }
            $row['preview_img'] = $img;
            $plain = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
            $row['preview_text'] = $plain === '' ? '' : mb_substr($plain, 0, 48);
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function adQueues(): array
    {
        $zero = ['all' => 0, 'header' => 0, 'footer' => 0, 'play' => 0, 'expired' => 0, 'off' => 0];
        try {
            if (! Schema::hasTable('video_ads')) {
                return $zero;
            }
            $now = time();
            $expired = 0;
            if (Schema::hasColumn('video_ads', 'expire_at')) {
                $expired = (int) VideoAd::query()->where('expire_at', '>', 0)->where('expire_at', '<', $now)->count();
            }

            return [
                'all' => (int) VideoAd::query()->count(),
                'header' => (int) VideoAd::query()->where('slot', 'header')->count(),
                'footer' => (int) VideoAd::query()->where('slot', 'footer')->count(),
                'play' => (int) VideoAd::query()->where('slot', 'play')->count(),
                'expired' => $expired,
                'off' => (int) VideoAd::query()->where('status', 0)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    private function parseExpireAt(mixed $raw): int
    {
        if ($raw === null || $raw === '' || $raw === '0' || $raw === 0) {
            return 0;
        }
        if (is_numeric($raw)) {
            return max(0, (int) $raw);
        }
        $ts = strtotime((string) $raw);

        return $ts ? $ts : 0;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateLinks(array $rows): array
    {
        foreach ($rows as &$row) {
            $logo = trim((string) ($row['logo'] ?? ''));
            $row['has_logo'] = $logo !== '';
            $row['kind_label'] = $logo !== '' ? '图片' : '文字';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function linkQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'logo' => 0];
        try {
            if (! Schema::hasTable('friend_links')) {
                return $zero;
            }

            return [
                'all' => (int) FriendLink::query()->count(),
                'on' => (int) FriendLink::query()->where('status', 1)->count(),
                'off' => (int) FriendLink::query()->where('status', 0)->count(),
                'logo' => (int) FriendLink::query()->where('logo', '!=', '')->whereNotNull('logo')->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    private function normalizeLinkUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decoratePlayers(array $rows): array
    {
        $codes = [];
        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            if ($code !== '') {
                $codes[] = $code;
            }
        }
        $counts = [];
        if ($codes !== [] && Schema::hasTable('video_sources') && Schema::hasColumn('video_sources', 'player')) {
            $countRows = VideoSourceModel::query()
                ->selectRaw('player, COUNT(*) as c')
                ->whereIn('player', array_values(array_unique($codes)))
                ->groupBy('player')
                ->get();
            foreach ($countRows as $row) {
                $counts[(string) $row->player] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $model = new VideoPlayerModel;
            $model->forceFill($row);
            $engine = VideoPlayerModel::resolveEngine($model, '', '');
            $row['engine'] = $engine;
            $row['engine_label'] = VideoPlayerModel::engineLabel($engine);
            $code = trim((string) ($row['code'] ?? ''));
            $row['source_count'] = $counts[$code] ?? 0;
            $row['is_direct'] = $engine !== 'iframe';
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function playerQueues(): array
    {
        $zero = ['all' => 0, 'artplayer' => 0, 'dplayer' => 0, 'videojs' => 0, 'iframe' => 0, 'off' => 0];
        try {
            if (! Schema::hasTable('video_players')) {
                return $zero;
            }
            $this->ensurePlayerEngineColumn();
            $q = VideoPlayerModel::query();
            $out = [
                'all' => (int) (clone $q)->count(),
                'artplayer' => 0,
                'dplayer' => 0,
                'videojs' => 0,
                'iframe' => 0,
                'off' => (int) (clone $q)->where('status', 0)->count(),
            ];
            foreach ((clone $q)->get() as $row) {
                $engine = VideoPlayerModel::resolveEngine($row, '', '');
                if (isset($out[$engine])) {
                    $out[$engine]++;
                }
            }

            return $out;
        } catch (\Throwable) {
            return $zero;
        }
    }

    public function ensurePlayers(): array
    {
        try {
            if (! Schema::hasTable('video_players')) {
                return Result::fail('请先执行数据库迁移');
            }
        } catch (\Throwable) {
            return Result::fail('请先执行数据库迁移');
        }
        $this->ensurePlayerEngineColumn();
        $probe = new VideoPlayerModel;
        $added = 0;
        foreach (VideoPlayerModel::presets() as $row) {
            if (! $this->hasColumn($probe, 'engine')) {
                unset($row['engine']);
            }
            $exist = VideoPlayerModel::query()->where('code', $row['code'])->first();
            if ($exist) {
                if (isset($row['engine']) && $this->hasColumn($exist, 'engine') && trim((string) ($exist->engine ?? '')) === '') {
                    $exist->engine = $row['engine'];
                    $exist->save();
                }
                continue;
            }
            VideoPlayerModel::query()->create($row);
            $added++;
        }

        return Result::success(['added' => $added], $added > 0 ? ('已补齐 '.$added.' 个内置播放器') : '内置播放器已齐全');
    }

    /** @return array<string, mixed>|null */
    public function getArt(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        try {
            if (! Schema::hasTable('video_arts')) {
                return null;
            }
            $row = VideoArt::query()->find($id);
            if (! $row) {
                return null;
            }
            $decorated = $this->decorateArts([$row->toArray()]);

            return $decorated[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function getUnion(int $id): ?array
    {
        if ($id < 1) {
            return null;
        }
        try {
            if (! Schema::hasTable('video_unions')) {
                return null;
            }
            $row = VideoUnion::query()->find($id);
            if (! $row) {
                return null;
            }
            $decorated = $this->decorateUnions([$row->toArray()]);

            return $decorated[0] ?? null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string, int> */
    public function unionQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'pending' => 0, 'adopted' => 0];
        try {
            if (! Schema::hasTable('video_unions')) {
                return $zero;
            }
            $rows = $this->decorateUnions(VideoUnion::query()->get()->map(fn ($row) => $row->toArray())->all());
            $all = count($rows);
            $on = 0;
            $adopted = 0;
            foreach ($rows as $row) {
                if ((int) ($row['status'] ?? 0) === 1) {
                    $on++;
                }
                if ((int) ($row['adopted'] ?? 0) === 1) {
                    $adopted++;
                }
            }

            return [
                'all' => $all,
                'on' => $on,
                'off' => $all - $on,
                'adopted' => $adopted,
                'pending' => $all - $adopted,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    public function adoptUnion(int $id): array
    {
        $union = $this->getUnion($id);
        if ($union === null) {
            return Result::fail('这条推荐不存在');
        }
        $url = $this->normalizeUnionUrl((string) ($union['api_url'] ?? ''));
        if ($url === '') {
            return Result::fail('没有接口地址，先编辑补上');
        }
        if (! Schema::hasTable('collect_sources')) {
            return Result::fail('请先执行数据库迁移');
        }
        $exist = $this->findCollectByApiUrl($url);
        if ($exist) {
            return Result::success(['id' => (int) $exist->id, 'existed' => true], '采集源里已经有这个接口');
        }
        $name = trim((string) ($union['name'] ?? ''));
        if ($name === '') {
            $name = (string) ($union['host'] ?? '');
        }
        if ($name === '') {
            $name = '资源站';
        }
        $now = time();
        $probe = new CollectSourceModel;
        $payload = [
            'name' => mb_substr($name, 0, 60),
            'api_url' => mb_substr($url, 0, 255),
            'api_type' => 'auto',
            'status' => 1,
            'sort' => max(0, (int) ($union['sort'] ?? 0)),
            'created_at' => $now,
            'updated_at' => $now,
        ];
        foreach ($payload as $col => $val) {
            if (! $this->hasColumn($probe, $col)) {
                unset($payload[$col]);
            }
        }
        $newId = (int) CollectSourceModel::query()->insertGetId($payload);
        if ($newId < 1) {
            return Result::fail('接入失败');
        }

        return $this->loggedModule('unions', 'adopt', '接入了资源联盟《'.$name.'》', $id, Result::success(['id' => $newId, 'existed' => false], '已接入采集源'));
    }

    /** @param list<mixed> $ids */
    public function adoptUnions(array $ids): array
    {
        $created = 0;
        $existed = 0;
        $fail = 0;
        AdminOpLog::quiet(function () use ($ids, &$created, &$existed, &$fail) {
            foreach ($ids as $id) {
                $id = (int) $id;
                if ($id < 1) {
                    continue;
                }
                $res = $this->adoptUnion($id);
                if ((int) ($res['code'] ?? 1) !== 0) {
                    $fail++;
                    continue;
                }
                if (! empty($res['data']['existed'])) {
                    $existed++;
                } else {
                    $created++;
                }
            }
        });
        if ($created + $existed === 0) {
            return Result::fail($fail > 0 ? '没有接入成功' : '请先勾选');
        }
        $msg = '已接入 '.$created.' 个';
        if ($existed > 0) {
            $msg .= '，'.$existed.' 个本来就在采集源里';
        }
        $result = Result::success(['created' => $created, 'existed' => $existed, 'fail' => $fail], $msg);
        if ($created > 0) {
            return $this->loggedModule('unions', 'adopt', '接入了 '.$created.' 条资源联盟', 0, $result, ['count' => $created]);
        }

        return $result;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateUnions(array $rows): array
    {
        $map = [];
        if (Schema::hasTable('collect_sources')) {
            try {
                foreach (CollectSourceModel::query()->get(['id', 'api_url']) as $src) {
                    $normalized = $this->normalizeUnionUrl((string) $src->api_url);
                    if ($normalized === '') {
                        continue;
                    }
                    $id = (int) $src->id;
                    $map[$normalized] = $id;
                    $map[rtrim($normalized, '/')] = $id;
                }
            } catch (\Throwable) {
            }
        }
        foreach ($rows as &$row) {
            $url = $this->normalizeUnionUrl((string) ($row['api_url'] ?? ''));
            $collectId = 0;
            if ($url !== '') {
                $collectId = (int) ($map[$url] ?? $map[rtrim($url, '/')] ?? 0);
            }
            $host = $url !== '' ? (string) (parse_url($url, PHP_URL_HOST) ?: '') : '';
            $row['host'] = $host;
            $row['collect_id'] = $collectId;
            $row['adopted'] = $collectId > 0 ? 1 : 0;
        }
        unset($row);

        return $rows;
    }

    /** @return list<string> */
    private function collectApiUrlVariants(): array
    {
        if (! Schema::hasTable('collect_sources')) {
            return [];
        }
        try {
            $out = [];
            foreach (CollectSourceModel::query()->pluck('api_url') as $raw) {
                $url = $this->normalizeUnionUrl((string) $raw);
                if ($url === '') {
                    continue;
                }
                $out[] = $url;
                $out[] = rtrim($url, '/');
                $out[] = rtrim($url, '/').'/';
                $out[] = (string) $raw;
            }

            return array_values(array_unique(array_filter($out)));
        } catch (\Throwable) {
            return [];
        }
    }

    private function findCollectByApiUrl(string $url): ?CollectSourceModel
    {
        $alts = array_values(array_unique(array_filter([
            $url,
            rtrim($url, '/'),
            rtrim($url, '/').'/',
        ])));
        if ($alts === []) {
            return null;
        }
        try {
            /** @var CollectSourceModel|null $row */
            $row = CollectSourceModel::query()->whereIn('api_url', $alts)->orderBy('id')->first();

            return $row;
        } catch (\Throwable) {
            return null;
        }
    }

    private function normalizeUnionUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }
        if (! preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            $url = 'https://'.ltrim($url, '/');
        }

        return $url;
    }

    private function ensurePlayerEngineColumn(): void
    {
        try {
            if (! Schema::hasTable('video_players') || Schema::hasColumn('video_players', 'engine')) {
                return;
            }
            Schema::table('video_players', function (Blueprint $table) {
                $table->string('engine', 20)->default('artplayer');
            });
        } catch (\Throwable) {
        }
    }

    public function topicVideos(int $topicId): array
    {
        $topic = VideoTopicModel::query()->find($topicId);
        if (! $topic) {
            return Result::fail('专题不存在');
        }
        $ids = VideoTopicRelModel::query()->where('topic_id', $topicId)->orderByDesc('sort')->pluck('video_id')->all();
        $videos = [];
        if ($ids !== []) {
            $map = VideoModel::query()->whereIn('id', $ids)->get(['id', 'title', 'cover'])->keyBy('id');
            foreach ($ids as $vid) {
                $row = $map->get((int) $vid);
                if ($row) {
                    $videos[] = [
                        'id' => (int) $row->id,
                        'title' => (string) $row->title,
                        'cover' => (string) ($row->cover ?? ''),
                    ];
                }
            }
        }

        return Result::success([
            'topic' => $topic->only(['id', 'name']),
            'video_ids' => implode(',', $ids),
            'videos' => $videos,
        ]);
    }

    public function saveTopicVideos(int $topicId, string $ids): array
    {
        $topic = VideoTopicModel::query()->find($topicId);
        if (! $topic) {
            return Result::fail('专题不存在');
        }
        $list = array_values(array_unique(array_filter(array_map('intval', preg_split('/[,\s]+/', $ids) ?: []))));
        VideoTopicRelModel::query()->where('topic_id', $topicId)->delete();
        $sort = count($list);
        foreach ($list as $vid) {
            if (! VideoModel::query()->where('id', $vid)->exists()) {
                continue;
            }
            VideoTopicRelModel::query()->create([
                'topic_id' => $topicId,
                'video_id' => $vid,
                'sort' => $sort--,
            ]);
        }

        return $this->loggedModule(
            'topics',
            'save',
            '给专题《'.trim((string) $topic->name).'》绑了 '.count($list).' 部影片',
            $topicId,
            Result::success([], '已绑定 '.count($list).' 部')
        );
    }

    public function topicArts(int $topicId): array
    {
        $topic = VideoTopicModel::query()->find($topicId);
        if (! $topic) {
            return Result::fail('专题不存在');
        }
        if (! Schema::hasTable('video_topic_art_rel') || ! Schema::hasTable('video_arts')) {
            return Result::success([
                'topic' => $topic->only(['id', 'name']),
                'art_ids' => '',
                'arts' => [],
            ]);
        }
        $ids = VideoTopicArtRelModel::query()->where('topic_id', $topicId)->orderByDesc('sort')->pluck('art_id')->all();
        $arts = [];
        if ($ids !== []) {
            $map = VideoArt::query()->whereIn('id', $ids)->get(['id', 'title', 'cover'])->keyBy('id');
            foreach ($ids as $aid) {
                $row = $map->get((int) $aid);
                if ($row) {
                    $arts[] = [
                        'id' => (int) $row->id,
                        'title' => (string) $row->title,
                        'cover' => (string) ($row->cover ?? ''),
                    ];
                }
            }
        }

        return Result::success([
            'topic' => $topic->only(['id', 'name']),
            'art_ids' => implode(',', $ids),
            'arts' => $arts,
        ]);
    }

    public function saveTopicArts(int $topicId, string $ids): array
    {
        $topic = VideoTopicModel::query()->find($topicId);
        if (! $topic) {
            return Result::fail('专题不存在');
        }
        if (! Schema::hasTable('video_topic_art_rel') || ! Schema::hasTable('video_arts')) {
            return Result::fail('请先执行数据库迁移');
        }
        $list = array_values(array_unique(array_filter(array_map('intval', preg_split('/[,\s]+/', $ids) ?: []))));
        VideoTopicArtRelModel::query()->where('topic_id', $topicId)->delete();
        $sort = count($list);
        foreach ($list as $aid) {
            if (! VideoArt::query()->where('id', $aid)->exists()) {
                continue;
            }
            VideoTopicArtRelModel::query()->create([
                'topic_id' => $topicId,
                'art_id' => $aid,
                'sort' => $sort--,
            ]);
        }

        return $this->loggedModule(
            'topics',
            'save',
            '给专题《'.trim((string) $topic->name).'》绑了 '.count($list).' 篇文章',
            $topicId,
            Result::success([], '已绑定 '.count($list).' 篇')
        );
    }

    private function uniqueCardCode(): string
    {
        do {
            $code = strtoupper(Str::random(16));
        } while (VideoCard::query()->where('code', $code)->exists());

        return $code;
    }

    private function uniqueInviteCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (MemberInvite::query()->where('code', $code)->exists());

        return $code;
    }

    public function generateCards(int $count, int $points): array
    {
        $count = min(200, max(1, $count));
        $points = max(1, $points);
        $now = time();
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = $this->uniqueCardCode();
            VideoCard::query()->create([
                'code' => $code,
                'points' => $points,
                'status' => 1,
                'used_by' => 0,
                'used_at' => 0,
                'created_at' => $now,
            ]);
            $codes[] = $code;
        }

        return $this->loggedModule(
            'cards',
            'generate',
            '生成了 '.$count.' 张积分卡密',
            0,
            Result::success(['codes' => $codes], '已生成 '.$count.' 张'),
            ['count' => $count, 'points' => $points]
        );
    }

    public function generateInvites(int $count, int $points, int $memberId = 0): array
    {
        if (! Schema::hasTable('member_invites')) {
            return Result::fail('邀请码表不存在');
        }
        $count = min(200, max(1, $count));
        $points = max(0, $points);
        $memberId = max(0, $memberId);
        $now = time();
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = $this->uniqueInviteCode();
            MemberInvite::query()->create([
                'code' => $code,
                'member_id' => $memberId,
                'used_by' => 0,
                'points' => $points,
                'status' => 1,
                'created_at' => $now,
            ]);
            $codes[] = $code;
        }

        return $this->loggedModule(
            'invites',
            'generate',
            '生成了 '.$count.' 个邀请码',
            $memberId,
            Result::success(['codes' => $codes], '已生成 '.$count.' 个'),
            ['count' => $count, 'points' => $points]
        );
    }

    public function runCollectTask(int $id): array
    {
        $task = VideoCollectTask::query()->find($id);
        if (! $task) {
            return Result::fail('任务不存在');
        }
        $result = app(\App\Services\Collect\CollectIngestService::class)->run((int) $task->collect_source_id, [
            'pages' => max(1, (int) $task->pages),
            'hours' => (int) $task->hours,
        ]);
        $task->last_run_at = time();
        $task->last_msg = mb_substr((string) ($result['msg'] ?? ''), 0, 250);
        $task->save();

        return $result;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @param  array{code?:int,msg?:string,data?:mixed}  $result
     * @return array{code?:int,msg?:string,data?:mixed}
     */
    private function loggedModule(string $module, string $action, string $summary, int $targetId, array $result, array $extra = []): array
    {
        return AdminOpLog::ifOk($result, $action, $summary, [
            'module' => AdminOpLog::object($module),
            'target_type' => $module,
            'target_id' => $targetId,
            'payload' => $extra,
        ]);
    }

    private function hasColumn(Model $model, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
