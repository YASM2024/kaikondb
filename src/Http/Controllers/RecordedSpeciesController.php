<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Species;
use Kaikon2\Kaikondb\Models\Family;
use Kaikon2\Kaikondb\Models\Order;

class RecordedSpeciesController extends Controller
{
    //

    public function showSearchMenu()
    {
        $records = Record::select("species_id")->groupBy('species_id');
        $species_query = $records->toSql();
        $order = [];

        $data_tmp = Species::join('orders', 'speciess.order_id', '=', 'orders.id')
            ->joinSub($species_query, 'species_query', function($join){
                $join->on('speciess.id', '=', 'species_query.species_id');
            });
    
        $species_count = $data_tmp->count();
    
        $data = $data_tmp->select('order_id', 'orders.order_ja', 'orders.order')
            ->selectRaw('COUNT(order_id) as count')
            ->groupBy('order_id', 'orders.order_ja', 'orders.order')
            ->orderBy('order_id', 'asc')
            ->get();

        $view = 'kaikon::records.index';
        return view($view, ['orders' => $data, 'species_count' => $species_count]);
    }

    public function search( Request $request )
    {
        $category=$request->category;
        $keyword=$request->keyword;
        $code=$request->code;

        $records = Record::select("species_id")->groupBy('species_id'); 
        $species_query = $records->toSql();

        if( $category == 'family' && $code ){

            $family = Family::where('code', '=', $code)->select('family_ja','family','order_id')->firstOrFail()->toArray();
            // array_splice($family, 2, 1)は、$familyから'order_id'(３つめ)を取出し、$familyからは削除する。
            $order = Order::where('id', '=', array_splice($family, 2, 1))->select('id','order_ja','order')->firstOrFail()->toArray();

            $data_tmp = Species::where('families.code', '=', $code)
                ->join('families','speciess.family_id','=','families.id')
                ->joinSub($species_query,'species_query',function($join){
                    $join->on('speciess.id','=','species_query.species_id');
                })->orderBy('speciess.code','asc');
            $species_count = $data_tmp->get()->count(); 
            $data = $data_tmp->select('random_key', 'species_ja','species')->paginate(25);
    
        }elseif( $category == 'order' && $code ){
            
            $family = [];
            $order = Order::where('id', '=', $code)->select('id','order_ja','order')->firstOrFail()->toArray();

            $data_tmp = Species::where('species_ja', 'LIKE', "%{$keyword}%")
                ->orWhere('species', 'LIKE', "%{$keyword}%")
                ->joinSub($species_query, 'species_query', function($join) {
                    $join->on('speciess.id', '=', 'species_query.species_id');
                });
            $species_count = $data_tmp->count();

            $data_tmp = Species::where('families.order_id', '=', $code)
                ->join('families', 'speciess.family_id', '=', 'families.id')
                ->joinSub($species_query, 'species_query', function($join) {
                    $join->on('speciess.id', '=', 'species_query.species_id');
                })
                ->select('families.code', 'family_ja', 'family', DB::raw('COUNT(family_id) as count'), 'families.id as family_id')
                ->groupBy('families.code', 'family_ja', 'family', 'families.id', 'families.order_id')
                ->orderBy('families.code', 'asc');

            $data = $data_tmp->paginate(25);

            $data->getCollection()->transform(function ($item) {
                unset($item->family_id);  // family_idカラムを削除
                return $item;
            });

        }elseif(isset($keyword)){
        
            $family = [];
            $order = [];

            $data_tmp = Species::where('species_ja', 'LIKE', "%{$keyword}%")
                ->orWhere('species', 'LIKE', "%{$keyword}%")
                ->joinSub($species_query,'species_query',function($join){
                    $join->on('speciess.id','=','species_query.species_id');
                });
            $species_count = $data_tmp->get()->count(); 
            $data = $data_tmp->select('random_key', 'species_ja','species')->paginate(25);
    
        }else{

            $family = [];
            $order = [];

            $data_tmp = Species::join('orders','speciess.order_id','=','orders.id')
                ->joinSub($species_query,'species_query',function($join){
                    $join->on('speciess.id','=','species_query.species_id');
                });
            $species_count = $data_tmp->get()->count(); 
            $data = $data_tmp->select('order_id','order_ja','order')->selectRaw('COUNT(order_id) as count')
                ->groupBy('order_id')->paginate(25);

        }
        
        $json = $data->toArray();
        $json['species_count'] = $species_count;
        $json['keyword'] = $keyword;
        $json['order'] = $order;
        $json['family'] = $family;

        $del_keys = ['links', 'first_page_url', 'last_page_url', 'next_page_url', 'prev_page_url'];
        foreach($del_keys as $del_key){
            unset($json[$del_key]);
        };

        return $json;

    }

