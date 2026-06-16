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
        Schema::create('landmarks', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('prefecture_id');
            $table->string('code', 64);
            $table->string('label');
            $table->decimal('lat', 9, 6);
            $table->decimal('lon', 9, 6);
            $table->string('pattern', 16)->default('mountain');
            $table->unsignedInteger('sort_order')->default(0);

            $table->unique(['prefecture_id', 'code']);
            $table->index(['prefecture_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('landmarks');
    }
};
