<x-app-layout>
    <x-slot name="header">
        Orçamento: {{ $orcamento->instituicao->nome }} — {{ $orcamento->ano_fiscal }}
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.orcamentos.edit', $orcamento) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            Editar Tecto
        </a>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-blue-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Tecto Aprovado</div>
            <div class="text-xl sm:text-2xl font-bold text-blue-700">{{ number_format($orcamento->valor_total, 2, ',', '.') }}</div>
            <div class="text-xs text-gray-400">AOA</div>
        </div>
        <div class="bg-white border border-red-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Executado</div>
            <div class="text-xl sm:text-2xl font-bold text-red-700">{{ number_format($totalExecutado, 2, ',', '.') }}</div>
            <div class="text-xs text-gray-400">AOA ({{ number_format($percentualExecucao, 1) }}%)</div>
        </div>
        <div class="bg-white border border-green-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Saldo Disponível</div>
            <div class="text-xl sm:text-2xl font-bold {{ $saldoDisponivel >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($saldoDisponivel, 2, ',', '.') }}</div>
            <div class="text-xs text-gray-400">AOA</div>
        </div>
    </div>

    <!-- Execução por Estado -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-4">Execução Orçamental por Estado</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach(['PENDENTE_CABIMENTADA', 'LIQUIDADA_APROVADA', 'PAGA'] as $estado)
                @php
                    $cor = match($estado) {
                        'PENDENTE_CABIMENTADA' => 'yellow',
                        'LIQUIDADA_APROVADA' => 'blue',
                        'PAGA' => 'green',
                    };
                @endphp
                <div class="border border-{{ $cor }}-200 rounded-lg p-4 bg-{{ $cor }}-50">
                    <div class="text-xs font-medium text-gray-600">{{ str_replace('_', ' ', $estado) }}</div>
                    <div class="text-lg font-bold text-{{ $cor }}-700">{{ number_format($despesas->get($estado, 0), 2, ',', '.') }} AOA</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Detalhes -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase mb-4">Detalhes</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Instituição</dt>
                <dd class="text-sm text-gray-900">{{ $orcamento->instituicao->codigo }} — {{ $orcamento->instituicao->nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Ano Fiscal</dt>
                <dd class="text-sm text-gray-900">{{ $orcamento->ano_fiscal }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Atribuído por</dt>
                <dd class="text-sm text-gray-900">{{ $orcamento->usuario->nome }}</dd>
            </div>
            <div>
                <dt class="text-xs font-semibold text-gray-500 uppercase">Data de Criação</dt>
                <dd class="text-sm text-gray-900">{{ $orcamento->created_at->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    <a href="{{ route('admin.orcamentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">
        &larr; Voltar à Lista
    </a>
</x-app-layout>
