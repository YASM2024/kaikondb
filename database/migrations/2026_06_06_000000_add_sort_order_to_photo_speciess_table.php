<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_speciess', function (Blueprint $table) {
            $table->unsignedTinyInteger('sort_order')->default(1)->after('species_id');
        });

        $speciesIds = DB::table('photo_speciess')->distinct()->pluck('species_id');

        foreach ($speciesIds as $speciesId) {
            $pivotIds = DB::table('photo_speciess')
                ->join('photos', 'photos.id', '=', 'photo_speciess.photo_id')
                ->where('photo_speciess.species_id', $speciesId)
                ->orderByDesc('photos.id')
                ->pluck('photo_speciess.id');

            foreach ($pivotIds->values() as $index => $pivotId) {
                DB::table('photo_speciess')
                    ->where('id', $pivotId)
                    ->update(['sort_order' => $index + 1]);
            }
        }

        Schema::table('photo_speciess', function (Blueprint $table) {
            $table->unique(['species_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('photo_speciess', function (Blueprint $table) {
            $table->dropUnique(['species_id', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
