<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $table = 'audit_logs';

    protected $fillable = [
        'id_user',
        'acao',
        'ip_address',
        'contexto',
    ];

    protected function casts(): array
    {
        return [
            'contexto' => 'array',
        ];
    }
}
