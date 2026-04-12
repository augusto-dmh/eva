<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('professional_history_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->string('tipo_evento');
            $table->date('data_efetivacao');
            $table->string('campo_alterado');
            $table->string('valor_anterior')->nullable();
            $table->string('valor_novo')->nullable();
            $table->string('motivo');
            $table->foreignId('dissidio_round_id')->nullable()->constrained('dissidio_rounds')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->foreignId('registrado_por_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('professional_history_entries');
    }
};
