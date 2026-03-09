<?php

namespace Database\Seeders;

use App\Models\ClassificacaoEconomica;
use Illuminate\Database\Seeder;

class ClassificacaoEconomicaSeeder extends Seeder
{
    /**
     * Seed de classificações económicas conforme OGE 2025 (RF04).
     */
    public function run(): void
    {
        $classificacoes = [
            ['descricao' => 'Impostos sobre o Rendimento',          'cod_classe' => 'CE-1100', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Impostos sobre Bens e Serviços',       'cod_classe' => 'CE-1200', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Impostos sobre o Comércio Externo',    'cod_classe' => 'CE-1300', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Taxas de Licenciamento',               'cod_classe' => 'CE-2100', 'tipo_receita' => 'Taxas'],
            ['descricao' => 'Taxas de Serviços Públicos',           'cod_classe' => 'CE-2200', 'tipo_receita' => 'Taxas'],
            ['descricao' => 'Contribuições Sociais',                'cod_classe' => 'CE-3100', 'tipo_receita' => 'Contribuições'],
            ['descricao' => 'Receitas Patrimoniais',                'cod_classe' => 'CE-4100', 'tipo_receita' => 'Receitas Patrimoniais'],
            ['descricao' => 'Transferências Correntes',             'cod_classe' => 'CE-5100', 'tipo_receita' => 'Transferências'],
        ];

        foreach ($classificacoes as $cls) {
            ClassificacaoEconomica::create($cls);
        }
    }
}
