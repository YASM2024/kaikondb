<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonMunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('municipalities')->insert([
            'id' => '1',
            'municipality_code' => '192015',
            'municipality_ja' => '甲府市',
            'municipality_en' => 'Kofu-city'
        ]);
        
        DB::table('municipalities')->insert([
            'id' => '2',
            'municipality_code' => '192023',
            'municipality_ja' => '富士吉田市',
            'municipality_en' => 'Fujiyoshida-city'
        ]);
                
        DB::table('municipalities')->insert([
            'id' => '3',
            'municipality_code' => '192040',
            'municipality_ja' => '都留市',
            'municipality_en' => 'Tsuru-city'
        ]);
        
        DB::table('municipalities')->insert([
            'id' => '4',
            'municipality_code' => '192058',
            'municipality_ja' => '山梨市',
            'municipality_en' => 'Yamanashi-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '5',
            'municipality_code' => '192066',
            'municipality_ja' => '大月市',
            'municipality_en' => 'Otsuki-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '6',
            'municipality_code' => '192074',
            'municipality_ja' => '韮崎市',
            'municipality_en' => 'Nirasaki-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '7',
            'municipality_code' => '192082',
            'municipality_ja' => '南アルプス市',
            'municipality_en' => 'Minamialps-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '8',
            'municipality_code' => '192091',
            'municipality_ja' => '北杜市',
            'municipality_en' => 'Hokuto-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '9',
            'municipality_code' => '192104',
            'municipality_ja' => '甲斐市',
            'municipality_en' => 'Kai-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '10',
            'municipality_code' => '192112',
            'municipality_ja' => '笛吹市',
            'municipality_en' => 'Fuefuki-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '11',
            'municipality_code' => '192121',
            'municipality_ja' => '上野原市',
            'municipality_en' => 'Uenohara-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '12',
            'municipality_code' => '192139',
            'municipality_ja' => '甲州市',
            'municipality_en' => 'Kosyu-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '13',
            'municipality_code' => '192147',
            'municipality_ja' => '中央市',
            'municipality_en' => 'Chuo-city'
        ]);

        DB::table('municipalities')->insert([
            'id' => '14',
            'municipality_code' => '193461',
            'municipality_ja' => '市川三郷町',
            'municipality_en' => 'Ichikawamisato-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '15',
            'municipality_code' => '193640',
            'municipality_ja' => '早川町',
            'municipality_en' => 'Hayakawa-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '16',
            'municipality_code' => '193658',
            'municipality_ja' => '身延町',
            'municipality_en' => 'Minobu-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '17',
            'municipality_code' => '193666',
            'municipality_ja' => '南部町',
            'municipality_en' => 'Nanbu-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '18',
            'municipality_code' => '193682',
            'municipality_ja' => '富士川町',
            'municipality_en' => 'Fujikawa-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '19',
            'municipality_code' => '193844',
            'municipality_ja' => '昭和町',
            'municipality_en' => 'Showa-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '20',
            'municipality_code' => '194221',
            'municipality_ja' => '道志村',
            'municipality_en' => 'Doshi-village'
        ]);

        DB::table('municipalities')->insert([
            'id' => '21',
            'municipality_code' => '194239',
            'municipality_ja' => '西桂町',
            'municipality_en' => 'Nishikatsura-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '22',
            'municipality_code' => '194247',
            'municipality_ja' => '忍野村',
            'municipality_en' => 'Oshino-village'
        ]);

        DB::table('municipalities')->insert([
            'id' => '23',
            'municipality_code' => '194255',
            'municipality_ja' => '山中湖村',
            'municipality_en' => 'Yamanakako-village'
        ]);

        DB::table('municipalities')->insert([
            'id' => '24',
            'municipality_code' => '194298',
            'municipality_ja' => '鳴沢村',
            'municipality_en' => 'Narusawa-village'
        ]);

        DB::table('municipalities')->insert([
            'id' => '25',
            'municipality_code' => '194301',
            'municipality_ja' => '富士河口湖町',
            'municipality_en' => 'Fujikawaguchiko-town'
        ]);

        DB::table('municipalities')->insert([
            'id' => '26',
            'municipality_code' => '194425',
            'municipality_ja' => '小菅村',
            'municipality_en' => 'Kosuge-village'
        ]);

        DB::table('municipalities')->insert([
            'id' => '27',
            'municipality_code' => '194433',
            'municipality_ja' => '丹波山村',
            'municipality_en' => 'Tabayama-village'
        ]);

        DB::table('municipalities')->insert([
            'id' => '28',
            'municipality_code' => '199900',
            'municipality_ja' => '詳細不明',
            'municipality_en' => 'unknown'
        ]);
    }
}
