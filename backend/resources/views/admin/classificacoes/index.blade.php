<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Classificações Económicas (OGE 2025)') }}
            </h2>
            <a href="{{ route('admin.classificacoes.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Nova Classificação
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-sm font-semibold mb-3">Filtrar por Tipo</h3>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('admin.classificacoes.index') }}" 
                           class="px-3 py-1 rounded {{ !$tipo ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                            Todos
                        </a>
                        @foreach ($tipos as $t)
                            <a href="{{ route('admin.classificacoes.index', ['tipo' => $t->tipo_receita]) }}" 
                               class="px-3 py-1 rounded {{ $tipo == $t->tipo_receita ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                                {{ $t->tipo_receita }} ({{ $t->total }})
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($classificacoes as $cls)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $cls->cod_classe }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $cls->descricao }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            {{ str_contains($cls->tipo_receita, 'Impostos') ? 'bg-blue-100 text-blue-800' : 
                                               (str_contains($cls->tipo_receita, 'Despesas Correntes') ? 'bg-red-100 text-red-800' : 
                                               (str_contains($cls->tipo_receita, 'Despesas de Capital') ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                                            {{ $cls->tipo_receita }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('admin.classificacoes.edit', $cls) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">Editar</a>
                                        <form action="{{ route('admin.classificacoes.destroy', $cls) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhuma classificação encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $classificacoes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
