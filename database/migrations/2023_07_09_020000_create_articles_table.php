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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('author');
            $table->string('author_en')->nullable();
            $table->integer('year');
            $table->string('title');
            $table->string('title_en')->nullable();
            $table->string('vol_no');
            $table->unsignedBigInteger('journal_id');
            $table->foreign('journal_id')->references('id')->on('journals');
            $table->string('publisher')->nullable();
            $table->string('page');
            $table->integer('language_id');
            $table->string('memo1')->nullable();
            $table->string('memo2')->nullable();
            $table->string('memo3')->nullable();
            $table->string('memo4')->nullable();
            $table->string('memo5')->nullable();
            $table->string('memo6')->nullable();
            $table->string('memo7')->nullable();
            $table->string('memo8')->nullable();
            $table->string('memo9')->nullable();
            $table->string('memo10')->nullable();
            $table->integer('inventory');
            $table->string('random_id',70)->unique();
            $table->string('link')->nullable();
            $table->string('comment')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('pdf');
            $table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
