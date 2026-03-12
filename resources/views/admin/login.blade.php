<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('admin/index/login/title') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            @if(!empty($background))
            background-image: url('{{ $background }}');
            background-size: cover;
            background-position: center;
            @endif
        }
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="login-card">
    <h3 class="text-center mb-4">{{ __('admin/index/login/tip_sys') }}</h3>
    
    @if($errors->any())
        @php
            $translatedErrors = collect($errors->all())->map(function ($error) {
                if ($error === 'Invalid credentials') {
                    return __('admin/index/login/error_invalid');
                }
                if ($error === 'Account disabled') {
                    return __('admin/index/login/error_disabled');
                }
                return $error;
            });
        @endphp
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">{{ __('admin/index/login/error_title') }}</div>
            <div class="small">
                @foreach($translatedErrors as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="admin_name" class="form-label">{{ __('admin/index/login/filed_no') }}</label>
            <input type="text" class="form-control" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" autocomplete="username" autofocus required>
        </div>
        <div class="mb-3">
            <label for="admin_pwd" class="form-label">{{ __('admin/index/login/filed_pass') }}</label>
            <input type="password" class="form-control" id="admin_pwd" name="admin_pwd" autocomplete="current-password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">{{ __('admin/index/login/btn_submit') }}</button>
        </div>
    </form>
    
    <div class="text-center mt-3 text-muted">
        <small>{{ __('admin/index/login/tip_welcome') }}</small>
    </div>
</div>

</body>
</html>
