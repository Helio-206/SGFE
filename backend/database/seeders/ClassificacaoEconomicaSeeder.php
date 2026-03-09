<?php

namespace Database\Seeders;

use App\Models\ClassificacaoEconomica;
use Illuminate\Database\Seeder;

class ClassificacaoEconomicaSeeder extends Seeder
{
    /**
     * Seed de classificações económicas conforme OGE 2025 Angola.
     * Estrutura: Receitas + Despesas (Correntes e Capital)
     */
    public function run(): void
    {
        $classificacoes = [
            // ── RECEITAS ────────────────────────────────────────────────────
            ['descricao' => 'Impostos sobre o Rendimento',                  'cod_classe' => '1.1.00', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Imposto sobre Salários (IRT)',                 'cod_classe' => '1.1.01', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Imposto Industrial',                           'cod_classe' => '1.1.02', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Impostos sobre Bens e Serviços (IVA)',         'cod_classe' => '1.2.00', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Imposto de Consumo',                           'cod_classe' => '1.2.01', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Direitos Aduaneiros',                          'cod_classe' => '1.3.00', 'tipo_receita' => 'Impostos'],
            ['descricao' => 'Taxas de Licenciamento',                       'cod_classe' => '2.1.00', 'tipo_receita' => 'Taxas'],
            ['descricao' => 'Taxas de Registo Civil e Predial',             'cod_classe' => '2.1.01', 'tipo_receita' => 'Taxas'],
            ['descricao' => 'Taxas de Serviços Públicos',                   'cod_classe' => '2.2.00', 'tipo_receita' => 'Taxas'],
            ['descricao' => 'Contribuições para a Segurança Social',        'cod_classe' => '3.1.00', 'tipo_receita' => 'Contribuições'],
            ['descricao' => 'Rendas (Arrendamentos)',                       'cod_classe' => '4.1.00', 'tipo_receita' => 'Receitas Patrimoniais'],
            ['descricao' => 'Dividendos de Empresas Públicas',              'cod_classe' => '4.2.00', 'tipo_receita' => 'Receitas Patrimoniais'],
            ['descricao' => 'Transferências Correntes do Exterior',         'cod_classe' => '5.1.00', 'tipo_receita' => 'Transferências'],
            ['descricao' => 'Receitas de Petróleo',                         'cod_classe' => '6.1.00', 'tipo_receita' => 'Receitas Petrolíferas'],

            // ── DESPESAS CORRENTES ──────────────────────────────────────────
            ['descricao' => 'Despesas com Pessoal e Encargos',              'cod_classe' => '01.00', 'tipo_receita' => 'Despesas Correntes - Pessoal'],
            ['descricao' => 'Remunerações Certas e Permanentes',            'cod_classe' => '01.01', 'tipo_receita' => 'Despesas Correntes - Pessoal'],
            ['descricao' => 'Abonos Variáveis ou Eventuais',                'cod_classe' => '01.02', 'tipo_receita' => 'Despesas Correntes - Pessoal'],
            ['descricao' => 'Segurança Social',                             'cod_classe' => '01.03', 'tipo_receita' => 'Despesas Correntes - Pessoal'],
            ['descricao' => 'Bens e Serviços',                              'cod_classe' => '02.00', 'tipo_receita' => 'Despesas Correntes - Bens e Serviços'],
            ['descricao' => 'Aquisição de Bens',                            'cod_classe' => '02.01', 'tipo_receita' => 'Despesas Correntes - Bens e Serviços'],
            ['descricao' => 'Aquisição de Serviços',                        'cod_classe' => '02.02', 'tipo_receita' => 'Despesas Correntes - Bens e Serviços'],
            ['descricao' => 'Deslocações e Estadas',                        'cod_classe' => '02.03', 'tipo_receita' => 'Despesas Correntes - Bens e Serviços'],
            ['descricao' => 'Encargos das Instalações',                     'cod_classe' => '02.04', 'tipo_receita' => 'Despesas Correntes - Bens e Serviços'],
            ['descricao' => 'Juros e Outros Encargos (Dívida)',             'cod_classe' => '03.00', 'tipo_receita' => 'Despesas Correntes - Juros'],
            ['descricao' => 'Transferências Correntes',                     'cod_classe' => '04.00', 'tipo_receita' => 'Despesas Correntes - Transferências'],
            ['descricao' => 'Subsídios',                                    'cod_classe' => '04.01', 'tipo_receita' => 'Despesas Correntes - Transferências'],

            // ── DESPESAS DE CAPITAL ─────────────────────────────────────────
            ['descricao' => 'Investimentos',                                'cod_classe' => '07.00', 'tipo_receita' => 'Despesas de Capital - Investimentos'],
            ['descricao' => 'Aquisição de Bens de Capital',                 'cod_classe' => '07.01', 'tipo_receita' => 'Despesas de Capital - Investimentos'],
            ['descricao' => 'Construção e Reabilitação de Infraestruturas', 'cod_classe' => '07.02', 'tipo_receita' => 'Despesas de Capital - Investimentos'],
            ['descricao' => 'Equipamento de Transporte',                    'cod_classe' => '07.03', 'tipo_receita' => 'Despesas de Capital - Investimentos'],
            ['descricao' => 'Equipamento Informático',                      'cod_classe' => '07.04', 'tipo_receita' => 'Despesas de Capital - Investimentos'],
            ['descricao' => 'Transferências de Capital',                    'cod_classe' => '08.00', 'tipo_receita' => 'Despesas de Capital - Transferências'],
            ['descricao' => 'Outras Despesas de Capital',                   'cod_classe' => '09.00', 'tipo_receita' => 'Despesas de Capital - Outras'],
        ];

        foreach ($classificacoes as $cls) {
            ClassificacaoEconomica::create($cls);
        }
    }
}
