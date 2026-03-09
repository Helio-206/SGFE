<?php

namespace App\Http\Controllers\Gestao;

use App\Http\Controllers\Controller;
use App\Models\ClassificacaoEconomica;
use App\Models\TransacaoReceita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransacaoReceitaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $receitas = TransacaoReceita::with(['classificacaoEconomica'])
            ->where('id_inst', $user->id_inst)
            ->latest('data_registro')
            ->paginate(20);

        return view('gestao.receitas.index', compact('receitas'));
    }

    public function create(): View
    {
        $classificacoes = ClassificacaoEconomica::orderBy('cod_classe')->get();

        return view('gestao.receitas.create', compact('classificacoes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'font_receita'     => 'required|in:Petrolífera,Não Petrolífera,Patrimonial',
            'data_registro'    => 'required|date',
            'valor_arrecadado' => 'required|numeric|min:0.01|max:999999999999.99',
            'id_classe'        => 'required|exists:classificacoes_economicas,id_classe',
        ]);

        $user = $request->user();
        $validated['id_inst'] = $user->id_inst;
        $validated['codigo_rupe'] = $this->gerarCodigoRupe();

        TransacaoReceita::create($validated);

        return redirect()->route('gestao.receitas.index')
            ->with('success', 'Receita registrada com sucesso. RUPE: '.$validated['codigo_rupe']);
    }

    private function gerarCodigoRupe(): string
    {
        do {
            $codigo = '';

            for ($i = 0; $i < 20; $i++) {
                $codigo .= (string) random_int(0, 9);
            }
        } while (TransacaoReceita::where('codigo_rupe', $codigo)->exists());

        return $codigo;
    }
}
