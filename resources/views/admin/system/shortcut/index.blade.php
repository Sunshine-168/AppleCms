@extends('admin.layouts.inner')
@section('title', admin_t('page.shortcut'))

@php
    $groups = $groups ?? [];
    $todoTotal = (int) ($todo_total ?? 0);
@endphp

@section('plain')
<div class="card card-panel shortcut-index">
    <div class="card-header">
        <span>常用@if($todoTotal > 0) <em>· {{ $todoTotal }}</em>@endif</span>
        <div>
            <a class="btn btn-sm" href="/admin/plugins">{{ admin_t('nav.plugins') }}</a>
            <a class="btn btn-muted btn-sm" href="/admin/more">全部功能</a>
        </div>
    </div>
    <div class="card-body">
        <p class="muted recycle-lead">日常会点的入口。漫画、商城走右上角「<a href="/admin/plugins">插件</a>」。不常用的工具在「<a href="/admin/more">全部功能</a>」里搜。</p>

        @foreach($groups as $group)
            <section class="shortcut-block">
                <h2>
                    <span>{{ $group['title'] }}</span>
                    @if(! empty($group['hint']))
                        <span class="muted">{{ $group['hint'] }}</span>
                    @endif
                </h2>
                <div class="more-grid">
                    @foreach($group['items'] as $item)
                        @php $count = (int) ($item['count'] ?? 0); @endphp
                        <a class="more-tile{{ $count > 0 ? ' has-count' : '' }}" href="{{ $item['url'] }}">
                            <strong>
                                {{ $item['title'] }}
                                @if($count > 0)
                                    <em>{{ $count }}</em>
                                @endif
                            </strong>
                            <span>{{ $item['desc'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
@endsection
