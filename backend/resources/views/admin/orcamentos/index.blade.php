<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Distribuição de Tectos Orçamentais') }} — {{ $anoFiscal }}
            </h2>
            <a href="{{ route('admin.orcamentos.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Atribuir Tecto
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if ($instituicoesSemOrcamento->count() > 0)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">
                    <strong>Atenção:</strong> {{ $instituicoesSemOrcamento->count() }} instituição(ões) ainda não possui(em) orçamento para {{ $anoFiscal }}.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold mb-2">Resumo Geral</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-blue-50 p-4 rounded">
                                <div class="text-sm text-gray-600">Total Aprovado</div>
                                <div class="text-2xl font-bold text-blue-600">
                                    {{ number_format($orcamentos->sum('valor_total'), 2, ',', '.') }} AOA
                                </div>
                            </div>
                            <div class="bg-red-50 p-4 rounded">
                                <div class="text-sm text-gray-600">Total Executado</div>
                                <div class="text-2xl font-bold text-red-600">
                                    {{ number_format($orcamentos->sum('despesas_executadas'), 2, ',', '.') }} AOA
                                </div>
                            </div>
                            <div class="bg-green-50 p-4 rounded">
                                <div class="text-sm text-gray-600">Saldo Disponível</div>
                                <div class="text-2xl font-bold text-green-600">
                                    {{ number_format($orcamentos->sum('saldo_disponivel'), 2, ',', '.') }} AOA
                                </div>
                            </div>
                        </div>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 mt-6">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instituição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tecto Aprovado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Despesas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">% Execução</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($orcamentos as $orc)
                                @php
                                    $percentual = $orc->valor_total > 0 ? ($orc->despesas_executadas / $orc->valor_total) * 100 : 0;
                                @endphp
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $orc->instituicao->codigo }} — {{ $orc->instituicao->nome }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($orc->valor_total, 2, ',', '.') }} AOA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($orc->despesas_executadas, 2, ',', '.') }} AOA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $orc->saldo_disponivel >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($orc->saldo_disponivel, 2, ',', '.') }} AOA
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                                @php
                                                    $barColor = $percentual > 90 ? 'bg-red-600' : ($percentual > 75 ? 'bg-yellow-600' : 'bg-blue-600');
                                                @endphp
                                                <div class="{{ $barColor }} h-2.5 rounded-full" style="width: {{ min($percentual, 100) }}%"></div>
                                            </div>
                                            <span class="text-xs">{{ number_format($percentual, 1) }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.orcamentos.show', $orc) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Ver</a>
                                        <a href="{{ route('admin.orcamentos.edit', $orc) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</a>
                                        <form action="{{ route('admin.orcamentos.destroy', $orc) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum orçamento atribuído para este ano fiscal.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
