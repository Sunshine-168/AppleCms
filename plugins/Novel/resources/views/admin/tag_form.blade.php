@extends('admin.layouts.inner')
@php
    $tag = is_array($tag ?? null) ? $tag : [];
    $isEdit = (bool) ($isEdit ?? false);
@endphp
@section('title', $isEdit ? admin_t('ui.edit_tag') : admin_t('ui.new_tag'))

@section('plain')
    @include('admin.partials.named-slug-form', [
        'kind' => 'tag',
        'isEdit' => $isEdit,
        'entity' => $tag,
        'formId' => 'novel-tag-form',
        'listUrl' => '/admin/video/novel-tags',
        'saveUrl' => '/admin/video/novel-tags/save',
        'count' => (int) ($tag['novel_count'] ?? 0),
        'countUrl' => $isEdit ? '/admin/video/novels?tag_id='.(int) ($tag['id'] ?? 0) : '',
        'frontPrefix' => '/novel?tag=',
        'example' => 'rexue',
        'namePh' => admin_t('ui.ph_tag_name'),
        'offHint' => admin_t('ui.tag_off_cloud'),
        'usedTail' => admin_t('ui.rename_keeps_tags'),
        'needName' => admin_t('ui.please_fill_tag_name'),
    ])
@endsection
