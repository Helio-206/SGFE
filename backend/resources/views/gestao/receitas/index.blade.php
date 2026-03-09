<x-app-layout>
    <x-slot name="header">
        Arrecadação de Receitas
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('gestao.receitas.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nova Receita
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fonte</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">RUPE</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Classificação</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor (AOA)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($receitas as $receita)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $receita->data_registro->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $receita->font_receita === 'Petrolífera' ? 'bg-blue-100 text-blue-700' : ($receita->font_receita === 'Não Petrolífera' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                {{ $receita->font_receita }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-blue-700 text-xs">{{ $receita->codigo_rupe }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $receita->classificacaoEconomica?->cod_classe }} — {{ $receita->classificacaoEconomica?->descricao }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-green-700">{{ number_format($receita->valor_arrecadado, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Sem receitas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $receitas->links() }}</div>
    </div>
</x-app-layout>
