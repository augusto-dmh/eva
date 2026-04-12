<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_entities', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('apelido')->unique();
            $table->string('cnpj', 18)->unique();
            $table->string('sindicato_patronal')->nullable();
            $table->string('sindicato_trabalhadores')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_entities');
    }
};
