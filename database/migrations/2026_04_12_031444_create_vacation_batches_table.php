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
        Schema::create('vacation_batches', function (Blueprint $table) {
            $table->id();
            $table->string('mes_referencia', 7);
            $table->string('tipo');
            $table->unsignedTinyInteger('periodo_aquisitivo_minimo_meses');
            $table->unsignedTinyInteger('dias_ferias');
            $table->string('status')->default('rascunho');
            $table->timestamp('data_abertura')->nullable();
            $table->timestamp('data_fechamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->foreignId('criado_por_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_batches');
    }
};
