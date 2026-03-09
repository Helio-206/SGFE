<x-app-layout>
    <x-slot name="header">
        Nova Classificação Económica
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
            <form method="POST" action="{{ route('admin.classificacoes.store') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="cod_classe" class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                        <input type="text" name="cod_classe" id="cod_classe" value="{{ old('cod_classe') }}" required
                               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                               placeholder="01.01">
                        @error('cod_classe') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="tipo_receita" class="block text-sm font-medium text-gray-700 mb-1">Tipo *</label>
                        <select name="tipo_receita" id="tipo_receita" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">-- Selecione --</option>
                            <option value="Impostos">Impostos</option>
                            <option value="Taxas">Taxas</option>
                            <option value="Contribuições">Contribuições</option>
                            <option value="Receitas Patrimoniais">Receitas Patrimoniais</option>
                            <option value="Transferências">Transferências</option>
                            <option value="Receitas Petrolíferas">Receitas Petrolíferas</option>
                            <option value="Despesas Correntes - Pessoal">Despesas Correntes - Pessoal</option>
                            <option value="Despesas Correntes - Bens e Serviços">Despesas Correntes - Bens e Serviços</option>
                            <option value="Despesas Correntes - Juros">Despesas Correntes - Juros</option>
                            <option value="Despesas Correntes - Transferências">Despesas Correntes - Transferências</option>
                            <option value="Despesas de Capital - Investimentos">Despesas de Capital - Investimentos</option>
                            <option value="Despesas de Capital - Transferências">Despesas de Capital - Transferências</option>
                            <option value="Despesas de Capital - Outras">Despesas de Capital - Outras</option>
                        </select>
                        @error('tipo_receita') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="descricao" class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                    <input type="text" name="descricao" id="descricao" value="{{ old('descricao') }}" required
                           class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('descricao') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.classificacoes.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg">Cancelar</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm">Criar Classificação</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
