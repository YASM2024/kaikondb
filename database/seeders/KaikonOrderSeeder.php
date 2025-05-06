<?php

namespace Kaikon2\Kaikondb\Database\Seeders;

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
        DB::table('orders')->insert(['id' => '1','order_ja' => 'カマアシムシ目','order' => 'Protura']);
        DB::table('orders')->insert(['id' => '2','order_ja' => 'トビムシ目','order' => 'Collembola']);
        DB::table('orders')->insert(['id' => '3','order_ja' => 'コムシ目','order' => 'Diplura']);
        DB::table('orders')->insert(['id' => '4','order_ja' => 'イシノミ目','order' => 'Archaeognatha']);
        DB::table('orders')->insert(['id' => '5','order_ja' => 'シミ目','order' => 'Thysanura']);
        DB::table('orders')->insert(['id' => '6','order_ja' => 'カゲロウ目','order' => 'Ephemeroptera']);
        DB::table('orders')->insert(['id' => '7','order_ja' => 'トンボ目','order' => 'Odonata']);
        DB::table('orders')->insert(['id' => '8','order_ja' => 'カワゲラ目','order' => 'Plecoptera']);
        DB::table('orders')->insert(['id' => '9','order_ja' => 'シロアリモドキ目','order' => 'Embioptera']);
        DB::table('orders')->insert(['id' => '10','order_ja' => '直翅目(バッタ目)','order' => 'Orthoptera']);
        DB::table('orders')->insert(['id' => '11','order_ja' => 'ナナフシ目','order' => 'Phasmida']);
        DB::table('orders')->insert(['id' => '12','order_ja' => 'ハサミムシ目','order' => 'Dermaptera']);
        DB::table('orders')->insert(['id' => '13','order_ja' => 'ゴキブリ目','order' => 'Blattaria']);
        DB::table('orders')->insert(['id' => '14','order_ja' => 'シロアリ目','order' => 'Isoptera']);
        DB::table('orders')->insert(['id' => '15','order_ja' => 'カマキリ目','order' => 'Mantodea']);
        DB::table('orders')->insert(['id' => '16','order_ja' => 'ガロアムシ目','order' => 'Notoptera']);
        DB::table('orders')->insert(['id' => '17','order_ja' => 'チャタテムシ目','order' => 'Psocoptera']);
        DB::table('orders')->insert(['id' => '18','order_ja' => 'ハジラミ目','order' => 'Mallophaga']);
        DB::table('orders')->insert(['id' => '19','order_ja' => 'シラミ目','order' => 'Anoplura']);
        DB::table('orders')->insert(['id' => '20','order_ja' => '半翅目(カメムシ目)','order' => 'Hemiptera']);
        DB::table('orders')->insert(['id' => '21','order_ja' => 'アザミウマ目','order' => 'Thysanoptera']);
        DB::table('orders')->insert(['id' => '22','order_ja' => 'アミメカゲロウ目','order' => 'Neuroptera']);
        DB::table('orders')->insert(['id' => '23','order_ja' => '鞘翅目(甲虫)','order' => 'Coleoptera']);
        DB::table('orders')->insert(['id' => '24','order_ja' => 'ネジレバネ目','order' => 'Strepsiptera']);
        DB::table('orders')->insert(['id' => '25','order_ja' => 'シリアゲムシ目','order' => 'Mecoptera']);
        DB::table('orders')->insert(['id' => '26','order_ja' => 'ノミ目','order' => 'Siphonaptera']);
        DB::table('orders')->insert(['id' => '27','order_ja' => '双翅目(ハエ目)','order' => 'Diptera']);
        DB::table('orders')->insert(['id' => '28','order_ja' => '鱗翅目(チョウ目)','order' => 'Lepidoptera']);
        DB::table('orders')->insert(['id' => '29','order_ja' => 'トビケラ目','order' => 'Trichoptera']);
        DB::table('orders')->insert(['id' => '30','order_ja' => '膜翅目(ハチ目)','order' => 'Hymenoptera']);
    }
}
