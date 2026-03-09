<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Nova Classificação Económica') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.classificacoes.store') }}">
                        @csrf

                        <div class="mb-4">
                            <label for="cod_classe" class="block text-sm font-medium text-gray-700">Código *</label>
                            <input type="text" name="cod_classe" id="cod_classe" value="{{ old('cod_classe') }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                   placeholder="01.01">
                            @error('cod_classe')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição *</label>
                            <input type="text" name="descricao" id="descricao" value="{{ old('descricao') }}" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('descricao')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="tipo_receita" class="block text-sm font-medium text-gray-700">Tipo *</label>
                            <select name="tipo_receita" id="tipo_receita" required 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                            @error('tipo_receita')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.classificacoes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
                                Cancelar
                            </a>
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Criar Classificação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
