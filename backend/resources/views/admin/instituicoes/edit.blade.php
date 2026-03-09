<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Instituição') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.instituicoes.update', $instituicao) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="codigo" class="block text-sm font-medium text-gray-700">Código UO *</label>
                            <input type="text" name="codigo" id="codigo" value="{{ old('codigo', $instituicao->codigo) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('codigo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="nome" class="block text-sm font-medium text-gray-700">Nome *</label>
                            <input type="text" name="nome" id="nome" value="{{ old('nome', $instituicao->nome) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('nome')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tipo" class="block text-sm font-medium text-gray-700">Tipo *</label>
                            <select name="tipo" id="tipo" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Ministério" {{ old('tipo', $instituicao->tipo) == 'Ministério' ? 'selected' : '' }}>Ministério</option>
                                <option value="Governo Provincial" {{ old('tipo', $instituicao->tipo) == 'Governo Provincial' ? 'selected' : '' }}>Governo Provincial</option>
                                <option value="Administração Municipal" {{ old('tipo', $instituicao->tipo) == 'Administração Municipal' ? 'selected' : '' }}>Administração Municipal</option>
                                <option value="Instituto Público" {{ old('tipo', $instituicao->tipo) == 'Instituto Público' ? 'selected' : '' }}>Instituto Público</option>
                                <option value="Empresa Pública" {{ old('tipo', $instituicao->tipo) == 'Empresa Pública' ? 'selected' : '' }}>Empresa Pública</option>
                            </select>
                            @error('tipo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="responsavel" class="block text-sm font-medium text-gray-700">Responsável *</label>
                            <input type="text" name="responsavel" id="responsavel" value="{{ old('responsavel', $instituicao->responsavel) }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('responsavel')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.instituicoes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Atualizar Instituição
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
