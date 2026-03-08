<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('specimens', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained();

            // 採集ラベル情報
            $table->string('locality', 255)->nullable();
            $table->decimal('decimal_latitude', 10, 7)->nullable();
            $table->decimal('decimal_longitude', 10, 7)->nullable();
            $table->string('collection_date_text', 255)->nullable();// 精度が低い、期間、不明の場合に対応
            $table->string('collected_by', 100)->nullable();
            $table->string('owner', 100)->nullable();

            // 同定ラベル情報
            $table->string('species', 255)->nullable();
            $table->string('species_ja', 255)->nullable();
            $table->string('sex', 20)->nullable();
            $table->string('identified_by', 100)->nullable();

            // タイプ標本情報
            $table->string('type_status', 50)->nullable();

            // 画像URL　最大3枚まで
            $table->string('image_1', 255)->nullable();
            $table->string('image_2', 255)->nullable();
            $table->string('image_3', 255)->nullable();

            // 標本の保存方法
            $table->string('preservation_method', 50)->nullable();

            // 標本保管情報
            $table->string('repository_institution', 150)->nullable();
            $table->string('repository_catalog_number', 100)->nullable();

            // 備考
            //公開か非公開
            $table->boolean('is_public')->default(false);
            $table->text('remarks')->nullable();
            $table->foreignId('license_id')->constrained('licenses');
            $table->timestamps();

            // インデックス指定
            $table->index('species');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('specimens');
    }
};
