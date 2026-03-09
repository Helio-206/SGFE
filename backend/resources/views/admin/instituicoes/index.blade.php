<x-app-layout>
    <x-slot name="header">
        Gestão de Instituições / Unidades Orçamentais
    </x-slot>

    <div class="flex justify-end mb-4">
        <a href="{{ route('admin.instituicoes.create') }}" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-4 rounded-lg shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nova Instituição
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="responsive-table">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Código UO</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nome</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tipo</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Responsável</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Utilizadores</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($instituicoes as $inst)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $inst->codigo }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $inst->nome }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">{{ $inst->tipo }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $inst->responsavel }}</td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $inst->usuarios_count }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right space-x-2">
                            <a href="{{ route('admin.instituicoes.show', $inst) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver</a>
                            <a href="{{ route('admin.instituicoes.edit', $inst) }}" class="text-amber-600 hover:text-amber-800 font-medium">Editar</a>
                            <form action="{{ route('admin.instituicoes.destroy', $inst) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 font-medium">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Nenhuma instituição cadastrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $instituicoes->links() }}</div>
    </div>
</x-app-layout>
