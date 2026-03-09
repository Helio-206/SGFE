<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>SGFE — Sistema de Gestão Financeira do Estado</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="font-family:'Inter',sans-serif;">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div class="text-center">
                <a href="/" class="inline-flex flex-col items-center gap-2">
                    <img src="{{ asset('images/brasao-angola.png') }}" alt="Brasão de Angola" class="w-20 h-20 object-contain">
                    <div>
                        <div class="text-lg font-bold text-gray-800 tracking-wide">SGFE</div>
                        <div class="text-xs text-gray-500">Sistema de Gestão Financeira do Estado</div>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>

            {{-- MINFIN footer --}}
            <div class="mt-8 mb-4">
                <img src="{{ asset('images/minfin-logo.svg') }}" alt="Ministério das Finanças de Angola" class="h-10 opacity-70">
            </div>
        </div>
    </body>
</html>
