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
        'formId' => 'art-tag-form',
        'listUrl' => '/admin/video/art-tags',
        'saveUrl' => '/admin/video/art-tags/save',
        'count' => (int) ($tag['art_count'] ?? 0),
        'countUrl' => $isEdit ? '/admin/video/arts?tag_id='.(int) ($tag['id'] ?? 0) : '',
        'frontPrefix' => '/art/tag/',
        'example' => 'news',
        'namePh' => admin_t('ui.ph_tag_name'),
        'offHint' => admin_t('ui.art_tag_off'),
        'usedTail' => admin_t('ui.rename_keeps_tags'),
        'needName' => admin_t('ui.please_fill_tag_name'),
    ])
@endsection
