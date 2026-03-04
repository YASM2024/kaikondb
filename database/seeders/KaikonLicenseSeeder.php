<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonLicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('licenses')->insert([
            'id' => 1,
            'code' => 'CC0',
            'name' => 'CC0',
            'summary' => 'Public Domain',
        ]);

        DB::table('licenses')->insert([
            'id' => 2,
            'code' => 'CC-BY',
            'name' => 'CC BY',
            'summary' => 'Attribution',
        ]);

        DB::table('licenses')->insert([
            'id' => 3,
            'code' => 'CC-BY-SA',
            'name' => 'CC BY-SA',
            'summary' => 'Attribution-ShareAlike',
        ]);

        DB::table('licenses')->insert([
            'id' => 4,
            'code' => 'CC-BY-NC',
            'name' => 'CC BY-NC',
            'summary' => 'Attribution-NonCommercial',
        ]);

        DB::table('licenses')->insert([
            'id' => 5,
            'code' => 'ALL-RIGHTS-RESERVED',
            "name" => "ALL_RIGHTS_RESERVED",
            "summary" => "All Rights Reserved",
        ]);

    }
}
