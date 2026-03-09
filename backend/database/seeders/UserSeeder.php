<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed de utilizadores de teste com diferentes papéis e instituições.
     */
    public function run(): void
    {
        // Admin — acesso global (Ministério das Finanças, id_inst = 1)
        User::create([
            'nome'     => 'Administrador do Sistema',
            'username' => 'admin',
            'email'    => 'admin@sgfe.gov.ao',
            'password' => Hash::make('password'),
            'role'     => 'admin',
            'status'   => 'ativo',
            'id_inst'  => 1,
        ]);

        // Gestor — Ministério da Saúde (id_inst = 2)
        User::create([
            'nome'     => 'Gestor da Saúde',
            'username' => 'gestor.saude',
            'email'    => 'gestor@saude.gov.ao',
            'password' => Hash::make('password'),
            'role'     => 'gestor',
            'status'   => 'ativo',
            'id_inst'  => 2,
        ]);

        // Gestor — Administração de Viana (id_inst = 4)
        User::create([
            'nome'     => 'Gestor de Viana',
            'username' => 'gestor.viana',
            'email'    => 'gestor@viana.gov.ao',
            'password' => Hash::make('password'),
            'role'     => 'gestor',
            'status'   => 'ativo',
            'id_inst'  => 4,
        ]);

        // Auditor — acesso read-only (Gov. Provincial Luanda, id_inst = 5)
        User::create([
            'nome'     => 'Auditor Provincial',
            'username' => 'auditor.luanda',
            'email'    => 'auditor@luanda.gov.ao',
            'password' => Hash::make('password'),
            'role'     => 'auditor',
            'status'   => 'ativo',
            'id_inst'  => 5,
        ]);

        // Utilizador inativo — para testar bloqueio de login (RNF08)
        User::create([
            'nome'     => 'Funcionário Inativo',
            'username' => 'inativo.teste',
            'email'    => 'inativo@sgfe.gov.ao',
            'password' => Hash::make('password'),
            'role'     => 'gestor',
            'status'   => 'inativo',
            'id_inst'  => 1,
        ]);
    }
}
