<?php

namespace App\Services\Admin\Video;

use App\Models\Member\Member;
use App\Models\Member\MemberFavorite;
use App\Models\Member\MemberGroup;
use App\Models\Member\MemberInvite;
use App\Models\Member\MemberOrder;
use App\Models\Member\MemberPm;
use App\Models\Member\MemberPointLog;
use App\Models\Member\MemberSign;
use App\Models\Member\MemberSignMilestone;
use App\Models\Member\MemberTask;
use App\Models\Member\MemberTaskLog;
use App\Models\Member\MemberWithdraw;
use App\Services\Member\MemberActivityService;
use App\Models\Video\CollectSourceModel;
use App\Models\Video\FriendLink;
use App\Models\Video\ActorModel;
use App\Models\Video\VideoAd;
use App\Models\Video\VideoArt;
use App\Models\Video\VideoArtTag;
use App\Models\Video\VideoCard;
use App\Models\Video\VideoClass;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoDownloader;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoPlayerModel;
use App\Models\Video\VideoSlide;
use App\Models\Video\VideoSourceModel;
use App\Models\Video\VideoSynonym;
use App\Models\Video\VideoTopicArtRelModel;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTopicRelModel;
use App\Models\Video\VideoTypeModel;
use App\Models\Video\VideoWebsite;
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
use App\Models\Video\VideoPlot;
use App\Models\Video\VideoReport;
use App\Models\Video\VideoRole;
use App\Models\Video\VideoServer;
use App\Models\Video\VideoAccessLog;
use App\Models\Video\VideoDomain;
use App\Services\Stats\SpiderDetector;
use App\Services\Video\DomainBindService;
use App\Services\Video\SynonymService;
use App\Support\AdminOpLog;
use App\Support\AdminPage;
use App\Support\Utils\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                'hint' => '下载页把剧集地址套进模板。不是后台去下文件，也不是播放器。',
                'model' => \App\Models\Video\VideoDownloader::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'code', 'label' => '标识', 'type' => 'text'],
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'parse', 'label' => '模板', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'code', 'name', 'status', 'sort'],
            ],
            'servers' => [
                'title' => '服务器组',
                'hint' => '相对播放地址会拼上此前缀。已经是 http(s) 的地址不会改。不是下载器，也不是播放内核。',
                'model' => \App\Models\Video\VideoServer::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'url', 'label' => '地址前缀', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
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
                    ['name' => 'channel', 'label' => '渠道', 'type' => 'select', 'options' => ['manual' => '人工', 'wechat' => '微信', 'alipay' => '支付宝', 'epay' => '易支付', 'dfpay' => 'DfPay']],
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
                'hint' => '片子里的角色名，挂到影片和演员。不是后台管理员。启用的才会出现在详情页。',
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
                'hint' => '顶栏「导航」里的站外目录，可按分类分组。页脚交换链接请去友情链接，不是同一张表。',
                'model' => \App\Models\Video\VideoWebsite::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'type_id', 'label' => '分类ID', 'type' => 'number'],
                    ['name' => 'url', 'label' => '链接', 'type' => 'text'],
                    ['name' => 'logo', 'label' => 'Logo', 'type' => 'text'],
                    ['name' => 'blurb', 'label' => '简介', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'hits', 'label' => '人气', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '隐藏']],
                ],
                'cols' => ['id', 'type_id', 'name', 'url', 'hits', 'status', 'sort'],
            ],
            'arts' => [
                'title' => '文章管理',
                'hint' => '站内资讯，有自己的栏目，不是影片。按 LaraCMS 内容列表来写稿、发布。',
                'model' => \App\Models\Video\VideoArt::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'type_id', 'label' => '栏目', 'type' => 'number'],
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'blurb', 'label' => '摘要', 'type' => 'text'],
                    ['name' => 'cover', 'label' => '封面', 'type' => 'text'],
                    ['name' => 'content', 'label' => '正文', 'type' => 'textarea'],
                    ['name' => 'source', 'label' => '来源', 'type' => 'text'],
                    ['name' => 'author', 'label' => '署名', 'type' => 'text'],
                    ['name' => 'tag', 'label' => '标签', 'type' => 'text'],
                    ['name' => 'flags', 'label' => '推荐属性', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'seo_title', 'label' => '搜索标题', 'type' => 'text'],
                    ['name' => 'seo_key', 'label' => '关键字', 'type' => 'text'],
                    ['name' => 'seo_des', 'label' => '搜索描述', 'type' => 'text'],
                    ['name' => 'published_at', 'label' => '定时发布', 'type' => 'number'],
                    ['name' => 'hits', 'label' => '点击', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '已发布', '0' => '草稿']],
                ],
                'cols' => ['id', 'type_id', 'title', 'hits', 'status', 'flags', 'sort'],
            ],
            'domains' => [
                'title' => '绑定域名',
                'hint' => '同一套片库。按访问域名换站名和模板；没绑的走站点设置。不是独立分站库。',
                'model' => \App\Models\Video\VideoDomain::class,
                'search' => 'q',
                'fields' => [
                    ['name' => 'host', 'label' => '域名', 'type' => 'text'],
                    ['name' => 'theme', 'label' => '模板', 'type' => 'text'],
                    ['name' => 'site_name', 'label' => '站点名', 'type' => 'text'],
                    ['name' => 'site_keyword', 'label' => '关键词', 'type' => 'text'],
                    ['name' => 'site_description', 'label' => '描述', 'type' => 'text'],
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
                'hint' => '按集写剧情简介，挂到一部片子。详情页会列出；不是整部片子的剧情简介。',
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
                'hint' => '搜到这个词时，按那个词去查。采集入库时片名也会换。不会改已经在库里的片子。',
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
                'hint' => '分类页「类型」筛选词，如喜剧、动作。不是电影/电视剧那种栏目树，栏目请去分类；聚合词请去标签。',
                'model' => \App\Models\Video\VideoClass::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'name', 'sort', 'status'],
            ],
            'favorites' => [
                'title' => '收藏',
                'hint' => '会员在影片页点的收藏。后台只查看和删除，不能代收藏。',
                'model' => \App\Models\Member\MemberFavorite::class,
                'search' => 'q',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员', 'type' => 'number'],
                    ['name' => 'video_id', 'label' => '影片', 'type' => 'number'],
                ],
                'cols' => ['id', 'member_id', 'video_id', 'created_at'],
            ],
            'accesslogs' => [
                'title' => '访问风控',
                'hint' => '前台页面 GET。不是封 IP。静态和插件资源不记。爬虫是同一张表的切片。',
                'model' => \App\Models\Video\VideoAccessLog::class,
                'search' => 'q',
                'fields' => [
                    ['name' => 'ip', 'label' => 'IP', 'type' => 'text'],
                    ['name' => 'url', 'label' => '地址', 'type' => 'text'],
                    ['name' => 'ua', 'label' => '标识', 'type' => 'text'],
                ],
                'cols' => ['id', 'ip', 'url', 'ua', 'created_at'],
            ],
            'botlogs' => [
                'title' => '爬虫日志',
                'hint' => '访问风控同一张表，只看爬虫。图表在蜘蛛统计。不是封 IP。',
                'model' => \App\Models\Video\VideoAccessLog::class,
                'search' => 'q',
                'where' => ['is_bot' => 1],
                'fields' => [
                    ['name' => 'ip', 'label' => 'IP', 'type' => 'text'],
                    ['name' => 'url', 'label' => '地址', 'type' => 'text'],
                    ['name' => 'ua', 'label' => '标识', 'type' => 'text'],
                ],
                'cols' => ['id', 'ip', 'url', 'ua', 'created_at'],
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
            'activity' => [
                'title' => '用户活动',
                'hint' => '对照苹果：每日签到当场入账；连续天数达标再加里程碑。观看、评论、复制链接会涨每日任务。绑定手机有号才发一次。绑定邮箱默认关闭（注册就要邮箱）。分享不是微信。',
                'model' => MemberTask::class,
                'search' => 'name',
                'view' => 'admin.video.activity',
                'desks' => ['tasks', 'logs', 'signs', 'milestones'],
                'default_desk' => 'tasks',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'type', 'label' => '类型', 'type' => 'select', 'options' => ['1' => '每日', '2' => '新手']],
                    ['name' => 'action', 'label' => '动作', 'type' => 'text'],
                    ['name' => 'hint', 'label' => '说明', 'type' => 'text'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'target', 'label' => '目标', 'type' => 'number'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '未启用']],
                ],
                'cols' => ['id', 'name', 'type', 'action', 'points', 'target', 'status', 'sort'],
            ],
            'task_logs' => [
                'title' => '任务记录',
                'hint' => '会员完成任务的进度和入账。由前台产生。',
                'model' => MemberTaskLog::class,
                'search' => 'action',
                'redirect' => '/admin/video/activity?desk=logs',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'task_id', 'label' => '任务ID', 'type' => 'number'],
                    ['name' => 'action', 'label' => '动作', 'type' => 'text'],
                    ['name' => 'progress', 'label' => '进度', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '进行中', '1' => '待领取', '2' => '已入账']],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'day_key', 'label' => '日期', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'action', 'progress', 'status', 'points', 'day_key'],
            ],
            'signs' => [
                'title' => '签到记录',
                'hint' => '每日签到和连续奖励。由会员在前台点。',
                'model' => MemberSign::class,
                'search' => 'day_key',
                'redirect' => '/admin/video/activity?desk=signs',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'days', 'label' => '连续天数', 'type' => 'number'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'day_key', 'label' => '日期Ymd', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'days', 'points', 'day_key', 'created_at'],
            ],
            'sign_milestones' => [
                'title' => '签到里程碑',
                'hint' => '达到连续天数当场入账。',
                'model' => MemberSignMilestone::class,
                'search' => 'name',
                'redirect' => '/admin/video/activity?desk=milestones',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'days', 'label' => '连续天数', 'type' => 'number'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '未启用']],
                ],
                'cols' => ['id', 'name', 'days', 'points', 'status', 'sort'],
            ],
            default => throw new \InvalidArgumentException('未知模块'),
        };
    }

    public static function names(): array
    {
        return ['topics', 'players', 'links', 'comments', 'reports', 'members', 'cards', 'downloaders', 'servers', 'playfails', 'audits', 'collect_tasks', 'ads', 'guestbooks', 'groups', 'orders', 'withdraws', 'pms', 'collect_logs', 'plogs', 'roles', 'websites', 'arts', 'domains', 'unions', 'ulogs', 'plots', 'synonyms', 'invites', 'classes', 'favorites', 'accesslogs', 'botlogs', 'searchwords', 'slides', 'notifies', 'collect_temps', 'activity', 'task_logs', 'signs', 'sign_milestones'];
    }

    public function lists(string $module, array $params): array
    {
        $cfg = $this->config($module);
        $handled = $this->dispatchPlugin($cfg, 'lists', [$params]);
        if ($handled !== null) {
            return $handled;
        }
        if ($module === 'manga_types') {
            return $this->listMangaTypes($params);
        }
        /** @var class-string<Model> $class */
        $class = $cfg['model'];
        try {
            if (! Schema::hasTable((new $class)->getTable())) {
                return Result::fail('请先执行数据库迁移');
            }
        } catch (\Throwable) {
            return Result::fail('请先执行数据库迁移');
        }
        $limit = max(1, (int) ($params['limit'] ?? 15));
        $q = $class::query();
        if ($module === 'arts' && Schema::hasColumn('video_arts', 'deleted_at') && (string) ($params['trash'] ?? '') === '1') {
            $q = $class::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0);
        }
        foreach ($cfg['where'] ?? [] as $col => $val) {
            $q->where($col, $val);
        }
        $kw = trim((string) ($params[$cfg['search']] ?? $params['q'] ?? ''));
        if ($kw !== '') {
            if ($module === 'comments') {
                $commentMid = (int) ($params['comment_mid'] ?? 1) === 2 ? 2 : 1;
                $q->where(function ($inner) use ($kw, $commentMid) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('author_name', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('video_id', (int) $kw);
                    }
                    if ($commentMid === 2 && Schema::hasTable('video_arts')) {
                        $artIds = VideoArt::query()
                            ->where('title', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($artIds !== []) {
                            $inner->orWhereIn('video_id', $artIds);
                        }
                    } elseif (Schema::hasTable('videos')) {
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
                    foreach (['blurb', 'tag', 'author', 'source'] as $col) {
                        if (Schema::hasColumn('video_arts', $col)) {
                            $inner->orWhere($col, 'like', '%'.$kw.'%');
                        }
                    }
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
            } elseif ($module === 'websites') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('url', 'like', '%'.$kw.'%')
                        ->orWhere('blurb', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)
                            ->orWhere('type_id', (int) $kw);
                    }
                    if (Schema::hasTable('video_types')) {
                        $typeQ = VideoTypeModel::query()->where('name', 'like', '%'.$kw.'%')->limit(50);
                        if (Schema::hasColumn('video_types', 'mid')) {
                            $typeQ->where('mid', 3);
                        }
                        $typeIds = $typeQ->pluck('id')->all();
                        if ($typeIds !== []) {
                            $inner->orWhereIn('type_id', $typeIds);
                        }
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
            } elseif ($module === 'favorites') {
                $q->where(function ($inner) use ($kw) {
                    if (ctype_digit($kw)) {
                        $inner->where('id', (int) $kw)
                            ->orWhere('member_id', (int) $kw)
                            ->orWhere('video_id', (int) $kw);
                    } else {
                        $inner->whereRaw('1 = 0');
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
            } elseif ($module === 'botlogs' || $module === 'accesslogs') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('ip', 'like', '%'.$kw.'%')
                        ->orWhere('url', 'like', '%'.$kw.'%')
                        ->orWhere('ua', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'roles') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('blurb', 'like', '%'.$kw.'%')
                        ->orWhere('slug', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)
                            ->orWhere('video_id', (int) $kw)
                            ->orWhere('actor_id', (int) $kw);
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
                    if (Schema::hasTable('actors')) {
                        $actorIds = ActorModel::query()
                            ->where('name', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($actorIds !== []) {
                            $inner->orWhereIn('actor_id', $actorIds);
                        }
                    }
                });
            } elseif ($module === 'plots') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('content', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)
                            ->orWhere('video_id', (int) $kw)
                            ->orWhere('episode_num', (int) $kw);
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
            } elseif ($module === 'synonyms') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('from_word', 'like', '%'.$kw.'%')
                        ->orWhere('to_word', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'downloaders') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('code', 'like', '%'.$kw.'%')
                        ->orWhere('parse', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'servers') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('url', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'domains') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('host', 'like', '%'.$kw.'%')
                        ->orWhere('theme', 'like', '%'.$kw.'%')
                        ->orWhere('remark', 'like', '%'.$kw.'%');
                    if (Schema::hasColumn('video_domains', 'site_name')) {
                        $inner->orWhere('site_name', 'like', '%'.$kw.'%');
                    }
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'mangas') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('author', 'like', '%'.$kw.'%')
                        ->orWhere('remarks', 'like', '%'.$kw.'%');
                    if (Schema::hasColumn('plugin_mangas', 'tags')) {
                        $inner->orWhere('tags', 'like', '%'.$kw.'%');
                    }
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'manga_chapters') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('manga_id', (int) $kw);
                    }
                    if (Schema::hasTable('plugin_mangas')) {
                        $mangaIds = \Plugins\Manga\Models\Manga::query()
                            ->where('title', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($mangaIds !== []) {
                            $inner->orWhereIn('manga_id', $mangaIds);
                        }
                    }
                });
            } elseif ($module === 'manga_pics') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('url', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)
                            ->orWhere('manga_id', (int) $kw)
                            ->orWhere('chapter_id', (int) $kw);
                    }
                });
            } elseif ($module === 'manga_comments') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('content', 'like', '%'.$kw.'%')
                        ->orWhere('author_name', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('manga_id', (int) $kw);
                    }
                    if (Schema::hasTable('plugin_mangas')) {
                        $mangaIds = \Plugins\Manga\Models\Manga::query()
                            ->where('title', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($mangaIds !== []) {
                            $inner->orWhereIn('manga_id', $mangaIds);
                        }
                    }
                });
            } elseif ($module === 'manga_favors') {
                $q->where(function ($inner) use ($kw) {
                    if (ctype_digit($kw)) {
                        $inner->where('id', (int) $kw)
                            ->orWhere('member_id', (int) $kw)
                            ->orWhere('manga_id', (int) $kw);
                    } else {
                        $inner->whereRaw('1 = 0');
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
                    if (Schema::hasTable('plugin_mangas')) {
                        $mangaIds = \Plugins\Manga\Models\Manga::query()
                            ->where('title', 'like', '%'.$kw.'%')
                            ->limit(50)
                            ->pluck('id')
                            ->all();
                        if ($mangaIds !== []) {
                            $inner->orWhereIn('manga_id', $mangaIds);
                        }
                    }
                });
            } elseif ($module === 'activity') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%')
                        ->orWhere('action', 'like', '%'.$kw.'%')
                        ->orWhere('hint', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } elseif ($module === 'task_logs') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('action', 'like', '%'.$kw.'%')
                        ->orWhere('day_key', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('member_id', (int) $kw)->orWhere('task_id', (int) $kw);
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
            } elseif ($module === 'signs') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('day_key', 'like', '%'.$kw.'%');
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
            } elseif ($module === 'sign_milestones') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('name', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw)->orWhere('days', (int) $kw);
                    }
                });
            } else {
                $q->where($cfg['search'], 'like', '%'.$kw.'%');
            }
        }
        if ($module === 'comments' || $module === 'topics' || $module === 'arts' || $module === 'slides' || $module === 'members' || $module === 'orders' || $module === 'groups' || $module === 'reports' || $module === 'guestbooks' || $module === 'playfails' || $module === 'roles') {
            $artQueue = $module === 'arts' ? trim((string) ($params['queue'] ?? '')) : '';
            $artQueued = in_array($artQueue, ['published', 'draft', 'pending'], true);
            if (! $artQueued && array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ($module === 'comments' && (string) ($params['report'] ?? '') === '1' && Schema::hasColumn('video_comments', 'comment_report')) {
                $q->where('comment_report', '>', 0);
            }
            if ($module === 'comments') {
                $this->applyCommentMid($q, (int) ($params['comment_mid'] ?? 1) === 2 ? 2 : 1);
            }
            if ($module === 'arts' && array_key_exists('type_id', $params) && $params['type_id'] !== '' && $params['type_id'] !== null) {
                $this->applyArtTypeFilter($q, (int) $params['type_id']);
            }
            if ($module === 'arts') {
                $this->applyArtQueue($q, $artQueue);
                $flag = strtolower(trim((string) ($params['flag'] ?? '')));
                if (in_array($flag, VideoArt::FLAGS, true) && Schema::hasColumn('video_arts', 'flags')) {
                    $q->withFlag($flag);
                }
                $this->applyArtTagFilter($q, $params);
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
            if ($module === 'roles') {
                if (array_key_exists('video_id', $params) && $params['video_id'] !== '' && $params['video_id'] !== null) {
                    $q->where('video_id', (int) $params['video_id']);
                }
                if (array_key_exists('actor_id', $params) && $params['actor_id'] !== '' && $params['actor_id'] !== null) {
                    $q->where('actor_id', (int) $params['actor_id']);
                }
                if ((string) ($params['empty_video'] ?? '') === '1') {
                    $q->where(function ($inner) {
                        $inner->where('video_id', 0)->orWhereNull('video_id');
                    });
                }
                if ((string) ($params['empty_actor'] ?? '') === '1') {
                    $q->where(function ($inner) {
                        $inner->where('actor_id', 0)->orWhereNull('actor_id');
                    });
                }
                if ((string) ($params['empty_pic'] ?? '') === '1') {
                    $q->where(function ($inner) {
                        $inner->whereNull('cover')->orWhere('cover', '');
                    });
                }
            }
        }
        if ($module === 'plots') {
            if (array_key_exists('video_id', $params) && $params['video_id'] !== '' && $params['video_id'] !== null) {
                $q->where('video_id', (int) $params['video_id']);
            }
            if ((string) ($params['empty_video'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->where('video_id', 0)->orWhereNull('video_id');
                });
            }
            if ((string) ($params['empty_content'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->whereNull('content')->orWhere('content', '');
                });
            }
            if ((string) ($params['empty_title'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->whereNull('title')->orWhere('title', '');
                });
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
        if ($module === 'botlogs') {
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            $this->applyBotlogEngine($q, trim((string) ($params['engine'] ?? '')));
        }
        if ($module === 'accesslogs') {
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            $visitor = trim((string) ($params['visitor'] ?? ''));
            if ($visitor === 'people') {
                $q->where('is_bot', 0);
            } elseif ($visitor === 'bot') {
                $q->where('is_bot', 1);
            }
            $ip = trim((string) ($params['ip'] ?? ''));
            if ($ip !== '') {
                $q->where('ip', $ip);
            }
        }
        if ($module === 'favorites') {
            if (array_key_exists('member_id', $params) && $params['member_id'] !== '' && $params['member_id'] !== null) {
                $q->where('member_id', (int) $params['member_id']);
            }
            if (array_key_exists('video_id', $params) && $params['video_id'] !== '' && $params['video_id'] !== null) {
                $q->where('video_id', (int) $params['video_id']);
            }
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            if ((string) ($params['missing'] ?? '') === '1' && Schema::hasTable('videos')) {
                $q->whereNotIn('video_id', VideoModel::query()->select('id'));
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
        if ($module === 'websites') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            $hasType = Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'type_id');
            if ($hasType && array_key_exists('type_id', $params) && $params['type_id'] !== '' && $params['type_id'] !== null) {
                $q->where('type_id', (int) $params['type_id']);
            }
            if ($hasType && (string) ($params['empty_type'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->where('type_id', 0)->orWhereNull('type_id');
                });
            }
            if ((string) ($params['logo'] ?? '') === '1') {
                $q->where('logo', '!=', '')->whereNotNull('logo');
            }
        }
        if ($module === 'domains') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['current'] ?? '') === '1') {
                $hosts = DomainBindService::hostCandidates(DomainBindService::currentHost());
                if ($hosts === []) {
                    $q->whereRaw('1 = 0');
                } else {
                    $q->whereIn('host', $hosts);
                }
            }
        }
        if ($module === 'mangas') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if (array_key_exists('yid', $params) && $params['yid'] !== '' && $params['yid'] !== null && Schema::hasColumn('plugin_mangas', 'yid')) {
                $q->where('yid', (int) $params['yid']);
            }
            if (array_key_exists('type_id', $params) && $params['type_id'] !== '' && $params['type_id'] !== null && Schema::hasColumn('plugin_mangas', 'type_id')) {
                $typeId = (int) $params['type_id'];
                if ($typeId > 0 && Schema::hasTable('plugin_manga_types')) {
                    $ids = array_values(array_unique(array_merge(
                        [$typeId],
                        array_map('intval', \Plugins\Manga\Models\MangaType::query()->where('parent_id', $typeId)->pluck('id')->all())
                    )));
                    $q->whereIn('type_id', $ids);
                } else {
                    $q->where('type_id', $typeId);
                }
            }
            if (array_key_exists('serialize', $params) && $params['serialize'] !== '' && $params['serialize'] !== null && Schema::hasColumn('plugin_mangas', 'serialize')) {
                $q->where('serialize', (int) $params['serialize']);
            }
            if (array_key_exists('recommend', $params) && $params['recommend'] !== '' && $params['recommend'] !== null && Schema::hasColumn('plugin_mangas', 'recommend')) {
                $q->where('recommend', (int) $params['recommend']);
            }
            $this->applyMangaTagFilter($q, $params);
            $this->applyMangaAuthorFilter($q, $params);
        }
        if ($module === 'manga_types') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
        }
        if ($module === 'manga_chapters' || $module === 'manga_pics') {
            if (array_key_exists('manga_id', $params) && $params['manga_id'] !== '' && $params['manga_id'] !== null) {
                $q->where('manga_id', (int) $params['manga_id']);
            }
        }
        if ($module === 'manga_pics' && array_key_exists('chapter_id', $params) && $params['chapter_id'] !== '' && $params['chapter_id'] !== null) {
            $q->where('chapter_id', (int) $params['chapter_id']);
        }
        if ($module === 'manga_comments') {
            if (array_key_exists('manga_id', $params) && $params['manga_id'] !== '' && $params['manga_id'] !== null) {
                $q->where('manga_id', (int) $params['manga_id']);
            }
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
        }
        if ($module === 'manga_favors') {
            if (array_key_exists('member_id', $params) && $params['member_id'] !== '' && $params['member_id'] !== null) {
                $q->where('member_id', (int) $params['member_id']);
            }
            if (array_key_exists('manga_id', $params) && $params['manga_id'] !== '' && $params['manga_id'] !== null) {
                $q->where('manga_id', (int) $params['manga_id']);
            }
            if ((string) ($params['today'] ?? '') === '1') {
                $q->where('created_at', '>=', strtotime('today'));
            }
            if ((string) ($params['missing'] ?? '') === '1' && Schema::hasTable('plugin_mangas')) {
                $q->whereNotIn('manga_id', \Plugins\Manga\Models\Manga::query()->select('id'));
            }
        }
        if ($module === 'mall_goods') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if (array_key_exists('type', $params) && $params['type'] !== '' && $params['type'] !== null && Schema::hasColumn('plugin_mall_goods', 'type')) {
                $q->where('type', strtolower(trim((string) $params['type'])));
            }
        }
        if ($module === 'mall_orders') {
            $desk = strtolower(trim((string) ($params['desk'] ?? '')));
            if ($desk === 'ship') {
                $q->where('status', 1);
                if (Schema::hasColumn('plugin_mall_orders', 'goods_type')) {
                    $q->where(function ($inner) {
                        $inner->where('goods_type', 'goods')->orWhere('goods_type', '')->orWhereNull('goods_type');
                    });
                }
            } else {
                if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                    $q->where('status', (int) $params['status']);
                }
                if (array_key_exists('goods_type', $params) && $params['goods_type'] !== '' && $params['goods_type'] !== null && Schema::hasColumn('plugin_mall_orders', 'goods_type')) {
                    $q->where('goods_type', strtolower(trim((string) $params['goods_type'])));
                }
            }
        }
        if ($module === 'activity') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if (array_key_exists('type', $params) && $params['type'] !== '' && $params['type'] !== null) {
                $q->where('type', (int) $params['type']);
            }
        }
        if ($module === 'task_logs') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
        }
        if ($module === 'sign_milestones') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
        }
        if ($module === 'classes') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['unused'] ?? '') === '1') {
                $used = array_keys($this->classUsageCounts());
                if ($used !== []) {
                    $q->whereNotIn('name', $used);
                }
            }
        }
        if ($module === 'synonyms') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['empty_to'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->whereNull('to_word')->orWhere('to_word', '');
                });
            }
        }
        if ($module === 'downloaders') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            $kind = trim((string) ($params['kind'] ?? ''));
            if ($kind === 'empty') {
                $q->where(function ($inner) {
                    $inner->whereNull('parse')->orWhere('parse', '');
                });
            } elseif ($kind === 'tpl') {
                $q->where(function ($inner) {
                    $inner->where('parse', 'like', '%{url}%')->orWhere('parse', 'like', '%{id}%');
                });
            } elseif ($kind === 'prefix') {
                $q->where('parse', '!=', '')->whereNotNull('parse')
                    ->where('parse', 'not like', '%{url}%')
                    ->where('parse', 'not like', '%{id}%');
            }
        }
        if ($module === 'servers') {
            if (array_key_exists('status', $params) && $params['status'] !== '' && $params['status'] !== null) {
                $q->where('status', (int) $params['status']);
            }
            if ((string) ($params['empty_url'] ?? '') === '1') {
                $q->where(function ($inner) {
                    $inner->whereNull('url')->orWhere('url', '');
                });
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
        if ($module === 'topics' || $module === 'slides' || $module === 'groups' || $module === 'ads' || $module === 'links' || $module === 'websites' || $module === 'players' || $module === 'downloaders' || $module === 'servers' || $module === 'unions' || $module === 'audits' || $module === 'roles' || $module === 'classes') {
            $q->orderByDesc('sort')->orderByDesc('id');
        } elseif ($module === 'plots') {
            $q->orderByDesc('video_id')->orderBy('episode_num')->orderBy('sort')->orderBy('id');
        } elseif ($module === 'synonyms') {
            $q->orderBy('from_word')->orderBy('id');
        } elseif ($module === 'arts') {
            if ((string) ($params['trash'] ?? '') === '1' && Schema::hasColumn('video_arts', 'deleted_at')) {
                $q->orderByDesc('deleted_at')->orderByDesc('id');
            } else {
                $q->orderByDesc('updated_at')->orderByDesc('id');
            }
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
        } elseif ($module === 'mangas' || $module === 'manga_types' || $module === 'mall_goods' || $module === 'activity' || $module === 'sign_milestones') {
            $q->orderByDesc('sort')->orderByDesc('id');
        } elseif ($module === 'manga_chapters' || $module === 'manga_pics') {
            $q->orderBy('sort')->orderBy('id');
        } else {
            $q->orderByDesc('id');
        }
        $pageNo = max(1, (int) ($params['page'] ?? request()->input('page', 1)));
        $page = $q->paginate($limit, ['*'], 'page', $pageNo);
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
        if ($module === 'websites') {
            $rows = $this->decorateWebsites($rows);
        }
        if ($module === 'domains') {
            $rows = $this->decorateDomains($rows);
        }
        if ($module === 'mangas') {
            $rows = $this->decorateMangas($rows);
        }
        if ($module === 'manga_chapters') {
            $rows = $this->decorateMangaChapters($rows);
        }
        if ($module === 'manga_pics') {
            $rows = $this->decorateMangaPics($rows);
        }
        if ($module === 'manga_comments') {
            $rows = $this->decorateMangaComments($rows);
        }
        if ($module === 'manga_favors') {
            $rows = $this->decorateMangaFavors($rows);
        }
        if ($module === 'manga_types') {
            $rows = $this->decorateMangaTypes($rows);
        }
        if ($module === 'mall_goods') {
            $rows = $this->decorateMallGoods($rows);
        }
        if ($module === 'mall_orders') {
            $rows = $this->decorateMallOrders($rows);
        }
        if ($module === 'activity') {
            $rows = app(MemberActivityService::class)->decorateTasks($rows);
        }
        if ($module === 'task_logs') {
            $rows = app(MemberActivityService::class)->decorateTaskLogs($rows);
        }
        if ($module === 'signs') {
            $rows = app(MemberActivityService::class)->decorateSigns($rows);
        }
        if ($module === 'sign_milestones') {
            $rows = app(MemberActivityService::class)->decorateMilestones($rows);
        }
        if ($module === 'classes') {
            $rows = $this->decorateClasses($rows);
        }
        if ($module === 'synonyms') {
            $rows = $this->decorateSynonyms($rows);
        }
        if ($module === 'downloaders') {
            $rows = $this->decorateDownloaders($rows);
        }
        if ($module === 'servers') {
            $rows = $this->decorateServers($rows);
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
        if ($module === 'favorites') {
            $rows = $this->decorateFavorites($rows);
        }
        if ($module === 'roles') {
            $rows = $this->decorateRoles($rows);
        }
        if ($module === 'plots') {
            $rows = $this->decoratePlots($rows);
        }
        if ($module === 'botlogs' || $module === 'accesslogs') {
            $rows = $this->decorateBotlogs($rows);
        }

        return Result::success(AdminPage::of($page, $rows));
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
        if ($module === 'favorites') {
            return Result::fail('收藏由会员在影片页点出来。后台只查看和删除。');
        }
        if ($module === 'manga_favors') {
            return Result::fail('书架由会员在漫画页点出来。后台只查看和取消。');
        }
        if ($module === 'task_logs') {
            return Result::fail('任务记录由前台完成产生，不能手添或改。');
        }
        if ($module === 'signs') {
            return Result::fail('签到由会员在前台点。后台只查看和删除。');
        }
        if ($module === 'botlogs') {
            return Result::fail('爬虫日志是前台访问记下来的，不能手添。');
        }
        if ($module === 'accesslogs') {
            return Result::fail('访问流水是前台打开页面记下来的，不能手添。');
        }
        if ($module === 'withdraws' && $id === null) {
            return Result::fail('提现由会员申请。后台只审核打款或拒绝。');
        }
        $cfg = $this->config($module);
        $handled = $this->dispatchPlugin($cfg, 'save', [$data, $id]);
        if ($handled !== null) {
            return $handled;
        }
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
                $tid = max(0, (int) $payload['type_id']);
                if ($tid > 0) {
                    if (! Schema::hasTable('video_types')) {
                        return Result::fail('请先执行数据库迁移');
                    }
                    $type = VideoTypeModel::query()->find($tid);
                    if (! $type) {
                        return Result::fail('请选择文章栏目');
                    }
                    if (Schema::hasColumn('video_types', 'mid') && (int) ($type->mid ?? 0) !== 2) {
                        return Result::fail('请选择文章栏目，不要用影片分类');
                    }
                    if (! $type->acceptsArticles()) {
                        return Result::fail($type->kind() === 'link'
                            ? '外链栏目不能挂文章，请换到列表或单页栏目'
                            : '频道栏目只做目录，请把文章挂到下级列表栏目');
                    }
                }
                $payload['type_id'] = $tid;
            }
            foreach (['cover' => 255, 'blurb' => 500, 'source' => 120, 'author' => 80, 'tag' => 255, 'seo_title' => 255, 'seo_key' => 255, 'seo_des' => 500] as $col => $max) {
                if (array_key_exists($col, $payload)) {
                    $payload[$col] = mb_substr(trim((string) $payload[$col]), 0, $max);
                }
            }
            if (array_key_exists('flags', $payload) || array_key_exists('flag_list', $data)
                || array_key_exists('flag_top', $data) || array_key_exists('flag_recommend', $data) || array_key_exists('flag_hot', $data)) {
                $rawFlags = $payload['flags'] ?? '';
                if (! empty($data['flag_list']) && is_array($data['flag_list'])) {
                    $rawFlags = $data['flag_list'];
                }
                if (is_string($rawFlags) || is_array($rawFlags)) {
                    $merged = is_array($rawFlags) ? $rawFlags : (preg_split('/[,，]/u', (string) $rawFlags) ?: []);
                    foreach (VideoArt::FLAGS as $flag) {
                        $key = 'flag_'.$flag;
                        if (array_key_exists($key, $data) && ! in_array((string) $data[$key], ['', '0', 'false'], true)) {
                            $merged[] = $flag;
                        }
                    }
                    $payload['flags'] = VideoArt::normalizeFlags($merged);
                } else {
                    $payload['flags'] = VideoArt::normalizeFlags($rawFlags);
                }
            }
            if (array_key_exists('published_at', $payload)) {
                $payload['published_at'] = $this->parseExpireAt($payload['published_at']);
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
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
        if ($module === 'websites') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写站点名称');
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
            $typeId = array_key_exists('type_id', $payload) || $id === null
                ? max(0, (int) ($payload['type_id'] ?? 0))
                : null;
            if ($typeId !== null) {
                $payload['type_id'] = $typeId;
                if ($typeId > 0) {
                    if (! Schema::hasTable('video_types')) {
                        return Result::fail('分类不存在');
                    }
                    $type = VideoTypeModel::query()->find($typeId);
                    if (! $type) {
                        return Result::fail('分类不存在');
                    }
                    if (Schema::hasColumn('video_types', 'mid') && (int) ($type->mid ?? 0) !== 3) {
                        return Result::fail('这个分类不是网址导航，请到「导航分类」里新建');
                    }
                }
            }
            if (array_key_exists('logo', $payload)) {
                $payload['logo'] = trim((string) $payload['logo']);
            }
            if (array_key_exists('blurb', $payload)) {
                $payload['blurb'] = mb_substr(trim((string) $payload['blurb']), 0, 255);
            }
            if (array_key_exists('hits', $payload) && Schema::hasColumn('video_websites', 'hits')) {
                $payload['hits'] = max(0, (int) $payload['hits']);
            } elseif ($id === null && Schema::hasColumn('video_websites', 'hits')) {
                $payload['hits'] = 0;
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
        if ($module === 'domains') {
            $rawHost = array_key_exists('host', $payload) || $id === null
                ? (string) ($payload['host'] ?? '')
                : null;
            if ($rawHost !== null) {
                $host = DomainBindService::normalizeHost($rawHost);
                if ($host === '') {
                    return Result::fail('请填写域名，不要带 http 和路径');
                }
                $dup = VideoDomain::query()->where('host', $host);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个域名已经绑过');
                }
                $payload['host'] = $host;
            }
            if (array_key_exists('theme', $payload) || $id === null) {
                $theme = trim((string) ($payload['theme'] ?? ''));
                if ($theme !== '' && ! DomainBindService::themeExists($theme)) {
                    return Result::fail('这个模板目录不存在');
                }
                $payload['theme'] = $theme;
            }
            foreach (['site_name' => 120, 'site_keyword' => 255, 'site_description' => 500, 'remark' => 255] as $col => $max) {
                if (array_key_exists($col, $payload) && $this->hasColumn($probe, $col)) {
                    $payload[$col] = mb_substr(trim((string) $payload[$col]), 0, $max);
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if ($id === null && ! array_key_exists('status', $payload)) {
                $payload['status'] = 1;
            }
        }
        $mangaSave = $this->applyMangaSave($module, $payload, $id);
        if ($mangaSave !== null) {
            return $mangaSave;
        }
        $mallSave = $this->applyMallSave($module, $payload, $data, $id);
        if ($mallSave !== null) {
            return $mallSave;
        }
        $activitySave = $this->applyActivitySave($module, $payload, $id);
        if ($activitySave !== null) {
            return $activitySave;
        }
        if ($module === 'classes') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写类型词');
            }
            if ($name !== '') {
                if (preg_match('/[,，]/u', $name)) {
                    return Result::fail('一次只写一个词，不要逗号');
                }
                $name = mb_substr($name, 0, 80);
                $dup = VideoClass::query()->where('name', $name);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个词已经有了');
                }
                $payload['name'] = $name;
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        if ($module === 'synonyms') {
            $from = array_key_exists('from_word', $payload) || $id === null
                ? mb_substr(trim((string) ($payload['from_word'] ?? '')), 0, 80)
                : null;
            if ($id === null && ($from === null || $from === '')) {
                return Result::fail('请填写原词');
            }
            if ($from !== null) {
                if ($from === '') {
                    return Result::fail('请填写原词');
                }
                $payload['from_word'] = $from;
            }
            $to = array_key_exists('to_word', $payload) || $id === null
                ? mb_substr(trim((string) ($payload['to_word'] ?? '')), 0, 80)
                : null;
            if ($id === null && ($to === null || $to === '')) {
                return Result::fail('请填写要当成的词');
            }
            if ($to !== null) {
                if ($to === '') {
                    return Result::fail('请填写要当成的词');
                }
                $payload['to_word'] = $to;
            }
            $checkFrom = $from;
            $checkTo = $to;
            if ($id !== null) {
                $existing = VideoSynonym::query()->find($id);
                if ($existing) {
                    if ($checkFrom === null) {
                        $checkFrom = trim((string) $existing->from_word);
                    }
                    if ($checkTo === null) {
                        $checkTo = trim((string) $existing->to_word);
                    }
                }
            }
            if ($checkFrom !== null && $checkTo !== null && $checkFrom !== '' && $checkFrom === $checkTo) {
                return Result::fail('原词和当成的词不能一样');
            }
            if ($checkFrom !== null && $checkFrom !== '') {
                $dup = VideoSynonym::query()->where('from_word', $checkFrom);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个原词已经有了');
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if ($id === null && ! array_key_exists('status', $payload)) {
                $payload['status'] = 1;
            }
        }
        if ($module === 'downloaders') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写名称');
            }
            if ($name !== '') {
                $payload['name'] = mb_substr($name, 0, 80);
            }
            if (array_key_exists('code', $payload) || $id === null) {
                $code = strtolower(trim((string) ($payload['code'] ?? '')));
                if ($code === '') {
                    return Result::fail('请填写标识，要和线路上的下载器字段一致');
                }
                if (! preg_match('/^[a-z][a-z0-9._-]{0,39}$/', $code)) {
                    return Result::fail('标识用英文字母开头，如 http、xunlei');
                }
                $dup = VideoDownloader::query()->where('code', $code);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个标识已经有了');
                }
                $payload['code'] = $code;
            }
            if (array_key_exists('parse', $payload)) {
                $parse = trim((string) $payload['parse']);
                if (preg_match('#^(javascript|data|vbscript):#i', $parse)) {
                    return Result::fail('模板地址不能用这种协议');
                }
                $payload['parse'] = $parse;
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if ($id === null && ! array_key_exists('status', $payload)) {
                $payload['status'] = 1;
            }
        }
        if ($module === 'servers') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写名称');
            }
            if ($name !== '') {
                $payload['name'] = mb_substr($name, 0, 80);
                $dup = VideoServer::query()->where('name', $payload['name']);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个名称已经有了');
                }
            }
            if (array_key_exists('url', $payload) || $id === null) {
                $raw = trim((string) ($payload['url'] ?? ''));
                if (preg_match('#^(javascript|data|vbscript):#i', $raw)) {
                    return Result::fail('前缀不能用这种协议');
                }
                if (preg_match('#^(https?:)?/+$#i', $raw)) {
                    return Result::fail('前缀不完整');
                }
                $url = $this->normalizeServerPrefix($raw);
                if ($raw !== '' && $url === '') {
                    return Result::fail('前缀不能用这种协议');
                }
                if (mb_strlen($url) > 255) {
                    return Result::fail('前缀太长');
                }
                $payload['url'] = $url;
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if ($id === null && ! array_key_exists('status', $payload)) {
                $payload['status'] = 1;
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
        if ($module === 'roles') {
            $name = trim((string) ($payload['name'] ?? ''));
            if ($id === null && $name === '') {
                return Result::fail('请填写角色名');
            }
            if ($name !== '') {
                $payload['name'] = $name;
            }
            $videoId = array_key_exists('video_id', $payload) || $id === null
                ? max(0, (int) ($payload['video_id'] ?? 0))
                : null;
            if ($videoId !== null) {
                $payload['video_id'] = $videoId;
                if ($videoId > 0) {
                    if (! Schema::hasTable('videos') || ! VideoModel::query()->where('id', $videoId)->exists()) {
                        return Result::fail('影片不存在');
                    }
                }
            }
            $actorId = array_key_exists('actor_id', $payload) || $id === null
                ? max(0, (int) ($payload['actor_id'] ?? 0))
                : null;
            if ($actorId !== null) {
                $payload['actor_id'] = $actorId;
                if ($actorId > 0) {
                    if (! Schema::hasTable('actors') || ! ActorModel::query()->where('id', $actorId)->exists()) {
                        return Result::fail('演员不存在');
                    }
                }
            }
            if (array_key_exists('slug', $payload)) {
                $payload['slug'] = trim((string) $payload['slug']);
            }
            if (array_key_exists('cover', $payload)) {
                $payload['cover'] = trim((string) $payload['cover']);
            }
            if (array_key_exists('blurb', $payload)) {
                $payload['blurb'] = mb_substr(trim((string) $payload['blurb']), 0, 255);
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
        }
        if ($module === 'plots') {
            $videoId = array_key_exists('video_id', $payload) || $id === null
                ? (int) ($payload['video_id'] ?? 0)
                : null;
            if ($id === null && $videoId < 1) {
                return Result::fail('请填写影片 ID');
            }
            if ($videoId !== null) {
                if ($videoId < 1) {
                    return Result::fail('请填写影片 ID');
                }
                if (! Schema::hasTable('videos') || ! VideoModel::query()->where('id', $videoId)->exists()) {
                    return Result::fail('影片不存在');
                }
                $payload['video_id'] = $videoId;
            }
            $ep = array_key_exists('episode_num', $payload) || $id === null
                ? (int) ($payload['episode_num'] ?? 0)
                : null;
            if ($id === null && $ep < 1) {
                return Result::fail('请填写集数，从 1 开始');
            }
            if ($ep !== null) {
                if ($ep < 1) {
                    return Result::fail('请填写集数，从 1 开始');
                }
                $payload['episode_num'] = $ep;
            }
            $content = array_key_exists('content', $payload) ? trim((string) $payload['content']) : null;
            if ($id === null && ($content === null || $content === '')) {
                return Result::fail('请填写这一集的剧情');
            }
            if ($content !== null) {
                $payload['content'] = $content;
            }
            if (array_key_exists('title', $payload)) {
                $payload['title'] = mb_substr(trim((string) $payload['title']), 0, 200);
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            $checkVideo = $videoId;
            $checkEp = $ep;
            if ($id !== null) {
                $existing = VideoPlot::query()->find($id);
                if ($existing) {
                    if ($checkVideo === null) {
                        $checkVideo = (int) $existing->video_id;
                    }
                    if ($checkEp === null) {
                        $checkEp = (int) $existing->episode_num;
                    }
                }
            }
            if ($checkVideo !== null && $checkVideo > 0 && $checkEp !== null && $checkEp > 0) {
                $dup = VideoPlot::query()->where('video_id', $checkVideo)->where('episode_num', $checkEp);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这一集已经写过剧情');
                }
            }
        }
        if ($module === 'comments') {
            $wantMid = (int) ($data['mid'] ?? 1) === 2 ? 2 : 1;
            if ($id) {
                $existing = VideoComment::query()->find($id);
                if ($existing && Schema::hasColumn('video_comments', 'mid')) {
                    $existingMid = (int) ($existing->mid ?: 1);
                    if ($existingMid === 2 && $wantMid !== 2) {
                        return Result::fail('这条是文章评论，请到文章里处理');
                    }
                    if ($existingMid !== 2 && $wantMid === 2) {
                        return Result::fail('这条是影片评论，请到影片里处理');
                    }
                    $wantMid = $existingMid === 2 ? 2 : 1;
                }
            } elseif (Schema::hasColumn('video_comments', 'mid')) {
                $payload['mid'] = $wantMid;
            }
            $rid = (int) ($payload['video_id'] ?? 0);
            if ($id === null && $rid < 1) {
                return Result::fail($wantMid === 2 ? '请填写文章编号' : '请填写影片ID');
            }
            if ($rid > 0) {
                if ($wantMid === 2) {
                    if (! Schema::hasTable('video_arts') || ! VideoArt::query()->where('id', $rid)->exists()) {
                        return Result::fail('文章不存在');
                    }
                } elseif (Schema::hasTable('videos') && ! VideoModel::query()->where('id', $rid)->exists()) {
                    return Result::fail('影片不存在');
                }
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
            $this->syncArtTagsIfPresent($id, $data, $module);
            $this->syncMangaTagsIfPresent($id, $data, $module);
            $this->syncMangaAuthorsIfPresent($id, $data, $module);

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
        $this->syncArtTagsIfPresent($newId, $data, $module);
        $this->syncMangaTagsIfPresent($newId, $data, $module);
        $this->syncMangaAuthorsIfPresent($newId, $data, $module);

        return $this->loggedModule($module, 'save', AdminOpLog::moduleSaveSummary($module, false, AdminOpLog::subjectFrom($payload, $row), $newId), $newId, Result::success(['id' => $newId]));
    }

    private function afterMoneySave(string $module, Model $row, ?int $oldStatus): void
    {
        $status = (int) ($row->status ?? 0);
        if ($module === 'orders' && $status === 1 && $oldStatus !== 1) {
            app(\App\Services\Video\MemberOrderService::class)->fulfillById((int) $row->id);
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
        $handled = $this->dispatchPlugin($cfg, 'delete', [$id]);
        if ($handled !== null) {
            return $handled;
        }
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
        if ($module === 'manga_types') {
            if (Schema::hasTable('plugin_manga_types')) {
                $childCount = (int) \Plugins\Manga\Models\MangaType::query()->where('parent_id', $id)->count();
                if ($childCount > 0) {
                    return Result::fail('请先删掉下级分类');
                }
            }
            if (Schema::hasTable('plugin_mangas') && Schema::hasColumn('plugin_mangas', 'type_id')) {
                $useCount = (int) \Plugins\Manga\Models\Manga::query()->where('type_id', $id)->count();
                if ($useCount > 0) {
                    return Result::fail('该分类下还有作品，请先移走再删');
                }
            }
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
        if ($module === 'downloaders') {
            $code = trim((string) ($row->code ?? ''));
            $name = trim((string) ($row->name ?? ''));
            if (($code !== '' || $name !== '') && Schema::hasTable('video_sources') && Schema::hasColumn('video_sources', 'downer')) {
                $used = (int) VideoSourceModel::query()->where(function ($inner) use ($code, $name) {
                    if ($code !== '') {
                        $inner->where('downer', $code);
                    }
                    if ($name !== '') {
                        $code !== '' ? $inner->orWhere('downer', $name) : $inner->where('downer', $name);
                    }
                })->count();
                if ($used > 0) {
                    return Result::fail('还有 '.$used.' 条线路在用这个下载器。先改线路再删。');
                }
            }
        }
        if ($module === 'servers') {
            $sid = (int) ($row->id ?? 0);
            if ($sid > 0 && Schema::hasTable('video_sources') && Schema::hasColumn('video_sources', 'server_id')) {
                $used = (int) VideoSourceModel::query()->where('server_id', $sid)->count();
                if ($used > 0) {
                    return Result::fail('还有 '.$used.' 条线路在用这个组。先改线路再删。');
                }
            }
        }
        $subject = AdminOpLog::subjectFrom([], $row);
        if ($module === 'arts' && Schema::hasColumn('video_arts', 'deleted_at')) {
            $row->deleted_at = time();
            if ($this->hasColumn($row, 'updated_at')) {
                $row->updated_at = time();
            }
            $row->save();

            return $this->loggedModule($module, 'delete', AdminOpLog::moduleDeleteSummary($module, $subject, $id), $id, Result::success([], '已移入回收站'));
        }
        if ($module === 'arts') {
            app(ArtTagService::class)->detachArt($id);
        }
        if ($module === 'mangas') {
            try {
                app(\Plugins\Manga\Services\MangaService::class)->purgeWork($id);
            } catch (\Throwable) {
            }
        }
        if ($module === 'manga_chapters') {
            try {
                app(\Plugins\Manga\Services\MangaService::class)->purgeChapter($id);
            } catch (\Throwable) {
            }
        }
        $row->delete();

        return $this->loggedModule($module, 'delete', AdminOpLog::moduleDeleteSummary($module, $subject, $id), $id, Result::success());
    }

    /** @return array<string, int> */
    public function commentQueues(int $mid = 1): array
    {
        $zero = ['all' => 0, 'pending' => 0, 'pass' => 0, 'report' => 0];
        $mid = $mid === 2 ? 2 : 1;
        try {
            if (! Schema::hasTable('video_comments')) {
                return $zero;
            }
            $q = VideoComment::query();
            $this->applyCommentMid($q, $mid);

            return [
                'all' => (int) (clone $q)->count(),
                'pending' => (int) (clone $q)->where('status', 0)->count(),
                'pass' => (int) (clone $q)->where('status', 1)->count(),
                'report' => Schema::hasColumn('video_comments', 'comment_report')
                    ? (int) (clone $q)->where('comment_report', '>', 0)->count()
                    : 0,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /**
     * @return array{ready: bool, flags: list<array{key:string,label:string,hint:string,art_count:int,url:string}>}
     */
    public function artFlagBoard(): array
    {
        $defs = [
            ['key' => 'top', 'label' => '置顶', 'hint' => '列表里排在最前。写稿时勾选。'],
            ['key' => 'recommend', 'label' => '推荐', 'hint' => '给前台当推荐稿。写稿时勾选。'],
            ['key' => 'hot', 'label' => '热门', 'hint' => '给前台当热门稿。写稿时勾选。'],
        ];
        $ready = Schema::hasTable('video_arts') && Schema::hasColumn('video_arts', 'flags');
        $flags = [];
        foreach ($defs as $def) {
            $flags[] = [
                'key' => $def['key'],
                'label' => $def['label'],
                'hint' => $def['hint'],
                'art_count' => $ready
                    ? (int) VideoArt::query()->withFlag($def['key'])->count()
                    : 0,
                'url' => '/admin/video/arts?flag='.$def['key'],
            ];
        }

        return ['ready' => $ready, 'flags' => $flags];
    }

    /** @return array<string, int> */
    public function roleQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'no_video' => 0, 'no_actor' => 0, 'no_cover' => 0];
        try {
            if (! Schema::hasTable('video_roles')) {
                return $zero;
            }
            $q = VideoRole::query();

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'no_video' => (int) (clone $q)->where(function ($inner) {
                    $inner->where('video_id', 0)->orWhereNull('video_id');
                })->count(),
                'no_actor' => (int) (clone $q)->where(function ($inner) {
                    $inner->where('actor_id', 0)->orWhereNull('actor_id');
                })->count(),
                'no_cover' => (int) (clone $q)->where(function ($inner) {
                    $inner->whereNull('cover')->orWhere('cover', '');
                })->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function plotQueues(): array
    {
        $zero = ['all' => 0, 'no_content' => 0, 'no_title' => 0, 'no_video' => 0];
        try {
            if (! Schema::hasTable('video_plots')) {
                return $zero;
            }
            $q = VideoPlot::query();

            return [
                'all' => (int) (clone $q)->count(),
                'no_content' => (int) (clone $q)->where(function ($inner) {
                    $inner->whereNull('content')->orWhere('content', '');
                })->count(),
                'no_title' => (int) (clone $q)->where(function ($inner) {
                    $inner->whereNull('title')->orWhere('title', '');
                })->count(),
                'no_video' => (int) (clone $q)->where(function ($inner) {
                    $inner->where('video_id', 0)->orWhereNull('video_id');
                })->count(),
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
        try {
            $cfg = $this->config($module);
        } catch (\InvalidArgumentException) {
            return Result::fail('不支持的操作');
        }
        $handled = $this->dispatchPlugin($cfg, 'batch', [$ids, $action, $value]);
        if ($handled !== null) {
            return $handled;
        }
        if (! in_array($module, ['comments', 'topics', 'arts', 'slides', 'members', 'orders', 'withdraws', 'groups', 'cards', 'invites', 'plogs', 'ads', 'links', 'websites', 'domains', 'classes', 'synonyms', 'downloaders', 'servers', 'roles', 'plots', 'players', 'collect_logs', 'collect_tasks', 'collect_temps', 'audits', 'searchwords', 'reports', 'guestbooks', 'playfails', 'pms', 'notifies', 'favorites', 'botlogs', 'accesslogs', 'mangas', 'manga_comments', 'manga_favors', 'manga_chapters', 'manga_pics', 'manga_types'], true)) {
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
                'websites' => '请先勾选站点',
                'domains' => '请先勾选域名',
                'classes' => '请先勾选类型词',
                'synonyms' => '请先勾选同义词',
                'downloaders' => '请先勾选下载器',
                'servers' => '请先勾选服务器组',
                'roles' => '请先勾选角色',
                'plots' => '请先勾选剧情',
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
                'favorites' => '请先勾选收藏',
                'botlogs' => '请先勾选记录',
                'accesslogs' => '请先勾选记录',
                'audits' => '请先勾选规则',
                'mangas' => '请先勾选作品',
                'manga_comments' => '请先勾选评论',
                'manga_favors' => '请先勾选书架记录',
                'manga_chapters' => '请先勾选章节',
                'manga_pics' => '请先勾选图片',
                'manga_types' => '请先勾选分类',
                default => '请先勾选评论',
            });
        }
        $ok = 0;
        $fail = 0;
        AdminOpLog::quiet(function () use ($ids, $module, $action, $value, &$ok, &$fail) {
            foreach ($ids as $id) {
                $res = match ($action) {
                    'status' => $this->save($module, ['status' => (int) $value], $id),
                    'yid' => $module === 'mangas'
                        ? $this->save($module, ['yid' => (int) $value], $id)
                        : Result::fail('不支持的操作'),
                    'recommend' => $module === 'mangas'
                        ? $this->save($module, ['recommend' => (int) $value], $id)
                        : Result::fail('不支持的操作'),
                    'parent' => $module === 'manga_types'
                        ? $this->save($module, ['parent_id' => (int) $value], $id)
                        : Result::fail('不支持的操作'),
                    'type' => $this->save($module, ['type_id' => (int) $value], $id),
                    'slot' => $this->save($module, ['slot' => (string) $value], $id),
                    'group' => $this->save($module, ['group_id' => (int) $value], $id),
                    'engine' => $this->save($module, ['engine' => (string) $value], $id),
                    'points' => $this->adjustMemberPoints($id, (int) $value),
                    'flags' => $module === 'arts'
                        ? $this->save($module, ['flags' => (string) $value], $id)
                        : Result::fail('不支持的操作'),
                    'flag_top', 'flag_recommend', 'flag_hot' => $module === 'arts'
                        ? $this->applyArtFlag($id, substr($action, 5), true)
                        : Result::fail('不支持的操作'),
                    'unflag_top', 'unflag_recommend', 'unflag_hot' => $module === 'arts'
                        ? $this->applyArtFlag($id, substr($action, 7), false)
                        : Result::fail('不支持的操作'),
                    'copy' => $module === 'arts' ? $this->copyArt($id) : Result::fail('不支持的操作'),
                    'restore' => $module === 'arts' ? $this->restoreArt($id) : Result::fail('不支持的操作'),
                    'purge' => $module === 'arts' ? $this->purgeArt($id) : Result::fail('不支持的操作'),
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
        $channels = ['wechat' => '微信', 'alipay' => '支付宝', 'manual' => '人工', 'epay' => '易支付', 'dfpay' => 'DfPay'];
        $statuses = ['0' => '待付', '1' => '已付', '2' => '关闭'];
        $channelTitles = [];
        try {
            if (Schema::hasTable('plugin_pay_channels') && class_exists(\Plugins\Pay\Models\PayChannel::class)) {
                $channelTitles = \Plugins\Pay\Models\PayChannel::query()->pluck('title', 'id')->all();
            }
        } catch (\Throwable) {
            $channelTitles = [];
        }
        foreach ($rows as &$row) {
            $mid = (int) ($row['member_id'] ?? 0);
            $member = $members[$mid] ?? null;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) $member->email : '';
            $fen = (int) ($row['amount'] ?? 0);
            $row['amount_yuan'] = number_format($fen / 100, 2, '.', '');
            $ch = trim((string) ($row['channel'] ?? ''));
            $row['channel_label'] = $channels[$ch] ?? ($ch !== '' ? $ch : '人工');
            $cid = (int) ($row['pay_channel_id'] ?? 0);
            $title = $cid > 0 ? trim((string) ($channelTitles[$cid] ?? '')) : '';
            if ($title !== '') {
                $row['channel_label'] = $row['channel_label'].' · '.$title;
            }
            $row['status_label'] = $statuses[(string) ($row['status'] ?? '0')] ?? '待付';
            $row['paid_at_text'] = (int) ($row['paid_at'] ?? 0) > 0
                ? date('Y-m-d H:i', (int) $row['paid_at'])
                : '';
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

    /** @return array<string, int> */
    public function favoriteQueues(): array
    {
        $zero = ['all' => 0, 'today' => 0, 'missing' => 0];
        try {
            if (! Schema::hasTable('member_favorites')) {
                return $zero;
            }
            $missing = 0;
            if (Schema::hasTable('videos')) {
                $missing = (int) MemberFavorite::query()->whereNotIn('video_id', VideoModel::query()->select('id'))->count();
            }

            return [
                'all' => (int) MemberFavorite::query()->count(),
                'today' => (int) MemberFavorite::query()->where('created_at', '>=', strtotime('today'))->count(),
                'missing' => $missing,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array{member_name:string,video_title:string} */
    public function favoriteFocus(int $memberId, int $videoId): array
    {
        $name = '';
        $title = '';
        try {
            if ($memberId > 0 && Schema::hasTable('members')) {
                $name = trim((string) (Member::query()->where('id', $memberId)->value('name') ?? ''));
            }
            if ($videoId > 0 && Schema::hasTable('videos')) {
                $title = trim((string) (VideoModel::query()->where('id', $videoId)->value('title') ?? ''));
            }
        } catch (\Throwable) {
        }

        return ['member_name' => $name, 'video_title' => $title];
    }

    /** @return array{video_title:string,actor_name:string} */
    public function roleFocus(int $videoId, int $actorId): array
    {
        $title = '';
        $name = '';
        try {
            if ($videoId > 0 && Schema::hasTable('videos')) {
                $title = trim((string) (VideoModel::query()->where('id', $videoId)->value('title') ?? ''));
            }
            if ($actorId > 0 && Schema::hasTable('actors')) {
                $name = trim((string) (ActorModel::query()->where('id', $actorId)->value('name') ?? ''));
            }
        } catch (\Throwable) {
        }

        return ['video_title' => $title, 'actor_name' => $name];
    }

    /** @return array{video_title:string} */
    public function plotFocus(int $videoId): array
    {
        $focus = $this->roleFocus($videoId, 0);

        return ['video_title' => $focus['video_title']];
    }

    /** @return array<string, int> */
    public function botlogQueues(): array
    {
        $zero = ['all' => 0, 'today' => 0, 'baidu' => 0, 'google' => 0, 'bing' => 0, 'other' => 0];
        try {
            if (! Schema::hasTable('video_access_logs')) {
                return $zero;
            }
            $base = VideoAccessLog::query()->where('is_bot', 1);

            return [
                'all' => (int) (clone $base)->count(),
                'today' => (int) (clone $base)->where('created_at', '>=', strtotime('today'))->count(),
                'baidu' => (int) $this->botlogEngineQuery('baidu')->count(),
                'google' => (int) $this->botlogEngineQuery('google')->count(),
                'bing' => (int) $this->botlogEngineQuery('bing')->count(),
                'other' => (int) $this->botlogEngineQuery('other')->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function accesslogQueues(): array
    {
        $zero = ['all' => 0, 'today' => 0, 'people' => 0, 'bot' => 0];
        try {
            if (! Schema::hasTable('video_access_logs')) {
                return $zero;
            }
            $base = VideoAccessLog::query();

            return [
                'all' => (int) (clone $base)->count(),
                'today' => (int) (clone $base)->where('created_at', '>=', strtotime('today'))->count(),
                'people' => (int) (clone $base)->where('is_bot', 0)->count(),
                'bot' => (int) (clone $base)->where('is_bot', 1)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $q */
    private function applyBotlogEngine($q, string $engine): void
    {
        $engine = strtolower($engine);
        if (! in_array($engine, ['baidu', 'google', 'bing', 'other'], true)) {
            return;
        }
        $detector = app(SpiderDetector::class);
        if ($engine === 'other') {
            foreach ($detector->watchNeedles() as $needle) {
                $q->where('ua', 'not like', '%'.$needle.'%');
            }

            return;
        }
        $needles = $detector->engineNeedles($engine);
        if ($needles === []) {
            return;
        }
        $q->where(function ($inner) use ($needles) {
            foreach ($needles as $i => $needle) {
                $method = $i === 0 ? 'where' : 'orWhere';
                $inner->{$method}('ua', 'like', '%'.$needle.'%');
            }
        });
    }

    /** @return \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> */
    private function botlogEngineQuery(string $engine)
    {
        $q = VideoAccessLog::query()->where('is_bot', 1);
        $this->applyBotlogEngine($q, $engine);

        return $q;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateBotlogs(array $rows): array
    {
        $detector = app(SpiderDetector::class);
        foreach ($rows as &$row) {
            $ua = (string) ($row['ua'] ?? '');
            [, $name] = $detector->detect($ua);
            $group = $detector->group($name);
            $ts = (int) ($row['created_at'] ?? 0);
            $row['spider_name'] = (string) $name;
            $row['spider_label'] = $detector->displayName($name);
            $row['group'] = $group;
            $row['group_label'] = $detector->groupLabel($group);
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['ua_short'] = $this->shortBotUa($ua);
            $row['url_short'] = $this->shortBotUrl((string) ($row['url'] ?? ''));
            $bot = (int) ($row['is_bot'] ?? 0) === 1;
            $row['visitor_kind'] = $bot ? 'bot' : 'people';
            $row['visitor_label'] = $bot ? ((string) $row['spider_label'] !== '' ? (string) $row['spider_label'] : '爬虫') : '访客';
        }
        unset($row);

        return $rows;
    }

    private function shortBotUa(string $ua): string
    {
        $ua = trim($ua);
        if ($ua === '') {
            return '';
        }
        if (function_exists('mb_strlen') && mb_strlen($ua) > 72) {
            return mb_substr($ua, 0, 72).'…';
        }
        if (strlen($ua) > 72) {
            return substr($ua, 0, 72).'…';
        }

        return $ua;
    }

    private function shortBotUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        $path = (string) (parse_url($url, PHP_URL_PATH) ?: $url);
        $query = (string) (parse_url($url, PHP_URL_QUERY) ?: '');
        $shown = $query !== '' ? $path.'?'.$query : $path;
        if (function_exists('mb_strlen') && mb_strlen($shown) > 64) {
            return mb_substr($shown, 0, 64).'…';
        }
        if (strlen($shown) > 64) {
            return substr($shown, 0, 64).'…';
        }

        return $shown;
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
            $row['status_label'] = $status === 1 ? admin_t('ui.enabled') : admin_t('ui.disabled');
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
        return [
            'title' => admin_t('ui.title_label'),
            'content' => admin_t('ui.intro'),
            'actor' => admin_t('ui.actors'),
        ];
    }

    /** @return array<string, string> */
    public function auditActionOptions(): array
    {
        return [
            'skip' => admin_t('ui.audit_skip_full'),
            'review' => admin_t('ui.audit_review_full'),
            'replace' => admin_t('ui.audit_replace_full'),
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
            $row['status_label'] = $status === 1 ? admin_t('ui.enabled') : admin_t('ui.disabled');
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
        $filmIds = [];
        $artIds = [];
        foreach ($rows as $row) {
            $rid = (int) ($row['video_id'] ?? 0);
            if ($rid < 1) {
                continue;
            }
            if ((int) ($row['mid'] ?? 1) === 2) {
                $artIds[] = $rid;
            } else {
                $filmIds[] = $rid;
            }
        }
        $filmTitles = [];
        if ($filmIds !== [] && Schema::hasTable('videos')) {
            $filmTitles = VideoModel::query()->whereIn('id', array_values(array_unique($filmIds)))->pluck('title', 'id')->all();
        }
        $artTitles = [];
        if ($artIds !== [] && Schema::hasTable('video_arts')) {
            $artTitles = VideoArt::query()->whereIn('id', array_values(array_unique($artIds)))->pluck('title', 'id')->all();
        }
        foreach ($rows as &$row) {
            $rid = (int) ($row['video_id'] ?? 0);
            $mid = (int) ($row['mid'] ?? 1) === 2 ? 2 : 1;
            $ts = (int) ($row['created_at'] ?? 0);
            $row['mid'] = $mid;
            $row['video_title'] = $mid === 2
                ? (string) ($artTitles[$rid] ?? '')
                : (string) ($filmTitles[$rid] ?? '');
            $row['target_url'] = $rid > 0
                ? ($mid === 2 ? '/art/'.$rid : '/vod/'.$rid)
                : '';
            $row['target_kind'] = $mid === 2 ? 'art' : 'vod';
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['comment_report'] = (int) ($row['comment_report'] ?? 0);
            $row['comment_up'] = (int) ($row['comment_up'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    private function applyCommentMid($query, int $mid): void
    {
        if (! Schema::hasColumn('video_comments', 'mid')) {
            if ($mid === 2) {
                $query->whereRaw('0 = 1');
            }

            return;
        }
        if ($mid === 2) {
            $query->where('mid', 2);

            return;
        }
        $query->where(function ($inner) {
            $inner->where('mid', 1)->orWhereNull('mid')->orWhere('mid', 0);
        });
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
    private function decorateFavorites(array $rows): array
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
        $members = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            try {
                $members = Member::query()
                    ->whereIn('id', array_values(array_unique($memberIds)))
                    ->get(['id', 'name', 'email'])
                    ->keyBy('id')
                    ->all();
            } catch (\Throwable) {
                $members = [];
            }
        }
        foreach ($rows as &$row) {
            $vid = (int) ($row['video_id'] ?? 0);
            $mid = (int) ($row['member_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $member = $members[$mid] ?? null;
            $row['video_title'] = (string) ($titles[$vid] ?? '');
            $row['video_missing'] = $vid > 0 && ! array_key_exists($vid, $titles) ? 1 : 0;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) $member->email : '';
            $row['member_missing'] = $mid > 0 && ! $member ? 1 : 0;
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateRoles(array $rows): array
    {
        $videoIds = [];
        $actorIds = [];
        foreach ($rows as $row) {
            $vid = (int) ($row['video_id'] ?? 0);
            if ($vid > 0) {
                $videoIds[] = $vid;
            }
            $aid = (int) ($row['actor_id'] ?? 0);
            if ($aid > 0) {
                $actorIds[] = $aid;
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
        $actors = [];
        if ($actorIds !== [] && Schema::hasTable('actors')) {
            try {
                $actors = ActorModel::query()->whereIn('id', array_values(array_unique($actorIds)))->pluck('name', 'id')->all();
            } catch (\Throwable) {
                $actors = [];
            }
        }
        foreach ($rows as &$row) {
            $vid = (int) ($row['video_id'] ?? 0);
            $aid = (int) ($row['actor_id'] ?? 0);
            $row['video_title'] = (string) ($titles[$vid] ?? '');
            $row['video_missing'] = $vid > 0 && ! array_key_exists($vid, $titles) ? 1 : 0;
            $row['actor_name'] = (string) ($actors[$aid] ?? '');
            $row['actor_missing'] = $aid > 0 && ! array_key_exists($aid, $actors) ? 1 : 0;
            $row['has_cover'] = trim((string) ($row['cover'] ?? '')) !== '';
            $row['is_on'] = (int) ($row['status'] ?? 0) === 1;
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decoratePlots(array $rows): array
    {
        $videoIds = [];
        foreach ($rows as $row) {
            $vid = (int) ($row['video_id'] ?? 0);
            if ($vid > 0) {
                $videoIds[] = $vid;
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
        foreach ($rows as &$row) {
            $vid = (int) ($row['video_id'] ?? 0);
            $ep = (int) ($row['episode_num'] ?? 0);
            $title = trim((string) ($row['title'] ?? ''));
            $content = trim((string) ($row['content'] ?? ''));
            $ts = (int) ($row['created_at'] ?? 0);
            $row['video_title'] = (string) ($titles[$vid] ?? '');
            $row['video_missing'] = $vid > 0 && ! array_key_exists($vid, $titles) ? 1 : 0;
            $row['episode_label'] = $ep > 0 ? '第'.$ep.'集' : '未写集数';
            $row['title_text'] = $title !== '' ? $title : ($ep > 0 ? '第'.$ep.'集' : '未写标题');
            $row['has_content'] = $content !== '' ? 1 : 0;
            $row['content_preview'] = mb_substr($content, 0, 80);
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
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
        $now = time();
        $flagMap = ['top' => '置顶', 'recommend' => '推荐', 'hot' => '热门'];
        $tagMap = $this->artTagMap(array_values(array_filter(array_map(static fn ($row) => (int) ($row['id'] ?? 0), $rows))));
        foreach ($rows as &$row) {
            $tid = (int) ($row['type_id'] ?? 0);
            $ts = (int) ($row['updated_at'] ?? ($row['created_at'] ?? 0));
            $row['type_name'] = (string) ($names[$tid] ?? '');
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['updated_at_unix'] = $ts;
            $row['deleted_at_unix'] = (int) ($row['deleted_at'] ?? 0);
            $row['has_cover'] = trim((string) ($row['cover'] ?? '')) !== '';
            $flags = array_values(array_filter(array_map('trim', explode(',', (string) ($row['flags'] ?? '')))));
            $labels = [];
            foreach ($flags as $flag) {
                if (isset($flagMap[$flag])) {
                    $labels[] = $flagMap[$flag];
                }
            }
            $row['flags_label'] = implode('/', $labels);
            $pub = (int) ($row['published_at'] ?? 0);
            if ($pub > $now) {
                $row['published_text'] = '定时 '.date('Y-m-d H:i', $pub);
            } elseif ($pub > 0) {
                $row['published_text'] = date('Y-m-d H:i', $pub);
            } else {
                $created = (int) ($row['created_at'] ?? 0);
                $row['published_text'] = $created > 0 ? date('Y-m-d H:i', $created) : '';
            }
            $row['listed'] = ((int) ($row['status'] ?? 0) === 1) && ($pub === 0 || $pub <= $now);
            $artId = (int) ($row['id'] ?? 0);
            $tags = $tagMap[$artId] ?? [];
            if ($tags === []) {
                foreach (preg_split('/[,，]/u', (string) ($row['tag'] ?? '')) ?: [] as $name) {
                    $name = trim((string) $name);
                    if ($name !== '') {
                        $tags[] = ['id' => 0, 'name' => $name, 'slug' => ''];
                    }
                }
            }
            $row['tag_list'] = $tags;
            $row['tag_label'] = implode(' / ', array_column($tags, 'name'));
        }
        unset($row);

        return $rows;
    }

    /** @return list<array{id:int,name:string,parent_id:int,depth:int}> */
    public function artTypeOptions(): array
    {
        return $this->typeOptionTree(2);
    }

    /** @return list<array{id:int,name:string,parent_id:int,depth:int}> */
    public function websiteTypeOptions(): array
    {
        return $this->typeOptionTree(3);
    }

    /** @return array<string, int> */
    public function artQueues(): array
    {
        $zero = ['all' => 0, 'published' => 0, 'draft' => 0, 'pending' => 0];
        try {
            if (! Schema::hasTable('video_arts')) {
                return $zero;
            }
            $pending = 0;
            $publishedQ = VideoArt::query()->listed();
            if (Schema::hasColumn('video_arts', 'published_at')) {
                $pending = (int) VideoArt::query()->where('status', 1)->where('published_at', '>', time())->count();
            }

            return [
                'all' => (int) VideoArt::query()->count(),
                'published' => (int) $publishedQ->count(),
                'draft' => (int) VideoArt::query()->where('status', 0)->count(),
                'pending' => $pending,
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    private function applyArtTypeFilter(\Illuminate\Database\Eloquent\Builder $q, int $typeId): void
    {
        if ($typeId < 1) {
            $q->where(function ($inner) {
                $inner->where('type_id', 0)->orWhereNull('type_id');
            });

            return;
        }
        $type = VideoTypeModel::query()->find($typeId);
        if (! $type) {
            $q->whereRaw('0 = 1');

            return;
        }
        if (Schema::hasColumn('video_types', 'mid') && (int) ($type->mid ?? 0) !== 2) {
            $q->whereRaw('0 = 1');

            return;
        }
        $q->whereIn('type_id', $type->descendantIds());
    }

    public function artLooseCount(): int
    {
        try {
            if (! Schema::hasTable('video_arts')) {
                return 0;
            }

            return (int) VideoArt::query()->where(function ($inner) {
                $inner->where('type_id', 0)->orWhereNull('type_id');
            })->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    public function resolveArtTypeId(int $typeId): int
    {
        if ($typeId < 1) {
            return 0;
        }
        foreach ($this->artTypeOptions() as $row) {
            if ((int) ($row['id'] ?? 0) === $typeId) {
                return $typeId;
            }
        }

        return 0;
    }

    private function applyArtQueue(\Illuminate\Database\Eloquent\Builder $q, string $queue): void
    {
        if ($queue === 'draft') {
            $q->where('status', 0);

            return;
        }
        if ($queue === 'pending') {
            $q->where('status', 1);
            if (Schema::hasColumn('video_arts', 'published_at')) {
                $q->where('published_at', '>', time());
            } else {
                $q->whereRaw('0 = 1');
            }

            return;
        }
        if ($queue === 'published') {
            $q->listed();
        }
    }

    private function applyArtFlag(int $id, string $flag, bool $on): array
    {
        $flag = strtolower(trim($flag));
        if (! in_array($flag, VideoArt::FLAGS, true)) {
            return Result::fail('不支持的操作');
        }
        $row = VideoArt::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $list = $row->flagList();
        if ($on) {
            if (! in_array($flag, $list, true)) {
                $list[] = $flag;
            }
        } else {
            $list = array_values(array_filter($list, fn ($item) => $item !== $flag));
        }

        return $this->save('arts', ['flags' => implode(',', $list)], $id);
    }

    private function applyArtTagFilter(\Illuminate\Database\Eloquent\Builder $q, array $params): void
    {
        $tagId = (int) ($params['tag_id'] ?? 0);
        if ($tagId < 1) {
            return;
        }
        if (Schema::hasTable('video_art_tag_rel') && Schema::hasTable('video_art_tags')) {
            $q->whereHas('tags', fn ($inner) => $inner->where('video_art_tags.id', $tagId));

            return;
        }
        $name = '';
        if (Schema::hasTable('video_art_tags')) {
            $name = trim((string) (VideoArtTag::query()->find($tagId)?->name ?? ''));
        }
        if ($name !== '' && Schema::hasColumn('video_arts', 'tag')) {
            $q->where('tag', 'like', '%'.$name.'%');

            return;
        }
        $q->whereRaw('0 = 1');
    }

    /**
     * @param  list<int>  $artIds
     * @return array<int, list<array{id:int,name:string,slug:string}>>
     */
    private function artTagMap(array $artIds): array
    {
        $artIds = array_values(array_unique(array_filter($artIds)));
        if ($artIds === [] || ! Schema::hasTable('video_art_tag_rel') || ! Schema::hasTable('video_art_tags')) {
            return [];
        }
        $map = [];
        $rows = DB::table('video_art_tag_rel as r')
            ->join('video_art_tags as t', 't.id', '=', 'r.tag_id')
            ->whereIn('r.art_id', $artIds)
            ->orderByDesc('t.sort')
            ->orderByDesc('t.id')
            ->get(['r.art_id', 't.id', 't.name', 't.slug']);
        foreach ($rows as $row) {
            $map[(int) $row->art_id][] = [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'slug' => (string) $row->slug,
            ];
        }

        return $map;
    }

    private function syncArtTagsIfPresent(int $id, array $data, string $module): void
    {
        if ($module !== 'arts' || $id < 1) {
            return;
        }
        if (
            ! array_key_exists('tag', $data)
            && ! array_key_exists('tags', $data)
            && ! array_key_exists('tag_ids', $data)
            && ! array_key_exists('tag_ids[]', $data)
            && ! array_key_exists('tag_extra', $data)
        ) {
            return;
        }
        if (! isset($data['tag_ids']) && isset($data['tag_ids[]'])) {
            $data['tag_ids'] = $data['tag_ids[]'];
        }
        app(ArtTagService::class)->syncArt($id, $data);
    }

    private function syncMangaTagsIfPresent(int $id, array $data, string $module): void
    {
        if ($module !== 'mangas' || $id < 1) {
            return;
        }
        if (
            ! array_key_exists('tag', $data)
            && ! array_key_exists('tags', $data)
            && ! array_key_exists('tag_ids', $data)
            && ! array_key_exists('tag_ids[]', $data)
            && ! array_key_exists('tag_extra', $data)
        ) {
            return;
        }
        if (! isset($data['tag_ids']) && isset($data['tag_ids[]'])) {
            $data['tag_ids'] = $data['tag_ids[]'];
        }
        try {
            app(\Plugins\Manga\Services\MangaTagService::class)->syncManga($id, $data);
        } catch (\Throwable) {
        }
    }

    private function syncMangaAuthorsIfPresent(int $id, array $data, string $module): void
    {
        if ($module !== 'mangas' || $id < 1) {
            return;
        }
        if (
            ! array_key_exists('author', $data)
            && ! array_key_exists('authors', $data)
            && ! array_key_exists('author_ids', $data)
            && ! array_key_exists('author_ids[]', $data)
            && ! array_key_exists('author_extra', $data)
        ) {
            return;
        }
        if (! isset($data['author_ids']) && isset($data['author_ids[]'])) {
            $data['author_ids'] = $data['author_ids[]'];
        }
        try {
            app(\Plugins\Manga\Services\MangaAuthorService::class)->syncManga($id, $data);
        } catch (\Throwable) {
        }
    }

    private function applyMangaTagFilter(\Illuminate\Database\Eloquent\Builder $q, array $params): void
    {
        $tagId = (int) ($params['tag_id'] ?? 0);
        if ($tagId < 1) {
            return;
        }
        if (Schema::hasTable('plugin_manga_tag_rel') && Schema::hasTable('plugin_manga_tags')) {
            $q->whereHas('tagRels', fn ($inner) => $inner->where('plugin_manga_tags.id', $tagId));

            return;
        }
        $name = '';
        if (Schema::hasTable('plugin_manga_tags')) {
            $name = trim((string) (\Plugins\Manga\Models\MangaTag::query()->find($tagId)?->name ?? ''));
        }
        if ($name !== '' && Schema::hasColumn('plugin_mangas', 'tags')) {
            $q->where(function ($inner) use ($name) {
                $inner->where('tags', $name)
                    ->orWhere('tags', 'like', $name.',%')
                    ->orWhere('tags', 'like', '%,'.$name)
                    ->orWhere('tags', 'like', '%,'.$name.',%');
            });
        }
    }

    private function applyMangaAuthorFilter(\Illuminate\Database\Eloquent\Builder $q, array $params): void
    {
        $authorId = (int) ($params['author_id'] ?? 0);
        if ($authorId < 1) {
            return;
        }
        if (Schema::hasTable('plugin_manga_author_rel') && Schema::hasTable('plugin_manga_authors')) {
            $q->whereHas('authorRels', fn ($inner) => $inner->where('plugin_manga_authors.id', $authorId));

            return;
        }
        $name = '';
        if (Schema::hasTable('plugin_manga_authors')) {
            $name = trim((string) (\Plugins\Manga\Models\MangaAuthor::query()->find($authorId)?->name ?? ''));
        }
        if ($name !== '' && Schema::hasColumn('plugin_mangas', 'author')) {
            $q->where(function ($inner) use ($name) {
                $inner->where('author', $name)
                    ->orWhere('author', 'like', $name.',%')
                    ->orWhere('author', 'like', '%,'.$name)
                    ->orWhere('author', 'like', '%,'.$name.',%');
            });
        }
    }

    public function copyArt(int $id): array
    {
        $row = VideoArt::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        $now = time();
        $payload = $row->getAttributes();
        unset($payload['id']);
        $title = trim((string) ($payload['title'] ?? ''));
        $payload['title'] = mb_substr($title === '' ? '副本' : $title.' 副本', 0, 200);
        $payload['status'] = 0;
        $payload['hits'] = 0;
        if (Schema::hasColumn('video_arts', 'deleted_at')) {
            $payload['deleted_at'] = 0;
        }
        $payload['created_at'] = $now;
        $payload['updated_at'] = $now;
        $copy = VideoArt::query()->create($payload);
        $newId = (int) $copy->id;
        app(ArtTagService::class)->syncArt($newId, [
            'tag_ids' => app(ArtTagService::class)->idsForArt($id),
            'tag' => (string) ($row->tag ?? ''),
        ]);

        return Result::success(['id' => $newId], '已复制为草稿');
    }

    public function restoreArt(int $id): array
    {
        if (! Schema::hasColumn('video_arts', 'deleted_at')) {
            return Result::fail('请先执行数据库迁移');
        }
        $row = VideoArt::query()->withoutGlobalScope('alive')->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if ((int) ($row->deleted_at ?? 0) < 1) {
            return Result::fail('不在回收站');
        }
        $row->deleted_at = 0;
        if ($this->hasColumn($row, 'updated_at')) {
            $row->updated_at = time();
        }
        $row->save();

        return $this->loggedModule('arts', 'restore', '从回收站还原了文章「'.trim((string) $row->title).'」', $id, Result::success(['id' => $id], '已还原'));
    }

    public function purgeArt(int $id): array
    {
        $row = Schema::hasColumn('video_arts', 'deleted_at')
            ? VideoArt::query()->withoutGlobalScope('alive')->find($id)
            : VideoArt::query()->find($id);
        if (! $row) {
            return Result::fail('数据不存在');
        }
        if (Schema::hasColumn('video_arts', 'deleted_at') && (int) ($row->deleted_at ?? 0) < 1) {
            return Result::fail('请先删进回收站');
        }
        $subject = trim((string) $row->title);
        app(ArtTagService::class)->detachArt($id);
        $row->delete();

        return $this->loggedModule('arts', 'purge', '彻底删除了文章「'.$subject.'」', $id, Result::success([], '已彻底删除'));
    }

    public function emptyArtRecycle(): array
    {
        if (! Schema::hasColumn('video_arts', 'deleted_at')) {
            return Result::fail('请先执行数据库迁移');
        }
        $ids = VideoArt::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0)->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($ids === []) {
            return Result::fail('回收站是空的');
        }
        $ok = 0;
        AdminOpLog::quiet(function () use ($ids, &$ok) {
            foreach ($ids as $id) {
                $res = $this->purgeArt($id);
                if ((int) ($res['code'] ?? 1) === 0) {
                    $ok++;
                }
            }
        });
        if ($ok === 0) {
            return Result::fail('操作失败');
        }

        return $this->loggedModule('arts', 'purge', '清空了文章回收站 '.$ok.' 篇', 0, Result::success(['ok' => $ok], '已清空回收站'), ['count' => $ok]);
    }

    public function artRecycleCount(): int
    {
        try {
            if (! Schema::hasTable('video_arts') || ! Schema::hasColumn('video_arts', 'deleted_at')) {
                return 0;
            }

            return (int) VideoArt::query()->withoutGlobalScope('alive')->where('deleted_at', '>', 0)->count();
        } catch (\Throwable) {
            return 0;
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

    /** @return list<array{id:int,name:string,parent_id:int,depth:int}> */
    public function vodTypeOptions(): array
    {
        return $this->typeOptionTree(1);
    }

    /**
     * @return list<array{id:int,name:string,parent_id:int,depth:int}>
     */
    private function typeOptionTree(int $mid): array
    {
        try {
            if (! Schema::hasTable('video_types')) {
                return [];
            }
            $q = VideoTypeModel::query()->orderByDesc('sort')->orderBy('id');
            if (Schema::hasColumn('video_types', 'mid')) {
                $q->where('mid', $mid);
            } elseif ($mid !== 1) {
                return [];
            }
            $cols = ['id', 'name', 'parent_id'];
            if (Schema::hasColumn('video_types', 'kind')) {
                $cols[] = 'kind';
            }
            $rows = $q->get($cols);
            $counts = [];
            if ($mid === 2 && Schema::hasTable('video_arts')) {
                try {
                    $counts = VideoArt::query()
                        ->selectRaw('type_id, count(*) as c')
                        ->groupBy('type_id')
                        ->pluck('c', 'type_id')
                        ->all();
                } catch (\Throwable) {
                    $counts = [];
                }
            }
            $byParent = [];
            $ids = [];
            foreach ($rows as $row) {
                $ids[(int) $row->id] = true;
            }
            foreach ($rows as $row) {
                $pid = (int) ($row->parent_id ?? 0);
                if ($pid > 0 && ! isset($ids[$pid])) {
                    $pid = 0;
                }
                $byParent[$pid][] = $row;
            }
            $out = [];
            $walk = function (int $parentId, int $depth) use (&$walk, &$out, $byParent, $counts): void {
                foreach ($byParent[$parentId] ?? [] as $row) {
                    $id = (int) $row->id;
                    $raw = (string) $row->name;
                    $pad = $depth > 0 ? str_repeat('└ ', $depth) : '';
                    $kind = VideoTypeModel::normalizeKind($row->kind ?? 'list');
                    $out[] = [
                        'id' => $id,
                        'name' => $pad.$raw,
                        'title' => $raw,
                        'parent_id' => (int) ($row->parent_id ?? 0),
                        'depth' => $depth,
                        'kind' => $kind,
                        'kind_label' => VideoTypeModel::kindLabel($kind),
                        'art_count' => (int) ($counts[$id] ?? $counts[(string) $id] ?? 0),
                    ];
                    $walk($id, $depth + 1);
                }
            };
            $walk(0, 0);

            return $out;
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

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateWebsites(array $rows): array
    {
        $typeIds = [];
        $hasType = Schema::hasTable('video_websites') && Schema::hasColumn('video_websites', 'type_id');
        if ($hasType) {
            foreach ($rows as $row) {
                $tid = (int) ($row['type_id'] ?? 0);
                if ($tid > 0) {
                    $typeIds[] = $tid;
                }
            }
        }
        $types = [];
        $hasMid = Schema::hasTable('video_types') && Schema::hasColumn('video_types', 'mid');
        if ($typeIds !== [] && Schema::hasTable('video_types')) {
            try {
                $cols = ['id', 'name'];
                if ($hasMid) {
                    $cols[] = 'mid';
                }
                foreach (VideoTypeModel::query()->whereIn('id', array_values(array_unique($typeIds)))->get($cols) as $type) {
                    $types[(int) $type->id] = $type;
                }
            } catch (\Throwable) {
                $types = [];
            }
        }
        foreach ($rows as &$row) {
            $tid = $hasType ? (int) ($row['type_id'] ?? 0) : 0;
            $type = $types[$tid] ?? null;
            $logo = trim((string) ($row['logo'] ?? ''));
            $url = trim((string) ($row['url'] ?? ''));
            $ts = (int) ($row['created_at'] ?? 0);
            $id = (int) ($row['id'] ?? 0);
            $row['type_name'] = $type ? (string) $type->name : '';
            $row['type_missing'] = $tid > 0 && $type === null ? 1 : 0;
            $row['type_wrong'] = $type && $hasMid && (int) ($type->mid ?? 0) !== 3 ? 1 : 0;
            $row['has_logo'] = $logo !== '' ? 1 : 0;
            $row['has_url'] = $url !== '' ? 1 : 0;
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['front_url'] = $id > 0 ? vod_url('website', ['id' => $id]) : '';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateClasses(array $rows): array
    {
        $counts = $this->classUsageCounts();
        foreach ($rows as &$row) {
            $name = trim((string) ($row['name'] ?? ''));
            $used = $name !== '' ? (int) ($counts[$name] ?? 0) : 0;
            $row['used_count'] = $used;
            $row['is_on'] = (int) ($row['status'] ?? 0) === 1;
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateSynonyms(array $rows): array
    {
        foreach ($rows as &$row) {
            $from = trim((string) ($row['from_word'] ?? ''));
            $to = trim((string) ($row['to_word'] ?? ''));
            $row['is_on'] = (int) ($row['status'] ?? 0) === 1;
            $row['empty_to'] = $to === '';
            $row['preview'] = ($from !== '' && $to !== '') ? ('「'.$from.'」当成「'.$to.'」') : '';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateDownloaders(array $rows): array
    {
        $keys = [];
        foreach ($rows as $row) {
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            if ($code !== '') {
                $keys[] = $code;
            }
            if ($name !== '') {
                $keys[] = $name;
            }
        }
        $counts = [];
        if ($keys !== [] && Schema::hasTable('video_sources') && Schema::hasColumn('video_sources', 'downer')) {
            $countRows = VideoSourceModel::query()
                ->selectRaw('downer, COUNT(*) as c')
                ->whereIn('downer', array_values(array_unique($keys)))
                ->groupBy('downer')
                ->get();
            foreach ($countRows as $row) {
                $counts[(string) $row->downer] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $code = trim((string) ($row['code'] ?? ''));
            $name = trim((string) ($row['name'] ?? ''));
            $parse = trim((string) ($row['parse'] ?? ''));
            $kind = $this->downloaderParseKind($parse);
            $row['is_on'] = (int) ($row['status'] ?? 0) === 1;
            $row['parse_kind'] = $kind;
            $row['parse_kind_label'] = match ($kind) {
                'tpl' => '模板',
                'prefix' => '前缀',
                default => '原样',
            };
            $row['source_count'] = ($counts[$code] ?? 0) + ($name !== '' && $name !== $code ? ($counts[$name] ?? 0) : 0);
            $row['parse_preview'] = $parse === '' ? '' : mb_substr($parse, 0, 80);
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateServers(array $rows): array
    {
        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }
        $counts = [];
        if ($ids !== [] && Schema::hasTable('video_sources') && Schema::hasColumn('video_sources', 'server_id')) {
            $countRows = VideoSourceModel::query()
                ->selectRaw('server_id, COUNT(*) as c')
                ->whereIn('server_id', $ids)
                ->groupBy('server_id')
                ->get();
            foreach ($countRows as $row) {
                $counts[(int) $row->server_id] = (int) $row->c;
            }
        }
        foreach ($rows as &$row) {
            $url = trim((string) ($row['url'] ?? ''));
            $row['is_on'] = (int) ($row['status'] ?? 0) === 1;
            $row['has_url'] = $url !== '';
            $row['url_preview'] = $url === '' ? '' : mb_substr($url, 0, 80);
            $row['source_count'] = (int) ($counts[(int) ($row['id'] ?? 0)] ?? 0);
        }
        unset($row);

        return $rows;
    }

    private function downloaderParseKind(string $parse): string
    {
        $parse = trim($parse);
        if ($parse === '') {
            return 'empty';
        }
        if (str_contains($parse, '{url}') || str_contains($parse, '{id}')) {
            return 'tpl';
        }

        return 'prefix';
    }

    /** @return array<string, int> */
    private function classUsageCounts(): array
    {
        $counts = [];
        try {
            if (! Schema::hasTable('videos') || ! Schema::hasColumn('videos', 'class')) {
                return $counts;
            }
            $rows = VideoModel::query()->where('class', '!=', '')->pluck('class');
            foreach ($rows as $raw) {
                foreach (preg_split('/[,，]+/u', (string) $raw) ?: [] as $part) {
                    $part = trim($part);
                    if ($part !== '') {
                        $counts[$part] = ($counts[$part] ?? 0) + 1;
                    }
                }
            }
        } catch (\Throwable) {
            return [];
        }

        return $counts;
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

    /** @return array<string, int> */
    public function websiteQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'no_type' => 0, 'logo' => 0];
        try {
            if (! Schema::hasTable('video_websites')) {
                return $zero;
            }
            $q = VideoWebsite::query();
            $hasType = Schema::hasColumn('video_websites', 'type_id');

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'no_type' => $hasType
                    ? (int) (clone $q)->where(function ($inner) {
                        $inner->where('type_id', 0)->orWhereNull('type_id');
                    })->count()
                    : 0,
                'logo' => (int) (clone $q)->where('logo', '!=', '')->whereNotNull('logo')->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    public function domainThemeOptions(): array
    {
        return DomainBindService::themeOptions();
    }

    /** @return array<string, int> */
    public function domainQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'current' => 0];
        try {
            if (! Schema::hasTable('video_domains')) {
                return $zero;
            }
            $hosts = DomainBindService::hostCandidates(DomainBindService::currentHost());
            $q = VideoDomain::query();

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'current' => $hosts === [] ? 0 : (int) (clone $q)->whereIn('host', $hosts)->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateDomains(array $rows): array
    {
        $current = DomainBindService::hostCandidates(DomainBindService::currentHost());
        foreach ($rows as &$row) {
            $host = (string) ($row['host'] ?? '');
            $theme = trim((string) ($row['theme'] ?? ''));
            $name = trim((string) ($row['site_name'] ?? ''));
            $row['is_current'] = $host !== '' && in_array($host, $current, true);
            $row['theme_label'] = $theme === '' ? '跟站点设置' : DomainBindService::themeTitle($theme);
            $row['theme_missing'] = $theme !== '' && ! DomainBindService::themeExists($theme);
            $row['site_name_label'] = $name !== '' ? $name : '跟站点设置';
            $row['status'] = (int) ($row['status'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    /** @return array<string, int> */
    public function classQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'unused' => 0];
        try {
            if (! Schema::hasTable('video_classes')) {
                return $zero;
            }
            $q = VideoClass::query();
            $used = array_keys($this->classUsageCounts());
            $unusedQ = clone $q;
            if ($used !== []) {
                $unusedQ->whereNotIn('name', $used);
            }

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'unused' => (int) $unusedQ->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function synonymQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'empty_to' => 0];
        try {
            if (! Schema::hasTable('video_synonyms')) {
                return $zero;
            }
            $q = VideoSynonym::query();

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'empty_to' => (int) (clone $q)->where(function ($inner) {
                    $inner->whereNull('to_word')->orWhere('to_word', '');
                })->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @param  array<string, mixed>  $data */
    public function trySynonym(array $data): array
    {
        $kw = trim((string) ($data['kw'] ?? $data['word'] ?? $data['sample'] ?? ''));
        if ($kw === '') {
            return Result::fail('请填一个词试试');
        }
        $on = 0;
        try {
            if (Schema::hasTable('video_synonyms')) {
                $on = (int) VideoSynonym::query()->where('status', 1)->count();
            }
        } catch (\Throwable) {
            $on = 0;
        }
        $out = app(SynonymService::class)->expand($kw);
        if ($on === 0) {
            return Result::success(['from' => $kw, 'to' => $out, 'changed' => false], '还没有启用的规则，搜什么就是什么');
        }
        if ($out !== $kw) {
            return Result::success(['from' => $kw, 'to' => $out, 'changed' => true], '会当成「'.$out.'」去查');
        }

        return Result::success(['from' => $kw, 'to' => $out, 'changed' => false], '现有启用规则不会改这个词');
    }

    /** @return array<string, int> */
    public function downloaderQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'tpl' => 0, 'prefix' => 0, 'empty' => 0];
        try {
            if (! Schema::hasTable('video_downloaders')) {
                return $zero;
            }
            $q = VideoDownloader::query();
            $tpl = (clone $q)->where(function ($inner) {
                $inner->where('parse', 'like', '%{url}%')->orWhere('parse', 'like', '%{id}%');
            });
            $empty = (clone $q)->where(function ($inner) {
                $inner->whereNull('parse')->orWhere('parse', '');
            });

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'tpl' => (int) $tpl->count(),
                'empty' => (int) $empty->count(),
                'prefix' => (int) (clone $q)->where('parse', '!=', '')->whereNotNull('parse')
                    ->where('parse', 'not like', '%{url}%')
                    ->where('parse', 'not like', '%{id}%')
                    ->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return array<string, int> */
    public function serverQueues(): array
    {
        $zero = ['all' => 0, 'on' => 0, 'off' => 0, 'empty' => 0];
        try {
            if (! Schema::hasTable('video_servers')) {
                return $zero;
            }
            $q = VideoServer::query();

            return [
                'all' => (int) (clone $q)->count(),
                'on' => (int) (clone $q)->where('status', 1)->count(),
                'off' => (int) (clone $q)->where('status', 0)->count(),
                'empty' => (int) (clone $q)->where(function ($inner) {
                    $inner->whereNull('url')->orWhere('url', '');
                })->count(),
            ];
        } catch (\Throwable) {
            return $zero;
        }
    }

    /** @return list<array<string, mixed>> */
    public function enabledServers(): array
    {
        try {
            if (! Schema::hasTable('video_servers')) {
                return [];
            }

            return VideoServer::query()
                ->where('status', 1)
                ->orderByDesc('sort')
                ->orderBy('id')
                ->get(['id', 'name', 'url'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    /** @param  array<string, mixed>  $data */
    public function tryDownloader(array $data): array
    {
        $url = trim((string) ($data['url'] ?? $data['sample'] ?? ''));
        if ($url === '') {
            return Result::fail('请填一条下载地址试试');
        }
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return Result::fail('这个地址不能用');
        }
        $code = strtolower(trim((string) ($data['code'] ?? '')));
        $videoId = max(0, (int) ($data['video_id'] ?? $data['id'] ?? 0));
        $source = new VideoSourceModel;
        $source->downer = $code;
        $episode = new VideoEpisodeModel;
        $episode->url = $url;
        $out = app(\App\Services\Video\SiteFrontService::class)->resolveDownUrl($source, $episode, $videoId);
        if ($out === $url) {
            return Result::success(['from' => $url, 'to' => $out, 'changed' => false], '没有匹配的启用模板，地址原样');
        }

        return Result::success(['from' => $url, 'to' => $out, 'changed' => true], '会变成 '.$out);
    }

    /** @param  array<string, mixed>  $data */
    public function tryServer(array $data): array
    {
        $url = trim((string) ($data['url'] ?? $data['sample'] ?? ''));
        if ($url === '') {
            return Result::fail('请填一条相对路径试试');
        }
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return Result::fail('这个地址不能用');
        }
        $id = (int) ($data['server_id'] ?? $data['id'] ?? 0);
        $source = new VideoSourceModel;
        $source->server_id = $id;
        $episode = new VideoEpisodeModel;
        $episode->url = $url;
        $out = app(\App\Services\Video\SiteFrontService::class)->resolvePlayUrl($source, $episode);
        if (preg_match('#^(https?:)?//#i', $url)) {
            return Result::success(['from' => $url, 'to' => $out, 'changed' => false], '这是完整地址，不会拼前缀');
        }
        if ($id < 1) {
            return Result::success(['from' => $url, 'to' => $out, 'changed' => false], '没选组，地址原样');
        }
        if ($out === $url) {
            return Result::success(['from' => $url, 'to' => $out, 'changed' => false], '这组停用或前缀空着，地址原样');
        }

        return Result::success(['from' => $url, 'to' => $out, 'changed' => true], '会变成 '.$out);
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

    private function normalizeServerPrefix(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        if (preg_match('#^(javascript|data|vbscript):#i', $url)) {
            return '';
        }
        if (preg_match('#^(https?:)?/+$#i', $url)) {
            return '';
        }
        $url = rtrim($url, '/');
        if ($url === '' || preg_match('#^https?:$#i', $url)) {
            return '';
        }
        if (str_starts_with($url, '/') || str_starts_with($url, '.')) {
            return $url;
        }
        if (preg_match('#^(https?:)?//#i', $url)) {
            return $url;
        }
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $url)) {
            return preg_match('#^https?://#i', $url) ? $url : '';
        }

        return 'https://'.$url;
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
            $art = $decorated[0] ?? null;
            if (! is_array($art)) {
                return null;
            }
            $svc = app(ArtTagService::class);
            $ids = $svc->idsForArt($id);
            $comma = $svc->collectNames(['tag' => (string) ($art['tag'] ?? '')]);
            if ($ids === [] && $comma !== []) {
                foreach ($svc->options() as $opt) {
                    if (in_array((string) ($opt['name'] ?? ''), $comma, true)) {
                        $ids[] = (int) $opt['id'];
                    }
                }
            }
            $picked = [];
            foreach ($svc->options() as $opt) {
                if (in_array((int) $opt['id'], $ids, true)) {
                    $picked[] = (string) $opt['name'];
                }
            }
            $extra = [];
            foreach ($comma as $name) {
                if (! in_array($name, $picked, true)) {
                    $extra[] = $name;
                }
            }
            $art['tag_ids'] = array_values(array_unique($ids));
            $art['tag_extra'] = implode(',', $extra);

            return $art;
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

    /**
     * @param  array<string, mixed>  $payload
     * @return array{code?:int,msg?:string,data?:mixed}|null
     */
    private function applyMangaSave(string $module, array &$payload, ?int $id): ?array
    {
        if ($module === 'mangas') {
            $title = array_key_exists('title', $payload) || $id === null
                ? trim((string) ($payload['title'] ?? ''))
                : null;
            if ($id === null && ($title === null || $title === '')) {
                return Result::fail('请填写名称');
            }
            if ($title !== null) {
                if ($title === '') {
                    return Result::fail('请填写名称');
                }
                $payload['title'] = mb_substr($title, 0, 200);
            }
            if (array_key_exists('serialize', $payload)) {
                $payload['serialize'] = (int) $payload['serialize'] === 1 ? 1 : 0;
            }
            if (array_key_exists('yid', $payload)) {
                $payload['yid'] = (int) $payload['yid'] === 1 ? 1 : 0;
            }
            if (array_key_exists('recommend', $payload)) {
                $payload['recommend'] = (int) $payload['recommend'] === 1 ? 1 : 0;
            }
            if (array_key_exists('type_id', $payload)) {
                $payload['type_id'] = max(0, (int) $payload['type_id']);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if (array_key_exists('tags', $payload)) {
                $payload['tags'] = mb_substr(trim((string) ($payload['tags'] ?? '')), 0, 255);
            }
            if (array_key_exists('cover', $payload)) {
                $payload['cover'] = mb_substr(trim((string) ($payload['cover'] ?? '')), 0, 500);
            }
            if (array_key_exists('author', $payload)) {
                $payload['author'] = mb_substr(trim((string) ($payload['author'] ?? '')), 0, 80);
            }
            if (array_key_exists('remarks', $payload)) {
                $payload['remarks'] = mb_substr(trim((string) ($payload['remarks'] ?? '')), 0, 80);
            }
            if (array_key_exists('content', $payload)) {
                $payload['content'] = (string) ($payload['content'] ?? '');
            }
            if (array_key_exists('hits', $payload)) {
                $payload['hits'] = max(0, (int) $payload['hits']);
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            // SQLite NOT NULL string cols reject null; empty form fields must be ''.
            if ($id === null) {
                foreach (['cover', 'author', 'remarks', 'tags', 'content'] as $col) {
                    if (! array_key_exists($col, $payload) || $payload[$col] === null) {
                        $payload[$col] = '';
                    }
                }
                if (! array_key_exists('hits', $payload)) {
                    $payload['hits'] = 0;
                }
                if (! array_key_exists('sort', $payload)) {
                    $payload['sort'] = 0;
                }
                if (! array_key_exists('status', $payload)) {
                    $payload['status'] = 1;
                }
            }

            return null;
        }
        if ($module === 'manga_chapters') {
            $mangaId = array_key_exists('manga_id', $payload) || $id === null
                ? (int) ($payload['manga_id'] ?? 0)
                : null;
            if ($mangaId !== null) {
                if ($mangaId < 1 || ! Schema::hasTable('plugin_mangas') || ! \Plugins\Manga\Models\Manga::query()->where('id', $mangaId)->exists()) {
                    return Result::fail('漫画作品不存在');
                }
                $payload['manga_id'] = $mangaId;
            }
            if (array_key_exists('name', $payload) || $id === null) {
                $name = trim((string) ($payload['name'] ?? ''));
                if ($id === null && $name === '') {
                    return Result::fail('请填写章节名');
                }
                if ($name !== '') {
                    $payload['name'] = mb_substr($name, 0, 120);
                }
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('vip', $payload) && Schema::hasColumn('plugin_manga_chapters', 'vip')) {
                $payload['vip'] = (int) $payload['vip'] === 1 ? 1 : 0;
            }

            return null;
        }
        if ($module === 'manga_pics') {
            $url = array_key_exists('url', $payload) || $id === null
                ? (string) ($payload['url'] ?? '')
                : null;
            if ($url !== null) {
                $safe = \Plugins\Manga\Models\MangaChapter::safeUrl($url);
                if ($safe === '') {
                    return Result::fail('图片地址必须是 http(s) 或站内路径，不能是 javascript:');
                }
                $payload['url'] = mb_substr($safe, 0, 500);
            }
            if (array_key_exists('manga_id', $payload)) {
                $payload['manga_id'] = max(0, (int) $payload['manga_id']);
            }
            if (array_key_exists('chapter_id', $payload) || $id === null) {
                $chapterId = (int) ($payload['chapter_id'] ?? 0);
                if ($chapterId < 1 || ! Schema::hasTable('plugin_manga_chapters') || ! \Plugins\Manga\Models\MangaChapter::query()->where('id', $chapterId)->exists()) {
                    return Result::fail('章节不存在');
                }
                $payload['chapter_id'] = $chapterId;
                if (! array_key_exists('manga_id', $payload) || (int) $payload['manga_id'] < 1) {
                    $chapter = \Plugins\Manga\Models\MangaChapter::query()->find($chapterId);
                    if ($chapter) {
                        $payload['manga_id'] = (int) $chapter->manga_id;
                    }
                }
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }

            return null;
        }
        if ($module === 'manga_types') {
            $name = array_key_exists('name', $payload) || $id === null
                ? trim((string) ($payload['name'] ?? ''))
                : null;
            if ($id === null && ($name === null || $name === '')) {
                return Result::fail('请填写名称');
            }
            if ($name !== null) {
                if ($name === '') {
                    return Result::fail('请填写名称');
                }
                $payload['name'] = mb_substr($name, 0, 80);
            }
            if (array_key_exists('slug', $payload)) {
                $slug = strtolower(trim((string) $payload['slug']));
                if ($slug !== '' && ! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
                    return Result::fail('别名只填英文、数字和短横线');
                }
                if ($slug !== '' && Schema::hasTable('plugin_manga_types') && Schema::hasColumn('plugin_manga_types', 'slug')) {
                    $dup = \Plugins\Manga\Models\MangaType::query()->where('slug', $slug);
                    if ($id !== null) {
                        $dup->where('id', '!=', $id);
                    }
                    if ($dup->exists()) {
                        return Result::fail('这个别名已经被用了');
                    }
                }
                $payload['slug'] = mb_substr($slug, 0, 80);
            }
            if (array_key_exists('pic', $payload)) {
                $pic = trim((string) $payload['pic']);
                if ($pic !== '' && ! preg_match('#^(https?:)?/#i', $pic) && ! str_starts_with($pic, 'data:')) {
                    return Result::fail('封面请填 http(s) 地址或站内路径');
                }
                if (stripos($pic, 'javascript:') === 0) {
                    return Result::fail('封面地址不合法');
                }
                $payload['pic'] = mb_substr($pic, 0, 255);
            }
            if (array_key_exists('parent_id', $payload)) {
                $parentId = max(0, (int) $payload['parent_id']);
                if ($id !== null && $parentId === (int) $id) {
                    return Result::fail('上级不能是自己');
                }
                if ($parentId > 0 && Schema::hasTable('plugin_manga_types')) {
                    if (! \Plugins\Manga\Models\MangaType::query()->where('id', $parentId)->exists()) {
                        return Result::fail('上级分类不存在');
                    }
                    if ($id !== null && $this->mangaTypeIsDescendant((int) $id, $parentId)) {
                        return Result::fail('不能挂到自己的下级下面');
                    }
                }
                $payload['parent_id'] = $parentId;
            }
            if (array_key_exists('sort', $payload)) {
                $payload['sort'] = max(0, (int) $payload['sort']);
            }
            if (array_key_exists('page_size', $payload)) {
                $payload['page_size'] = max(0, min(100, (int) $payload['page_size']));
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            foreach (['seo_title' => 120, 'seo_keywords' => 255, 'seo_description' => 500] as $col => $max) {
                if (array_key_exists($col, $payload)) {
                    $payload[$col] = mb_substr(trim((string) $payload[$col]), 0, $max);
                }
            }

            return null;
        }
        if ($module === 'manga_comments') {
            $mangaId = array_key_exists('manga_id', $payload) || $id === null
                ? (int) ($payload['manga_id'] ?? 0)
                : null;
            if ($mangaId !== null) {
                if ($mangaId < 1 || ! Schema::hasTable('plugin_mangas') || ! \Plugins\Manga\Models\Manga::query()->where('id', $mangaId)->exists()) {
                    return Result::fail('漫画作品不存在');
                }
                $payload['manga_id'] = $mangaId;
            }
            if (array_key_exists('content', $payload) || $id === null) {
                $content = trim((string) ($payload['content'] ?? ''));
                if ($content === '') {
                    return Result::fail('请填写评论');
                }
                $payload['content'] = mb_substr($content, 0, 2000);
            }
            if (array_key_exists('author_name', $payload)) {
                $payload['author_name'] = mb_substr(trim((string) $payload['author_name']), 0, 80);
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            if (array_key_exists('member_id', $payload)) {
                $payload['member_id'] = max(0, (int) $payload['member_id']);
            }

            return null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{code?:int,msg?:string,data?:mixed}|null
     */
    private function applyActivitySave(string $module, array &$payload, ?int $id): ?array
    {
        if ($module === 'activity') {
            $name = array_key_exists('name', $payload) || $id === null
                ? trim((string) ($payload['name'] ?? ''))
                : null;
            if ($id === null && ($name === null || $name === '')) {
                return Result::fail('请填写名称');
            }
            if ($name !== null) {
                if ($name === '') {
                    return Result::fail('请填写名称');
                }
                $payload['name'] = mb_substr($name, 0, 40);
            }
            $action = array_key_exists('action', $payload) || $id === null
                ? trim((string) ($payload['action'] ?? ''))
                : null;
            if ($id === null && ($action === null || $action === '')) {
                return Result::fail('请填写动作标识');
            }
            if ($action !== null) {
                if ($action === '') {
                    return Result::fail('请填写动作标识');
                }
                if (! preg_match('/^[a-z][a-z0-9_]{0,39}$/', $action)) {
                    return Result::fail('动作标识用英文字母开头，如 daily_sign');
                }
                $dup = MemberTask::query()->where('action', $action);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('这个动作标识已经有了');
                }
                $payload['action'] = $action;
            }
            if (array_key_exists('type', $payload) || $id === null) {
                $type = (int) ($payload['type'] ?? 1);
                $payload['type'] = $type === 2 ? 2 : 1;
            }
            if (array_key_exists('hint', $payload)) {
                $payload['hint'] = mb_substr(trim((string) $payload['hint']), 0, 255);
            }
            foreach (['points', 'sort'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
            if (array_key_exists('target', $payload) || $id === null) {
                $payload['target'] = max(1, (int) ($payload['target'] ?? 1));
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }

            return null;
        }
        if ($module === 'sign_milestones') {
            $name = array_key_exists('name', $payload) || $id === null
                ? trim((string) ($payload['name'] ?? ''))
                : null;
            if ($id === null && ($name === null || $name === '')) {
                return Result::fail('请填写名称');
            }
            if ($name !== null) {
                if ($name === '') {
                    return Result::fail('请填写名称');
                }
                $payload['name'] = mb_substr($name, 0, 40);
            }
            if (array_key_exists('days', $payload) || $id === null) {
                $days = max(1, (int) ($payload['days'] ?? 0));
                $dup = MemberSignMilestone::query()->where('days', $days);
                if ($id) {
                    $dup->where('id', '!=', $id);
                }
                if ($dup->exists()) {
                    return Result::fail('已经有这个连续天数');
                }
                $payload['days'] = $days;
            }
            foreach (['points', 'sort'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }

            return null;
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $data
     * @return array{code?:int,msg?:string,data?:mixed}|null
     */
    private function applyMallSave(string $module, array &$payload, array $data, ?int $id): ?array
    {
        if ($module === 'mall_goods') {
            $name = array_key_exists('name', $payload) || $id === null
                ? trim((string) ($payload['name'] ?? ''))
                : null;
            if ($id === null && ($name === null || $name === '')) {
                return Result::fail('请填写名称');
            }
            if ($name !== null) {
                if ($name === '') {
                    return Result::fail('请填写名称');
                }
                $payload['name'] = mb_substr($name, 0, 120);
            }
            $type = array_key_exists('type', $payload) || $id === null
                ? \Plugins\Mall\Services\MallService::normalizeType((string) ($payload['type'] ?? 'goods'))
                : null;
            if ($type !== null) {
                if (! in_array($type, ['goods', 'vip', 'card'], true)) {
                    return Result::fail('没有这种商品');
                }
                $payload['type'] = $type;
            }
            foreach (['points', 'stock', 'sort'] as $intField) {
                if (array_key_exists($intField, $payload)) {
                    $payload[$intField] = max(0, (int) $payload[$intField]);
                }
            }
            if (array_key_exists('status', $payload)) {
                $payload['status'] = (int) $payload['status'] === 1 ? 1 : 0;
            }
            $touchExt = array_key_exists('group_id', $data)
                || array_key_exists('card_points', $data)
                || array_key_exists('card_mode', $data)
                || array_key_exists('mode', $data)
                || array_key_exists('vip_days', $data)
                || array_key_exists('days', $data)
                || array_key_exists('auto_credit', $data)
                || array_key_exists('type', $payload);
            if ($touchExt && $this->hasColumn(new \Plugins\Mall\Models\MallGood, 'ext')) {
                $ext = [];
                if ($id) {
                    $old = \Plugins\Mall\Models\MallGood::query()->find($id);
                    if ($old) {
                        $ext = \Plugins\Mall\Services\MallService::decodeExt($old->ext ?? '');
                    }
                }
                if (array_key_exists('group_id', $data)) {
                    $ext['group_id'] = max(0, (int) $data['group_id']);
                }
                if (array_key_exists('vip_days', $data) || array_key_exists('days', $data)) {
                    $ext['days'] = max(0, (int) ($data['vip_days'] ?? $data['days'] ?? 0));
                    $ext['vip_days'] = $ext['days'];
                }
                if (array_key_exists('card_points', $data)) {
                    $ext['card_points'] = max(0, (int) $data['card_points']);
                }
                if (array_key_exists('auto_credit', $data)) {
                    $ext['auto_credit'] = (int) $data['auto_credit'] === 1 ? 1 : 0;
                }
                $mode = strtolower(trim((string) ($data['card_mode'] ?? $data['mode'] ?? ($ext['card_mode'] ?? $ext['mode'] ?? 'generate'))));
                if (! in_array($mode, ['generate', 'assign'], true)) {
                    $mode = 'generate';
                }
                $resolvedType = $type ?? \Plugins\Mall\Services\MallService::normalizeType((string) ($payload['type'] ?? ''));
                if ($resolvedType === 'card' || array_key_exists('card_mode', $data) || array_key_exists('mode', $data)) {
                    $ext['card_mode'] = $mode;
                    $ext['mode'] = $mode;
                }
                if ($resolvedType === 'goods') {
                    $payload['ext'] = '';
                } else {
                    $payload['ext'] = json_encode($ext, JSON_UNESCAPED_UNICODE) ?: '';
                }
            }
            if (array_key_exists('is_hot', $payload) && $this->hasColumn(new \Plugins\Mall\Models\MallGood, 'is_hot')) {
                $payload['is_hot'] = (int) $payload['is_hot'] === 1 ? 1 : 0;
            }

            return null;
        }
        if ($module === 'mall_orders') {
            if (array_key_exists('status', $payload)) {
                $status = (int) $payload['status'];
                if (! in_array($status, [1, 2], true)) {
                    return Result::fail('订单只能标待发货或已完成');
                }
                $payload['status'] = $status;
                if ($status === 2 && $this->hasColumn(new \Plugins\Mall\Models\MallOrder, 'complete_at')) {
                    $payload['complete_at'] = time();
                }
            }
            if (array_key_exists('remark', $payload)) {
                $payload['remark'] = mb_substr(trim((string) $payload['remark']), 0, 250);
            }

            return null;
        }

        return null;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMallGoods(array $rows): array
    {
        foreach ($rows as &$row) {
            $type = \Plugins\Mall\Services\MallService::normalizeType((string) ($row['type'] ?? 'goods'));
            $row['type'] = $type;
            $row['type_label'] = \Plugins\Mall\Services\MallService::typeLabel($type);
            $row['status_label'] = (int) ($row['status'] ?? 0) === 1 ? '上架' : '下架';
            $ext = \Plugins\Mall\Services\MallService::decodeExt($row['ext'] ?? '');
            $row['group_id'] = (int) ($ext['group_id'] ?? 0);
            $row['vip_days'] = (int) ($ext['days'] ?? $ext['vip_days'] ?? 0);
            $row['card_points'] = (int) ($ext['card_points'] ?? 0);
            $row['card_mode'] = (string) ($ext['card_mode'] ?? $ext['mode'] ?? 'generate');
            $row['auto_credit'] = (int) ($ext['auto_credit'] ?? 0);
            $row['is_hot'] = (int) ($row['is_hot'] ?? 0);
            $row['sales'] = (int) ($row['sales'] ?? 0);
            $row['pool_remain'] = 0;
            if ($type === 'card' && $row['card_mode'] === 'assign') {
                $row['pool_remain'] = app(\Plugins\Mall\Services\MallService::class)->poolRemain($ext);
            }
            $row['vip_days_label'] = \Plugins\Mall\Services\MallService::vipDaysLabel((int) $row['vip_days']);
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMallOrders(array $rows): array
    {
        foreach ($rows as &$row) {
            $type = \Plugins\Mall\Services\MallService::normalizeType((string) ($row['goods_type'] ?? 'goods'));
            $row['goods_type'] = $type;
            $row['type_label'] = \Plugins\Mall\Services\MallService::typeLabel($type);
            $status = (int) ($row['status'] ?? 0);
            $row['status_label'] = match ($status) {
                2 => '已完成',
                1 => '待发货',
                default => '已关闭',
            };
            $delivery = \Plugins\Mall\Services\MallService::decodeExt($row['delivery'] ?? '');
            $code = strtoupper(trim((string) ($delivery['code'] ?? '')));
            $parts = [];
            if ($code !== '') {
                $parts[] = $code;
                if ((int) ($delivery['auto_credit'] ?? 0) === 1) {
                    $parts[] = '已到账';
                }
            }
            if ($type === 'vip') {
                $days = (int) ($delivery['days'] ?? 0);
                $parts[] = \Plugins\Mall\Services\MallService::vipDaysLabel($days);
                $exp = (int) ($delivery['expire_at'] ?? 0);
                if ($exp > 0) {
                    $parts[] = '至 '.date('Y-m-d', $exp);
                }
            }
            $contact = trim((string) ($row['contact'] ?? ''));
            if ($contact !== '') {
                $parts[] = $contact;
            }
            $row['delivery_label'] = implode(' · ', $parts);
            $row['contact'] = $contact;
            $row['address'] = (string) ($row['address'] ?? '');
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMangas(array $rows): array
    {
        $ids = [];
        $typeIds = [];
        foreach ($rows as $row) {
            $ids[] = (int) ($row['id'] ?? 0);
            $typeIds[] = (int) ($row['type_id'] ?? 0);
        }
        $ids = array_values(array_filter(array_unique($ids)));
        $typeIds = array_values(array_filter(array_unique($typeIds)));
        $typeNames = [];
        if ($typeIds !== [] && Schema::hasTable('plugin_manga_types')) {
            $typeNames = \Plugins\Manga\Models\MangaType::query()->whereIn('id', $typeIds)->pluck('name', 'id')->all();
        }
        $chapterCounts = [];
        if ($ids !== [] && Schema::hasTable('plugin_manga_chapters')) {
            $chapterCounts = \Plugins\Manga\Models\MangaChapter::query()
                ->selectRaw('manga_id, COUNT(*) as c')
                ->whereIn('manga_id', $ids)
                ->groupBy('manga_id')
                ->pluck('c', 'manga_id')
                ->all();
        }
        $tagMap = [];
        if ($ids !== [] && Schema::hasTable('plugin_manga_tag_rel') && Schema::hasTable('plugin_manga_tags')) {
            $rels = DB::table('plugin_manga_tag_rel')
                ->whereIn('manga_id', $ids)
                ->orderBy('tag_id')
                ->get(['manga_id', 'tag_id']);
            foreach ($rels as $rel) {
                $mangaId = (int) $rel->manga_id;
                $tagMap[$mangaId][] = (int) $rel->tag_id;
            }
        }
        $authorMap = [];
        $authorNames = [];
        if ($ids !== [] && Schema::hasTable('plugin_manga_author_rel') && Schema::hasTable('plugin_manga_authors')) {
            $rels = DB::table('plugin_manga_author_rel')
                ->whereIn('manga_id', $ids)
                ->orderBy('author_id')
                ->get(['manga_id', 'author_id']);
            $authorIds = [];
            foreach ($rels as $rel) {
                $mangaId = (int) $rel->manga_id;
                $aid = (int) $rel->author_id;
                $authorMap[$mangaId][] = $aid;
                if ($aid > 0) {
                    $authorIds[] = $aid;
                }
            }
            $authorIds = array_values(array_unique($authorIds));
            if ($authorIds !== []) {
                $authorNames = \Plugins\Manga\Models\MangaAuthor::query()
                    ->whereIn('id', $authorIds)
                    ->pluck('name', 'id')
                    ->all();
            }
        }
        $favorCounts = [];
        if ($ids !== [] && Schema::hasTable('plugin_manga_favors')) {
            $favorCounts = \Plugins\Manga\Models\MangaFavor::query()
                ->selectRaw('manga_id, COUNT(*) as c')
                ->whereIn('manga_id', $ids)
                ->groupBy('manga_id')
                ->pluck('c', 'manga_id')
                ->all();
        }
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $typeId = (int) ($row['type_id'] ?? 0);
            $row['type_name'] = (string) ($typeNames[$typeId] ?? '');
            $row['chapter_count'] = (int) ($chapterCounts[$id] ?? 0);
            $row['favor_count'] = (int) ($favorCounts[$id] ?? 0);
            $row['tag_ids'] = array_values($tagMap[$id] ?? []);
            $aids = array_values($authorMap[$id] ?? []);
            $row['author_ids'] = $aids;
            $labels = [];
            foreach ($aids as $aid) {
                $n = trim((string) ($authorNames[$aid] ?? ''));
                if ($n !== '') {
                    $labels[] = $n;
                }
            }
            $fallback = trim((string) ($row['author'] ?? ''));
            $row['author_label'] = $labels !== [] ? implode('、', $labels) : $fallback;
            $row['serialize'] = (int) ($row['serialize'] ?? 0);
            $row['serialize_label'] = $row['serialize'] === 1 ? '完结' : '连载';
            $row['yid'] = (int) ($row['yid'] ?? 0);
            $row['yid_label'] = $row['yid'] === 1 ? '待审' : '已审';
            $row['status'] = (int) ($row['status'] ?? 0);
            $row['recommend'] = (int) ($row['recommend'] ?? 0);
            $row['front_url'] = '/manga/'.$id;
            $row['collect_id'] = (string) ($row['collect_id'] ?? '');
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMangaChapters(array $rows): array
    {
        $ids = [];
        $mangaIds = [];
        foreach ($rows as $row) {
            $ids[] = (int) ($row['id'] ?? 0);
            $mangaIds[] = (int) ($row['manga_id'] ?? 0);
        }
        $ids = array_values(array_filter(array_unique($ids)));
        $mangaIds = array_values(array_filter(array_unique($mangaIds)));
        $titles = [];
        if ($mangaIds !== [] && Schema::hasTable('plugin_mangas')) {
            $titles = \Plugins\Manga\Models\Manga::query()->whereIn('id', $mangaIds)->pluck('title', 'id')->all();
        }
        $picCounts = [];
        if ($ids !== [] && Schema::hasTable('plugin_manga_pics')) {
            $picCounts = \Plugins\Manga\Models\MangaPic::query()
                ->selectRaw('chapter_id, COUNT(*) as c')
                ->whereIn('chapter_id', $ids)
                ->groupBy('chapter_id')
                ->pluck('c', 'chapter_id')
                ->all();
        }
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $mangaId = (int) ($row['manga_id'] ?? 0);
            $row['manga_title'] = (string) ($titles[$mangaId] ?? '');
            $row['vip'] = (int) ($row['vip'] ?? 0);
            $row['vip_label'] = $row['vip'] === 1 ? 'VIP' : '免费';
            if (isset($picCounts[$id])) {
                $row['pic_count'] = (int) $picCounts[$id];
            } else {
                $n = 0;
                foreach (preg_split('/\r\n|\r|\n/', (string) ($row['pics'] ?? '')) ?: [] as $line) {
                    if (trim((string) $line) !== '') {
                        $n++;
                    }
                }
                $row['pic_count'] = $n;
            }
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMangaPics(array $rows): array
    {
        $mangaIds = [];
        $chapterIds = [];
        foreach ($rows as $row) {
            $mangaIds[] = (int) ($row['manga_id'] ?? 0);
            $chapterIds[] = (int) ($row['chapter_id'] ?? 0);
        }
        $mangaIds = array_values(array_filter(array_unique($mangaIds)));
        $chapterIds = array_values(array_filter(array_unique($chapterIds)));
        $titles = [];
        $names = [];
        if ($mangaIds !== [] && Schema::hasTable('plugin_mangas')) {
            $titles = \Plugins\Manga\Models\Manga::query()->whereIn('id', $mangaIds)->pluck('title', 'id')->all();
        }
        if ($chapterIds !== [] && Schema::hasTable('plugin_manga_chapters')) {
            $names = \Plugins\Manga\Models\MangaChapter::query()->whereIn('id', $chapterIds)->pluck('name', 'id')->all();
        }
        foreach ($rows as &$row) {
            $row['manga_title'] = (string) ($titles[(int) ($row['manga_id'] ?? 0)] ?? '');
            $row['chapter_name'] = (string) ($names[(int) ($row['chapter_id'] ?? 0)] ?? '');
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMangaTypes(array $rows): array
    {
        $parentIds = [];
        foreach ($rows as $row) {
            $parentIds[] = (int) ($row['parent_id'] ?? 0);
        }
        $parentIds = array_values(array_filter(array_unique($parentIds)));
        $names = [];
        if ($parentIds !== [] && Schema::hasTable('plugin_manga_types')) {
            $names = \Plugins\Manga\Models\MangaType::query()->whereIn('id', $parentIds)->pluck('name', 'id')->all();
        }
        foreach ($rows as &$row) {
            $parentId = (int) ($row['parent_id'] ?? 0);
            $row['parent_name'] = $parentId > 0 ? (string) ($names[$parentId] ?? ('#'.$parentId)) : '顶级';
            $row['depth'] = (int) ($row['depth'] ?? 0);
            $row['child_count'] = (int) ($row['child_count'] ?? 0);
            $row['manga_count'] = (int) ($row['manga_count'] ?? 0);
        }
        unset($row);

        return $rows;
    }

    /** @param array<string, mixed> $params */
    private function listMangaTypes(array $params): array
    {
        if (! Schema::hasTable('plugin_manga_types')) {
            return Result::fail('请先执行数据库迁移');
        }

        $all = \Plugins\Manga\Models\MangaType::query()
            ->orderByDesc('sort')
            ->orderBy('id')
            ->get()
            ->map(static fn ($row) => $row->toArray())
            ->all();

        $mangaCounts = [];
        try {
            if (Schema::hasTable('plugin_mangas') && Schema::hasColumn('plugin_mangas', 'type_id')) {
                $countRows = \Plugins\Manga\Models\Manga::query()
                    ->selectRaw('type_id, COUNT(*) as c')
                    ->groupBy('type_id')
                    ->get();
                foreach ($countRows as $row) {
                    $mangaCounts[(int) $row->type_id] = (int) $row->c;
                }
            }
        } catch (\Throwable) {
        }

        $byParent = [];
        $byId = [];
        foreach ($all as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id < 1) {
                continue;
            }
            $byId[$id] = $row;
            $byParent[(int) ($row['parent_id'] ?? 0)][] = $row;
        }

        $flat = [];
        $walk = function (int $parentId, int $depth) use (&$walk, &$flat, $byParent): void {
            foreach ($byParent[$parentId] ?? [] as $row) {
                $row['depth'] = $depth;
                $flat[] = $row;
                $walk((int) ($row['id'] ?? 0), $depth + 1);
            }
        };
        $walk(0, 0);
        $seen = [];
        foreach ($flat as $row) {
            $seen[(int) ($row['id'] ?? 0)] = true;
        }
        foreach ($all as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0 && ! isset($seen[$id])) {
                $row['depth'] = 0;
                $flat[] = $row;
                $seen[$id] = true;
            }
        }

        $name = trim((string) ($params['name'] ?? $params['q'] ?? ''));
        if ($name !== '') {
            $keep = [];
            foreach ($flat as $row) {
                if (mb_stripos((string) ($row['name'] ?? ''), $name) === false) {
                    continue;
                }
                $id = (int) ($row['id'] ?? 0);
                $keep[$id] = true;
                $pid = (int) ($row['parent_id'] ?? 0);
                $guard = 0;
                while ($pid > 0 && $guard++ < 8 && isset($byId[$pid])) {
                    $keep[$pid] = true;
                    $pid = (int) ($byId[$pid]['parent_id'] ?? 0);
                }
            }
            $flat = array_values(array_filter(
                $flat,
                static fn (array $row): bool => isset($keep[(int) ($row['id'] ?? 0)])
            ));
        }

        foreach ($flat as &$item) {
            $id = (int) ($item['id'] ?? 0);
            $pid = (int) ($item['parent_id'] ?? 0);
            $item['parent_name'] = $pid > 0 ? (string) ($byId[$pid]['name'] ?? ('#'.$pid)) : '顶级';
            $item['manga_count'] = $mangaCounts[$id] ?? 0;
            $item['child_count'] = count($byParent[$id] ?? []);
            $item['depth'] = (int) ($item['depth'] ?? 0);
            $item['status'] = (int) ($item['status'] ?? 0);
            $item['slug'] = (string) ($item['slug'] ?? '');
            $item['pic'] = (string) ($item['pic'] ?? '');
            $item['page_size'] = (int) ($item['page_size'] ?? 0);
        }
        unset($item);

        return Result::success([
            'total' => count($flat),
            'data' => $flat,
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => max(count($flat), 1),
        ]);
    }

    private function mangaTypeIsDescendant(int $rootId, int $candidateId): bool
    {
        if ($rootId < 1 || $candidateId < 1 || ! Schema::hasTable('plugin_manga_types')) {
            return false;
        }
        $children = \Plugins\Manga\Models\MangaType::query()
            ->where('parent_id', $rootId)
            ->pluck('id')
            ->all();
        $queue = array_map('intval', $children);
        $guard = 0;
        while ($queue !== [] && $guard++ < 200) {
            $cur = array_shift($queue);
            if ($cur === $candidateId) {
                return true;
            }
            $more = \Plugins\Manga\Models\MangaType::query()
                ->where('parent_id', $cur)
                ->pluck('id')
                ->all();
            foreach ($more as $cid) {
                $queue[] = (int) $cid;
            }
        }

        return false;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMangaComments(array $rows): array
    {
        $mangaIds = [];
        foreach ($rows as $row) {
            $mangaIds[] = (int) ($row['manga_id'] ?? 0);
        }
        $mangaIds = array_values(array_filter(array_unique($mangaIds)));
        $titles = [];
        if ($mangaIds !== [] && Schema::hasTable('plugin_mangas')) {
            $titles = \Plugins\Manga\Models\Manga::query()->whereIn('id', $mangaIds)->pluck('title', 'id')->all();
        }
        foreach ($rows as &$row) {
            $mangaId = (int) ($row['manga_id'] ?? 0);
            $row['manga_title'] = (string) ($titles[$mangaId] ?? '');
            $row['status'] = (int) ($row['status'] ?? 0);
            $row['status_label'] = $row['status'] === 1 ? '显示' : '待审';
            $ts = (int) ($row['created_at'] ?? 0);
            $row['created_label'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
            $row['front_url'] = $mangaId > 0 ? '/manga/'.$mangaId : '';
        }
        unset($row);

        return $rows;
    }

    /** @param list<array<string, mixed>> $rows @return list<array<string, mixed>> */
    private function decorateMangaFavors(array $rows): array
    {
        $mangaIds = [];
        $memberIds = [];
        foreach ($rows as $row) {
            $mid = (int) ($row['manga_id'] ?? 0);
            if ($mid > 0) {
                $mangaIds[] = $mid;
            }
            $uid = (int) ($row['member_id'] ?? 0);
            if ($uid > 0) {
                $memberIds[] = $uid;
            }
        }
        $titles = [];
        if ($mangaIds !== [] && Schema::hasTable('plugin_mangas')) {
            try {
                $titles = \Plugins\Manga\Models\Manga::query()
                    ->whereIn('id', array_values(array_unique($mangaIds)))
                    ->pluck('title', 'id')
                    ->all();
            } catch (\Throwable) {
                $titles = [];
            }
        }
        $members = [];
        if ($memberIds !== [] && Schema::hasTable('members')) {
            try {
                $members = Member::query()
                    ->whereIn('id', array_values(array_unique($memberIds)))
                    ->get(['id', 'name', 'email'])
                    ->keyBy('id')
                    ->all();
            } catch (\Throwable) {
                $members = [];
            }
        }
        foreach ($rows as &$row) {
            $mangaId = (int) ($row['manga_id'] ?? 0);
            $memberId = (int) ($row['member_id'] ?? 0);
            $ts = (int) ($row['created_at'] ?? 0);
            $member = $members[$memberId] ?? null;
            $row['manga_title'] = (string) ($titles[$mangaId] ?? '');
            $row['manga_missing'] = $mangaId > 0 && ! array_key_exists($mangaId, $titles) ? 1 : 0;
            $row['member_name'] = $member ? (string) $member->name : '';
            $row['member_email'] = $member ? (string) $member->email : '';
            $row['member_missing'] = $memberId > 0 && ! $member ? 1 : 0;
            $row['created_at_text'] = $ts > 0 ? date('Y-m-d H:i', $ts) : '';
        }
        unset($row);

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $cfg
     * @param  list<mixed>  $args
     * @return array<string, mixed>|null
     */
    private function dispatchPlugin(array $cfg, string $method, array $args): ?array
    {
        $handler = trim((string) ($cfg['handler'] ?? ''));
        if ($handler === '' || ! class_exists($handler)) {
            return null;
        }
        $svc = app($handler);
        if (! method_exists($svc, $method)) {
            return null;
        }
        $out = $svc->{$method}(...$args);

        return is_array($out) ? $out : null;
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
