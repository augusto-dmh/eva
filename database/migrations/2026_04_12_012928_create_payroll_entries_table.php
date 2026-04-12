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
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained()->cascadeOnDelete();
            $table->string('tipo_contrato');
            $table->foreignId('legal_entity_id')->constrained();
            $table->decimal('salario_bruto', 12, 2)->default(0);
            $table->boolean('salario_proporcional')->default(false);
            $table->tinyInteger('dias_trabalhados')->unsigned()->nullable();
            $table->tinyInteger('dias_uteis_mes')->unsigned()->nullable();
            $table->decimal('valor_comissao_bruta', 12, 2)->default(0);
            $table->decimal('valor_dsr', 12, 2)->default(0);
            $table->decimal('valor_comissao_total', 12, 2)->default(0);
            $table->decimal('desconto_inss', 12, 2)->default(0);
            $table->decimal('desconto_irrf', 12, 2)->default(0);
            $table->decimal('desconto_contribuicao_assistencial', 12, 2)->default(0);
            $table->decimal('desconto_petlove', 10, 2)->default(0);
            $table->decimal('desconto_outros', 10, 2)->default(0);
            $table->string('descricao_desconto_outros')->nullable();
            $table->decimal('bonificacoes', 12, 2)->default(0);
            $table->string('descricao_bonificacoes')->nullable();
            $table->decimal('valor_liquido', 12, 2)->default(0);
            $table->decimal('valor_fgts', 12, 2)->default(0);
            $table->decimal('valor_inss_patronal', 12, 2)->default(0);
            $table->decimal('valor_nota_fiscal_pj', 12, 2)->nullable();
            $table->string('status')->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->unique(['payroll_cycle_id', 'collaborator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
