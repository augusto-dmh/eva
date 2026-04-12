<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plr_committee_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plr_round_id')->constrained('plr_rounds')->cascadeOnDelete();
            $table->foreignId('collaborator_id')->constrained('collaborators')->cascadeOnDelete();
            $table->foreignId('legal_entity_id')->constrained('legal_entities')->cascadeOnDelete();
            $table->string('papel');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plr_committee_members');
    }
};
