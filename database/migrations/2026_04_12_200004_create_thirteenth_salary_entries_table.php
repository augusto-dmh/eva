<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thirteenth_salary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thirteenth_salary_round_id')->constrained('thirteenth_salary_rounds')->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->unsignedTinyInteger('meses_trabalhados');
            $table->decimal('salario_base', 12, 2);
            $table->decimal('media_comissoes', 12, 2)->default(0);
            $table->decimal('base_calculo', 12, 2);
            $table->decimal('valor_integral', 12, 2);
            $table->decimal('primeira_parcela_valor', 12, 2);
            $table->decimal('segunda_parcela_valor', 12, 2);
            $table->decimal('desconto_inss', 12, 2);
            $table->decimal('desconto_irrf', 12, 2);
            $table->string('primeira_parcela_status')->default('pendente');
            $table->string('segunda_parcela_status')->default('pendente');
            $table->timestamps();

            $table->unique(['thirteenth_salary_round_id', 'collaborator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thirteenth_salary_entries');
    }
};
