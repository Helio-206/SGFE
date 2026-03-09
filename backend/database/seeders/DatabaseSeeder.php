<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * Ordem: Instituições → Classificações Económicas → Utilizadores
     */
    public function run(): void
    {
        $this->call([
            InstituicaoSeeder::class,
            ClassificacaoEconomicaSeeder::class,
            RubricasOge2025Seeder::class,
            UserSeeder::class,
        ]);
    }
}
