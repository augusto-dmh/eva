<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('mes_referencia', 7); // YYYY-MM
            $table->smallInteger('ano')->unsigned();
            $table->tinyInteger('mes')->unsigned();
            $table->string('status')->default('aberto');
            $table->timestamp('data_abertura');
            $table->timestamp('data_fechamento')->nullable();
            $table->date('data_pagamento_folha')->nullable();
            $table->date('data_pagamento_comissao')->nullable();
            $table->decimal('salarios_brutos', 14, 2)->default(0);
            $table->decimal('comissoes', 14, 2)->default(0);
            $table->decimal('deducoes', 14, 2)->default(0);
            $table->decimal('liquido', 14, 2)->default(0);
            $table->decimal('pj', 14, 2)->default(0);
            $table->text('observacoes')->nullable();
            $table->foreignId('fechado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['ano', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_cycles');
    }
};
