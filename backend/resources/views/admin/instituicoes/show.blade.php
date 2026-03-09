<x-app-layout>
    <x-slot name="header">
        {{ $instituicao->codigo }} — {{ $instituicao->nome }}
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.instituicoes.edit', $instituicao) }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
            Editar
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Dados da Instituição -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase mb-4">Dados da Instituição</h3>
            <dl class="space-y-3">
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Tipo</dt>
                    <dd class="text-sm text-gray-900">{{ $instituicao->tipo }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Responsável</dt>
                    <dd class="text-sm text-gray-900">{{ $instituicao->responsavel }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold text-gray-500 uppercase">Utilizadores Ativos</dt>
                    <dd class="text-sm text-gray-900">{{ $instituicao->usuarios->count() }}</dd>
                </div>
            </dl>
        </div>

        <!-- Orçamento Atual -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-700 uppercase mb-4">Orçamento Atual ({{ date('Y') }})</h3>
            @if ($orcamentoAtual)
                <dl class="space-y-3">
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase">Tecto Aprovado</dt>
                        <dd class="text-lg font-bold text-green-700">{{ number_format($orcamentoAtual->valor_total, 2, ',', '.') }} AOA</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase">Total de Despesas</dt>
                        <dd class="text-sm text-gray-900">{{ number_format($totalDespesas, 2, ',', '.') }} AOA</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold text-gray-500 uppercase">Saldo Disponível</dt>
                        <dd class="text-lg font-bold {{ ($orcamentoAtual->valor_total - $totalDespesas) >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($orcamentoAtual->valor_total - $totalDespesas, 2, ',', '.') }} AOA
                        </dd>
                    </div>
                </dl>
            @else
                <p class="text-sm text-gray-400">Nenhum orçamento atribuído para este ano fiscal.</p>
            @endif
        </div>
    </div>

    <!-- Utilizadores -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Utilizadores</h3>
        </div>
        @if ($instituicao->usuarios->count() > 0)
            <div class="responsive-table">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Role</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($instituicao->usuarios as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $user->nome }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ $user->role == 'admin' ? 'bg-red-100 text-red-700' : ($user->role == 'gestor' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6"><p class="text-sm text-gray-400">Nenhum utilizador associado.</p></div>
        @endif
    </div>

    <!-- Últimas Despesas -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700 uppercase">Últimas Despesas</h3>
        </div>
        @if ($instituicao->transacoesDespesas->count() > 0)
            <div class="responsive-table">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Data</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Valor</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($instituicao->transacoesDespesas as $desp)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ $desp->data_registro->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ Str::limit($desp->descricao, 50) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-right font-semibold text-gray-900">{{ number_format($desp->valor_bruto, 2, ',', '.') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm">
                                @php $corEstado = match($desp->estado) { 'PAGA' => 'bg-green-100 text-green-700', 'LIQUIDADA_APROVADA' => 'bg-blue-100 text-blue-700', default => 'bg-yellow-100 text-yellow-700' }; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $corEstado }}">{{ $desp->estado }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-6"><p class="text-sm text-gray-400">Nenhuma despesa registrada.</p></div>
        @endif
    </div>

    <a href="{{ route('admin.instituicoes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">
        &larr; Voltar à Lista
    </a>
</x-app-layout>
