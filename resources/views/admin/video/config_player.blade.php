@extends('admin.layouts.inner')
@section('title', admin_t('page.config_player'))

@section('content')
    <form class="admin-form" id="site-form">
        <label>{{ admin_t('ui.buffer_seconds') }}</label>
        <input type="number" name="play_buffer" value="{{ $site['play_buffer'] ?? 5 }}">
        <label>{{ admin_t('ui.url_encode') }}</label>
        <select name="play_encrypt">
            <option value="0" @selected(($site['play_encrypt'] ?? '0')==='0')>{{ admin_t('ui.plaintext') }}</option>
            <option value="1" @selected(($site['play_encrypt'] ?? '0')==='1')>{{ admin_t('ui.front_base64') }}</option>
        </select>
        <div class="form-actions">
            <button type="button" class="btn" id="site-save">{{ admin_t('ui.save') }}</button>
        </div>
    </form>
@endsection

@include('admin.partials.site-save')
