<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profiles')->insert([
            'id' => '1',
            'user_id' => '-1',
            'descripttion' => '自己紹介文がありません',
            'icon' => 'anonymousIcon.svg',
            'created_at' => NULL,
            'updated_at' => NULL
        ]);
    }
}
