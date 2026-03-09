<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\TransacaoDespesa;
use App\Models\TransacaoReceita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const ANO_FISCAL = 2025;

    public function __invoke(Request $request): View
    {
        $user = $request->user();

        // ── Escopo: gestor vê só a sua UO, admin/auditor vê tudo ──
        $scopeFilter = fn ($q) => $user->isGestor() ? $q->where('id_inst', $user->id_inst) : $q;

        // ── KPI 1: Tecto Total ──
        $tectoTotal = (float) $scopeFilter(
            Orcamento::where('ano_fiscal', self::ANO_FISCAL)
        )->sum('valor_total');

        // ── KPI 2: Valor Gasto (PAGA) ──
        $valorPago = (float) $scopeFilter(
            TransacaoDespesa::whereYear('data_registro', self::ANO_FISCAL)
                ->where('estado', TransacaoDespesa::ESTADO_PAGA)
        )->sum('valor_bruto');

        // ── KPI 3: Valor Cabimentado (Pendente) ──
        $valorCabimentado = (float) $scopeFilter(
            TransacaoDespesa::whereYear('data_registro', self::ANO_FISCAL)
                ->where('estado', TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA)
        )->sum('valor_bruto');

        // ── KPI 4: Saldo Disponível ──
        $totalComprometido = (float) $scopeFilter(
            TransacaoDespesa::whereYear('data_registro', self::ANO_FISCAL)
                ->whereIn('estado', [
                    TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
                    TransacaoDespesa::ESTADO_LIQUIDADA_APROVADA,
                    TransacaoDespesa::ESTADO_PAGA,
                ])
        )->sum('valor_bruto');
        $saldoDisponivel = max(0, $tectoTotal - $totalComprometido);

        // ── Donut: percentagem consumida ──
        $percentagemConsumo = $tectoTotal > 0 ? round(($totalComprometido / $tectoTotal) * 100, 1) : 0;

        // ── Barras: arrecadação mensal por fonte ──
        $receitasMensais = $this->receitasMensaisPorFonte($user);

        // ── Instituição do gestor ──
        $instituicao = null;
        if ($user->isGestor()) {
            $instituicao = Orcamento::with('instituicao')
                ->where('id_inst', $user->id_inst)
                ->where('ano_fiscal', self::ANO_FISCAL)
                ->first()?->instituicao;
        }

        // ── Pendências (notificação inline) ──
        $pendentes = (int) $scopeFilter(
            TransacaoDespesa::where('estado', TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA)
        )->count();

        return view('dashboard', [
            'anoFiscal'          => self::ANO_FISCAL,
            'tectoTotal'         => $tectoTotal,
            'valorPago'          => $valorPago,
            'valorCabimentado'   => $valorCabimentado,
            'saldoDisponivel'    => $saldoDisponivel,
            'percentagemConsumo' => $percentagemConsumo,
            'receitasMensais'    => $receitasMensais,
            'instituicao'        => $instituicao,
            'pendentes'          => $pendentes,
            'contexto'           => $user->isGestor() ? 'gestor' : 'admin_auditor',
        ]);
    }

    /**
     * Retorna array com dados mensais por fonte (Petrolífera / Não Petrolífera / Patrimonial).
     */
    private function receitasMensaisPorFonte($user): array
    {
        $query = DB::table('transacoes_receitas')
            ->selectRaw('MONTH(data_registro) as mes, font_receita, SUM(valor_arrecadado) as total')
            ->whereYear('data_registro', self::ANO_FISCAL)
            ->groupByRaw('MONTH(data_registro), font_receita')
            ->orderByRaw('MONTH(data_registro)');

        if ($user->isGestor()) {
            $query->where('id_inst', $user->id_inst);
        }

        $raw = $query->get();

        $fontes = ['Petrolífera', 'Não Petrolífera', 'Patrimonial'];
        $series = [];

        foreach ($fontes as $fonte) {
            $data = [];
            for ($m = 1; $m <= 12; $m++) {
                $val = $raw->first(fn ($r) => $r->mes == $m && $r->font_receita === $fonte);
                $data[] = (float) ($val->total ?? 0);
            }
            $series[] = ['name' => $fonte, 'data' => $data];
        }

        return $series;
    }
}
