<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransacaoReceita extends Model
{
    use HasFactory;

    protected $table = 'transacoes_receitas';
    protected $primaryKey = 'id_receita';

    protected $fillable = [
        'font_receita',
        'codigo_rupe',
        'data_registro',
        'valor_arrecadado',
        'id_classe',
        'id_inst',
    ];

    protected function casts(): array
    {
        return [
            'valor_arrecadado' => 'decimal:2',
            'data_registro' => 'date',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Classificação económica da receita.
     */
    public function classificacaoEconomica(): BelongsTo
    {
        return $this->belongsTo(ClassificacaoEconomica::class, 'id_classe', 'id_classe');
    }

    /**
     * Instituição que arrecadou a receita.
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class, 'id_inst', 'id_inst');
    }
}
