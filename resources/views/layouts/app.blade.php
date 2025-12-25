<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Grow Trackeamento</title>
        <link rel="icon" href="{{ asset('/img/logo.png') }}" type="image/x-icon">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Flatpickr CSS -->
        <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css"><!-- ICONES BOTSTRAP -->

        
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /><!-- CSS do Select2 -->

        <style>
            /* Faz o contêiner ocupar a primeira dobra (100vh) */
            .first-fold {
                height: calc(100vh - 50px); /* Desconta o espaço do rodapé */
                display: flex;
                flex-direction: column;
            }

            /* Espaço entre header e conteúdo */
            .content-container {
                flex-grow: 1;
                margin-top: 20px; /* Espaçamento entre o header e o conteúdo */
                display: flex;
                flex-direction: column;
            }

            /* Faz a tabela rolar internamente */
            .table-wrapper {
                flex-grow: 1;
                overflow-y: auto; /* Barra de rolagem vertical */
            }

            /* Rodapé fixo na parte inferior */
            footer {
                height: 50px;
                background-color: #f8f9fa;
                text-align: center;
                padding: 15px;
                position: fixed;
                width: 100%;
                bottom: 0;
                left: 0;
                border-top: 1px solid #dee2e6;
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-100 dark:bg-gray-900">
        <div class=" first-fold"> <!-- Flexbox principal -->
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="content-container">
                {{ $slot }} <!-- O conteúdo flexível que será preenchido -->
            </main>
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><!-- jQuery -->
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script> <!-- Localização em Português -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script><!-- JS do Select2 -->
        
    </body>
</html>
