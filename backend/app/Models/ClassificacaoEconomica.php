<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassificacaoEconomica extends Model
{
    use HasFactory;

    protected $table = 'classificacoes_economicas';
    protected $primaryKey = 'id_classe';

    protected $fillable = [
        'descricao',
        'cod_classe',
        'tipo_receita',
    ];

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Receitas associadas a esta classificação económica.
     */
    public function transacoesReceitas(): HasMany
    {
        return $this->hasMany(TransacaoReceita::class, 'id_classe', 'id_classe');
    }
}
