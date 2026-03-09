<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Orçamento: {{ $orcamento->instituicao->nome }} — {{ $orcamento->ano_fiscal }}
            </h2>
            <a href="{{ route('admin.orcamentos.edit', $orcamento) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Editar Tecto
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-blue-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Tecto Aprovado</div>
                    <div class="text-3xl font-bold text-blue-600">
                        {{ number_format($orcamento->valor_total, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500">AOA</div>
                </div>

                <div class="bg-red-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Total Executado</div>
                    <div class="text-3xl font-bold text-red-600">
                        {{ number_format($totalExecutado, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500">AOA ({{ number_format($percentualExecucao, 1) }}%)</div>
                </div>

                <div class="bg-green-50 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-600 mb-1">Saldo Disponível</div>
                    <div class="text-3xl font-bold {{ $saldoDisponivel >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($saldoDisponivel, 2, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500">AOA</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Execução Orçamental por Estado</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach(['pendente', 'aprovada', 'executada', 'rejeitada'] as $estado)
                            <div class="border rounded p-4">
                                <div class="text-sm text-gray-600">{{ ucfirst($estado) }}</div>
                                <div class="text-xl font-bold">
                                    {{ number_format($despesas->get($estado, 0), 2, ',', '.') }} AOA
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Detalhes</h3>
                    <dl class="grid grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Instituição</dt>
                            <dd class="text-sm text-gray-900">{{ $orcamento->instituicao->codigo }} — {{ $orcamento->instituicao->nome }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Ano Fiscal</dt>
                            <dd class="text-sm text-gray-900">{{ $orcamento->ano_fiscal }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Atribuído por</dt>
                            <dd class="text-sm text-gray-900">{{ $orcamento->usuario->nome }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Data de Criação</dt>
                            <dd class="text-sm text-gray-900">{{ $orcamento->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.orcamentos.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Voltar à Lista
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
