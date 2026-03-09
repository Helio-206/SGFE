<?php

namespace Tests\Feature;

use App\Models\ClassificacaoEconomica;
use App\Models\Instituicao;
use App\Models\Orcamento;
use App\Models\TransacaoDespesa;
use App\Models\TransacaoReceita;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Testes de Integridade (Caixa Preta) — SGFE
 *
 * RF05 - Validação de saldo orçamental antes de cabimentar despesa
 * RNF05 - Integridade referencial (3FN) impede eliminação de entidades com dependências
 * RF03 - Fluxo de 3 fases da despesa (NCD → NLD → Pagamento)
 */
class IntegridadeTest extends TestCase
{
    use RefreshDatabase;

    private Instituicao $instituicao;
    private User $gestor;
    private User $admin;
    private Orcamento $orcamento;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instituicao = Instituicao::factory()->create();

        $this->gestor = User::factory()->create([
            'role' => 'gestor',
            'id_inst' => $this->instituicao->id_inst,
        ]);

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'id_inst' => $this->instituicao->id_inst,
        ]);

        $this->orcamento = Orcamento::create([
            'id_user' => $this->admin->id_user,
            'id_inst' => $this->instituicao->id_inst,
            'valor_total' => 1000000.00,
            'ano_fiscal' => now()->year,
        ]);
    }

    // ══════════════════════════════════════════════════════════
    //  RF05 — Bloqueio de despesa acima do saldo disponível
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function bloqueia_despesa_superior_ao_saldo_disponivel(): void
    {
        // Cabimentar despesa que consome quase todo o tecto
        TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao' => 'Despesa prévia',
            'valor_bruto' => 900000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $this->instituicao->id_inst,
            'id_user' => $this->gestor->id_user,
        ]);

        // Tentar cabimentar valor que ultrapassa o saldo restante (100.000 AOA)
        $response = $this->actingAs($this->gestor)->post(route('gestao.despesas.store'), [
            'descricao' => 'Despesa bloqueada',
            'valor_bruto' => 200000.00, // Excede os 100.000 AOA de saldo
            'data_registro' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('valor_bruto');

        $this->assertDatabaseMissing('transacoes_despesas', [
            'descricao' => 'Despesa bloqueada',
        ]);
    }

    #[Test]
    public function permite_despesa_dentro_do_saldo_disponivel(): void
    {
        $response = $this->actingAs($this->gestor)->post(route('gestao.despesas.store'), [
            'descricao' => 'Despesa válida',
            'valor_bruto' => 500000.00,
            'data_registro' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('gestao.despesas.index'));

        $this->assertDatabaseHas('transacoes_despesas', [
            'descricao' => 'Despesa válida',
            'estado' => 'PENDENTE_CABIMENTADA',
            'valor_bruto' => 500000.00,
        ]);
    }

    #[Test]
    public function bloqueia_despesa_quando_nao_ha_orcamento(): void
    {
        // Criar nova instituição SEM orçamento
        $instSemOrc = Instituicao::factory()->create();
        $gestorSemOrc = User::factory()->create([
            'role' => 'gestor',
            'id_inst' => $instSemOrc->id_inst,
        ]);

        $response = $this->actingAs($gestorSemOrc)->post(route('gestao.despesas.store'), [
            'descricao' => 'Despesa sem orçamento',
            'valor_bruto' => 1.00,
            'data_registro' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('valor_bruto');
    }

    // ══════════════════════════════════════════════════════════
    //  RF03 — Fluxo de 3 fases obrigatório (NCD → NLD → PAG)
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function nao_pode_pagar_despesa_sem_liquidar_primeiro(): void
    {
        $despesa = TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao' => 'Teste fluxo',
            'valor_bruto' => 5000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $this->instituicao->id_inst,
            'id_user' => $this->gestor->id_user,
        ]);

        // Tentar pagar diretamente (pular liquidação)
        $response = $this->actingAs($this->gestor)
            ->patch(route('gestao.despesas.pagar', $despesa->id_despesa));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('transacoes_despesas', [
            'id_despesa' => $despesa->id_despesa,
            'estado' => 'PENDENTE_CABIMENTADA', // Estado não mudou
        ]);
    }

    #[Test]
    public function fluxo_completo_ncd_nld_pagamento(): void
    {
        $despesa = TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao' => 'Fluxo completo',
            'valor_bruto' => 10000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $this->instituicao->id_inst,
            'id_user' => $this->gestor->id_user,
        ]);

        // Fase 1 → 2: Liquidar (NLD)
        $this->actingAs($this->gestor)
            ->patch(route('gestao.despesas.liquidar', $despesa->id_despesa))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('transacoes_despesas', [
            'id_despesa' => $despesa->id_despesa,
            'estado' => 'LIQUIDADA_APROVADA',
        ]);

        // Fase 2 → 3: Pagar
        $this->actingAs($this->gestor)
            ->patch(route('gestao.despesas.pagar', $despesa->id_despesa))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('transacoes_despesas', [
            'id_despesa' => $despesa->id_despesa,
            'estado' => 'PAGA',
        ]);
    }

    #[Test]
    public function nao_pode_liquidar_despesa_ja_paga(): void
    {
        $despesa = TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PAGA,
            'descricao' => 'Já paga',
            'valor_bruto' => 5000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $this->instituicao->id_inst,
            'id_user' => $this->gestor->id_user,
        ]);

        $response = $this->actingAs($this->gestor)
            ->patch(route('gestao.despesas.liquidar', $despesa->id_despesa));

        $response->assertSessionHas('error');
    }

    // ══════════════════════════════════════════════════════════
    //  RNF05 — Integridade referencial (3FN)
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function bloqueia_eliminacao_de_instituicao_com_utilizadores(): void
    {
        // A instituição já tem gestor e admin associados
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.instituicoes.destroy', $this->instituicao));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('instituicoes', [
            'id_inst' => $this->instituicao->id_inst,
        ]);
    }

    #[Test]
    public function bloqueia_eliminacao_de_instituicao_com_transacoes(): void
    {
        // Criar instituição com despesa mas sem utilizadores extras
        $inst = Instituicao::factory()->create();
        $user = User::factory()->create(['id_inst' => $inst->id_inst, 'role' => 'gestor']);

        TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao' => 'Despesa vinculada',
            'valor_bruto' => 1000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $inst->id_inst,
            'id_user' => $user->id_user,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.instituicoes.destroy', $inst));

        $response->assertSessionHas('error');

        $this->assertDatabaseHas('instituicoes', [
            'id_inst' => $inst->id_inst,
        ]);
    }

    #[Test]
    public function permite_eliminar_instituicao_sem_dependencias(): void
    {
        $instLimpa = Instituicao::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.instituicoes.destroy', $instLimpa));

        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('instituicoes', [
            'id_inst' => $instLimpa->id_inst,
        ]);
    }

    #[Test]
    public function fk_restrict_impede_delete_user_com_despesas_via_db(): void
    {
        // Criar despesa vinculada ao gestor
        TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao' => 'Despesa do gestor',
            'valor_bruto' => 5000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $this->instituicao->id_inst,
            'id_user' => $this->gestor->id_user,
        ]);

        // Tentar eliminar o utilizador diretamente (FK RESTRICT deve impedir)
        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->gestor->delete();
    }

    #[Test]
    public function fk_restrict_impede_delete_instituicao_com_orcamento_via_db(): void
    {
        // A instituição tem um orçamento via FK RESTRICT
        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->instituicao->delete();
    }

    // ══════════════════════════════════════════════════════════
    //  Validações de dados
    // ══════════════════════════════════════════════════════════

    #[Test]
    public function rejeita_despesa_com_valor_zero_ou_negativo(): void
    {
        $response = $this->actingAs($this->gestor)->post(route('gestao.despesas.store'), [
            'descricao' => 'Valor inválido',
            'valor_bruto' => 0,
            'data_registro' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('valor_bruto');
    }

    #[Test]
    public function rejeita_despesa_sem_descricao(): void
    {
        $response = $this->actingAs($this->gestor)->post(route('gestao.despesas.store'), [
            'descricao' => '',
            'valor_bruto' => 1000.00,
            'data_registro' => now()->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('descricao');
    }

    #[Test]
    public function gestor_nao_pode_operar_despesas_de_outra_instituicao(): void
    {
        $outraInst = Instituicao::factory()->create();
        $outroUser = User::factory()->create(['id_inst' => $outraInst->id_inst]);

        $despesa = TransacaoDespesa::create([
            'estado' => TransacaoDespesa::ESTADO_PENDENTE_CABIMENTADA,
            'descricao' => 'De outra UO',
            'valor_bruto' => 1000.00,
            'data_registro' => now()->format('Y-m-d'),
            'id_inst' => $outraInst->id_inst,
            'id_user' => $outroUser->id_user,
        ]);

        // Gestor da instituição original tenta liquidar despesa de outra UO
        $response = $this->actingAs($this->gestor)
            ->patch(route('gestao.despesas.liquidar', $despesa->id_despesa));

        $response->assertStatus(403);
    }
}
