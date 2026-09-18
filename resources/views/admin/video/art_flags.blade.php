@extends('admin.layouts.inner')
@section('title', $title)

@php
    $ready = (bool) ($ready ?? false);
    $flags = is_array($flags ?? null) ? $flags : [];
@endphp

@section('plain')
<div class="card card-panel art-flag-index">
    <div class="card-header">
        <span>推荐属性</span>
        <a class="btn btn-muted btn-sm" href="/admin/video/arts">返回文章</a>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">写稿勾选这三项。不能自己加标识码，也没有单独再建一张推荐表。</p>
        @if(! $ready)
            <p class="muted">请先执行数据库迁移。</p>
        @else
            <ul class="flag-board">
                @foreach($flags as $flag)
                    <li>
                        <a href="{{ $flag['url'] }}">{{ $flag['label'] }}</a>
                        <em>{{ (int) ($flag['art_count'] ?? 0) }} 篇</em>
                        <span class="muted">{{ $flag['hint'] }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
@endsection