    public function show(string $id)
    {
        $municipalities = [];

        // 種データの取得
        $species = Record::join('speciess', 'records.species_id', '=', 'speciess.id')
            ->where('speciess.random_key', '=', $id)
            ->select('speciess.id as species_id', 'code as species_code', 'species_ja', 'species', 'random_key')
            ->firstOrFail()
            ->toArray();

        $species_id = $species['species_id'];

        // 文献データの取得
        $literatures = Record::join('literatures', 'records.literature_id', '=', 'literatures.id')
            ->join('journals', 'literatures.journal_id', '=', 'journals.id')
            ->join('users', 'records.user_id', '=', 'users.id')
            ->where('species_id', '=', $species_id)
            ->select('records.literature_id', 'literatures.random_id AS code', 'users.name AS user_name')// code追加
            ->selectRaw("CONCAT(author, '(', year, ')') AS short_summary")
            ->selectRaw("CONCAT(title, '.', journal_name_ja, vol_no, ':', page) AS full_summary")
            ->groupBy('records.literature_id', 'random_id', 'author', 'year', 'title', 'journal_name_ja', 'vol_no', 'page', 'users.name')
            ->get()
            ->map(function ($literature) use ($species_id){
                // municipalityのcodeをJOINして取得
                $records = Record::join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
                    ->select(
                        'records.is_collected',
                        'records.memo',
                        'municipalities.municipality_code',
                        'municipalities.municipality_ja'
                    )
                    ->where('records.literature_id', '=', $literature->literature_id)
                    ->where('records.species_id', '=', $species_id)
                    ->get()
                    ->groupBy(function ($record) {
                        return $record->is_collected ? 'collections' : 'observations';
                    });

                $observations = $records->get('observations', collect());
                $collections = $records->get('collections', collect());

                return [
                    "code" => $literature->code,// 追加
                    "user_name" => $literature->user_name,// 追加
                    "short_summary" => $literature->short_summary,
                    "full_summary" => $literature->full_summary,
                    "records" => [
                        "observations" => [
                            "codes" => $observations->pluck('municipality_code')->implode(';'),
                            "names" => $observations->pluck('municipality_ja')->implode(';')
                        ],
                        "collections" => [
                            "codes" => $collections->pluck('municipality_code')->implode(';'),
                            "names" => $collections->pluck('municipality_ja')->implode(';')
                        ]
                    ]
                ];
            });

        return [
            'species' => $species,
            'literatures' => $literatures,
            'articles' => $literatures,
        ];
    }

    public function downloadSummary()
    {
            
        $results = Record::with(['species.family.order'])
            ->selectRaw('families.code, families.family, families.family_ja, orders.order, orders.order_ja, COUNT(DISTINCT speciess.id) as count')
            ->join('speciess', 'records.species_id', '=', 'speciess.id')
            ->join('families', 'speciess.family_id', '=', 'families.id')
            ->join('orders', 'families.order_id', '=', 'orders.id')
            ->whereNull('records.deleted_at')
            ->groupBy('families.code', 'families.family', 'families.family_ja', 'orders.order', 'orders.order_ja')
            ->orderBy('families.code')
            ->get();

        $stream = fopen('php://temp', 'w');
        $csvheader = '"order","order_ja","family","family_ja","count"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($results as $result) {
            $csvdata = array(
                $result->order,
                $result->order_ja,
                $result->family,
                $result->family_ja,
                $result->count
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=summary.csv'
        );

        return Response::make($csv, 200, $headers);
    }
}
