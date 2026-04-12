<?php

namespace Database\Factories;

use App\Models\LegalEntity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LegalEntity>
 */
class LegalEntityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nome' => fake()->company(),
            'apelido' => fake()->unique()->slug(2),
            'cnpj' => fake()->unique()->numerify('##.###.###/####-##'),
            'sindicato_patronal' => fake()->optional()->company(),
            'sindicato_trabalhadores' => fake()->optional()->company(),
            'ativo' => true,
        ];
    }
}
