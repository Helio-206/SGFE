<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nome',
        'username',
        'email',
        'password',
        'role',
        'status',
        'id_inst',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ── Relacionamentos ──────────────────────────────────────

    /**
     * Instituição à qual o utilizador está vinculado.
     */
    public function instituicao(): BelongsTo
    {
        return $this->belongsTo(Instituicao::class, 'id_inst', 'id_inst');
    }

    /**
     * Despesas registradas por este utilizador.
     */
    public function transacoesDespesas(): HasMany
    {
        return $this->hasMany(TransacaoDespesa::class, 'id_user', 'id_user');
    }

    /**
     * Orçamentos criados por este utilizador.
     */
    public function orcamentos(): HasMany
    {
        return $this->hasMany(Orcamento::class, 'id_user', 'id_user');
    }

    // ── Helpers de role ──────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGestor(): bool
    {
        return $this->role === 'gestor';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function isAtivo(): bool
    {
        return $this->status === 'ativo';
    }
}
