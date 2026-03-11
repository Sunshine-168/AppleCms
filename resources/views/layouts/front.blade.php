<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('maccms.site.site_name'))</title>
    <meta name="keywords" content="@yield('keywords', config('maccms.site.site_keywords'))">
    <meta name="description" content="@yield('description', config('maccms.site.site_description'))">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .vod-item { margin-bottom: 20px; }
        .vod-item img { width: 100%; height: 200px; object-fit: cover; border-radius: 5px; }
        .vod-title { margin-top: 10px; font-weight: bold; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="/">{{ config('maccms.site.site_name') }}</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('vod.index') }}">All Videos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('art.index') }}">Articles</a>
                    </li>
                    @if(isset($types))
                        @foreach($types as $type)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('vod.type', $type->type_id) }}">{{ $type->type_name }}</a>
                        </li>
                        @endforeach
                    @endif
                    @if(Auth::check())
                        <li class="nav-item"><a class="nav-link" href="/user/index">Profile</a></li>
                        <li class="nav-item"><a class="nav-link" href="/user/logout">Logout</a></li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="/user/login">Login</a></li>
                    @endif
                </ul>
                <form class="d-flex" action="{{ route('vod.search') }}" method="GET">
                    <input class="form-control me-2" type="search" name="wd" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        @yield('content')
    </div>

    <footer class="bg-light text-center text-lg-start mt-5">
        <div class="text-center p-3" style="background-color: rgba(0, 0, 0, 0.2);">
            © {{ date('Y') }} {{ config('maccms.site.site_name') }}
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    @stack('scripts')
</body>
</html>
