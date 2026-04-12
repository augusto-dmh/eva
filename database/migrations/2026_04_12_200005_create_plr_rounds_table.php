<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plr_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ano_referencia')->unique();
            $table->string('documento_politica_path')->nullable();
            $table->boolean('documento_politica_revisado')->default(false);
            $table->string('status_sindicato')->default('nao_iniciado');
            $table->date('data_aprovacao_sindicato')->nullable();
            $table->decimal('valor_total_distribuido', 14, 2)->nullable();
            $table->string('status')->default('rascunho');
            $table->text('observacoes')->nullable();
            $table->foreignId('criado_por_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plr_rounds');
    }
};
