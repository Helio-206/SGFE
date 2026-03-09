<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Instituicao extends Model
{
    use HasFactory;

    protected $table = 'instituicoes';
    protected $primaryKey = 'id_inst';

    protected $fillable = [
        'nome',
        'tipo',
        'codigo',
        'responsavel',
    ];

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Utilizadores vinculados a esta instituição.
     */
    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'id_inst', 'id_inst');
    }

    /**
     * Despesas da instituição.
     */
    public function transacoesDespesas(): HasMany
    {
        return $this->hasMany(TransacaoDespesa::class, 'id_inst', 'id_inst');
    }

    /**
     * Receitas da instituição.
     */
    public function transacoesReceitas(): HasMany
    {
        return $this->hasMany(TransacaoReceita::class, 'id_inst', 'id_inst');
    }

    /**
     * Orçamento da instituição (1:1 por ano fiscal).
     */
    public function orcamento(): HasOne
    {
        return $this->hasOne(Orcamento::class, 'id_inst', 'id_inst')
                    ->where('ano_fiscal', 2025);
    }

    /**
     * Todos os orçamentos históricos da instituição.
     */
    public function orcamentos(): HasMany
    {
        return $this->hasMany(Orcamento::class, 'id_inst', 'id_inst');
    }
}
