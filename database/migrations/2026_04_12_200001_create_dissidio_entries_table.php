<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dissidio_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dissidio_round_id')->constrained('dissidio_rounds')->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->decimal('salario_anterior', 12, 2);
            $table->decimal('percentual_aplicado', 6, 4);
            $table->decimal('salario_novo', 12, 2);
            $table->decimal('diferenca_retroativa', 12, 2)->default(0);
            $table->tinyInteger('meses_retroativos')->unsigned()->default(0);
            $table->string('status')->default('simulado');
            $table->timestamps();
            $table->unique(['dissidio_round_id', 'collaborator_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dissidio_entries');
    }
};
