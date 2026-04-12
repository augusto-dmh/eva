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
        Schema::create('vacation_batch_collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vacation_batch_id')->constrained('vacation_batches')->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->date('data_admissao');
            $table->date('periodo_aquisitivo_inicio');
            $table->date('periodo_aquisitivo_fim');
            $table->unsignedTinyInteger('meses_acumulados');
            $table->boolean('elegivel')->default(false);
            $table->date('data_inicio_ferias')->nullable();
            $table->date('data_fim_ferias')->nullable();
            $table->decimal('valor_ferias', 12, 2)->nullable();
            $table->decimal('valor_terco_constitucional', 12, 2)->nullable();
            $table->string('status')->default('pendente');
            $table->boolean('aviso_enviado')->default(false);
            $table->boolean('aviso_assinado')->default(false);
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->unique(['vacation_batch_id', 'collaborator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacation_batch_collaborators');
    }
};
