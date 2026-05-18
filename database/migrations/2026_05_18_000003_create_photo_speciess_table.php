<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_speciess', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained('photos')->cascadeOnDelete();
            $table->foreignId('species_id')->constrained('speciess')->cascadeOnDelete();
            $table->unique(['photo_id', 'species_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_speciess');
    }
};
