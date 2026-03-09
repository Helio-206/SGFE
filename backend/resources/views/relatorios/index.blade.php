<x-app-layout>
    <x-slot name="header">
        Relatórios e Fiscalização
    </x-slot>

    {{-- ═══ EXPORT BUTTONS ═══ --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <a href="{{ route('relatorios.exportar.pdf') }}"
           class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m.75 12l3 3m0 0l3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            Resumo Financeiro (PDF)
        </a>
        <a href="{{ route('relatorios.despesa.natureza') }}"
           class="inline-flex items-center gap-2 bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            Despesa por Natureza (PDF)
        </a>
    </div>

    {{-- ═══ EXCEL EXPORT WITH DATE RANGE ═══ --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 12.75v-1.5c0-.621.504-1.125 1.125-1.125m18.375 2.625V5.625m0 12.75c0 .621-.504 1.125-1.125 1.125m1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125m0 3.75h-7.5A1.125 1.125 0 0112 18.375m9.75-12.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125m19.5 0v1.5c0 .621-.504 1.125-1.125 1.125M2.25 5.625v1.5c0 .621.504 1.125 1.125 1.125m0 0h17.25m-17.25 0h7.5c.621 0 1.125.504 1.125 1.125M3.375 8.25c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125m17.25-3.75h-7.5c-.621 0-1.125.504-1.125 1.125m8.625-1.125c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125m-17.25 0h7.5m-7.5 0c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125M12 10.875v-1.5m0 1.5c0 .621-.504 1.125-1.125 1.125M12 10.875c0 .621.504 1.125 1.125 1.125m-2.25 0c.621 0 1.125.504 1.125 1.125M10.875 12c-.621 0-1.125.504-1.125 1.125M12 12c.621 0 1.125.504 1.125 1.125m0 0v1.5c0 .621-.504 1.125-1.125 1.125m0 0c-.621 0-1.125.504-1.125 1.125"/></svg>
            Mapa de Receitas via RUPE (Exportação Excel)
        </h3>
        <form method="GET" action="{{ route('relatorios.exportar.receitas.excel') }}" class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="data_inicio" class="rounded-lg border-gray-300 text-sm" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="data_fim" class="rounded-lg border-gray-300 text-sm" />
            </div>
            <button type="submit" class="inline-flex items-center gap-2 bg-green-700 hover:bg-green-800 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Exportar Excel
            </button>
        </form>
    </div>

    {{-- ═══ ADVANCED FILTERS ═══ --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z"/></svg>
            Filtros Avançados
        </h3>
        <form method="GET" action="{{ route('relatorios.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                @if($instituicoes->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Unidade Orçamental</label>
                    <select name="id_inst" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">Todas</option>
                        @foreach($instituicoes as $inst)
                            <option value="{{ $inst->id_inst }}" {{ (string)$idInst === (string)$inst->id_inst ? 'selected' : '' }}>
                                {{ $inst->codigo }} — {{ $inst->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Classificação (cod_classe)</label>
                    <input type="text" name="cod_classe" value="{{ $codClasse }}" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Ex: 01.01" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Valor Mínimo (AOA)</label>
                    <input type="number" step="0.01" name="valor_min" value="{{ $valorMin }}" class="w-full rounded-lg border-gray-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Valor Máximo (AOA)</label>
                    <input type="number" step="0.01" name="valor_max" value="{{ $valorMax }}" class="w-full rounded-lg border-gray-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                    <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="w-full rounded-lg border-gray-300 text-sm" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                    <input type="date" name="data_fim" value="{{ $dataFim }}" class="w-full rounded-lg border-gray-300 text-sm" />
                </div>
            </div>
            <div class="flex items-center gap-3 mt-4">
                <button type="submit" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-5 rounded-lg shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                    Filtrar
                </button>
                <a href="{{ route('relatorios.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium">Limpar filtros</a>
            </div>
        </form>
    </div>

    {{-- ═══ RECEITAS TABLE ═══ --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Transações de Receita</h3>
        </div>
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">RUPE</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Instituição</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Fonte</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Classificação</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor (AOA)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($receitas as $receita)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $receita->data_registro->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-blue-700 text-xs">{{ $receita->codigo_rupe }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $receita->instituicao?->codigo }} — {{ $receita->instituicao?->nome }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ $receita->font_receita === 'Petrolífera' ? 'bg-blue-100 text-blue-700' : ($receita->font_receita === 'Não Petrolífera' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700') }}">
                                {{ $receita->font_receita }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $receita->classificacaoEconomica?->cod_classe }}</td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-800">{{ number_format($receita->valor_arrecadado, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Sem receitas encontradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $receitas->appends(request()->query())->links() }}</div>
    </div>

    {{-- ═══ DESPESAS TABLE ═══ --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Transações de Despesa</h3>
        </div>
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Instituição</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor (AOA)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($despesas as $despesa)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $despesa->data_registro->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $despesa->instituicao?->codigo }} — {{ $despesa->instituicao?->nome }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($despesa->descricao, 50) }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold
                                {{ $despesa->estado === 'PENDENTE_CABIMENTADA' ? 'bg-yellow-100 text-yellow-700' : ($despesa->estado === 'LIQUIDADA_APROVADA' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700') }}">
                                {{ $despesa->estado }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-800">{{ number_format($despesa->valor_bruto, 2, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Sem despesas encontradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $despesas->appends(request()->query())->links() }}</div>
    </div>
</x-app-layout>
