<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('section', 32)->unique();
            $table->boolean('enabled')->default(false);
            $table->text('message_ja')->nullable();
            $table->text('message_en')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_maintenances');
    }
};
