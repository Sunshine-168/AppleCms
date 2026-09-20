@extends('admin.layouts.inner')
@php
    $author = is_array($author ?? null) ? $author : [];
    $isEdit = (bool) ($isEdit ?? false);
@endphp
@section('title', $isEdit ? admin_t('ui.edit_author') : admin_t('ui.new_author'))

@section('plain')
    @include('admin.partials.named-slug-form', [
        'kind' => 'author',
        'isEdit' => $isEdit,
        'entity' => $author,
        'formId' => 'novel-author-form',
        'listUrl' => '/admin/video/novel-authors',
        'saveUrl' => '/admin/video/novel-authors/save',
        'count' => (int) ($author['novel_count'] ?? 0),
        'countUrl' => $isEdit ? '/admin/video/novels?author_id='.(int) ($author['id'] ?? 0) : '',
        'frontPrefix' => '/novel?author=',
        'example' => 'oda',
        'namePh' => admin_t('ui.ph_author_name'),
        'offHint' => admin_t('ui.author_off_cloud'),
        'usedTail' => admin_t('ui.rename_keeps_authors'),
        'needName' => admin_t('ui.please_fill_author_name'),
    ])
@endsection
