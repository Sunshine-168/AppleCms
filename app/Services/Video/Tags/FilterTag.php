<?php

namespace App\Services\Video\Tags;

use App\Models\Video\VideoModel;
use App\Cms\CmsViewContext;
use Illuminate\Support\Collection;

class FilterTag
{
    public function __construct(private readonly CmsViewContext $context) {}

    /** @param  array<string, mixed>  $options */
    public function get(array $options = []): Collection
    {
        $groups = $options['by'] ?? ['class', 'area', 'year', 'lang', 'letter', 'order'];
        if (is_string($groups)) {
            $groups = preg_split('/\s*,\s*/', $groups) ?: [];
        }

        $labels = [
            'class' => '类型',
            'area' => '地区',
            'year' => '年份',
            'lang' => '语言',
            'letter' => '字母',
            'order' => '排序',
        ];

        $out = collect();
        foreach ($groups as $name) {
            $name = (string) $name;
            if (! isset($labels[$name])) {
                continue;
            }
            $out->push((object) [
                'name' => $name,
                'label' => $labels[$name],
                'current' => $this->currentValue($name),
                'choices' => $this->choices($name),
            ]);
        }

        return $out;
    }

    private function currentValue(string $name): string
    {
        $filters = $this->context->filters();

        return (string) ($filters[$name] ?? request($name, ''));
    }

    /** @return list<array{value:string,label:string,url:string,active:bool}> */
    private function choices(string $name): array
    {
        $current = $this->currentValue($name);
        $values = match ($name) {
            'order' => array_keys(config('video.orders', [])),
            'letter' => range('A', 'Z'),
            'year' => $this->fromSetting('filter_year') ?: $this->distinct('year'),
            'area' => $this->fromSetting('filter_area') ?: $this->distinct('area'),
            'lang' => $this->fromSetting('filter_lang') ?: $this->distinct('lang'),
            'class' => $this->classValues(),
            default => [],
        };

        $type = $this->context->type();
        $base = $type ? $type->url : vod_url('show');
        $query = request()->except(['page', $name]);

        $choices = [[
            'value' => '',
            'label' => '全部',
            'url' => $this->buildUrl($base, $query, $name, ''),
            'active' => $current === '' || $current === 'all',
        ]];

        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            $label = $name === 'order'
                ? (string) (config('video.orders.'.$value) ?? $value)
                : $value;
            $choices[] = [
                'value' => $value,
                'label' => $label,
                'url' => $this->buildUrl($base, $query, $name, $value),
                'active' => $current === $value,
            ];
        }

        return $choices;
    }

    /** @return list<string> */
    private function fromSetting(string $key): array
    {
        $raw = trim((string) app(\App\Services\Video\VideoSettingService::class)->get($key, ''));
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\s*,\s*/', $raw) ?: [])));
    }

    private function distinct(string $column): array
    {
        return VideoModel::query()->published()
            ->where($column, '!=', '')
            ->distinct()
            ->orderByDesc($column)
            ->limit(40)
            ->pluck($column)
            ->all();
    }

    private function classValues(): array
    {
        $rows = VideoModel::query()->published()->where('class', '!=', '')->pluck('class');
        $set = [];
        foreach ($rows as $row) {
            foreach (preg_split('/\s*,\s*/', (string) $row) ?: [] as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $set[$part] = true;
                }
            }
        }

        return array_keys($set);
    }

    private function buildUrl(string $base, array $query, string $name, string $value): string
    {
        if ($value === '') {
            unset($query[$name]);
        } else {
            $query[$name] = $value;
        }
        $qs = http_build_query($query);

        return $qs === '' ? $base : $base.'?'.$qs;
    }
}
