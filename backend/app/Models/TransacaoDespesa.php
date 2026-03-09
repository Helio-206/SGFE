<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransacaoDespesa extends Model
{
    use HasFactory;

    protected $table = 'transacoes_despesas';
    protected $primaryKey = 'id_despesa';

    protected $fillable = [
        'estado',
        'descricao',
        'valor_bruto',
        'data_registro',
        'id_inst',
        'id_user',
    ];

    protected function casts(): array
    {
        return [
            'valor_bruto' => 'decimal:2',
            'data_registro' => 'date',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Instituição responsável pela despesa.
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class, 'id_inst', 'id_inst');
    }

    /**
     * Utilizador que registrou a despesa.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
