<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Atribuir Tecto Orçamental') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($instituicoes->count() == 0)
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
                            Todas as instituições já possuem orçamento atribuído para o ano {{ $anoFiscal }}.
                            <a href="{{ route('admin.orcamentos.index') }}" class="underline">Voltar à lista</a>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.orcamentos.store') }}">
                            @csrf

                            <div class="mb-4">
                                <label for="id_inst" class="block text-sm font-medium text-gray-700">Instituição *</label>
                                <select name="id_inst" id="id_inst" required 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">-- Selecione a Instituição --</option>
                                    @foreach ($instituicoes as $inst)
                                        <option value="{{ $inst->id_inst }}" {{ old('id_inst') == $inst->id_inst ? 'selected' : '' }}>
                                            {{ $inst->codigo }} — {{ $inst->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_inst')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="ano_fiscal" class="block text-sm font-medium text-gray-700">Ano Fiscal *</label>
                                <input type="number" name="ano_fiscal" id="ano_fiscal" value="{{ old('ano_fiscal', $anoFiscal) }}" required min="2020" max="2050"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('ano_fiscal')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="valor_total" class="block text-sm font-medium text-gray-700">Tecto Orçamental (AOA) *</label>
                                <input type="number" name="valor_total" id="valor_total" value="{{ old('valor_total') }}" required min="0" step="0.01"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       placeholder="0.00">
                                @error('valor_total')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-sm text-gray-500">Este valor define o limite máximo de despesas que a instituição pode executar.</p>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.orcamentos.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                    Cancelar
                                </a>
                                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                    Atribuir Tecto
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
