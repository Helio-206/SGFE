<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Orcamento extends Model
{
    use HasFactory;

    protected $table = 'orcamentos';
    protected $primaryKey = 'id_orcamento';

    protected $fillable = [
        'id_user',
        'id_inst',
        'valor_total',
        'ano_fiscal',
    ];

    protected function casts(): array
    {
        return [
            'valor_total' => 'decimal:2',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Instituição vinculada ao orçamento (1:1 por ano fiscal).
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class, 'id_inst', 'id_inst');
    }

    /**
     * Utilizador que criou/atribuiu o orçamento.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
