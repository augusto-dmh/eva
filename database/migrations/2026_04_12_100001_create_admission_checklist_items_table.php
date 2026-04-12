<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admission_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admission_checklist_id')->constrained('admission_checklists')->cascadeOnDelete();
            $table->string('descricao');
            $table->boolean('obrigatorio')->default(true);
            $table->boolean('confirmado')->default(false);
            $table->timestamp('confirmado_em')->nullable();
            $table->foreignId('confirmado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('documento_path')->nullable();
            $table->text('observacoes')->nullable();
            $table->integer('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admission_checklist_items');
    }
};
