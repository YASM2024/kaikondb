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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('url');
            $table->string('thumbnail_url');
            $table->string('photo_title');
            $table->string('date');
            $table->string('place');
            $table->string('photographer');
            $table->integer('user_id');
            $table->string('memo');
            $table->integer('heart');
            $table->string('random_sp_id');
            $table->datetime('approved_at');
            $table->string('delpass');
            $table->integer('error_count');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('photos', function (Blueprint $table) {
            $table->datetime('approved_at')->nullable(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
