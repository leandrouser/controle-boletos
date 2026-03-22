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
        
        <style>
            /* Ajuste para o botão não "pular" de tamanho no loading */
            .btn-loading-state {
                min-width: 140px;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-light">
        <div class="min-h-screen">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-white shadow-sm mb-4">
                    <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="container py-4">
                {{-- Mensagem de Sucesso --}}
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Mensagem de Erro (Importante para Sessão Expirada) --}}
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
            // Lógica Global de Loading nos Botões
            document.addEventListener('submit', function (e) {
                const form = e.target;
                const btn = form.querySelector('button[type="submit"]');
                
                // Só aplica se o botão existir e não tiver a classe 'no-loading'
                if (btn && !btn.classList.contains('no-loading')) {
                    // Previne cliques múltiplos desativando o botão
                    btn.disabled = true;
                    
                    // Adiciona classe para controle de estilo se necessário
                    btn.classList.add('btn-loading-state');
                    
                    // Altera o conteúdo para o Spinner do Bootstrap
                    btn.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processando...
                    `;
                }
            });
        </script>
    </body>
</html>