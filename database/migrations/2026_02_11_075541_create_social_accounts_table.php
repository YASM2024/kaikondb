<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('platform', 32); // x, instagram, youtube...
            $table->string('account_name', 100)->nullable();
            $table->string('account_url', 2048)->nullable();

            $table->timestamps();
            
            $table->index(['user_id', 'platform']);
            $table->unique(['user_id', 'platform', 'account_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');
    }
};
