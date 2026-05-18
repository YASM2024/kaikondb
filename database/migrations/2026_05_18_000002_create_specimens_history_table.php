<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specimens_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('specimen_id')->nullable();
            $table->foreign('specimen_id')->references('id')->on('specimens')->nullOnDelete();
            $table->string('action', 20);
            $table->unsignedBigInteger('saved_by_user_id')->nullable();
            $table->foreign('saved_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('locality', 255)->nullable();
            $table->decimal('decimal_latitude', 10, 7)->nullable();
            $table->decimal('decimal_longitude', 10, 7)->nullable();
            $table->string('collection_date_text', 255)->nullable();
            $table->string('collected_by', 100)->nullable();
            $table->string('owner', 100)->nullable();
            $table->string('species', 255)->nullable();
            $table->string('species_ja', 255)->nullable();
            $table->string('sex', 20)->nullable();
            $table->string('identified_by', 100)->nullable();
            $table->string('type_status', 50)->nullable();
            $table->string('image_1', 255)->nullable();
            $table->string('image_2', 255)->nullable();
            $table->string('image_3', 255)->nullable();
            $table->string('preservation_method', 50)->nullable();
            $table->string('repository_institution', 150)->nullable();
            $table->string('repository_catalog_number', 100)->nullable();
            $table->boolean('is_public')->default(false);
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('license_id');
            $table->foreign('license_id')->references('id')->on('licenses');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->timestamp('recorded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specimens_history');
    }
};
