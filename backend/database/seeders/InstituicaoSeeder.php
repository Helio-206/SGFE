<?php

namespace Database\Seeders;

use App\Models\ClassificacaoEconomica;
use App\Models\Instituicao;
use Illuminate\Database\Seeder;

class InstituicaoSeeder extends Seeder
{
    /**
     * Seed de instituições públicas de Angola (RF02).
     */
    public function run(): void
    {
        $instituicoes = [
            ['nome' => 'Ministério das Finanças',            'tipo' => 'Ministério',                  'codigo' => 'UO-001', 'responsavel' => 'Ministro das Finanças'],
            ['nome' => 'Ministério da Saúde',                'tipo' => 'Ministério',                  'codigo' => 'UO-002', 'responsavel' => 'Ministro da Saúde'],
            ['nome' => 'Ministério da Educação',             'tipo' => 'Ministério',                  'codigo' => 'UO-003', 'responsavel' => 'Ministro da Educação'],
            ['nome' => 'Administração Municipal de Viana',   'tipo' => 'Administração Municipal',     'codigo' => 'UO-004', 'responsavel' => 'Administrador de Viana'],
            ['nome' => 'Governo Provincial de Luanda',       'tipo' => 'Governo Provincial',          'codigo' => 'UO-005', 'responsavel' => 'Governador de Luanda'],
        ];

        foreach ($instituicoes as $inst) {
            Instituicao::create($inst);
        }
    }
}
