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
        Schema::create('expanded_pages', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('title');
            $table->string('title_en');
            $table->text('body');
            $table->text('body_en');
            $table->integer('seq');
            $table->boolean('open');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expanded_pages');
    }
};
