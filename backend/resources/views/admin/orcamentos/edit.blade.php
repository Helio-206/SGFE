<x-app-layout>
    <x-slot name="header">
        Editar Tecto Orçamental — {{ $orcamento->instituicao->nome }}
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.orcamentos.update', $orcamento) }}">
                @csrf
                @method('PUT')

                <div class="mb-4 bg-gray-50 border border-gray-200 p-4 rounded-lg">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Instituição</dt>
                            <dd class="text-sm text-gray-900">{{ $orcamento->instituicao->nome }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-gray-500 uppercase">Ano Fiscal</dt>
                            <dd class="text-sm text-gray-900">{{ $orcamento->ano_fiscal }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="mb-4">
                    <label for="valor_total" class="block text-sm font-medium text-gray-700 mb-1">Tecto Orçamental (AOA) *</label>
                    <input type="number" name="valor_total" id="valor_total" value="{{ old('valor_total', $orcamento->valor_total) }}" required min="0" step="0.01"
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('valor_total') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">O novo valor não pode ser inferior ao valor já executado.</p>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.orcamentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm">Atualizar Tecto</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
