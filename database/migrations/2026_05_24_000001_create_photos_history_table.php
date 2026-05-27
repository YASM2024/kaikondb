<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('photo_id')->nullable();
            $table->foreign('photo_id')->references('id')->on('photos')->nullOnDelete();
            $table->string('action', 20);
            $table->unsignedBigInteger('saved_by_user_id')->nullable();
            $table->foreign('saved_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('photo_title')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photos_history');
    }
};
