<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Kaikon2\Kaikondb\Support\PrefectureMapConfig;

class KaikonPrefectureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * id は JIS X 0401 の都道府県コード（1–47）。
     * file は public/maps 配下の SVG ファイル名（ゼロ埋め2桁_ローマ字.svg）。
     * 01–07 は SVG 未配置のため、既存ファイルと同じ命名規則で file のみ先行登録。
     */
    public function run(): void
    {
        $prefectures = [
            ['id' => 1, 'prefecture_ja' => '北海道', 'prefecture_en' => 'hokkaido', 'file' => '01_hokkaido.svg'],
            ['id' => 2, 'prefecture_ja' => '青森県', 'prefecture_en' => 'aomori', 'file' => '02_aomori.svg'],
            ['id' => 3, 'prefecture_ja' => '岩手県', 'prefecture_en' => 'iwate', 'file' => '03_iwate.svg'],
            ['id' => 4, 'prefecture_ja' => '宮城県', 'prefecture_en' => 'miyagi', 'file' => '04_miyagi.svg'],
            ['id' => 5, 'prefecture_ja' => '秋田県', 'prefecture_en' => 'akita', 'file' => '05_akita.svg'],
            ['id' => 6, 'prefecture_ja' => '山形県', 'prefecture_en' => 'yamagata', 'file' => '06_yamagata.svg'],
            ['id' => 7, 'prefecture_ja' => '福島県', 'prefecture_en' => 'fukushima', 'file' => '07_fukushima.svg'],
            ['id' => 8, 'prefecture_ja' => '茨城県', 'prefecture_en' => 'ibaraki', 'file' => '08_ibaraki.svg'],
            ['id' => 9, 'prefecture_ja' => '栃木県', 'prefecture_en' => 'tochigi', 'file' => '09_tochigi.svg'],
            ['id' => 10, 'prefecture_ja' => '群馬県', 'prefecture_en' => 'gunma', 'file' => '10_gunma.svg'],
            ['id' => 11, 'prefecture_ja' => '埼玉県', 'prefecture_en' => 'saitama', 'file' => '11_saitama.svg'],
            ['id' => 12, 'prefecture_ja' => '千葉県', 'prefecture_en' => 'chiba', 'file' => '12_chiba.svg'],
            ['id' => 13, 'prefecture_ja' => '東京都', 'prefecture_en' => 'tokyo', 'file' => '13_tokyo.svg'],
            ['id' => 14, 'prefecture_ja' => '神奈川県', 'prefecture_en' => 'kanagawa', 'file' => '14_kanagawa.svg'],
            ['id' => 15, 'prefecture_ja' => '新潟県', 'prefecture_en' => 'niigata', 'file' => '15_niigata.svg'],
            ['id' => 16, 'prefecture_ja' => '富山県', 'prefecture_en' => 'toyama', 'file' => '16_toyama.svg'],
            ['id' => 17, 'prefecture_ja' => '石川県', 'prefecture_en' => 'ishikawa', 'file' => '17_ishikawa.svg'],
            ['id' => 18, 'prefecture_ja' => '福井県', 'prefecture_en' => 'fukui', 'file' => '18_fukui.svg'],
            ['id' => 19, 'prefecture_ja' => '山梨県', 'prefecture_en' => 'yamanashi', 'file' => '19_yamanashi.svg'],
            ['id' => 20, 'prefecture_ja' => '長野県', 'prefecture_en' => 'nagano', 'file' => '20_nagano.svg'],
            ['id' => 21, 'prefecture_ja' => '岐阜県', 'prefecture_en' => 'gifu', 'file' => '21_gifu.svg'],
            ['id' => 22, 'prefecture_ja' => '静岡県', 'prefecture_en' => 'shizuoka', 'file' => '22_shizuoka.svg'],
            ['id' => 23, 'prefecture_ja' => '愛知県', 'prefecture_en' => 'aichi', 'file' => '23_aichi.svg'],
            ['id' => 24, 'prefecture_ja' => '三重県', 'prefecture_en' => 'mie', 'file' => '24_mie.svg'],
            ['id' => 25, 'prefecture_ja' => '滋賀県', 'prefecture_en' => 'shiga', 'file' => '25_shiga.svg'],
            ['id' => 26, 'prefecture_ja' => '京都府', 'prefecture_en' => 'kyoto', 'file' => '26_kyoto.svg'],
            ['id' => 27, 'prefecture_ja' => '大阪府', 'prefecture_en' => 'osaka', 'file' => '27_osaka.svg'],
            ['id' => 28, 'prefecture_ja' => '兵庫県', 'prefecture_en' => 'hyougo', 'file' => '28_hyougo.svg'],
            ['id' => 29, 'prefecture_ja' => '奈良県', 'prefecture_en' => 'nara', 'file' => '29_nara.svg'],
            ['id' => 30, 'prefecture_ja' => '和歌山県', 'prefecture_en' => 'wakayama', 'file' => '30_wakayama.svg'],
            ['id' => 31, 'prefecture_ja' => '鳥取県', 'prefecture_en' => 'tottori', 'file' => '31_tottori.svg'],
            ['id' => 32, 'prefecture_ja' => '島根県', 'prefecture_en' => 'shimane', 'file' => '32_shimane.svg'],
            ['id' => 33, 'prefecture_ja' => '岡山県', 'prefecture_en' => 'okayama', 'file' => '33_okayama.svg'],
            ['id' => 34, 'prefecture_ja' => '広島県', 'prefecture_en' => 'hiroshima', 'file' => '34_hiroshima.svg'],
            ['id' => 35, 'prefecture_ja' => '山口県', 'prefecture_en' => 'yamaguchi', 'file' => '35_yamaguchi.svg'],
            ['id' => 36, 'prefecture_ja' => '徳島県', 'prefecture_en' => 'tokushima', 'file' => '36_tokushima.svg'],
            ['id' => 37, 'prefecture_ja' => '香川県', 'prefecture_en' => 'kagawa', 'file' => '37_kagawa.svg'],
            ['id' => 38, 'prefecture_ja' => '愛媛県', 'prefecture_en' => 'ehime', 'file' => '38_ehime.svg'],
            ['id' => 39, 'prefecture_ja' => '高知県', 'prefecture_en' => 'kouch', 'file' => '39_kouch.svg'],
            ['id' => 40, 'prefecture_ja' => '福岡県', 'prefecture_en' => 'fukuoka', 'file' => '40_fukuoka.svg'],
            ['id' => 41, 'prefecture_ja' => '佐賀県', 'prefecture_en' => 'saga', 'file' => '41_saga.svg'],
            ['id' => 42, 'prefecture_ja' => '長崎県', 'prefecture_en' => 'nagasaki', 'file' => '42_nagasaki.svg'],
            ['id' => 43, 'prefecture_ja' => '熊本県', 'prefecture_en' => 'kumamoto', 'file' => '43_kumamoto.svg'],
            ['id' => 44, 'prefecture_ja' => '大分県', 'prefecture_en' => 'oita', 'file' => '44_oita.svg'],
            ['id' => 45, 'prefecture_ja' => '宮崎県', 'prefecture_en' => 'miyazaki', 'file' => '45_miyazaki.svg'],
            ['id' => 46, 'prefecture_ja' => '鹿児島県', 'prefecture_en' => 'kagoshima', 'file' => '46_kagoshima.svg'],
            ['id' => 47, 'prefecture_ja' => '沖縄県', 'prefecture_en' => 'okinawa', 'file' => '47_okinawa.svg'],
            ['id' => 99, 'prefecture_ja' => 'その他', 'prefecture_en' => 'other', 'file' => null],
        ];

        foreach ($prefectures as $prefecture) {
            DB::table('prefectures')->insert($prefecture);
        }

        PrefectureMapConfig::forget();
    }
}
