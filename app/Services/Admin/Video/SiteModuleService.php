<?php

namespace App\Services\Admin\Video;

use App\Models\Video\VideoArt;
use App\Models\Video\VideoCard;
use App\Models\Video\VideoComment;
use App\Models\Video\VideoModel;
use App\Models\Video\VideoSlide;
use App\Models\Video\VideoTopicModel;
use App\Models\Video\VideoTopicRelModel;
use App\Models\Video\VideoTypeModel;
use App\Support\Utils\Result;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SiteModuleService
{
    /** @return array<string, mixed> */
    public function config(string $module): array
    {
        $fromPlugin = app(\App\Plugins\PluginHost::class)->findModule($module);
        if ($fromPlugin !== null) {
            return $fromPlugin;
        }

        return match ($module) {
            'topics' => [
                'title' => '专题管理',
                'hint' => '专题是片单。先建「贺岁档」「冷门佳片」这类栏目，再点绑片把影片挂进去。',
                'model' => \App\Models\Video\VideoTopicModel::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'slug', 'label' => '别名', 'type' => 'text'],
                    ['name' => 'cover', 'label' => '封面', 'type' => 'text'],
                    ['name' => 'blurb', 'label' => '简介', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '上架', '0' => '下架']],
                ],
                'cols' => ['id', 'name', 'slug', 'status', 'sort'],
            ],
            'players' => [
                'title' => '播放器',
                'model' => \App\Models\Video\VideoPlayerModel::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'code', 'label' => '标识', 'type' => 'text'],
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'parse', 'label' => '解析(可用{url})', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'code', 'name', 'status', 'sort'],
            ],
            'links' => [
                'title' => '友情链接',
                'model' => \App\Models\Video\FriendLink::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'url', 'label' => '链接', 'type' => 'text'],
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
                'title' => '报错管理',
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
                'title' => '会员管理',
                'model' => \App\Models\Member\Member::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '昵称', 'type' => 'text'],
                    ['name' => 'email', 'label' => '邮箱', 'type' => 'text'],
                    ['name' => 'password', 'label' => '密码(留空不改)', 'type' => 'text'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'group_id', 'label' => '会员组ID', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '正常', '0' => '禁用']],
                ],
                'cols' => ['id', 'name', 'email', 'points', 'group_id', 'status'],
            ],
            'cards' => [
                'title' => '积分卡密',
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
                'model' => \App\Models\Video\VideoAuditRule::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'scope', 'label' => '范围', 'type' => 'select', 'options' => ['title' => '标题', 'content' => '简介', 'actor' => '演员']],
                    ['name' => 'words', 'label' => '关键词/正则(逗号或换行)', 'type' => 'textarea'],
                    ['name' => 'is_regex', 'label' => '正则', 'type' => 'select', 'options' => ['0' => '普通匹配', '1' => '正则']],
                    ['name' => 'action', 'label' => '动作', 'type' => 'select', 'options' => ['skip' => '跳过入库', 'review' => '入库待审', 'replace' => '替换后入库']],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                ],
                'cols' => ['id', 'name', 'scope', 'action', 'status'],
            ],
            'collect_tasks' => [
                'title' => '定时采集',
                'model' => \App\Models\Video\VideoCollectTask::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'collect_source_id', 'label' => '采集源ID', 'type' => 'number'],
                    ['name' => 'cron_expression', 'label' => 'Cron', 'type' => 'text'],
                    ['name' => 'pages', 'label' => '页数', 'type' => 'number'],
                    ['name' => 'hours', 'label' => '最近小时', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '停用']],
                ],
                'cols' => ['id', 'name', 'collect_source_id', 'cron_expression', 'pages', 'hours', 'status', 'last_run_at', 'last_msg'],
            ],
            'ads' => [
                'title' => '广告位',
                'model' => \App\Models\Video\VideoAd::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'slot', 'label' => '标识 header/footer/play', 'type' => 'text'],
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'type_id', 'label' => '分类ID(0全部)', 'type' => 'number'],
                    ['name' => 'expire_at', 'label' => '过期时间戳(0不过期)', 'type' => 'number'],
                    ['name' => 'content', 'label' => 'HTML', 'type' => 'textarea'],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'slot', 'name', 'type_id', 'expire_at', 'status', 'sort'],
            ],
            'guestbooks' => [
                'title' => '留言',
                'model' => \App\Models\Video\VideoGuestbook::class,
                'search' => 'content',
                'fields' => [
                    ['name' => 'author_name', 'label' => '昵称', 'type' => 'text'],
                    ['name' => 'content', 'label' => '内容', 'type' => 'textarea'],
                    ['name' => 'reply', 'label' => '回复', 'type' => 'textarea'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '显示', '0' => '隐藏']],
                ],
                'cols' => ['id', 'author_name', 'content', 'reply', 'status'],
            ],
            'groups' => [
                'title' => '会员组',
                'model' => \App\Models\Member\MemberGroup::class,
                'search' => 'name',
                'fields' => [
                    ['name' => 'name', 'label' => '名称', 'type' => 'text'],
                    ['name' => 'points_min', 'label' => '积分门槛', 'type' => 'number'],
                    ['name' => 'trysee', 'label' => '试看秒数', 'type' => 'number'],
                    ['name' => 'day_free', 'label' => '每天免费条数', 'type' => 'number'],
                    ['name' => 'need_login', 'label' => '点播需登录', 'type' => 'select', 'options' => ['0' => '否', '1' => '是']],
                    ['name' => 'sort', 'label' => '排序', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '启用', '0' => '禁用']],
                ],
                'cols' => ['id', 'name', 'points_min', 'trysee', 'day_free', 'status', 'sort'],
            ],
            'orders' => [
                'title' => '会员订单',
                'model' => \App\Models\Member\MemberOrder::class,
                'search' => 'order_no',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'order_no', 'label' => '单号', 'type' => 'text'],
                    ['name' => 'amount', 'label' => '金额分', 'type' => 'number'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'channel', 'label' => '渠道 wechat/alipay/manual', 'type' => 'text'],
                    ['name' => 'trade_no', 'label' => '支付流水', 'type' => 'text'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '待付', '1' => '已付', '2' => '关闭']],
                    ['name' => 'remark', 'label' => '备注', 'type' => 'text'],
                ],
                'cols' => ['id', 'member_id', 'order_no', 'amount', 'points', 'channel', 'status'],
            ],
            'withdraws' => [
                'title' => '提现',
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
                'model' => \App\Models\Video\VideoCollectLog::class,
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
                'model' => \App\Models\Member\MemberPointLog::class,
                'search' => 'remark',
                'fields' => [
                    ['name' => 'member_id', 'label' => '会员ID', 'type' => 'number'],
                    ['name' => 'points', 'label' => '变动', 'type' => 'number'],
                    ['name' => 'balance', 'label' => '余额', 'type' => 'number'],
                    ['name' => 'type', 'label' => '类型', 'type' => 'text'],
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
                'model' => \App\Models\Member\MemberInvite::class,
                'search' => 'code',
                'fields' => [
                    ['name' => 'code', 'label' => '邀请码', 'type' => 'text'],
                    ['name' => 'member_id', 'label' => '所属会员ID', 'type' => 'number'],
                    ['name' => 'used_by', 'label' => '使用者ID', 'type' => 'number'],
                    ['name' => 'points', 'label' => '积分', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['1' => '未用', '0' => '已用']],
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
                'title' => '搜索词统计',
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
                'title' => '采集临时表',
                'model' => \App\Models\Video\VideoCollectTemp::class,
                'search' => 'title',
                'fields' => [
                    ['name' => 'collect_source_id', 'label' => '采集源ID', 'type' => 'number'],
                    ['name' => 'collect_id', 'label' => '远程ID', 'type' => 'text'],
                    ['name' => 'title', 'label' => '标题', 'type' => 'text'],
                    ['name' => 'cover', 'label' => '封面', 'type' => 'text'],
                    ['name' => 'type_id', 'label' => '分类ID', 'type' => 'number'],
                    ['name' => 'status', 'label' => '状态', 'type' => 'select', 'options' => ['0' => '待转入', '1' => '已转入']],
                    ['name' => 'msg', 'label' => '说明', 'type' => 'text'],
                ],
                'cols' => ['id', 'collect_source_id', 'title', 'type_id', 'status', 'msg', 'created_at'],
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
            } elseif ($module === 'arts') {
                $q->where(function ($inner) use ($kw) {
                    $inner->where('title', 'like', '%'.$kw.'%')
                        ->orWhere('content', 'like', '%'.$kw.'%');
                    if (ctype_digit($kw)) {
                        $inner->orWhere('id', (int) $kw);
                    }
                });
            } else {
                $q->where($cfg['search'], 'like', '%'.$kw.'%');
            }
        }
        if ($module === 'comments' || $module === 'topics' || $module === 'arts' || $module === 'slides') {
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
        }
        if ($module === 'topics' || $module === 'slides') {
            $q->orderByDesc('sort')->orderByDesc('id');
        } elseif ($module === 'arts') {
            $q->orderByDesc('updated_at')->orderByDesc('id');
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
        if ($module === 'topics') {
            $rows = $this->decorateTopics($rows);
        }
        if ($module === 'arts') {
            $rows = $this->decorateArts($rows);
        }
        if ($module === 'slides') {
            $rows = $this->decorateSlides($rows);
        }

        return Result::success([
            'total' => $page->total(),
            'data' => $rows,
        ]);
    }

    public function save(string $module, array $data, ?int $id = null): array
    {
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
        }
        if ($module === 'orders' && $id === null && trim((string) ($payload['order_no'] ?? '')) === '') {
            $payload['order_no'] = 'V'.date('YmdHis').Str::upper(Str::random(4));
        }
        if ($module === 'pms' && $id === null) {
            $payload['created_at'] = $payload['created_at'] ?? time();
        }
        if ($module === 'arts' && $id === null && trim((string) ($payload['title'] ?? '')) === '') {
            return Result::fail('请填写标题');
        }
        $now = time();
        $oldStatus = null;
        if ($id) {
            $row = $class::query()->find($id);
            if (! $row) {
                return Result::fail('数据不存在');
            }
            if ($this->hasColumn($row, 'status')) {
                $oldStatus = (int) $row->status;
            }
            if ($this->hasColumn($row, 'updated_at')) {
                $payload['updated_at'] = $now;
            }
            $row->fill($payload)->save();
            $this->afterMoneySave($module, $row, $oldStatus);

            return Result::success(['id' => $id]);
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

        return Result::success(['id' => $row->id]);
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
        $row->delete();

        return Result::success();
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

    /**
     * @param list<int|string> $ids
     */
    public function batch(string $module, array $ids, string $action, mixed $value = ''): array
    {
        if (! in_array($module, ['comments', 'topics', 'arts'], true)) {
            return Result::fail('不支持的操作');
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return Result::fail(match ($module) {
                'topics' => '请先勾选专题',
                'arts' => '请先勾选文章',
                default => '请先勾选评论',
            });
        }
        $ok = 0;
        $fail = 0;
        foreach ($ids as $id) {
            $res = match ($action) {
                'status' => $this->save($module, ['status' => (int) $value], $id),
                'type' => $this->save($module, ['type_id' => (int) $value], $id),
                'delete' => $this->delete($module, $id),
                default => Result::fail('不支持的操作'),
            };
            if (($res['code'] ?? 1) === 0) {
                $ok++;
            } else {
                $fail++;
            }
        }
        if ($ok === 0) {
            return Result::fail('操作失败');
        }

        return Result::success(['ok' => $ok, 'fail' => $fail], $fail > 0 ? ('完成 '.$ok.' 条，'.$fail.' 条未处理') : '操作成功');
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
        foreach ($rows as &$row) {
            $id = (int) ($row['id'] ?? 0);
            $row['video_count'] = $counts[$id] ?? 0;
            $row['has_cover'] = trim((string) ($row['cover'] ?? '')) !== '';
        }
        unset($row);

        return $rows;
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

        return Result::success([], '已绑定 '.count($list).' 部');
    }

    public function generateCards(int $count, int $points): array
    {
        $count = min(200, max(1, $count));
        $points = max(1, $points);
        $now = time();
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(16));
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

        return Result::success(['codes' => $codes], '已生成 '.$count.' 张');
    }

    public function generateInvites(int $count, int $points, int $memberId = 0): array
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('member_invites')) {
            return Result::fail('邀请码表不存在');
        }
        $count = min(200, max(1, $count));
        $points = max(0, $points);
        $memberId = max(0, $memberId);
        $now = time();
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(8));
            while (\App\Models\Member\MemberInvite::query()->where('code', $code)->exists()) {
                $code = strtoupper(Str::random(8));
            }
            \App\Models\Member\MemberInvite::query()->create([
                'code' => $code,
                'member_id' => $memberId,
                'used_by' => 0,
                'points' => $points,
                'status' => 1,
                'created_at' => $now,
            ]);
            $codes[] = $code;
        }

        return Result::success(['codes' => $codes], '已生成 '.$count.' 个');
    }

    public function runCollectTask(int $id): array
    {
        $task = \App\Models\Video\VideoCollectTask::query()->find($id);
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

    private function hasColumn(Model $model, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($model->getTable(), $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
