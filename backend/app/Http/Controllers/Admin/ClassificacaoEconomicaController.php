<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassificacaoEconomica;
use Illuminate\Http\Request;

/**
 * Controller de Gestão de Classificações Económicas
 * Acesso: Apenas ADMIN
 */
class ClassificacaoEconomicaController extends Controller
{
    public function index(Request $request)
    {
        $tipo = $request->input('tipo');

        $query = ClassificacaoEconomica::query();

        if ($tipo) {
            $query->where('tipo_receita', 'like', '%' . $tipo . '%');
        }

        $classificacoes = $query->orderBy('cod_classe')->paginate(20);

        // Agrupar por tipo para filtros
        $tipos = ClassificacaoEconomica::selectRaw('tipo_receita, COUNT(*) as total')
            ->groupBy('tipo_receita')
            ->get();

        return view('admin.classificacoes.index', compact('classificacoes', 'tipos', 'tipo'));
    }

    public function create()
    {
        return view('admin.classificacoes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao'    => 'required|string|max:100',
            'cod_classe'   => 'required|string|max:20|unique:classificacoes_economicas,cod_classe',
            'tipo_receita' => 'required|string|max:50',
        ]);

        ClassificacaoEconomica::create($validated);

        return redirect()->route('admin.classificacoes.index')
            ->with('success', 'Classificação económica criada com sucesso.');
    }

    public function edit(ClassificacaoEconomica $classificacao)
    {
        return view('admin.classificacoes.edit', compact('classificacao'));
    }

    public function update(Request $request, ClassificacaoEconomica $classificacao)
    {
        $validated = $request->validate([
            'descricao'    => 'required|string|max:100',
            'cod_classe'   => 'required|string|max:20|unique:classificacoes_economicas,cod_classe,' . $classificacao->id_classe . ',id_classe',
            'tipo_receita' => 'required|string|max:50',
        ]);

        $classificacao->update($validated);

        return redirect()->route('admin.classificacoes.index')
            ->with('success', 'Classificação económica atualizada com sucesso.');
    }

    public function destroy(ClassificacaoEconomica $classificacao)
    {
        if ($classificacao->transacoesReceitas()->count() > 0) {
            return back()->with('error', 'Não é possível eliminar esta classificação pois possui receitas associadas.');
        }

        $classificacao->delete();

        return redirect()->route('admin.classificacoes.index')
            ->with('success', 'Classificação económica eliminada com sucesso.');
    }
}
