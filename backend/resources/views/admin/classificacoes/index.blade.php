<x-app-layout>
    <x-slot name="header">
        Classificações Económicas (OGE 2025)
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.classificacoes.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nova Classificação
        </a>
    </div>

    <!-- Filtros por Tipo -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-4 mb-4">
        <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Filtrar por Tipo</h3>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.classificacoes.index') }}"
               class="px-3 py-1 rounded-lg text-sm font-medium {{ !$tipo ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                Todos
            </a>
            @foreach ($tipos as $t)
                <a href="{{ route('admin.classificacoes.index', ['tipo' => $t->tipo_receita]) }}"
                   class="px-3 py-1 rounded-lg text-sm font-medium {{ $tipo == $t->tipo_receita ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                    {{ $t->tipo_receita }} ({{ $t->total }})
                </a>
            @endforeach
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Código</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Descrição</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($classificacoes as $cls)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $cls->cod_classe }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $cls->descricao }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                {{ str_contains($cls->tipo_receita, 'Impostos') ? 'bg-blue-100 text-blue-700' :
                                   (str_contains($cls->tipo_receita, 'Despesas Correntes') ? 'bg-red-100 text-red-700' :
                                   (str_contains($cls->tipo_receita, 'Despesas de Capital') ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ $cls->tipo_receita }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right space-x-2">
                            <a href="{{ route('admin.classificacoes.edit', $cls) }}" class="text-amber-600 hover:text-amber-800 font-medium">Editar</a>
                            <form action="{{ route('admin.classificacoes.destroy', $cls) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-gray-400">Nenhuma classificação encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $classificacoes->links() }}</div>
    </div>
</x-app-layout>
