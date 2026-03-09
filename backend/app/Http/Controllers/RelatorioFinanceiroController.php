<?php

namespace App\Http\Controllers;

use App\Exports\ReceitasRupeExport;
use App\Models\AuditLog;
use App\Models\ClassificacaoEconomica;
use App\Models\Instituicao;
use App\Models\Orcamento;
use App\Models\TransacaoDespesa;
use App\Models\TransacaoReceita;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class RelatorioFinanceiroController extends Controller
{
    private const ANO_FISCAL = 2025;

    /**
     * Página principal de relatórios com filtros avançados.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // ── Filtros ──
        $codClasse  = $request->string('cod_classe')->toString();
        $valorMin   = $request->input('valor_min');
        $valorMax   = $request->input('valor_max');
        $idInst     = $request->input('id_inst');
        $dataInicio = $request->input('data_inicio');
        $dataFim    = $request->input('data_fim');

        // ── Receitas ──
        $receitasQuery = TransacaoReceita::with(['classificacaoEconomica', 'instituicao']);
        if ($user->isGestor()) {
            $receitasQuery->where('id_inst', $user->id_inst);
        } elseif ($idInst) {
            $receitasQuery->where('id_inst', $idInst);
        }
        if ($codClasse !== '') {
            $receitasQuery->whereHas('classificacaoEconomica', fn ($q) => $q->where('cod_classe', 'like', "%{$codClasse}%"));
        }
        if ($dataInicio) { $receitasQuery->where('data_registro', '>=', $dataInicio); }
        if ($dataFim)    { $receitasQuery->where('data_registro', '<=', $dataFim); }

        // ── Despesas ──
        $despesasQuery = TransacaoDespesa::with(['instituicao', 'usuario']);
        if ($user->isGestor()) {
            $despesasQuery->where('id_inst', $user->id_inst);
        } elseif ($idInst) {
            $despesasQuery->where('id_inst', $idInst);
        }
        if ($valorMin !== null && $valorMin !== '') { $despesasQuery->where('valor_bruto', '>=', (float) $valorMin); }
        if ($valorMax !== null && $valorMax !== '') { $despesasQuery->where('valor_bruto', '<=', (float) $valorMax); }
        if ($dataInicio) { $despesasQuery->where('data_registro', '>=', $dataInicio); }
        if ($dataFim)    { $despesasQuery->where('data_registro', '<=', $dataFim); }

        $receitas = $receitasQuery->latest('data_registro')->paginate(15, ['*'], 'receitas_page');
        $despesas = $despesasQuery->latest('data_registro')->paginate(15, ['*'], 'despesas_page');

        // Para filtros: lista de UOs (se admin/auditor)
        $instituicoes = (!$user->isGestor()) ? Instituicao::orderBy('codigo')->get() : collect();

        return view('relatorios.index', compact(
            'receitas', 'despesas', 'codClasse', 'valorMin', 'valorMax',
            'idInst', 'dataInicio', 'dataFim', 'instituicoes'
        ));
    }

    /**
     * SQL consolidação de gastos pagos por instituição (JSON).
     */
    public function consolidadoGastos(Request $request): JsonResponse
    {
        $user = $request->user();

        $sqlBase = "
            SELECT i.id_inst, i.codigo, i.nome,
                   COALESCE(SUM(td.valor_bruto), 0) AS total_gasto_pago
            FROM instituicoes i
            LEFT JOIN transacoes_despesas td ON td.id_inst = i.id_inst AND td.estado = 'PAGA'
            %s
            GROUP BY i.id_inst, i.codigo, i.nome
            ORDER BY total_gasto_pago DESC
        ";

        if ($user->isGestor()) {
            $resultado = DB::select(sprintf($sqlBase, 'WHERE i.id_inst = :id_inst'), ['id_inst' => $user->id_inst]);
        } else {
            $resultado = DB::select(sprintf($sqlBase, ''));
        }

        return response()->json(['dados' => $resultado]);
    }

    /**
     * Evolução mensal de receitas (JSON para gráficos).
     */
    public function evolucaoReceitasMensal(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = DB::table('transacoes_receitas')
            ->selectRaw('MONTH(data_registro) as mes, SUM(valor_arrecadado) as total')
            ->whereYear('data_registro', self::ANO_FISCAL)
            ->groupByRaw('MONTH(data_registro)')
            ->orderByRaw('MONTH(data_registro)');

        if ($user->isGestor()) {
            $query->where('id_inst', $user->id_inst);
        }

        $dados = $query->get()->keyBy('mes');
        $serie = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $serie[] = ['mes' => $mes, 'total' => (float) ($dados[$mes]->total ?? 0)];
        }

        return response()->json(['ano_fiscal' => self::ANO_FISCAL, 'serie' => $serie]);
    }

    /**
     * Exportar PDF — Resumo Financeiro institucional.
     */
    public function exportarPdf(Request $request)
    {
        $user = $request->user();

        $orcamento = Orcamento::with('instituicao')
            ->where('id_inst', $user->id_inst)
            ->where('ano_fiscal', self::ANO_FISCAL)
            ->first();

        $totalGastoPago = (float) TransacaoDespesa::where('id_inst', $user->id_inst)
            ->whereYear('data_registro', self::ANO_FISCAL)
            ->where('estado', 'PAGA')
            ->sum('valor_bruto');

        $totalArrecadado = (float) TransacaoReceita::where('id_inst', $user->id_inst)
            ->whereYear('data_registro', self::ANO_FISCAL)
            ->sum('valor_arrecadado');

        $tecto = (float) ($orcamento?->valor_total ?? 0);

        $pdf = Pdf::loadView('relatorios.pdf.resumo-financeiro', [
            'user'            => $user,
            'anoFiscal'       => self::ANO_FISCAL,
            'instituicao'     => $orcamento?->instituicao,
            'tecto'           => $tecto,
            'totalGastoPago'  => $totalGastoPago,
            'totalArrecadado' => $totalArrecadado,
            'saldo'           => max(0, $tecto - $totalGastoPago),
            'brasaoPath'      => public_path('images/brasao-angola.png'),
            'minfinLogoPath'  => public_path('images/minfin-logo.png'),
        ])->setPaper('a4', 'portrait');

        $this->registrarLog($request, 'EXPORTACAO_RELATORIO_FINANCEIRO_PDF', ['id_inst' => $user->id_inst]);

        return $pdf->download('resumo-financeiro-uo-'.$user->id_inst.'-'.self::ANO_FISCAL.'.pdf');
    }

    /**
     * PDF — Relatório de Despesa por Natureza (Classificação Económica).
     */
    public function despesaPorNatureza(Request $request)
    {
        $user = $request->user();

        $query = DB::table('transacoes_despesas as td')
            ->join('classificacoes_economicas as ce', 'td.id_classe', '=', 'ce.id_classe')
            ->selectRaw('ce.id_classe, ce.cod_classe, ce.descricao, SUM(td.valor_bruto) as total_gasto, COUNT(*) as qtd')
            ->where('td.estado', 'PAGA')
            ->whereYear('td.data_registro', self::ANO_FISCAL)
            ->groupBy('ce.id_classe', 'ce.cod_classe', 'ce.descricao')
            ->orderBy('ce.cod_classe');

        if ($user->isGestor()) {
            $query->where('td.id_inst', $user->id_inst);
        }

        $rubricas = $query->get();
        $totalGeral = $rubricas->sum('total_gasto');

        $instituicao = null;
        if ($user->isGestor()) {
            $instituicao = Orcamento::with('instituicao')
                ->where('id_inst', $user->id_inst)
                ->where('ano_fiscal', self::ANO_FISCAL)
                ->first()?->instituicao;
        }

        $pdf = Pdf::loadView('relatorios.pdf.despesa-por-natureza', [
            'user'        => $user,
            'anoFiscal'   => self::ANO_FISCAL,
            'instituicao' => $instituicao,
            'rubricas'    => $rubricas,
            'totalGeral'  => $totalGeral,
            'brasaoPath'      => public_path('images/brasao-angola.png'),
            'minfinLogoPath'  => public_path('images/minfin-logo.png'),
        ])->setPaper('a4', 'portrait');

        $this->registrarLog($request, 'EXPORTACAO_DESPESA_POR_NATUREZA_PDF', [
            'id_inst'   => $user->id_inst,
            'rubricas'  => $rubricas->count(),
        ]);

        return $pdf->download('despesa-por-natureza-'.self::ANO_FISCAL.'.pdf');
    }

    /**
     * Excel — Mapa de Receitas via RUPE (filtrável por datas).
     */
    public function exportarReceitasExcel(Request $request)
    {
        $request->validate([
            'data_inicio' => 'nullable|date',
            'data_fim'    => 'nullable|date|after_or_equal:data_inicio',
        ]);

        $this->registrarLog($request, 'EXPORTACAO_RECEITAS_RUPE_EXCEL', [
            'id_inst'     => $request->user()->id_inst,
            'data_inicio' => $request->input('data_inicio'),
            'data_fim'    => $request->input('data_fim'),
        ]);

        return Excel::download(
            new ReceitasRupeExport($request->user(), $request->input('data_inicio'), $request->input('data_fim')),
            'mapa-receitas-rupe-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    /**
     * Registrar log de auditoria para exportações sensíveis (RNF08).
     */
    private function registrarLog(Request $request, string $acao, array $contexto = []): void
    {
        AuditLog::create([
            'id_user'    => $request->user()?->id_user,
            'acao'       => $acao,
            'ip_address' => (string) $request->ip(),
            'contexto'   => $contexto,
        ]);
    }
}
