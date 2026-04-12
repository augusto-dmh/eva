<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin DP',
            'email' => 'admin@clubedovalor.com.br',
        ]);

        User::factory()->create([
            'name' => 'Colaborador Teste',
            'email' => 'colaborador@clubedovalor.com.br',
        ]);

        $this->call([
            LegalEntitySeeder::class,
            CollaboratorSeeder::class,
            PayrollCycleSeeder::class,
            VacationBatchSeeder::class,
            DissidioSeeder::class,
            ThirteenthSalarySeeder::class,
            PlrSeeder::class,
            TerminationSeeder::class,
            AssistiveConventionSeeder::class,
        ]);
    }
}
