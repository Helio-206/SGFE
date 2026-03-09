<x-app-layout>
    <x-slot name="header">
        Distribuição de Tectos Orçamentais — {{ $anoFiscal }}
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.orcamentos.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Atribuir Tecto
        </a>
    </div>

    @if ($instituicoesSemOrcamento->count() > 0)
        <div class="mb-4 bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm">
            <strong>Atenção:</strong> {{ $instituicoesSemOrcamento->count() }} instituição(ões) ainda não possui(em) orçamento para {{ $anoFiscal }}.
        </div>
    @endif

    <!-- Resumo Geral -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-blue-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Aprovado</div>
            <div class="text-xl sm:text-2xl font-bold text-blue-700">{{ number_format($orcamentos->sum('valor_total'), 2, ',', '.') }} <span class="text-sm font-normal text-gray-400">AOA</span></div>
        </div>
        <div class="bg-white border border-red-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Total Executado</div>
            <div class="text-xl sm:text-2xl font-bold text-red-700">{{ number_format($orcamentos->sum('despesas_executadas'), 2, ',', '.') }} <span class="text-sm font-normal text-gray-400">AOA</span></div>
        </div>
        <div class="bg-white border border-green-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Saldo Disponível</div>
            <div class="text-xl sm:text-2xl font-bold text-green-700">{{ number_format($orcamentos->sum('saldo_disponivel'), 2, ',', '.') }} <span class="text-sm font-normal text-gray-400">AOA</span></div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Instituição</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Tecto Aprovado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Despesas</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Saldo</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">% Execução</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orcamentos as $orc)
                        @php
                            $percentual = $orc->valor_total > 0 ? ($orc->despesas_executadas / $orc->valor_total) * 100 : 0;
                            $badgeColor = $percentual > 90 ? 'bg-red-100 text-red-700' : ($percentual > 75 ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700');
                        @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $orc->instituicao->codigo }} — {{ $orc->instituicao->nome }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($orc->valor_total, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($orc->despesas_executadas, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold {{ $orc->saldo_disponivel >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($orc->saldo_disponivel, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $badgeColor }}">{{ number_format($percentual, 1) }}%</span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right space-x-2">
                            <a href="{{ route('admin.orcamentos.show', $orc) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
                            <a href="{{ route('admin.orcamentos.edit', $orc) }}" class="text-amber-600 hover:text-amber-800 font-medium">Editar</a>
                            <form action="{{ route('admin.orcamentos.destroy', $orc) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Nenhum orçamento atribuído para este ano fiscal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
