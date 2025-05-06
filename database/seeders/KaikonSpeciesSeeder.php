<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonSpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('speciess')->insert([
            'id' => '1',
            'species_ja' => 'カブトムシ',
            'ginus' => 'Trypoxylus',
            'species' => 'Trypoxylus dichotomus septentrionalis Kôno, 1931',
            'code' => '230000000001',
            'order_id' => '23',
            'family_id' => '1',
            'random_key' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            'created_at' => NULL,
            'updated_at' => NULL
        ]);
        DB::table('speciess')->insert([
            'id' => '2',
            'species_ja' => 'アゲハチョウ',
            'ginus' => 'Papilio',
            'species' => 'Papilio xuthus Linnaeus, 1767',
            'code' => '280000000001',
            'order_id' => '28',
            'family_id' => '3',
            'random_key' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
            'created_at' => NULL,
            'updated_at' => NULL
        ]);
        DB::table('speciess')->insert([
            'id' => '3',
            'species_ja' => 'アブラゼミ',
            'ginus' => 'Graptopsaltria',
            'species' => 'Graptopsaltria nigrofuscata (Motschulsky, 1866)',
            'code' => '200000000001',
            'order_id' => '20',
            'family_id' => '4',
            'random_key' => 'cccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccccc',
            'created_at' => NULL,
            'updated_at' => NULL
        ]);
    }
}
