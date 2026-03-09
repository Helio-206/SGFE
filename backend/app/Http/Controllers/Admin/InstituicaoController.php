<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Instituicao;
use Illuminate\Http\Request;

/**
 * Controller de Gestão de Instituições / Unidades Orçamentais (UO)
 * Acesso: Apenas ADMIN (RF02)
 */
class InstituicaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $instituicoes = Instituicao::withCount(['usuarios', 'transacoesDespesas'])
            ->orderBy('codigo')
            ->paginate(15);

        return view('admin.instituicoes.index', compact('instituicoes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.instituicoes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome'        => 'required|string|max:150',
            'tipo'        => 'required|string|max:50',
            'codigo'      => 'required|string|max:20|unique:instituicoes,codigo',
            'responsavel' => 'required|string|max:100',
        ], [
            'codigo.unique' => 'Este código de Unidade Orçamental já está em uso.',
        ]);

        Instituicao::create($validated);

        return redirect()->route('admin.instituicoes.index')
            ->with('success', 'Instituição criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Instituicao $instituicao)
    {
        $instituicao->load([
            'usuarios' => fn($q) => $q->where('status', 'ativo'),
            'orcamentos',
            'transacoesDespesas' => fn($q) => $q->latest()->limit(10),
        ]);

        // Calcular estatísticas
        $totalDespesas = $instituicao->transacoesDespesas()
            ->whereIn('estado', ['aprovada', 'executada'])
            ->sum('valor_bruto');

        $orcamentoAtual = $instituicao->orcamento; // 1:1 para ano corrente

        return view('admin.instituicoes.show', compact('instituicao', 'totalDespesas', 'orcamentoAtual'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Instituicao $instituicao)
    {
        return view('admin.instituicoes.edit', compact('instituicao'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Instituicao $instituicao)
    {
        $validated = $request->validate([
            'nome'        => 'required|string|max:150',
            'tipo'        => 'required|string|max:50',
            'codigo'      => 'required|string|max:20|unique:instituicoes,codigo,' . $instituicao->id_inst . ',id_inst',
            'responsavel' => 'required|string|max:100',
        ]);

        $instituicao->update($validated);

        return redirect()->route('admin.instituicoes.index')
            ->with('success', 'Instituição atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Instituicao $instituicao)
    {
        // Verificar se há dependências
        if ($instituicao->usuarios()->count() > 0 || $instituicao->transacoesDespesas()->count() > 0) {
            return back()->with('error', 'Não é possível eliminar esta instituição pois possui utilizadores ou transações associadas.');
        }

        $instituicao->delete();

        return redirect()->route('admin.instituicoes.index')
            ->with('success', 'Instituição eliminada com sucesso.');
    }
}
