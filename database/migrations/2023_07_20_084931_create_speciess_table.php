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
        Schema::create('speciess', function (Blueprint $table) {
            $table->id();
            $table->string("species_ja");
            $table->string("ginus");
            $table->string("species");
            $table->string("code");
            $table->integer("order_id");
            $table->integer("family_id");
            $table->string("random_key");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('speciess');
    }
};
