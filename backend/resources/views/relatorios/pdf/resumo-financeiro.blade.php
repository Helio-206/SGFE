<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Resumo Financeiro UO</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 20px; }
        .brasao { width: 95px; height: auto; margin-bottom: 8px; }
        .title { font-size: 18px; font-weight: bold; }
        .subtitle { font-size: 12px; color: #444; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background: #f5f5f5; }
        .footer { margin-top: 30px; font-size: 11px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        @if(file_exists($brasaoPath))
            <img class="brasao" src="{{ $brasaoPath }}" alt="Brasão de Angola">
        @endif
        <div class="title">República de Angola - SGFE</div>
        <div class="subtitle">Resumo Financeiro da Unidade Orçamental ({{ $anoFiscal }})</div>
    </div>

    <table>
        <tr>
            <th>Unidade Orçamental</th>
            <td>{{ $instituicao?->codigo ?? 'N/D' }} - {{ $instituicao?->nome ?? 'Não definida' }}</td>
        </tr>
        <tr>
            <th>Tecto Orçamental</th>
            <td>{{ number_format($tecto, 2, ',', '.') }} AOA</td>
        </tr>
        <tr>
            <th>Total Arrecadado</th>
            <td>{{ number_format($totalArrecadado, 2, ',', '.') }} AOA</td>
        </tr>
        <tr>
            <th>Total Gasto (apenas estado = PAGA)</th>
            <td>{{ number_format($totalGastoPago, 2, ',', '.') }} AOA</td>
        </tr>
        <tr>
            <th>Saldo Restante</th>
            <td>{{ number_format($saldo, 2, ',', '.') }} AOA</td>
        </tr>
    </table>

    <div class="footer" style="margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 10px; color: #666;">
        <div style="float: left;">
            Emitido por: {{ $user->nome ?? $user->name }} ({{ strtoupper($user->role ?? '') }}) |
            Data: {{ now()->format('d/m/Y H:i') }}
        </div>
        <div style="float: right; text-align: right;">
            @if(isset($minfinLogoPath) && file_exists($minfinLogoPath))
                <img src="{{ $minfinLogoPath }}" alt="MINFIN" style="height: 28px; opacity: 0.7;">
            @else
                <span style="font-size: 9px;">Ministério das Finanças — minfin.gov.ao</span>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
