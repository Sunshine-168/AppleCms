<?php

function theme_asset($path): string
{
    $theme = config('theme.default', 'default');

    return '/themes/'.$theme.'/assets/'.$path;
}

function conf($val): string
{
    return (string) config('system.settings.'.$val);
}

function vod_theme(): string
{
    return (string) config('video.theme', 'default');
}

function vod_url(string $name, array $params = []): string
{
    $id = $params['id'] ?? null;
    $slug = $params['slug'] ?? null;
    $sid = $params['sid'] ?? 0;
    $nid = $params['nid'] ?? 0;
    $suffix = (string) config('video.rewrite.suffix', '');
    $mode = (string) config('video.rewrite.mode', 'laravel');

    if ($mode === 'mac') {
        return match ($name) {
            'home' => url('/'),
            'show' => url('/index.php/vod/show'.$suffix),
            'search' => url('/index.php/vod/search'.$suffix),
            'type' => url('/index.php/vod/type/id/'.($id ?? '').$suffix),
            'detail' => url('/index.php/vod/detail/id/'.($id ?? '').$suffix),
            'play' => url('/index.php/vod/play/id/'.($params['id'] ?? '').'/sid/'.(int) $sid.'/nid/'.(int) $nid.$suffix),
            'player' => url('/player/'.($params['id'] ?? '').((int) $sid ? '/'.$sid : '').((int) $nid ? '/'.$nid : '')),
            'down' => url('/index.php/vod/down/id/'.($params['id'] ?? '').'/sid/'.(int) $sid.'/nid/'.(int) $nid.$suffix),
            'tag' => url('/index.php/vod/tag/id/'.($slug ?? $id ?? '').$suffix),
            'actor' => url('/index.php/vod/actor/id/'.($id ?? '').$suffix),
            'topic' => url('/index.php/vod/topic/id/'.($id ?? '').$suffix),
            default => url('/'),
        };
    }

    $playTail = '';
    if ($sid) {
        $playTail .= '/'.$sid;
        if ($nid) {
            $playTail .= '/'.$nid;
        }
    }

    return match ($name) {
        'home' => url('/'),
        'show' => url('/show'),
        'search' => url('/search'),
        'type' => url('/type/'.($id ?? '')),
        'detail' => url('/vod/'.($id ?? '')),
        'play' => url('/play/'.($params['id'] ?? '').$playTail),
        'player' => url('/player/'.($params['id'] ?? '').$playTail),
        'down' => url('/down/'.($params['id'] ?? '').$playTail),
        'tag' => url('/tag/'.($slug ?? $id ?? '')),
        'actor' => url('/actor/'.($id ?? '')),
        'topic' => url('/topic/'.($id ?? '')),
        default => url('/'),
    };
}
