<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonFamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('families')->insert([
            'id' => '1',
            'code' => '23001',
            'family_ja' => 'コガネムシ科',
            'family' => 'Scarabidae',
            'order_id' => '23',
        ]);

        DB::table('families')->insert([
            'id' => '2',
            'code' => '23002',
            'family_ja' => 'タマムシ科',
            'family' => 'Buprestidae',
            'order_id' => '23',
        ]);

        DB::table('families')->insert([
            'id' => '3',
            'code' => '28001',
            'family_ja' => 'アゲハチョウ科',
            'family' => 'Papilionidae',
            'order_id' => '28',
        ]);

        DB::table('families')->insert([
            'id' => '4',
            'code' => '20001',
            'family_ja' => 'セミ科',
            'family' => 'Cicadidae',
            'order_id' => '20',
        ]);
    }
}
