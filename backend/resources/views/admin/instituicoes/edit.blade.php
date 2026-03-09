<x-app-layout>
    <x-slot name="header">
        Editar Instituição
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.instituicoes.update', $instituicao) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="codigo_uo" class="block text-sm font-medium text-gray-700 mb-1">Código UO (4 a 6 dígitos) *</label>
                        <input type="text" name="codigo_uo" id="codigo_uo" value="{{ old('codigo_uo', $instituicao->codigo) }}" required pattern="\d{4,6}" maxlength="6"
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @error('codigo_uo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tipo" class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                        <select name="tipo" id="tipo" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="Ministério" {{ old('tipo', $instituicao->tipo) == 'Ministério' ? 'selected' : '' }}>Ministério</option>
                            <option value="Governo Provincial" {{ old('tipo', $instituicao->tipo) == 'Governo Provincial' ? 'selected' : '' }}>Governo Provincial</option>
                            <option value="Administração Municipal" {{ old('tipo', $instituicao->tipo) == 'Administração Municipal' ? 'selected' : '' }}>Administração Municipal</option>
                            <option value="Instituto Público" {{ old('tipo', $instituicao->tipo) == 'Instituto Público' ? 'selected' : '' }}>Instituto Público</option>
                            <option value="Empresa Pública" {{ old('tipo', $instituicao->tipo) == 'Empresa Pública' ? 'selected' : '' }}>Empresa Pública</option>
                        </select>
                        @error('tipo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="nome" class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" id="nome" value="{{ old('nome', $instituicao->nome) }}" required
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('nome') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label for="responsavel" class="block text-sm font-medium text-gray-700 mb-1">Responsável *</label>
                    <input type="text" name="responsavel" id="responsavel" value="{{ old('responsavel', $instituicao->responsavel) }}" required
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('responsavel') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.instituicoes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm">Atualizar Instituição</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
