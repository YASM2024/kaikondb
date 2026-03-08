<?php

namespace Kaikon2\KaikondbSeeders;

use Kaikon2\Kaikondb\Models\License;
use Kaikon2\Kaikondb\Models\Specimen;
use Kaikon2\Kaikondb\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class SpecimenTestSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::pluck('id')->all();
        $licenseIds = License::pluck('id')->all();

        if (empty($userIds)) {
            throw new \RuntimeException('users テーブルにデータがありません。先に UserSeeder を実行してください。');
        }

        if (empty($licenseIds)) {
            throw new \RuntimeException('licenses テーブルにデータがありません。先に LicenseSeeder を実行してください。');
        }

        $localities = [
            '山梨県甲府市武田神社周辺',
            '長野県松本市美ヶ原高原',
            '静岡県富士宮市朝霧高原',
            '東京都八王子市高尾山',
            '神奈川県足柄下郡箱根町',
            '埼玉県秩父市三峰山',
            '群馬県吾妻郡草津町',
            '新潟県妙高市いもり池周辺',
            '岐阜県高山市乗鞍岳',
            '福島県南会津郡檜枝岐村',
        ];

        $speciesList = [
            ['species' => 'Carabus insulicola', 'species_ja' => 'オサムシ'],
            ['species' => 'Lucanus maculifemoratus', 'species_ja' => 'ミヤマクワガタ'],
            ['species' => 'Allomyrina dichotoma', 'species_ja' => 'カブトムシ'],
            ['species' => 'Papilio maackii', 'species_ja' => 'ミヤマカラスアゲハ'],
            ['species' => 'Graphium sarpedon', 'species_ja' => 'アオスジアゲハ'],
            ['species' => 'Lethocerus deyrollei', 'species_ja' => 'タガメ'],
            ['species' => 'Anotogaster sieboldii', 'species_ja' => 'オニヤンマ'],
            ['species' => 'Cicindela japonica', 'species_ja' => 'ハンミョウ'],
            ['species' => 'Bombus ignitus', 'species_ja' => 'クロマルハナバチ'],
            ['species' => 'Mantis religiosa', 'species_ja' => 'カマキリ'],
        ];

        $sexes = ['male', 'female', 'unknown'];
        $collectors = ['山田太郎', '佐藤花子', '鈴木一郎', '高橋次郎', '伊藤美咲'];
        $owners = ['個人収蔵', '甲府昆虫同好会', '山梨自然史研究会', '個人コレクション'];
        $identifiers = ['田中博士', '中村研究員', '渡辺学芸員', '小林太郎'];
        $typeStatuses = [null, null, null, 'holotype', 'paratype'];
        $preservationMethods = ['乾燥標本', '液浸標本', '展翅標本', '封入標本'];
        $institutions = ['山梨県立博物館', '個人保管', '甲府市自然史資料室', '信州昆虫標本館'];
        $dateTexts = [
            '2024-05-03',
            '2024-06-12',
            '2024-07-21',
            '2023-08',
            '2022年夏',
            '2021-09-15',
            '採集日不詳',
            '2020-04-01〜2020-04-03',
        ];

        for ($i = 1; $i <= 30; $i++) {
            $species = Arr::random($speciesList);

            Specimen::create([
                'user_id' => Arr::random($userIds),

                // 採集ラベル情報
                'locality' => Arr::random($localities),
                'decimal_latitude' => fake()->optional(0.85)->randomFloat(7, 35.0000000, 37.9999999),
                'decimal_longitude' => fake()->optional(0.85)->randomFloat(7, 138.0000000, 140.9999999),
                'collection_date_text' => Arr::random($dateTexts),
                'collected_by' => Arr::random($collectors),
                'owner' => Arr::random($owners),

                // 同定ラベル情報
                'species' => $species['species'],
                'species_ja' => $species['species_ja'],
                'sex' => Arr::random($sexes),
                'identified_by' => fake()->optional(0.8)->randomElement($identifiers),

                // タイプ標本情報
                'type_status' => Arr::random($typeStatuses),

                // 画像URL
                'image_1' => fake()->optional(0.9)->passthrough("https://example.com/images/specimens/specimen-{$i}-1.jpg"),
                'image_2' => fake()->optional(0.5)->passthrough("https://example.com/images/specimens/specimen-{$i}-2.jpg"),
                'image_3' => fake()->optional(0.3)->passthrough("https://example.com/images/specimens/specimen-{$i}-3.jpg"),

                // 保存方法
                'preservation_method' => Arr::random($preservationMethods),

                // 保管情報
                'repository_institution' => Arr::random($institutions),
                'repository_catalog_number' => 'SPM-' . now()->format('Y') . '-' . str_pad((string)$i, 4, '0', STR_PAD_LEFT),

                // 公開設定・備考
                'is_public' => fake()->boolean(70),
                'remarks' => fake()->optional(0.7)->realText(50),
                'license_id' => Arr::random($licenseIds),
            ]);
        }
    }
}