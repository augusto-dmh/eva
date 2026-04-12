<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistive_convention_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->unsignedSmallInteger('ano_referencia');
            $table->boolean('fez_oposicao')->default(false);
            $table->date('data_oposicao')->nullable();
            $table->string('comprovante_ar_path')->nullable();
            $table->boolean('confirmado_sindicato')->default(false);
            $table->unsignedTinyInteger('parcelas_descontadas')->default(0);
            $table->unsignedTinyInteger('total_parcelas')->default(4);
            $table->decimal('valor_parcela', 10, 2)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->unique(['collaborator_id', 'ano_referencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistive_convention_records');
    }
};
