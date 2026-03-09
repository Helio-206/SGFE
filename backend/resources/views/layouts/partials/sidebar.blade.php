{{-- ═══ SIDEBAR ═══ --}}
<aside class="sgfe-sidebar flex flex-col" :class="{ 'open': sidebarOpen }">
    {{-- Brasão + Título --}}
    <div class="px-5 py-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/brasao-angola.png') }}" alt="Brasão de Angola" class="w-10 h-10 flex-shrink-0 object-contain">
            <div>
                <div class="text-white font-bold text-sm tracking-wide">SGFE</div>
                <div class="text-slate-400 text-xs">República de Angola</div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 py-4 space-y-1 overflow-y-auto">
        <div class="nav-section">Principal</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
            </svg>
            Painel Principal
        </a>

        @if(in_array(Auth::user()->role, ['admin', 'gestor']))
        <div class="nav-section">Execução Orçamental</div>
        <a href="{{ route('gestao.tecto.index') }}" class="nav-item {{ request()->routeIs('gestao.tecto.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
            </svg>
            Tecto Orçamental
        </a>
        <a href="{{ route('gestao.receitas.index') }}" class="nav-item {{ request()->routeIs('gestao.receitas.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Receitas (RUPE)
        </a>
        <a href="{{ route('gestao.despesas.index') }}" class="nav-item {{ request()->routeIs('gestao.despesas.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
            Despesas (NCD)
        </a>
        @endif

        @if(Auth::user()->role === 'admin')
        <div class="nav-section">Administração</div>
        <a href="{{ route('admin.instituicoes.index') }}" class="nav-item {{ request()->routeIs('admin.instituicoes.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
            </svg>
            Unidades Orçamentais
        </a>
        <a href="{{ route('admin.orcamentos.index') }}" class="nav-item {{ request()->routeIs('admin.orcamentos.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z"/>
            </svg>
            Tectos / Orçamentos
        </a>
        <a href="{{ route('admin.classificacoes.index') }}" class="nav-item {{ request()->routeIs('admin.classificacoes.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
            </svg>
            Classificações
        </a>
        @endif

        @if(in_array(Auth::user()->role, ['admin', 'gestor', 'auditor']))
        <div class="nav-section">Fiscalização</div>
        <a href="{{ route('relatorios.index') }}" class="nav-item {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
            </svg>
            Relatórios
        </a>
        @endif
    </nav>

    {{-- User footer --}}
    <div class="border-t border-white/10 p-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr(Auth::user()->nome ?? Auth::user()->name ?? 'U', 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm text-white font-medium truncate">{{ Auth::user()->nome ?? Auth::user()->name }}</div>
                <div class="text-xs text-slate-400 uppercase">{{ Auth::user()->role }}</div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" title="Terminar sessão" class="text-slate-400 hover:text-red-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
