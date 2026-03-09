<?php

namespace Database\Seeders;

use App\Models\ClassificacaoEconomica;
use Illuminate\Database\Seeder;

class RubricasOge2025Seeder extends Seeder
{
    /**
     * Rubricas obrigatórias OGE 2025 (MINFIN)
     */
    public function run(): void
    {
        $rubricas = [
            ['cod_classe' => '01.01', 'descricao' => 'Despesas com Pessoal', 'tipo_receita' => 'Despesas Correntes - Pessoal'],
            ['cod_classe' => '02.01', 'descricao' => 'Bens e Serviços Correntes', 'tipo_receita' => 'Despesas Correntes - Bens e Serviços'],
            ['cod_classe' => '11.01', 'descricao' => 'Investimentos Públicos', 'tipo_receita' => 'Despesas de Capital - Investimentos'],
        ];

        foreach ($rubricas as $rubrica) {
            ClassificacaoEconomica::updateOrCreate(
                ['cod_classe' => $rubrica['cod_classe']],
                [
                    'descricao' => $rubrica['descricao'],
                    'tipo_receita' => $rubrica['tipo_receita'],
                ]
            );
        }
    }
}
