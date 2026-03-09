<?php

namespace App\Http\Controllers\Gestao;

use App\Http\Controllers\Controller;
use App\Models\Orcamento;
use App\Models\TransacaoDespesa;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrcamentoConsultaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $anoFiscal = 2025;

        $orcamento = Orcamento::with('instituicao')
            ->where('id_inst', $user->id_inst)
            ->where('ano_fiscal', $anoFiscal)
            ->first();

        $despesasComprometidas = (float) TransacaoDespesa::where('id_inst', $user->id_inst)
            ->whereYear('data_registro', $anoFiscal)
            ->whereIn('estado', ['PENDENTE_CABIMENTADA', 'LIQUIDADA_APROVADA', 'PAGA'])
            ->sum('valor_bruto');

        $valorTotalRecebido = (float) ($orcamento?->valor_total ?? 0);
        $saldoRestante = max(0, $valorTotalRecebido - $despesasComprometidas);

        return view('gestao.orcamento.index', compact(
            'orcamento',
            'anoFiscal',
            'valorTotalRecebido',
            'despesasComprometidas',
            'saldoRestante'
        ));
    }
}
