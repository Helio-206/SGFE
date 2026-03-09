<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Instituicao>
 */
class InstituicaoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'tipo' => fake()->randomElement(['Ministério', 'Governo Provincial', 'Instituto Público']),
            'codigo' => fake()->unique()->numerify('####'),
            'responsavel' => fake()->name(),
        ];
    }
}
