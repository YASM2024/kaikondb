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

class SpeciesController extends Controller
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
        
        
        return view('kaikon::static.records', ['orders' => $data, 'species_count' => $species_count]);
    }
    


    public function showSearchMenuNew()
    {
        $view = 'ja.search.species_new';
        return view($view);
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

    public function show($id){

        $municipalities = [];
        //種データ
        $sub_records1 = Record::join('speciess', 'records.species_id', '=', 'speciess.id')
            ->where('speciess.random_key', '=', $id)
            ->select('speciess.id as species_id','code as species_code','species_ja','species','random_key')
            ->firstOrFail()
            ->toArray();
        $species_id = $sub_records1['species_id'];

        //文献データ
        $sub_records2 = Record::join('articles', 'records.article_id', '=', 'articles.id')
            ->join('journals', 'articles.journal_id', '=', 'journals.id')
            ->where('species_id', '=', $species_id)
            ->select('random_id AS rdm_id')
            ->selectRaw( "CONCAT(author, '(', year, ')') AS short_summary" )
            ->selectRaw( "CONCAT(title, '.', journal_name_ja, vol_no, ':', page) AS full_summary" )
            ->selectRaw( "MIN(records.id) AS rc_id" )
            ->groupBy('article_id', 'random_id', 'author', 'year', 'title', 'journal_name_ja', 'vol_no', 'page')
            ->get()
            ->toJson();

        //分布データ
        $sub_records3 = Record::join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
            ->where('species_id', '=', $species_id)
            ->select('municipality_code', 'municipality_ja')
            ->groupBy('municipality_id', 'municipality_code', 'municipality_ja')
            ->get();
        $municipalities['codes'] = $sub_records3->implode('municipality_code', ';');
        $municipalities['names'] = $sub_records3->implode('municipality_ja', ';');
    

        //その他データ（備考）
        $memo = Record::where('species_id', '=', $species_id)
            ->where('memo', '!=', '')
            ->select('memo')
            ->get()
            ->unique('memo')
            ->implode('memo','; ');

        return [
            'species' => $sub_records1, 
            'articles' => json_decode($sub_records2), 
            'municipalities' => $municipalities, 
            'memo' => $memo 
        ];
    }


    public function show_new($id){
        $municipalities = [];
    
        // 種データの取得
        $species = Record::join('speciess', 'records.species_id', '=', 'speciess.id')
            ->where('speciess.random_key', '=', $id)
            ->select('speciess.id as species_id', 'code as species_code', 'species_ja', 'species', 'random_key')
            ->firstOrFail()
            ->toArray();
        
        $species_id = $species['species_id'];
    
        // 文献データの取得
        $articles = Record::join('articles', 'records.article_id', '=', 'articles.id')
            ->join('journals', 'articles.journal_id', '=', 'journals.id')
            ->where('species_id', '=', $species_id)
            ->select('records.article_id')
            ->selectRaw("CONCAT(author, '(', year, ')') AS short_summary")
            ->selectRaw("CONCAT(title, '.', journal_name_ja, vol_no, ':', page) AS full_summary")
            ->groupBy('records.article_id', 'author', 'year', 'title', 'journal_name_ja', 'vol_no', 'page')
            ->get()
            ->map(function ($article) use ($species_id){
                $records = Record::select('is_collected', 'municipality_id', 'memo')
                    ->where('article_id', '=', $article->article_id)
                    ->where('species_id', '=', $species_id)
                    ->get()
                    ->groupBy(function ($record) {
                        return $record->is_collected ? 'collections' : 'observations';
                    });
                
                $observations = $records->get('observations', collect());
                $collections = $records->get('collections', collect());
                
                return [
                    "short_summary" => $article->short_summary,
                    "full_summary" => $article->full_summary,
                    "records" => [
                        "observations" => [
                            "codes" => $observations->pluck('municipality_id')->implode(';'),
                            "names" => $observations->pluck('memo')->implode(';')
                        ],
                        "collections" => [
                            "codes" => $collections->pluck('municipality_id')->implode(';'),
                            "names" => $collections->pluck('memo')->implode(';')
                        ]
                    ]
                ];
            });
        
        return [
            'species' => $species,
            'articles' => $articles
        ];
    }
    

    public function showMaster(Request $request){
        
        $keyword = $request->keyword;
        if(isset($request->family_id)){
            $species = Species::where('family_id', '=', $request->family_id)
                ->orderBy('code', 'asc')
                ->get()
                ->map(function ($species) {
                    $species->exists_in_records = Record::where('species_id', $species->id)->exists();
                    return $species;
                });
        }
        if(isset($request->keyword)){
            $species = Species::where('species_ja', 'LIKE', "%{$keyword}%")
                ->orWhere('species', 'LIKE', "%{$keyword}%")
                ->orderBy('code', 'asc')
                ->get()
                ->map(function ($species) {
                    $species->exists_in_records = Record::where('species_id', $species->id)->exists();
                    return $species;
                });
        }
        if(isset($species)){ return $species;}
        return redirect()->route('top');
    }

    public function downloadMaster(){
            
        $speciess = Species::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","species_ja","ginus","species","code","order_id","family_id","random_key","created_at","updated_at"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($speciess as $species) {
            $csvdata = array(
                $species->id,
                $species->species_ja,
                $species->ginus,
                $species->species,
                $species->code,
                $species->order_id,
                $species->family_id,
                $species->random_key,
                $species->created_at,
                $species->updated_at
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=speciess.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    public function importMaster(){
        return "importMaster";
    }

    public function showEditMaster(){
        return view('kaikon::masters.species');
    }

    public function editMaster( Request $request ){

        $inputs = $request->all();
        $rules = [
            'id' => 'nullable | integer', 
            'order_id' => 'required | integer | between:1,30', 
            'family_id' => 'required | integer | between:1,9999', 
            'species_ja' => 'required | string | max:255', 
            'species' => 'required | string | max:255', 
            'code' => 'required| string | max:20'
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'error' ]; }

        DB::beginTransaction();
        try {
            
            if( isset($request->id) ){

                $species = Species::find($inputs['id']);
                $species->ginus = mb_strstr( $inputs['species'], ' ', true);
                $species->species_ja = $inputs['species_ja'];
                $species->species = $inputs['species'];
                $species->code = $inputs['code'];
                $species->save();

            }else{

                //insert 非推奨
                Species::create([
                    'order_id' => $inputs['order_id'],
                    'family_id' => $inputs['family_id'],
                    'ginus' => mb_strstr( $inputs['species'], ' ', true),
                    'species_ja' => $inputs['species_ja'],
                    'species' => $inputs['species'],
                    'code' => $inputs['code'],
                    'random_key' => hash('sha256', uniqid("", true)), 
                ]);
            }
            DB::commit();
            
        } catch (\Exception $e) {

            DB::rollback();
            return ['result'=>'error'];
            // エラーハンドリング
        }

        return ['result'=>'success'];
  
    }

    public function downloadSummary(){
            
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
