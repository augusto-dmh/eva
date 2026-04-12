<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('termination_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborator_id')->unique()->constrained('collaborators')->cascadeOnDelete();
            $table->string('tipo_desligamento');
            $table->date('data_comunicacao');
            $table->date('data_efetivacao');
            $table->text('motivo')->nullable();
            $table->unsignedTinyInteger('salario_proporcional_dias')->default(0);
            $table->decimal('salario_proporcional_valor', 12, 2)->default(0);
            $table->decimal('ferias_proporcionais_valor', 12, 2)->default(0);
            $table->decimal('terco_ferias_proporcionais', 12, 2)->default(0);
            $table->decimal('decimo_terceiro_proporcional', 12, 2)->default(0);
            $table->decimal('multa_fgts', 12, 2)->default(0);
            $table->decimal('aviso_previo_valor', 12, 2)->default(0);
            $table->decimal('indenizacao_rescisoria', 12, 2)->default(0);
            $table->decimal('valor_total_rescisao', 12, 2)->default(0);
            $table->decimal('ajuste_flash_valor', 10, 2)->default(0);
            $table->boolean('flash_cancelado')->default(false);
            $table->boolean('exame_demissional_agendado')->default(false);
            $table->date('exame_demissional_data')->nullable();
            $table->boolean('previa_contabilidade_solicitada')->default(false);
            $table->boolean('previa_contabilidade_conferida')->default(false);
            $table->boolean('documentos_enviados_rh')->default(false);
            $table->string('status')->default('iniciado');
            $table->foreignId('processado_por_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('termination_records');
    }
};
