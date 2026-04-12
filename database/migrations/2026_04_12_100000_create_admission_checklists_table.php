<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->unique()->constrained('collaborators')->cascadeOnDelete();
            $table->string('tipo_contrato');
            $table->string('status')->default('pendente');
            $table->date('data_limite');
            $table->timestamp('completado_em')->nullable();
            $table->foreignId('completado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_checklists');
    }
};
