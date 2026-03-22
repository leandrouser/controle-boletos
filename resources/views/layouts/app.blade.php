<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="shortcut icon" type="image/png" href="{{ asset('img/logo.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <script>
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <style>
            .btn-loading-state {
                min-width: 140px;
            }

            .dark body { background-color: #09090b !important; color: #f8fafc !important; }
            .dark .bg-light { background-color: #09090b !important; }
            .dark .bg-white { background-color: #09090b !important; color: #f8fafc !important; }
            .dark .text-dark { color: #f8fafc !important; }
            .dark .text-muted { color: #94a3b8 !important; }

            .dark .card { background-color: #09090b; border-color: #27272a !important; color: #f8fafc; }
            .dark .table { color: #f8fafc; border-color: #27272a; }
            .dark .table-hover tbody tr:hover { background-color: #18181b; color: #fff; }
            .dark .border-bottom, .dark .border-top, .dark .card-header, .dark .card-footer { border-color: #27272a !important; }

            .dark .form-control, .dark .form-select {
                background-color: #09090b;
                border-color: #27272a;
                color: #f8fafc;
            }
            .dark .form-control:focus { background-color: #0c0c0e; border-color: #52525b; color: #fff; box-shadow: none; }

            .dark .page-link { background-color: #09090b; border-color: #27272a; color: #f8fafc; }
            .dark .alert-success { background-color: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
            .dark .alert-danger { background-color: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
            .dark .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

            body { transition: background-color 0.3s ease, color 0.3s ease; }
        </style>
    </head>
    <body class="font-sans antialiased bg-light dark:bg-[#09090b] text-slate-900 dark:text-slate-100">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white dark:bg-[#09090b] shadow-sm border-b dark:border-zinc-800 mb-4 transition-colors">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="container py-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')

                @if(isset($slot))
                    {{ $slot }}
                @endif
            </main>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://github.com/mufflow/simple-mask-money/releases/download/v3.0.0/simple-mask-money.js"></script>

        <script>
            document.addEventListener('submit', function (e) {
    const form = e.target;

    if (form.style.display === 'none') return;

    const btn = form.querySelector('button[type="submit"]');

    if (btn && !btn.classList.contains('no-loading')) {
        btn.disabled = true;
        btn.classList.add('btn-loading-state');
        btn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Processando...
        `;
    }
});
        </script>
    </body>
</html>
