<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('records_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('record_id')->nullable();
            $table->foreign('record_id')->references('id')->on('records')->nullOnDelete();
            $table->string('action', 20);
            $table->unsignedBigInteger('saved_by_user_id')->nullable();
            $table->foreign('saved_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unsignedBigInteger('literature_id');
            $table->foreign('literature_id')->references('id')->on('literatures');
            $table->unsignedBigInteger('species_id');
            $table->foreign('species_id')->references('id')->on('speciess');
            $table->unsignedBigInteger('municipality_id');
            $table->foreign('municipality_id')->references('id')->on('municipalities');
            $table->string('memo')->nullable();
            $table->integer('tag_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->integer('is_collected')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('records_history');
    }
};
