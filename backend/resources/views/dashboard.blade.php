<x-app-layout>
    <x-slot name="header">
        Painel Orçamental — OGE {{ $anoFiscal }}
        @if($contexto === 'gestor' && $instituicao)
            <span class="text-sm font-normal text-gray-500 ml-2">{{ $instituicao->codigo }} — {{ $instituicao->nome }}</span>
        @endif
    </x-slot>

    {{-- ═══ KPI CARDS ═══ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        {{-- Tecto Total --}}
        <div class="kpi-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="kpi-label">Tecto Total</div>
                    <div class="kpi-value text-slate-800">{{ number_format($tectoTotal, 2, ',', '.') }}</div>
                    <div class="text-xs text-gray-400 mt-1">AOA</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Valor Pago --}}
        <div class="kpi-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="kpi-label">Valor Gasto (Pago)</div>
                    <div class="kpi-value text-red-700">{{ number_format($valorPago, 2, ',', '.') }}</div>
                    <div class="text-xs text-gray-400 mt-1">AOA</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Valor Cabimentado --}}
        <div class="kpi-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="kpi-label">Cabimentado (Pendente)</div>
                    <div class="kpi-value text-yellow-700">{{ number_format($valorCabimentado, 2, ',', '.') }}</div>
                    <div class="text-xs text-gray-400 mt-1">AOA</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Saldo Disponível --}}
        <div class="kpi-card">
            <div class="flex items-center justify-between">
                <div>
                    <div class="kpi-label">Saldo Disponível</div>
                    <div class="kpi-value text-green-700">{{ number_format($saldoDisponivel, 2, ',', '.') }}</div>
                    <div class="text-xs text-gray-400 mt-1">AOA</div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══ CHARTS ROW ═══ --}}
    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 mb-6">
        {{-- Donut: Execução Orçamental --}}
        <div class="xl:col-span-2 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Execução Orçamental</h3>
            <p class="text-xs text-gray-400 mb-4">Proporção do orçamento já consumido</p>
            <div id="chart-donut" class="flex justify-center"></div>
            @if($percentagemConsumo > 90)
            <div class="mt-3 bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-xs text-red-700 flex items-center gap-2">
                <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                ALERTA: Consumo acima de 90% do tecto orçamental
            </div>
            @endif
        </div>

        {{-- Barras: Arrecadação Mensal por Fonte --}}
        <div class="xl:col-span-3 bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-1">Arrecadação Mensal por Fonte</h3>
            <p class="text-xs text-gray-400 mb-4">Comparação Petrolífera vs Não Petrolífera vs Patrimonial</p>
            <div id="chart-barras"></div>
        </div>
    </div>

    {{-- ═══ INFO ROW ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Context card --}}
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Informações do Contexto</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Ano Fiscal</dt>
                    <dd class="font-semibold text-gray-800">{{ $anoFiscal }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Visão</dt>
                    <dd class="font-semibold text-gray-800">{{ $contexto === 'gestor' ? 'Institucional' : 'Consolidada (Nacional)' }}</dd>
                </div>
                @if($instituicao)
                <div class="flex justify-between">
                    <dt class="text-gray-500">Unidade Orçamental</dt>
                    <dd class="font-semibold text-gray-800">{{ $instituicao->codigo }} — {{ $instituicao->nome }}</dd>
                </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-500">Taxa de Execução</dt>
                    <dd class="font-semibold {{ $percentagemConsumo > 90 ? 'text-red-600' : ($percentagemConsumo > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                        {{ $percentagemConsumo }}%
                    </dd>
                </div>
            </dl>
        </div>

        {{-- Pendências --}}
        @if($pendentes > 0 && in_array(Auth::user()->role, ['admin', 'gestor']))
        <div class="bg-white border border-yellow-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-yellow-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
                Ações Pendentes
            </h3>
            <p class="text-sm text-gray-600 mb-3">
                Existem <strong class="text-yellow-700">{{ $pendentes }}</strong> cabimentações pendentes de aprovação (NLD).
            </p>
            <a href="{{ route('gestao.despesas.index') }}" class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:text-blue-800">
                Ir para Despesas
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
            </a>
        </div>
        @else
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3">Estado Operacional</h3>
            <div class="flex items-center gap-2 text-sm text-green-700">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                Sistema operacional — sem pendências
            </div>
        </div>
        @endif
    </div>

    {{-- ═══ APEX CHARTS SCRIPTS ═══ --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // ── Donut Chart ──
        var consumo = {{ $percentagemConsumo }};
        var restante = Math.max(0, 100 - consumo);
        var corConsumo = consumo > 90 ? '#dc2626' : (consumo > 70 ? '#d97706' : '#16a34a');

        new ApexCharts(document.querySelector('#chart-donut'), {
            chart: { type: 'donut', height: 280 },
            series: [consumo, restante],
            labels: ['Consumido', 'Disponível'],
            colors: [corConsumo, '#e2e8f0'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '13px', color: '#64748b' },
                            value: { show: true, fontSize: '22px', fontWeight: 700, color: '#1e293b',
                                formatter: function(val) { return parseFloat(val).toFixed(1) + '%'; }
                            },
                            total: {
                                show: true, label: 'Execução',
                                formatter: function() { return consumo.toFixed(1) + '%'; }
                            }
                        }
                    }
                }
            },
            dataLabels: { enabled: false },
            legend: { position: 'bottom', fontSize: '12px' },
            stroke: { width: 2, colors: ['#fff'] },
            tooltip: {
                y: { formatter: function(val) { return val.toFixed(1) + '%'; } }
            }
        }).render();

        // ── Bar Chart ──
        var meses = ['Jan','Fev','Mar','Abr','Mai','Jun','Jul','Ago','Set','Out','Nov','Dez'];
        var seriesData = @json($receitasMensais);

        var coresFonte = {
            'Petrolífera': '#1e40af',
            'Não Petrolífera': '#d97706',
            'Patrimonial': '#059669'
        };
        seriesData.forEach(function(s) {
            s.color = coresFonte[s.name] || '#64748b';
        });

        new ApexCharts(document.querySelector('#chart-barras'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
            series: seriesData,
            xaxis: { categories: meses, labels: { style: { fontSize: '11px', colors: '#64748b' } } },
            yaxis: {
                labels: {
                    style: { fontSize: '11px', colors: '#64748b' },
                    formatter: function(val) {
                        if (val >= 1e9) return (val / 1e9).toFixed(1) + ' Bi';
                        if (val >= 1e6) return (val / 1e6).toFixed(1) + ' Mi';
                        if (val >= 1e3) return (val / 1e3).toFixed(0) + ' mil';
                        return val;
                    }
                }
            },
            colors: ['#1e40af', '#d97706', '#059669'],
            plotOptions: { bar: { columnWidth: '55%', borderRadius: 3 } },
            dataLabels: { enabled: false },
            legend: { position: 'top', fontSize: '12px', markers: { size: 4 } },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: {
                y: { formatter: function(val) { return new Intl.NumberFormat('pt-AO').format(val) + ' AOA'; } }
            }
        }).render();
    });
    </script>
</x-app-layout>
