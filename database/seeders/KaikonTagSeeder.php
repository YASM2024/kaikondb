<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('tags')->insert([
            'id' => '23', 
            'name' => '甲虫担当'
        ]);
        DB::table('tags')->insert([
            'id' => '27', 
            'name' => 'ハエアブ担当'
        ]);
        DB::table('tags')->insert([
            'id' => '28', 
            'name' => '蝶蛾担当'
        ]);
        DB::table('tags')->insert([
            'id' => '30', 
            'name' => '膜翅担当'
        ]);
    }
}
