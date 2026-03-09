<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $instituicao->codigo }} — {{ $instituicao->nome }}
            </h2>
            <a href="{{ route('admin.instituicoes.edit', $instituicao) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Editar
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Dados da Instituição</h3>
                        <dl class="space-y-2">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tipo</dt>
                                <dd class="text-sm text-gray-900">{{ $instituicao->tipo }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Responsável</dt>
                                <dd class="text-sm text-gray-900">{{ $instituicao->responsavel }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Utilizadores Ativos</dt>
                                <dd class="text-sm text-gray-900">{{ $instituicao->usuarios->count() }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4">Orçamento Atual ({{ date('Y') }})</h3>
                        @if ($orcamentoAtual)
                            <dl class="space-y-2">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Tecto Aprovado</dt>
                                    <dd class="text-lg font-bold text-green-600">{{ number_format($orcamentoAtual->valor_total, 2, ',', '.') }} AOA</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Total de Despesas</dt>
                                    <dd class="text-sm text-gray-900">{{ number_format($totalDespesas, 2, ',', '.') }} AOA</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Saldo Disponível</dt>
                                    <dd class="text-lg font-bold {{ ($orcamentoAtual->valor_total - $totalDespesas) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ number_format($orcamentoAtual->valor_total - $totalDespesas, 2, ',', '.') }} AOA
                                    </dd>
                                </div>
                            </dl>
                        @else
                            <p class="text-sm text-gray-500">Nenhum orçamento atribuído para este ano fiscal.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Utilizadores</h3>
                    @if ($instituicao->usuarios->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($instituicao->usuarios as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $user->nome }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                {{ $user->role == 'admin' ? 'bg-red-100 text-red-800' : ($user->role == 'gestor' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500">Nenhum utilizador associado.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Últimas Despesas</h3>
                    @if ($instituicao->transacoesDespesas->count() > 0)
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($instituicao->transacoesDespesas as $desp)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $desp->data_registro->format('d/m/Y') }}</td>
                                        <td class="px-6 py-4 text-sm">{{ $desp->descricao }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ number_format($desp->valor_bruto, 2, ',', '.') }} AOA</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ $desp->estado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500">Nenhuma despesa registrada.</p>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <a href="{{ route('admin.instituicoes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                    Voltar à Lista
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
