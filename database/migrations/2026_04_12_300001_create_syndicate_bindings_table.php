<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syndicate_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_entity_id')->constrained('legal_entities')->cascadeOnDelete();
            $table->foreignId('syndicate_id')->constrained('syndicates')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['legal_entity_id', 'syndicate_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syndicate_bindings');
    }
};
