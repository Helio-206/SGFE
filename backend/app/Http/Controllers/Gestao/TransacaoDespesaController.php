<?php

namespace App\Http\Controllers\Gestao;

use App\Http\Controllers\Controller;
use App\Models\ClassificacaoEconomica;
use App\Models\Orcamento;
use App\Models\TransacaoDespesa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransacaoDespesaController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $despesas = TransacaoDespesa::with('usuario')
            ->where('id_inst', $user->id_inst)
            ->latest('data_registro')
            ->paginate(20);

        return view('gestao.despesas.index', compact('despesas'));
    }

    public function create(Request $request): View
    {
        [$saldoDisponivel, $anoFiscal] = $this->saldoAtualInstituicao($request->user()->id_inst, now()->year);
        $classificacoes = ClassificacaoEconomica::orderBy('cod_classe')->get();

        return view('gestao.despesas.create', compact('saldoDisponivel', 'anoFiscal', 'classificacoes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descricao'     => 'required|string|max:150',
            'valor_bruto'   => 'required|numeric|min:0.01|max:999999999999.99',
            'data_registro' => 'required|date',
            'id_classe'     => 'nullable|exists:classificacoes_economicas,id_classe',
        ]);

        $user = $request->user();
        $anoFiscal = (int) date('Y', strtotime($validated['data_registro']));

        [$saldoDisponivel] = $this->saldoAtualInstituicao($user->id_inst, $anoFiscal);

        if ((float) $validated['valor_bruto'] > $saldoDisponivel) {
            return back()->withInput()->withErrors([
                'valor_bruto' => 'Cabimentação bloqueada: valor superior ao saldo disponível ('.number_format($saldoDisponivel, 2, ',', '.').' AOA).',
            ]);
        }

        TransacaoDespesa::create([
            'estado'        => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao'     => $validated['descricao'],
            'valor_bruto'   => $validated['valor_bruto'],
            'data_registro' => $validated['data_registro'],
            'id_inst'       => $user->id_inst,
            'id_user'       => $user->id_user,
            'id_classe'     => $validated['id_classe'] ?? null,
        ]);

        return redirect()->route('gestao.despesas.index')
            ->with('success', 'NCD emitida com sucesso (Despesa cabimentada).');
    }

    public function liquidar(Request $request, TransacaoDespesa $despesa): RedirectResponse
    {
        $this->validarPertencimento($request->user()->id_inst, $despesa);

        if ($despesa->estado !== TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA) {
            return back()->with('error', 'Apenas despesas cabimentadas podem ser liquidadas (NLD).');
        }

        $despesa->update(['estado' => TransacaoDespesa::ESTADO_LIQUIDADA_APROVADA]);

        return back()->with('success', 'NLD concluída: despesa aprovada.');
    }

    public function pagar(Request $request, TransacaoDespesa $despesa): RedirectResponse
    {
        $this->validarPertencimento($request->user()->id_inst, $despesa);

        if ($despesa->estado !== TransacaoDespesa::ESTADO_LIQUIDADA_APROVADA) {
            return back()->with('error', 'Pagamento permitido apenas para despesas aprovadas.');
        }

        $despesa->update(['estado' => TransacaoDespesa::ESTADO_PAGA]);

        return back()->with('success', 'Pagamento registrado na CUT com sucesso.');
    }

    private function saldoAtualInstituicao(int $idInst, int $anoFiscal): array
    {
        $orcamento = Orcamento::where('id_inst', $idInst)
            ->where('ano_fiscal', $anoFiscal)
            ->first();

        if (! $orcamento) {
            return [0.0, $anoFiscal];
        }

        $comprometido = TransacaoDespesa::where('id_inst', $idInst)
            ->whereYear('data_registro', $anoFiscal)
            ->where('estado', TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA)
            ->sum('valor_bruto');

        return [max(0, (float) $orcamento->valor_total - (float) $comprometido), $anoFiscal];
    }

    private function validarPertencimento(int $idInstUser, TransacaoDespesa $despesa): void
    {
        if ((int) $despesa->id_inst !== (int) $idInstUser) {
            abort(403, 'Não pode operar despesas de outra instituição.');
        }
    }
}
