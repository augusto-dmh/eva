<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plr_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plr_round_id')->constrained('plr_rounds')->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->decimal('media_salarios_ano', 12, 2);
            $table->unsignedTinyInteger('meses_trabalhados');
            $table->decimal('valor_simulado', 12, 2);
            $table->decimal('valor_pago', 12, 2)->nullable();
            $table->decimal('desconto_irrf', 12, 2);
            $table->string('status')->default('simulado');
            $table->timestamps();

            $table->unique(['plr_round_id', 'collaborator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plr_entries');
    }
};
