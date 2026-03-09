<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGFE') }} — Sistema de Gestão Financeira do Estado</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    {{-- ApexCharts --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @include('layouts.partials.styles')
</head>
<body class="font-sans antialiased bg-gray-50" style="font-family:'Inter',sans-serif;">
    <div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

        {{-- Mobile overlay --}}
        <div class="sidebar-overlay" :class="{ 'open': sidebarOpen }" @click="sidebarOpen = false"></div>

        {{-- Sidebar --}}
        @include('layouts.partials.sidebar')

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar --}}
            @include('layouts.partials.topbar')

            {{-- Flash Messages --}}
            @if(session('success'))
            <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif
            @if(session('error'))
            <div class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
                    {{ session('error') }}
                </div>
            </div>
            @endif

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            <footer class="border-t border-gray-200 bg-white px-4 sm:px-6 lg:px-8 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <img src="{{ asset('images/brasao-angola.png') }}" alt="Brasão" class="w-5 h-5 object-contain opacity-50">
                        <span>SGFE — Sistema de Gestão Financeira do Estado &bull; República de Angola</span>
                    </div>
                    <img src="{{ asset('images/minfin-logo.svg') }}" alt="Ministério das Finanças" class="h-8 opacity-60">
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
