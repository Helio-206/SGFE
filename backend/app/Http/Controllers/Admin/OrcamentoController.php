<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use App\Models\Orcamento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller de Distribuição de Tectos Orçamentais
 * Acesso: Apenas ADMIN (RF03, RF04, RF08)
 */
class OrcamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $anoFiscal = $request->input('ano', date('Y'));

        $orcamentos = Orcamento::with(['instituicao', 'usuario'])
            ->where('ano_fiscal', $anoFiscal)
            ->orderBy('created_at', 'desc')
            ->get();

        // Calcular saldos para cada orçamento
        $orcamentos->each(function ($orc) {
            $despesasAprovadas = $orc->instituicao->transacoesDespesas()
                ->whereIn('estado', ['aprovada', 'executada'])
                ->whereYear('data_registro', $orc->ano_fiscal)
                ->sum('valor_bruto');

            $orc->despesas_executadas = $despesasAprovadas;
            $orc->saldo_disponivel = $orc->valor_total - $despesasAprovadas;
        });

        $instituicoesSemOrcamento = Instituicao::whereDoesntHave('orcamentos', function ($q) use ($anoFiscal) {
            $q->where('ano_fiscal', $anoFiscal);
        })->get();

        return view('admin.orcamentos.index', compact('orcamentos', 'anoFiscal', 'instituicoesSemOrcamento'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $anoFiscal = date('Y');
        $instituicoes = Instituicao::whereDoesntHave('orcamentos', function ($q) use ($anoFiscal) {
            $q->where('ano_fiscal', $anoFiscal);
        })->orderBy('nome')->get();

        return view('admin.orcamentos.create', compact('instituicoes', 'anoFiscal'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_inst'     => 'required|exists:instituicoes,id_inst',
            'valor_total' => 'required|numeric|min:0|max:999999999999.99',
            'ano_fiscal'  => 'required|integer|min:2020|max:2050',
        ]);

        // Verificar se já existe orçamento para esta instituição neste ano
        $existe = Orcamento::where('id_inst', $validated['id_inst'])
            ->where('ano_fiscal', $validated['ano_fiscal'])
            ->exists();

        if ($existe) {
            return back()->withErrors(['id_inst' => 'Já existe um orçamento para esta instituição no ano fiscal ' . $validated['ano_fiscal'] . '.']);
        }

        $validated['id_user'] = $request->user()->id_user;

        Orcamento::create($validated);

        return redirect()->route('admin.orcamentos.index')
            ->with('success', 'Tecto orçamental atribuído com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Orcamento $orcamento): View
    {
        $orcamento->load(['instituicao', 'usuario']);

        // Calcular execução orçamental
        $despesas = $orcamento->instituicao->transacoesDespesas()
            ->whereYear('data_registro', $orcamento->ano_fiscal)
            ->selectRaw('estado, SUM(valor_bruto) as total')
            ->groupBy('estado')
            ->get()
            ->pluck('total', 'estado');

        $totalExecutado = $despesas->get('aprovada', 0) + $despesas->get('executada', 0);
        $saldoDisponivel = $orcamento->valor_total - $totalExecutado;
        $percentualExecucao = $orcamento->valor_total > 0 ? ($totalExecutado / $orcamento->valor_total) * 100 : 0;

        return view('admin.orcamentos.show', compact('orcamento', 'despesas', 'totalExecutado', 'saldoDisponivel', 'percentualExecucao'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Orcamento $orcamento): View
    {
        return view('admin.orcamentos.edit', compact('orcamento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Orcamento $orcamento): RedirectResponse
    {
        $validated = $request->validate([
            'valor_total' => 'required|numeric|min:0|max:999999999999.99',
        ]);

        // Verificar se o novo valor não é inferior às despesas já aprovadas
        $despesasAprovadas = $orcamento->instituicao->transacoesDespesas()
            ->whereIn('estado', ['aprovada', 'executada'])
            ->whereYear('data_registro', $orcamento->ano_fiscal)
            ->sum('valor_bruto');

        if ($validated['valor_total'] < $despesasAprovadas) {
            return back()->withErrors([
                'valor_total' => 'O valor total não pode ser inferior ao valor já executado (' . number_format($despesasAprovadas, 2, ',', '.') . ' AOA).'
            ]);
        }

        $orcamento->update($validated);

        return redirect()->route('admin.orcamentos.index')
            ->with('success', 'Tecto orçamental atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Orcamento $orcamento): RedirectResponse
    {
        // Verificar se há despesas associadas
        $temDespesas = $orcamento->instituicao->transacoesDespesas()
            ->whereYear('data_registro', $orcamento->ano_fiscal)
            ->exists();

        if ($temDespesas) {
            return back()->with('error', 'Não é possível eliminar este orçamento pois já possui despesas registradas.');
        }

        $orcamento->delete();

        return redirect()->route('admin.orcamentos.index')
            ->with('success', 'Orçamento eliminado com sucesso.');
    }
}
