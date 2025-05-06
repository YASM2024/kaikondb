<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Models\Article;
use Kaikon2\Kaikondb\Models\Species;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Photo;

class HomeController extends Controller
{   

    public function showTopMenu() {
        $articles_count = Article::where('deleted_at', null)->count();
        $sql_articles_last_update = Article::max('created_at');
        $articles_last_update = date('Y.m.d', strtotime($sql_articles_last_update));

        $species_count = Record::all()->groupBy('species_id')->count();
        $sql_species_last_update = Record::max('created_at');
        $species_last_update = date('Y.m.d', strtotime($sql_species_last_update));

        $photos = Photo::orderBy('id', 'desc')->where('user_id','=','1')->limit(36)->get();

        return view('kaikon::welcome',
        [
            'articles_count'      => !empty($articles_count) ? $articles_count : 0, 
            'articles_last_update'=> $articles_last_update, 
            'species_count'       => !empty($species_count) ? $species_count : 0, 
            'species_last_update' => $species_last_update, 
            'photos'              => $photos
        ]);
    }

    public function showChart(){
        
        //recordのグラフ
        $species_query = Record::select("species_id")->groupBy('species_id')->toSql();
        $s_data = Species::join('orders','speciess.order_id','=','orders.id')
            ->joinSub($species_query,'species_query',function($join){
                $join->on('speciess.id','=','species_query.species_id');
            })
            ->select('order_id','order_ja','order')
            ->selectRaw('COUNT(order_id) as count')
            ->groupBy('order_id','order_ja','order')
            ->orderBy('count','desc')
            ->take(5)
            ->get();

        if(session('language')=='ja'){
            $s_orders = $s_data->pluck('order_ja')->toArray();
        }else{
            $s_orders = $s_data->pluck('order')->toArray();
        }
        $s_counts = $s_data->pluck('count')->toArray();

        $s_res = [
            'labels' => $s_orders,
            'datasets' => [
                [
                    'data' => $s_counts,
                    'backgroundColor' => [
                        "#47395c",
                        "#5c619f",
                        "#5f8bce",
                        "#8bafdf",
                        "#f1b847"
                    ],
                    'weight' => 100
                ]
            ]
        ];
        
        // articlesのグラフ
        $a_data = Article::join('article_order', 'articles.id', '=', 'article_order.article_id')
            ->join('orders', 'orders.id', '=', 'article_order.order_id')
            ->select('orders.id as order_id', 'orders.order', 'orders.order_ja')
            ->selectRaw('COUNT(orders.id) as count')
            ->groupBy('orders.id', 'orders.order', 'orders.order_ja')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();
    
        if(session('language')=='ja'){
            $a_orders = $a_data->pluck('order_ja')->toArray();
        }else{
            $a_orders = $a_data->pluck('order')->toArray();
        }
    
        $a_counts = $a_data->pluck('count')->toArray();
        
        $a_res = [
            'labels' => $a_orders,
            'datasets' => [
                [
                    'data' => $a_counts,
                    'backgroundColor' => [
                        "#47395c",
                        "#5c619f",
                        "#5f8bce",
                        "#8bafdf",
                        "#f1b847"
                    ],
                    'weight' => 100
                ]
            ]
        ];

        return [
            "articles" => $a_res,
            "species" => $s_res,
        ];
    }
}
