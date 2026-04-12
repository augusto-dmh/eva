<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dissidio_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('ano_referencia');
            $table->date('data_base');
            $table->date('data_publicacao')->nullable();
            $table->decimal('percentual', 6, 4);
            $table->boolean('aplica_estagiarios')->default(false);
            $table->string('status')->default('rascunho');
            $table->text('observacoes')->nullable();
            $table->foreignId('criado_por_id')->constrained('users');
            $table->foreignId('aplicado_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aplicado_em')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dissidio_rounds');
    }
};
