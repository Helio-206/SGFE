<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatório de Despesa por Natureza</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; margin: 30px; }
        .header { text-align: center; margin-bottom: 25px; border-bottom: 2px solid #162d50; padding-bottom: 15px; }
        .brasao { width: 80px; height: auto; margin-bottom: 6px; }
        .title { font-size: 16px; font-weight: bold; color: #162d50; }
        .subtitle { font-size: 11px; color: #555; margin-top: 3px; }
        .doc-title { font-size: 14px; font-weight: bold; color: #162d50; margin-top: 10px; text-transform: uppercase; letter-spacing: 1px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #162d50; color: #fff; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { border-bottom: 1px solid #ddd; padding: 8px 10px; font-size: 11px; }
        tr:nth-child(even) td { background: #f8f9fa; }
        .total-row td { font-weight: bold; background: #e8ecf1 !important; border-top: 2px solid #162d50; font-size: 12px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 10px; color: #666; display: flex; justify-content: space-between; }
        .footer-left { float: left; }
        .footer-right { float: right; }
        .info-box { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 4px; padding: 10px; margin-bottom: 15px; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists($brasaoPath))
            <img class="brasao" src="{{ $brasaoPath }}" alt="Brasão de Angola">
        @endif
        <div class="title">República de Angola</div>
        <div class="subtitle">Ministério das Finanças — Sistema de Gestão Financeira do Estado</div>
        <div class="doc-title">Relatório de Despesa por Natureza</div>
        <div class="subtitle">Exercício Fiscal {{ $anoFiscal }}</div>
    </div>

    <div class="info-box">
        @if($instituicao)
            <strong>Unidade Orçamental:</strong> {{ $instituicao->codigo }} — {{ $instituicao->nome }}<br>
        @else
            <strong>Visão:</strong> Consolidada (todas as Unidades Orçamentais)<br>
        @endif
        <strong>Estado considerado:</strong> PAGA (despesas efetivamente liquidadas e pagas)
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%">Código Rubrica</th>
                <th style="width: 40%">Descrição (Classificação Económica)</th>
                <th style="width: 15%; text-align: center">Qtd. Transações</th>
                <th style="width: 30%; text-align: right">Total Gasto (AOA)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rubricas as $rubrica)
            <tr>
                <td>{{ $rubrica->cod_classe }}</td>
                <td>{{ $rubrica->descricao }}</td>
                <td style="text-align: center">{{ $rubrica->qtd }}</td>
                <td style="text-align: right">{{ number_format($rubrica->total_gasto, 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; color: #999; padding: 20px;">Sem despesas pagas registradas para o período.</td>
            </tr>
            @endforelse
            @if($totalGeral > 0)
            <tr class="total-row">
                <td colspan="2">TOTAL GERAL</td>
                <td style="text-align: center">{{ $rubricas->sum('qtd') }}</td>
                <td style="text-align: right">{{ number_format($totalGeral, 2, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <span class="footer-left">
            Emitido por: {{ $user->nome ?? $user->name }} ({{ strtoupper($user->role) }}) |
            Data: {{ now()->format('d/m/Y H:i') }}
        </span>
        <span class="footer-right">
            @if(isset($minfinLogoPath) && file_exists($minfinLogoPath))
                <img src="{{ $minfinLogoPath }}" alt="MINFIN" style="height: 28px; opacity: 0.7; vertical-align: middle;">
            @else
                Ministério das Finanças — minfin.gov.ao
            @endif
        </span>
    </div>
</body>
</html>
