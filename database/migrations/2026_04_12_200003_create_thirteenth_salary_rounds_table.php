<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thirteenth_salary_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ano_referencia')->unique();
            $table->string('status')->default('aberto');
            $table->date('primeira_parcela_data_limite');
            $table->date('segunda_parcela_data_limite');
            $table->text('observacoes')->nullable();
            $table->foreignId('criado_por_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thirteenth_salary_rounds');
    }
};
