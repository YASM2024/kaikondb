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
        Schema::create('specimens', function (Blueprint $table) {

            // KAIKON-DB項目
            $table->id();
            $table->unsignedBigInteger('record_id');
            $table->foreign('record_id')->references('id')->on('municipalities');
            $table->timestamps();
            $table->softDeletes();

            // SNS連携項目
            $table->string('google_id')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('twitter_id')->nullable();

            // DarwinCore項目抜粋（全項目に対応していないことに注意）
            $table->unsignedBigInteger('field_notes');
            $table->string('sex')->nullable();                          // 性別
            $table->string('life_stage')->nullable();                   // 生育段階
            $table->string('collection_site')->nullable();              //
            $table->string('state_province')->nullable();               //
            $table->string('county')->nullable();                       // 国：JAPAN
            $table->string('municipality')->nullable();                 // 
            $table->string('verbatim_locality')->nullable();            //
            $table->string('decimal_latitude')->nullable();             //
            $table->string('decimal_longitude')->nullable();            //
            $table->integer('year')->nullable();                        // collected at
            $table->integer('month')->nullable();                       // 
            $table->integer('day')->nullable();                         //
            $table->string('verbatim_event_date')->nullable();          //
            $table->string('recorded_by')->nullable();                  // 記録者
            $table->string('identified_by')->nullable();                // 同定者
            $table->string('owner_institution_code');                   //
            $table->string('type_status');                              // type specimens
            $table->string('associated_media1')->nullable();            // media (image files only)
            $table->string('associated_media2')->nullable();            // 
            $table->string('associated_media3')->nullable();            // 
            $table->string('associated_media4')->nullable();            // 
            $table->string('associated_media5')->nullable();            // 
            $table->string('preparations')->nullable();                 // 標本形式
            $table->integer('rights');                                  // licence
            $table->string('occurrence_remarks')->nullable();           //
            $table->string('collecting_institution')->nullable();       //
            $table->string('occurrence_id')->nullable();                // 
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_user');
    }
};
