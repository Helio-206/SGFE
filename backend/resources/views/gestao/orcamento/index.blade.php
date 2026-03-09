<x-app-layout>
    <x-slot name="header">
        Tecto Orçamental — {{ $anoFiscal }}
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-blue-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Valor Total Recebido</div>
            <div class="text-xl sm:text-2xl font-bold text-blue-700">{{ number_format($valorTotalRecebido, 2, ',', '.') }} <span class="text-sm font-normal text-gray-400">AOA</span></div>
        </div>
        <div class="bg-white border border-yellow-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Despesas Comprometidas</div>
            <div class="text-xl sm:text-2xl font-bold text-yellow-700">{{ number_format($despesasComprometidas, 2, ',', '.') }} <span class="text-sm font-normal text-gray-400">AOA</span></div>
        </div>
        <div class="bg-white border border-green-200 rounded-xl p-5 shadow-sm">
            <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Saldo para Gastar</div>
            <div class="text-xl sm:text-2xl font-bold text-green-700">{{ number_format($saldoRestante, 2, ',', '.') }} <span class="text-sm font-normal text-gray-400">AOA</span></div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6">
        @if($orcamento)
            <p class="text-sm text-gray-700"><strong>Instituição:</strong> {{ $orcamento->instituicao->codigo }} — {{ $orcamento->instituicao->nome }}</p>
            <p class="text-sm text-gray-700 mt-1"><strong>Tecto definido por:</strong> {{ $orcamento->usuario?->nome ?? 'N/D' }}</p>
        @else
            <p class="text-sm text-gray-500">A sua instituição ainda não possui tecto orçamental para {{ $anoFiscal }}.</p>
        @endif
    </div>
</x-app-layout>
