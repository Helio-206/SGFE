<x-app-layout>
    <x-slot name="header">
        Atribuir Tecto Orçamental
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            @if ($instituicoes->count() == 0)
                <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg text-sm mb-4">
                    Todas as instituições já possuem orçamento atribuído para o ano {{ $anoFiscal }}.
                    <a href="{{ route('admin.orcamentos.index') }}" class="underline font-medium">Voltar à lista</a>
                </div>
            @else
                <form method="POST" action="{{ route('admin.orcamentos.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="id_inst" class="block text-sm font-medium text-gray-700 mb-1">Instituição *</label>
                        <select name="id_inst" id="id_inst" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Selecione a Instituição --</option>
                            @foreach ($instituicoes as $inst)
                                <option value="{{ $inst->id_inst }}" {{ old('id_inst') == $inst->id_inst ? 'selected' : '' }}>
                                    {{ $inst->codigo }} — {{ $inst->nome }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_inst') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ano Fiscal</label>
                            <div class="block w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700 text-sm">{{ $anoFiscal }}</div>
                            <input type="hidden" name="ano_fiscal" value="{{ $anoFiscal }}">
                        </div>

                        <div>
                            <label for="valor_total" class="block text-sm font-medium text-gray-700 mb-1">Tecto Orçamental (AOA) *</label>
                            <input type="number" name="valor_total" id="valor_total" value="{{ old('valor_total') }}" required min="0" step="0.01"
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                   placeholder="0,00">
                            @error('valor_total') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 mb-4">Este valor define o limite máximo de despesas que a instituição pode executar.</p>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.orcamentos.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm">Atribuir Tecto</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
