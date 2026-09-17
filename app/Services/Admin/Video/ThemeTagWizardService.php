<?php

namespace App\Services\Admin\Video;

use App\Services\Video\Tags\ActorTag;
use App\Services\Video\Tags\AdTag;
use App\Services\Video\Tags\ArtTag;
use App\Services\Video\Tags\CommentTag;
use App\Services\Video\Tags\FilterTag;
use App\Services\Video\Tags\GuestbookTag;
use App\Services\Video\Tags\LinkTag;
use App\Services\Video\Tags\SlideTag;
use App\Services\Video\Tags\TagListTag;
use App\Services\Video\Tags\TopicTag;
use App\Services\Video\Tags\TypeTag;
use App\Services\Video\Tags\VodTag;
use App\Support\Utils\Result;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ThemeTagWizardService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function catalog(): array
    {
        $out = [];
        foreach ($this->defs() as $def) {
            unset($def['class']);
            $out[] = $def;
        }

        return $out;
    }

    /** @param  array<string, mixed>  $input */
    public function snippet(string $tag, array $input = []): array
    {
        $def = $this->def($tag);
        if ($def === null) {
            return Result::fail('没有这个标签');
        }
        $opts = $this->optionsFromInput($def, $input);

        return Result::success([
            'tag' => $def['name'],
            'snippet' => $this->renderSnippet($def, $opts),
            'options' => $opts,
        ]);
    }

    /** @param  array<string, mixed>  $input */
    public function tryTag(string $tag, array $input = []): array
    {
        $def = $this->def($tag);
        if ($def === null) {
            return Result::fail('没有这个标签');
        }
        if (! ($def['tryable'] ?? false) || empty($def['class'])) {
            return Result::fail((string) ($def['try_fail'] ?? '这个标签不拉列表，贴到主题里才会出 HTML'));
        }
        $opts = $this->optionsFromInput($def, $input);
        if (($def['name'] ?? '') === 'vodComment' && (int) ($opts['id'] ?? 0) < 1) {
            return Result::fail('请填写影片 ID。评论只挂在片子上，后台没有当前播放页。');
        }
        $note = '';
        if (($def['name'] ?? '') === 'vodActor' && empty($opts['global'])) {
            $opts['global'] = 1;
            $note = '后台没有当前影片，按全站演员库试的。';
        }
        if (isset($opts['num'])) {
            $opts['num'] = min(50, max(1, (int) $opts['num']));
        }
        try {
            $items = app($def['class'])->get($opts);
        } catch (\Throwable $e) {
            $msg = trim($e->getMessage());

            return Result::fail($msg !== '' ? $msg : '试的时候出错了');
        }
        if ($items instanceof LengthAwarePaginator) {
            $total = (int) $items->total();
            $rows = collect($items->items());
        } else {
            $rows = $items instanceof Collection ? $items : collect($items);
            $total = $rows->count();
        }
        $samples = [];
        foreach ($rows->take(5) as $row) {
            $samples[] = $this->sampleLabel($def['name'], $row);
        }
        $msg = $total === 0
            ? '现在 0 条，贴到主题里也会是空的'
            : ('现在能捞到 '.$total.' 条');
        if ($note !== '') {
            $msg .= '。'.$note;
        }

        return Result::success([
            'tag' => $def['name'],
            'count' => $total,
            'samples' => $samples,
            'snippet' => $this->renderSnippet($def, $this->optionsFromInput($def, $input)),
        ], $msg);
    }

    /**
     * @return array<string, mixed>|null
     */
    private function def(string $name): ?array
    {
        $name = trim($name);

        return $name === '' ? null : ($this->defs()[$name] ?? null);
    }

    /**
     * @param  array<string, mixed>  $def
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    private function optionsFromInput(array $def, array $input): array
    {
        $opts = [];
        $fields = $def['fields'] ?? [];
        foreach ($fields as $field) {
            $key = (string) ($field['name'] ?? '');
            if ($key === '' || ! array_key_exists($key, $input)) {
                continue;
            }
            $raw = $input[$key];
            $type = (string) ($field['type'] ?? 'text');
            if ($type === 'checkbox') {
                if ($this->isTruthy($raw)) {
                    $opts[$key] = true;
                }
                continue;
            }
            if ($type === 'number') {
                if ($raw === '' || $raw === null) {
                    continue;
                }
                $opts[$key] = (int) $raw;
                continue;
            }
            $value = is_scalar($raw) ? trim((string) $raw) : '';
            if ($value === '') {
                continue;
            }
            $opts[$key] = $value;
        }
        if (($def['name'] ?? '') === 'vod' && ! isset($opts['order'])) {
            $by = trim((string) ($input['by'] ?? ''));
            if ($by !== '') {
                $opts['order'] = $by;
            }
        }

        return $opts;
    }

    /**
     * @param  array<string, mixed>  $def
     * @param  array<string, mixed>  $opts
     */
    private function renderSnippet(array $def, array $opts): string
    {
        $name = (string) $def['name'];
        if (empty($def['loop']) && ! empty($def['fixed'])) {
            return (string) ($def['sample'] ?? '@'.$name);
        }
        $expr = $this->exportPhpArray($opts);
        if (empty($def['loop'])) {
            return $expr === '' ? '@'.$name : '@'.$name.'('.$expr.')';
        }
        $open = $expr === '' ? '@'.$name : '@'.$name.'('.$expr.')';
        $body = (string) ($def['sample'] ?? '    {{ $item }}');

        return $open."\n".$body."\n@end".$name;
    }

    /** @param  array<string, mixed>  $opts */
    private function exportPhpArray(array $opts): string
    {
        if ($opts === []) {
            return '';
        }
        $parts = [];
        foreach ($opts as $key => $value) {
            $parts[] = $this->exportPhpKey((string) $key).' => '.$this->exportPhpValue($value);
        }

        return '['.implode(', ', $parts).']';
    }

    private function exportPhpKey(string $key): string
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) ? "'".$key."'" : var_export($key, true);
    }

    private function exportPhpValue(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        $s = (string) $value;

        return "'".str_replace(['\\', "'"], ['\\\\', "\\'"], $s)."'";
    }

    private function isTruthy(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        return in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'on'], true);
    }

    private function sampleLabel(string $tag, mixed $row): string
    {
        if (! is_object($row)) {
            return trim((string) $row);
        }
        $candidates = match ($tag) {
            'vod' => ['title', 'name'],
            'vodFilter' => ['label', 'name'],
            'vodArt' => ['title', 'name'],
            'vodComment', 'vodGbook' => ['author_name', 'content'],
            default => ['name', 'title', 'label'],
        };
        foreach ($candidates as $key) {
            $value = trim((string) ($row->{$key} ?? ''));
            if ($value !== '') {
                return $value;
            }
        }

        return '#'.(string) ($row->id ?? '');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function defs(): array
    {
        $list = [
            $this->vod(),
            $this->loop('vodType', '分类', '导航栏、首页按栏目出块。type=top 是一级栏目。', TypeTag::class, [
                ['name' => 'type', 'label' => '范围', 'type' => 'select', 'options' => [
                    'top' => '一级栏目',
                    'son' => '当前栏目的子类',
                    'peer' => '同级栏目',
                ], 'value' => 'top'],
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => ''],
                ['name' => 'id', 'label' => '父级 ID', 'type' => 'number', 'hint' => '空着时 top 就是全部一级。'],
                ['name' => 'as', 'label' => '循环变量', 'type' => 'text', 'hint' => '默认 item。首页嵌套列表时改成 type。'],
            ], [['code' => '$item->name', 'label' => '名称'], ['code' => '$item->url', 'label' => '栏目页']], "    <a href=\"{{ \$item->url }}\">{{ \$item->name }}</a>"),
            $this->loop('vodFilter', '筛选', '分类页的类型、地区、年份。不是单独的地区标签。', FilterTag::class, [
                ['name' => 'by', 'label' => '要哪些组', 'type' => 'text', 'hint' => '逗号分隔。空着就是类型、地区、年份、语言、字母、排序。'],
            ], [
                ['code' => '$item->label', 'label' => '组名'],
                ['code' => '$item->choices', 'label' => '选项（value/label/url/active）'],
            ], "    <strong>{{ \$item->label }}</strong>\n    @foreach(\$item->choices as \$choice)\n        <a href=\"{{ \$choice['url'] }}\">{{ \$choice['label'] }}</a>\n    @endforeach"),
            $this->loop('vodTag', '标签词', '贺岁、高分这种聚合词。给片子打标签请去「标签」。', TagListTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '30'],
            ], [['code' => '$item->name', 'label' => '名称'], ['code' => '$item->url', 'label' => '标签页']], "    <a href=\"{{ \$item->url }}\">{{ \$item->name }}</a>"),
            $this->loop('vodActor', '演员', '详情页默认只出这部片的演员。勾全站才是演员库。', ActorTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '20'],
                ['name' => 'global', 'label' => '全站演员库', 'type' => 'checkbox'],
            ], [['code' => '$item->name', 'label' => '名字'], ['code' => '$item->url', 'label' => '演员页'], ['code' => '$item->avatar', 'label' => '头像']], "    <a href=\"{{ \$item->url }}\">{{ \$item->name }}</a>"),
            $this->loop('vodTopic', '专题', '片单。排序参数是 by，影片列表才是 order。', TopicTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '10'],
                ['name' => 'by', 'label' => '排序', 'type' => 'select', 'options' => [
                    'sort' => '后台排序',
                    'time' => '更新时间',
                    'hits' => '人气',
                    'id' => 'ID',
                ]],
                ['name' => 'order', 'label' => '方向', 'type' => 'select', 'options' => ['desc' => '倒序', 'asc' => '正序']],
                ['name' => 'page', 'label' => '分页', 'type' => 'checkbox', 'hint' => '分页后下面要加 @vodPaginate。'],
                ['name' => 'wd', 'label' => '关键词', 'type' => 'text'],
            ], [['code' => '$item->name', 'label' => '名称'], ['code' => '$item->url', 'label' => '专题页'], ['code' => '$item->cover', 'label' => '封面'], ['code' => '$item->videos_count', 'label' => '挂了几部']], "    <a href=\"{{ \$item->url }}\">{{ \$item->name }}</a>"),
            $this->loop('vodArt', '文章', '资讯文章，不是影片简介。字段在 video_arts。', ArtTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '12'],
                ['name' => 'page', 'label' => '分页', 'type' => 'checkbox'],
            ], [['code' => '$item->title', 'label' => '标题'], ['code' => '$item->url', 'label' => '文章页']], "    <a href=\"{{ \$item->url }}\">{{ \$item->title }}</a>"),
            $this->loop('vodLink', '友链', '页脚文字链。顶栏目录是网址导航，不是这个标签。', LinkTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '30'],
            ], [['code' => '$item->name', 'label' => '名称'], ['code' => '$item->url', 'label' => '网址']], "    <a href=\"{{ \$item->url }}\" rel=\"nofollow\">{{ \$item->name }}</a>"),
            $this->loop('vodAd', '广告', '按位置插 HTML。过期或停用的不会出来。', AdTag::class, [
                ['name' => 'slot', 'label' => '位置', 'type' => 'select', 'options' => [
                    '' => '全部',
                    'header' => '页头',
                    'footer' => '页脚',
                    'play' => '播放页',
                ], 'value' => 'header'],
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '10'],
            ], [['code' => '$item->content', 'label' => 'HTML（未转义）'], ['code' => '$item->slot', 'label' => '位置']], '    {!! $item->content !!}'),
            $this->loop('vodSlide', '幻灯', '首页轮播、播放页贴片。先在幻灯片里上传图。', SlideTag::class, [
                ['name' => 'slot', 'label' => '位置', 'type' => 'select', 'options' => [
                    'home' => '首页',
                    'play' => '播放页',
                ], 'value' => 'home'],
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '8'],
            ], [['code' => '$item->name', 'label' => '标题'], ['code' => '$item->pic', 'label' => '图片'], ['code' => '$item->url', 'label' => '跳转']], "    <a href=\"{{ \$item->url ?: '#' }}\">{{ \$item->name }}</a>"),
            $this->loop('vodGbook', '留言', '留言板列表。后台回复会出现在 reply。', GuestbookTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '20'],
            ], [['code' => '$item->author_name', 'label' => '昵称'], ['code' => '$item->content', 'label' => '内容'], ['code' => '$item->reply', 'label' => '回复']], "    <p>{{ \$item->author_name }}：{{ \$item->content }}</p>"),
            $this->loop('vodComment', '评论', '只出这一部片子的评论。后台试的时候必须填影片 ID。', CommentTag::class, [
                ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '20'],
                ['name' => 'id', 'label' => '影片 ID', 'type' => 'number', 'hint' => '详情页可空，会用当前片子。'],
            ], [['code' => '$item->author_name', 'label' => '昵称'], ['code' => '$item->content', 'label' => '内容']], "    <p>{{ \$item->author_name }}：{{ \$item->content }}</p>", 'page'),
            $this->untryable('vodSource', '线路', '播放页/下载页的播放源。后台没有当前片子，试不了。', 'page', [
                ['name' => 'type', 'label' => '类型', 'type' => 'select', 'options' => [
                    'play' => '播放',
                    'down' => '下载',
                ], 'value' => 'play'],
            ], [['code' => '$item->name', 'label' => '线路名'], ['code' => '$item->id', 'label' => '线路 ID']], "    <a href=\"{{ vod_url('play', ['id' => \$video->id, 'sid' => \$item->id]) }}\">{{ \$item->name }}</a>", '线路和分集只在播放页有当前片子，后台试不到。'),
            $this->untryable('vodEpisode', '分集', '当前线路的集。没有线路时会退回这部片全部集。', 'page', [
                ['name' => 'sid', 'label' => '线路 ID', 'type' => 'number'],
            ], [['code' => '$item->display_name', 'label' => '集名'], ['code' => '$item->play_url', 'label' => '播放地址']], "    <a href=\"{{ \$item->play_url }}\">{{ \$item->display_name }}</a>", '线路和分集只在播放页有当前片子，后台试不到。'),
            $this->echoTag('vodSeo', '页头 SEO', '输出 title 和 meta。贴在 layout 的 head 里。', 'page', [], '@vodSeo', '这是输出 title 和 meta，不是列表。'),
            $this->echoTag('vodBreadcrumb', '面包屑', '首页 / 分类 / 当前页。', 'page', [
                ['name' => 'last', 'label' => '最后一段', 'type' => 'text', 'hint' => '没有当前影片时用这段文字。'],
            ], "@vodBreadcrumb(['last' => '搜索'])", '这是面包屑 HTML，不是列表。'),
            $this->echoTag('vodPaginate', '分页', '配 page => true 的列表，贴在列表下面。', 'page', [], '@vodPaginate', '分页要配带 page 的列表，贴在列表下面才会出。'),
            $this->echoTag('vodPrev', '上一部', '同分类里 ID 更小的一部。只在影片页有。', 'page', [
                ['name' => 'msg', 'label' => '没有时', 'type' => 'text', 'value' => '没有了'],
            ], "@vodPrev(['msg' => '没有了'])", '上一部下一部要当前影片页，后台试不到。'),
            $this->echoTag('vodNext', '下一部', '同分类里 ID 更大的一部。', 'page', [
                ['name' => 'msg', 'label' => '没有时', 'type' => 'text', 'value' => '没有了'],
            ], '@vodNext', '上一部下一部要当前影片页，后台试不到。'),
            $this->echoTag('vodDate', '日期', '把时间戳或日期格式化。name 是值，不是字段名。', 'format', [
                ['name' => 'format', 'label' => '格式', 'type' => 'text', 'value' => 'Y-m-d'],
            ], "@vodDate(['name' => \$item->created_at, 'format' => 'Y-m-d'])", '这是格式化日期，把值填进 name。', true),
            $this->echoTag('vodSubstr', '截字', '截一段简介。name 同样是值。', 'format', [
                ['name' => 'len', 'label' => '字数', 'type' => 'number', 'value' => '80'],
            ], "@vodSubstr(['name' => \$video->description, 'len' => 80])", '这是截字符串，把值填进 name。', true),
        ];
        $out = [];
        foreach ($list as $def) {
            $out[$def['name']] = $def;
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @param  list<array{code:string,label:string}>  $vars
     * @return array<string, mixed>
     */
    private function vod(): array
    {
        return $this->loop('vod', '影片', '首页、分类、搜索拉片子。排序参数是 order，不是苹果的 by。热门用 flag=hot。', VodTag::class, [
            ['name' => 'num', 'label' => '条数', 'type' => 'number', 'value' => '12'],
            ['name' => 'order', 'label' => '排序', 'type' => 'select', 'options' => [
                'time' => '更新时间',
                'hits' => '人气',
                'score' => '评分',
                'year' => '年份',
                'sort' => '后台排序',
            ], 'value' => 'time'],
            ['name' => 'flag', 'label' => '标记', 'type' => 'select', 'options' => [
                '' => '不限',
                'recommend' => '推荐',
                'hot' => '热门',
            ]],
            ['name' => 'typeid', 'label' => '分类 ID', 'type' => 'number', 'hint' => '空着：在分类页会跟当前栏目，首页则全部。'],
            ['name' => 'page', 'label' => '分页', 'type' => 'checkbox', 'hint' => '分页后下面要加 @vodPaginate。'],
            ['name' => 'wd', 'label' => '关键词', 'type' => 'text', 'hint' => '会走同义词。'],
            ['name' => 'ids', 'label' => '指定 ID', 'type' => 'text', 'hint' => '逗号分隔，如 1,2,3。'],
            ['name' => 'tag', 'label' => '标签', 'type' => 'text', 'hint' => '标签名或 ID。'],
        ], [
            ['code' => '$item->title', 'label' => '片名'],
            ['code' => '$item->url', 'label' => '详情页'],
            ['code' => '$item->cover', 'label' => '封面'],
            ['code' => '$item->year', 'label' => '年份'],
            ['code' => '$item->remarks', 'label' => '备注'],
        ], "    <a href=\"{{ \$item->url }}\">{{ \$item->title }}</a>");
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @param  list<array{code:string,label:string}>  $vars
     * @return array<string, mixed>
     */
    private function loop(string $name, string $title, string $hint, string $class, array $fields, array $vars, string $sample, string $group = 'list', string $tryFail = ''): array
    {
        return [
            'name' => $name,
            'title' => $title,
            'hint' => $hint,
            'group' => $group,
            'loop' => true,
            'tryable' => $class !== '',
            'try_fail' => $tryFail,
            'class' => $class,
            'fields' => $fields,
            'vars' => $vars,
            'sample' => $sample,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @param  list<array{code:string,label:string}>  $vars
     * @return array<string, mixed>
     */
    private function untryable(string $name, string $title, string $hint, string $group, array $fields, array $vars, string $sample, string $tryFail): array
    {
        $row = $this->loop($name, $title, $hint, '', $fields, $vars, $sample, $group, $tryFail);
        $row['tryable'] = false;

        return $row;
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return array<string, mixed>
     */
    private function echoTag(string $name, string $title, string $hint, string $group, array $fields, string $sample, string $tryFail, bool $fixed = false): array
    {
        return [
            'name' => $name,
            'title' => $title,
            'hint' => $hint,
            'group' => $group,
            'loop' => false,
            'tryable' => false,
            'try_fail' => $tryFail,
            'fixed' => $fixed,
            'class' => '',
            'fields' => $fields,
            'vars' => [],
            'sample' => $sample,
        ];
    }
}
