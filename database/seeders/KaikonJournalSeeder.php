<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonJournalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('journals')->insert([
            'journal_name_ja' => 'テストジャーナル', 
            'journal_name_en' => 'test', 
            'journal_code' => '000001', 
            'category' => '000001', 
            'publisher' => 'test', 
            'url' => 'https://test.example.com', 
            'provided_by' => 'test', 
        ]);
    }
}
