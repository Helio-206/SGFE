<x-app-layout>
    <x-slot name="header">
        Nova Receita
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('gestao.receitas.store') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="font_receita">Fonte da Receita *</label>
                        <select id="font_receita" name="font_receita" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Selecione --</option>
                            <option value="Petrolífera" @selected(old('font_receita') === 'Petrolífera')>Petrolífera</option>
                            <option value="Não Petrolífera" @selected(old('font_receita') === 'Não Petrolífera')>Não Petrolífera</option>
                            <option value="Patrimonial" @selected(old('font_receita') === 'Patrimonial')>Patrimonial</option>
                        </select>
                        @error('font_receita') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="id_classe">Classificação Económica *</label>
                        <select id="id_classe" name="id_classe" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Selecione --</option>
                            @foreach($classificacoes as $classe)
                                <option value="{{ $classe->id_classe }}" @selected((int) old('id_classe') === $classe->id_classe)>
                                    {{ $classe->cod_classe }} — {{ $classe->descricao }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_classe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="data_registro">Data de Registo *</label>
                        <input id="data_registro" type="date" name="data_registro" value="{{ old('data_registro', now()->format('Y-m-d')) }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        @error('data_registro') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="valor_arrecadado">Valor Arrecadado (AOA) *</label>
                        <input id="valor_arrecadado" type="number" step="0.01" min="0.01" name="valor_arrecadado" value="{{ old('valor_arrecadado') }}" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" />
                        @error('valor_arrecadado') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('gestao.receitas.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm">Registrar Receita</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
