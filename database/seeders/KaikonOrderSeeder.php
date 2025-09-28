<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use DB;

class KaikonOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orders')->insert(['id' => '1','order_ja' => 'カマアシムシ目','order' => 'Protura', 'code' => '010']);
        DB::table('orders')->insert(['id' => '2','order_ja' => 'トビムシ目','order' => 'Collembola', 'code' => '020']);
        DB::table('orders')->insert(['id' => '3','order_ja' => 'コムシ目','order' => 'Diplura', 'code' => '030']);
        DB::table('orders')->insert(['id' => '4','order_ja' => 'イシノミ目','order' => 'Archaeognatha', 'code' => '040']);
        DB::table('orders')->insert(['id' => '5','order_ja' => 'シミ目','order' => 'Thysanura', 'code' => '050']);
        DB::table('orders')->insert(['id' => '6','order_ja' => 'カゲロウ目','order' => 'Ephemeroptera', 'code' => '060']);
        DB::table('orders')->insert(['id' => '7','order_ja' => 'トンボ目','order' => 'Odonata', 'code' => '070']);
        DB::table('orders')->insert(['id' => '8','order_ja' => 'カワゲラ目','order' => 'Plecoptera', 'code' => '080']);
        DB::table('orders')->insert(['id' => '9','order_ja' => 'シロアリモドキ目','order' => 'Embioptera', 'code' => '090']);
        DB::table('orders')->insert(['id' => '10','order_ja' => '直翅目(バッタ目)','order' => 'Orthoptera', 'code' => '100']);
        DB::table('orders')->insert(['id' => '11','order_ja' => 'ナナフシ目','order' => 'Phasmida', 'code' => '110']);
        DB::table('orders')->insert(['id' => '12','order_ja' => 'ハサミムシ目','order' => 'Dermaptera', 'code' => '120']);
        DB::table('orders')->insert(['id' => '13','order_ja' => 'ゴキブリ目','order' => 'Blattaria', 'code' => '130']);
        DB::table('orders')->insert(['id' => '14','order_ja' => 'シロアリ目','order' => 'Isoptera', 'code' => '140']);
        DB::table('orders')->insert(['id' => '15','order_ja' => 'カマキリ目','order' => 'Mantodea', 'code' => '150']);
        DB::table('orders')->insert(['id' => '16','order_ja' => 'ガロアムシ目','order' => 'Notoptera', 'code' => '160']);
        DB::table('orders')->insert(['id' => '17','order_ja' => 'チャタテムシ目','order' => 'Psocoptera', 'code' => '170']);
        DB::table('orders')->insert(['id' => '18','order_ja' => 'ハジラミ目','order' => 'Mallophaga', 'code' => '180']);
        DB::table('orders')->insert(['id' => '19','order_ja' => 'シラミ目','order' => 'Anoplura', 'code' => '190']);
        DB::table('orders')->insert(['id' => '20','order_ja' => '半翅目(カメムシ目)','order' => 'Hemiptera', 'code' => '200']);
        DB::table('orders')->insert(['id' => '21','order_ja' => 'アザミウマ目','order' => 'Thysanoptera', 'code' => '210']);
        DB::table('orders')->insert(['id' => '22','order_ja' => 'アミメカゲロウ目','order' => 'Neuroptera', 'code' => '220']);
        DB::table('orders')->insert(['id' => '23','order_ja' => '鞘翅目(甲虫)','order' => 'Coleoptera', 'code' => '230']);
        DB::table('orders')->insert(['id' => '24','order_ja' => 'ネジレバネ目','order' => 'Strepsiptera', 'code' => '240']);
        DB::table('orders')->insert(['id' => '25','order_ja' => 'シリアゲムシ目','order' => 'Mecoptera', 'code' => '250']);
        DB::table('orders')->insert(['id' => '26','order_ja' => 'ノミ目','order' => 'Siphonaptera', 'code' => '260']);
        DB::table('orders')->insert(['id' => '27','order_ja' => '双翅目(ハエ目)','order' => 'Diptera', 'code' => '270']);
        DB::table('orders')->insert(['id' => '28','order_ja' => '鱗翅目(チョウ目)','order' => 'Lepidoptera', 'code' => '280']);
        DB::table('orders')->insert(['id' => '29','order_ja' => 'トビケラ目','order' => 'Trichoptera', 'code' => '290']);
        DB::table('orders')->insert(['id' => '30','order_ja' => '膜翅目(ハチ目)','order' => 'Hymenoptera', 'code' => '300']);
        DB::table('orders')->insert(['id' => '31','order_ja' => '広翅目（ヘビトンボ目）','order' => 'Megaloptera', 'code' => '216']);
        DB::table('orders')->insert(['id' => '32','order_ja' => '駱駝虫目（ラクダムシ目）','order' => 'Raphidioptera', 'code' => '218']);
    }
}
