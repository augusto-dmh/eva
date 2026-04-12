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
        Schema::create('pj_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payroll_cycle_id')->constrained()->cascadeOnDelete();
            $table->string('numero_nota');
            $table->decimal('valor', 12, 2);
            $table->string('arquivo_path');
            $table->string('arquivo_nome_original');
            $table->timestamp('data_upload');
            $table->date('data_emissao');
            $table->string('cnpj_emissor', 18);
            $table->string('cnpj_destinatario', 18);
            $table->string('status')->default('pendente');
            $table->text('observacoes')->nullable();
            $table->foreignId('uploaded_by_id')->constrained('users');
            $table->foreignId('revisado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['payroll_cycle_id', 'collaborator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pj_invoices');
    }
};
