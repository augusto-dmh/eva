<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->bigInteger('nexus_employee_id')->nullable()->unique();

            // Personal data
            $table->string('nome_completo');
            $table->string('cpf', 14)->unique();
            $table->string('email_corporativo')->unique();
            $table->string('email_pessoal')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('telefone', 20)->nullable();

            // Employment
            $table->string('tipo_contrato');
            $table->foreignId('legal_entity_id')->constrained();
            $table->string('departamento')->nullable();
            $table->string('cargo')->nullable();
            $table->string('nivel')->nullable();
            $table->string('trilha_carreira')->nullable();
            $table->string('lider_direto')->nullable();
            $table->string('status')->default('ativo');
            $table->date('data_admissao');
            $table->date('data_desligamento')->nullable();

            // Flash Benefits
            $table->string('flash_numero_cartao')->nullable();
            $table->decimal('flash_vale_alimentacao', 10, 2)->nullable();
            $table->decimal('flash_vale_refeicao', 10, 2)->nullable();
            $table->decimal('flash_vale_transporte', 10, 2)->nullable();
            $table->decimal('flash_saude', 10, 2)->nullable();
            $table->decimal('flash_cultura', 10, 2)->nullable();
            $table->decimal('flash_educacao', 10, 2)->nullable();
            $table->decimal('flash_home_office', 10, 2)->nullable();
            $table->decimal('flash_total', 10, 2)->nullable();

            // Compensation
            $table->decimal('salario_base', 12, 2);
            $table->string('tipo_comissao')->default('none');
            $table->decimal('minimo_garantido', 12, 2)->nullable();
            $table->boolean('elegivel_comissao')->default(false);
            $table->decimal('desconto_petlove', 10, 2)->nullable();

            // Banking
            $table->string('banco')->nullable();
            $table->string('agencia')->nullable();
            $table->string('conta')->nullable();
            $table->string('chave_pix')->nullable();

            // Integration
            $table->string('pis')->nullable();
            $table->string('slack_user_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_contrato');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborators');
    }
};
