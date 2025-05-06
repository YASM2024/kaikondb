<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('roles')->insert([
            'code' => '999', 
            'name' => 'Administrator', 
        ]);
        DB::table('roles')->insert([
            'code' => '010', 
            'name' => 'Moderator', 
        ]);
        DB::table('roles')->insert([
            'code' => '001', 
            'name' => 'User', 
        ]);

    }
}
