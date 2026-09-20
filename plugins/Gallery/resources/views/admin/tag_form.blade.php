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
        'formId' => 'gallery-tag-form',
        'listUrl' => '/admin/video/gallery-tags',
        'saveUrl' => '/admin/video/gallery-tags/save',
        'count' => (int) ($tag['gallery_count'] ?? 0),
        'countUrl' => $isEdit ? '/admin/video/galleries?tag_id='.(int) ($tag['id'] ?? 0) : '',
        'frontPrefix' => '/gallery?tag=',
        'example' => 'rexue',
        'namePh' => admin_t('ui.ph_tag_name'),
        'offHint' => admin_t('ui.tag_off_cloud'),
        'usedTail' => admin_t('ui.rename_keeps_tags'),
        'needName' => admin_t('ui.please_fill_tag_name'),
    ])
@endsection
