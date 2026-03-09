<x-app-layout>
    <x-slot name="header">
        Fluxo de Despesa Pública
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('gestao.despesas.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nova NCD (Cabimentação)
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor (AOA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($despesas as $despesa)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $despesa->data_registro->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ Str::limit($despesa->descricao, 60) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-800">{{ number_format($despesa->valor_bruto, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                {{ $despesa->estado === 'PENDENTE_CABIMENTADA' ? 'bg-yellow-100 text-yellow-700' : ($despesa->estado === 'LIQUIDADA_APROVADA' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ $despesa->estado }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            @if($despesa->estado === 'PENDENTE_CABIMENTADA')
                            <form class="inline" method="POST" action="{{ route('gestao.despesas.liquidar', $despesa) }}">
                                @csrf @method('PATCH')
                                <button class="text-blue-600 hover:text-blue-800 font-semibold text-xs">NLD (Aprovar)</button>
                            </form>
                            @endif
                            @if($despesa->estado === 'LIQUIDADA_APROVADA')
                            <form class="inline" method="POST" action="{{ route('gestao.despesas.pagar', $despesa) }}">
                                @csrf @method('PATCH')
                                <button class="text-green-600 hover:text-green-800 font-semibold text-xs">Pagamento</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Sem despesas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $despesas->links() }}</div>
    </div>
</x-app-layout>
