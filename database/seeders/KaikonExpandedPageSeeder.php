<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonExpandedPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('expanded_pages')->insert(array('id' => '1','route_name' => 'member','title' => '管理人','title_en' => '管理人','body' => '    <div class="container py-2">
            <div class="text-left bg-light p-3 p-sm-5 mb-4 rounded">
                <h3 class="mb-4">自己紹介</h3>
                <span class="h5">管理人は○○です。好きな昆虫は××です。</span><br>
            </div>
            </div>','seq' => '2','open' => '1','created_at' => '2025-04-22 00:00:00','updated_at' => '2025-04-22 00:00:00'));
        DB::table('expanded_pages')->insert(array('id' => '2','route_name' => 'memo','title' => '調査について','title_en' => '調査について','body' => '    <div class="container py-2">
            <h4 class="ssj mt-3 px-3 px-md-0">XX県昆虫同好会の活動</h4>
            <div class="text-left bg-light px-3 px-sm-5 mb-4">
                <p>～～～～～～～</p>
            </div>
            </div>','seq' => '1','open' => '1','created_at' => '2025-04-22 00:00:00','updated_at' => '2025-04-22 00:00:00'));
        DB::table('expanded_pages')->insert(array('id' => '3','route_name' => 'volunteer','title' => 'ご協力のお願い','title_en' => 'ご協力のお願い','body' => '    <div class="container py-2">
            <div class="text-left bg-light p-3 p-sm-5 pb-0 pb-sm-0 rounded">
                <h2 class="mb-4">○○のお知らせ</h2>
                <p>～～～</p>    
            </div>
            </div>','seq' => '3','open' => '1','created_at' => '2025-04-22 00:00:00','updated_at' => '2025-04-22 00:00:00'));
    
        DB::table('expanded_pages')->insert(array('id' => '4','route_name' => 'map','title' => '県地図','title_en' => '県地図','body' => '','body_en' => '','seq' => '4','open' => '0','created_at' => '2025-04-22 00:00:00','updated_at' => '2025-04-22 00:00:00'));
    }
}
