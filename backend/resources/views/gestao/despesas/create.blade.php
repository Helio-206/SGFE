<x-app-layout>
    <x-slot name="header">
        Nova NCD (Cabimentação)
    </x-slot>

    <div class="max-w-3xl">
        <div class="mb-4 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm">
            <strong>Saldo disponível {{ $anoFiscal }}:</strong> {{ number_format($saldoDisponivel, 2, ',', '.') }} AOA
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
            <div class="p-6">
                <form method="POST" action="{{ route('gestao.despesas.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1" for="descricao">Descrição *</label>
                        <input id="descricao" type="text" name="descricao" maxlength="150" value="{{ old('descricao') }}" required class="w-full rounded-lg border-gray-300 text-sm" />
                        @error('descricao') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1" for="data_registro">Data da NCD *</label>
                            <input id="data_registro" type="date" name="data_registro" value="{{ old('data_registro', now()->format('Y-m-d')) }}" required class="w-full rounded-lg border-gray-300 text-sm" />
                            @error('data_registro') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1" for="valor_bruto">Valor Cabimentado (AOA) *</label>
                            <input id="valor_bruto" type="number" step="0.01" min="0.01" name="valor_bruto" value="{{ old('valor_bruto') }}" required class="w-full rounded-lg border-gray-300 text-sm" />
                            @error('valor_bruto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-1" for="id_classe">Classificação Económica</label>
                        <select id="id_classe" name="id_classe" class="w-full rounded-lg border-gray-300 text-sm">
                            <option value="">— Seleccionar rubrica —</option>
                            @foreach($classificacoes as $classe)
                                <option value="{{ $classe->id_classe }}" {{ old('id_classe') == $classe->id_classe ? 'selected' : '' }}>
                                    {{ $classe->cod_classe }} — {{ $classe->descricao }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_classe') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <a href="{{ route('gestao.despesas.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium py-2 px-4">Cancelar</a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2 px-5 rounded-lg shadow-sm">Emitir NCD</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
