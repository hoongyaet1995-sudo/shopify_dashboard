<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shopify Dashboard')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @stack('styles') {{-- For page-specific CSS --}}
</head>
<body>
    <nav class="navbar navbar-light bg-light d-md-none border-bottom p-3">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold">Menu</span>
            <button class="btn btn-outline-dark" type="button" id="sidebarToggle">
                <i class="bi bi-three-dots-vertical"></i>
            </button>
        </div>
    </nav>

    <div class="d-flex">
        @include('partials.sidebar')
    </div>
    <div class="d-flex">
        @include('partials.sidebar')

        {{-- This container ensures your content doesn't overlap the fixed sidebar --}}
        <div class="main-content w-100">
            @yield('content')
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts') {{-- For page-specific JS --}}
</body>
</html>
