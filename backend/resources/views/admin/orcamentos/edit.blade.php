<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Tecto Orçamental') }} — {{ $orcamento->instituicao->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.orcamentos.update', $orcamento) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4 bg-gray-50 p-4 rounded">
                            <dl class="grid grid-cols-2 gap-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Instituição</dt>
                                    <dd class="text-sm text-gray-900">{{ $orcamento->instituicao->nome }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Ano Fiscal</dt>
                                    <dd class="text-sm text-gray-900">{{ $orcamento->ano_fiscal }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="mb-4">
                            <label for="valor_total" class="block text-sm font-medium text-gray-700">Tecto Orçamental (AOA) *</label>
                            <input type="number" name="valor_total" id="valor_total" value="{{ old('valor_total', $orcamento->valor_total) }}" required min="0" step="0.01"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('valor_total')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-sm text-gray-500">
                                O novo valor não pode ser inferior ao valor já executado.
                            </p>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.orcamentos.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Atualizar Tecto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
