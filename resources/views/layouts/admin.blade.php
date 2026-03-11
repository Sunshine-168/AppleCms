<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maccms Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/admin/index">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/actor">
                                Actors
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/vod">
                                Videos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/art">
                                Articles
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/annex/init">
                                Annex
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/user">
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/addon">
                                Addons
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/logout">
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 pt-3">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
