{{-- ═══ TOPBAR ═══ --}}
<header class="sgfe-topbar">
    <div class="flex items-center justify-between px-4 sm:px-6 lg:px-8 h-16">
        {{-- Mobile hamburger --}}
        <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700 mr-3">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
            </svg>
        </button>

        {{-- Page title --}}
        <div class="flex-1">
            @isset($header)
                <div class="text-lg font-semibold text-gray-800">{{ $header }}</div>
            @endisset
        </div>

        {{-- Right side --}}
        <div class="flex items-center gap-4">
            {{-- Notification bell --}}
            @if(in_array(Auth::user()->role, ['admin', 'gestor']))
            @php
                $pendingCount = \App\Models\TransacaoDespesa::query()
                    ->when(Auth::user()->isGestor(), fn($q) => $q->where('id_inst', Auth::user()->id_inst))
                    ->where('estado', 'PENDENTE_CABIMENTADA')
                    ->count();
            @endphp
            <div x-data="{ notifOpen: false }" class="relative">
                <button @click="notifOpen = !notifOpen" class="relative text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                    </svg>
                    @if($pendingCount > 0)
                    <span class="absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center badge-pulse">
                        {{ $pendingCount > 99 ? '99+' : $pendingCount }}
                    </span>
                    @endif
                </button>
                {{-- Dropdown --}}
                <div x-show="notifOpen" @click.away="notifOpen = false" x-transition
                     class="absolute right-0 mt-2 w-72 sm:w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <p class="text-sm font-semibold text-gray-800">Cabimentações Pendentes</p>
                    </div>
                    <div class="max-h-64 overflow-y-auto">
                        @if($pendingCount > 0)
                        <div class="px-4 py-3 text-sm text-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-yellow-500 flex-shrink-0"></span>
                                <span><strong>{{ $pendingCount }}</strong> despesa(s) aguardam aprovação (NLD)</span>
                            </div>
                            <a href="{{ route('gestao.despesas.index') }}" class="mt-2 inline-block text-sm text-blue-600 hover:underline font-medium">
                                Ver despesas pendentes →
                            </a>
                        </div>
                        @else
                        <div class="px-4 py-6 text-center text-sm text-gray-400">
                            Sem notificações pendentes.
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Profile dropdown --}}
            <div x-data="{ profileOpen: false }" class="relative hidden sm:block">
                <button @click="profileOpen = !profileOpen" class="flex items-center gap-2 text-sm text-gray-600 hover:text-gray-800">
                    <span class="font-medium">{{ Auth::user()->nome ?? Auth::user()->name }}</span>
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="profileOpen" @click.away="profileOpen = false" x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-200 z-50 py-1">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Perfil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Terminar Sessão</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
