<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f5f5;
            @if($background)
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
    <h3 class="text-center mb-4">Admin Login</h3>
    
    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('admin.login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="admin_name" class="form-label">Username</label>
            <input type="text" class="form-control" id="admin_name" name="admin_name" required>
        </div>
        <div class="mb-3">
            <label for="admin_pwd" class="form-label">Password</label>
            <input type="password" class="form-control" id="admin_pwd" name="admin_pwd" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
    
    <div class="text-center mt-3 text-muted">
        <small>Maccms Laravel Migration</small>
    </div>
</div>

</body>
</html>
