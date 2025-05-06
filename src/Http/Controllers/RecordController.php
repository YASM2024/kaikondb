<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Exception;

use Kaikon2\Kaikondb\Http\Controllers\Controller;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Species;
use Kaikon2\Kaikondb\Models\Article;
use Kaikon2\Kaikondb\Models\Municipality;
use Kaikon2\Kaikondb\Models\RecordingStatus;

class RecordController extends Controller
{
    
    public function getInfo_old($code) {
        $species = Species::All()
            ->where('random_key', '=', $code)
            ->firstOrFail()
            ->toArray();

        $articles = Record::All()
            ->where('species_id', '=', $species['id'])
            ->groupBy('species_id')
            ->firstOrFail()
            ->toArray();
        dd($articles);

    }

    public function complete(Request $request) {
        try {
            
            $on = filter_var($request->on, FILTER_VALIDATE_BOOLEAN);
            $validation = Validator::make(
                $request->all(), ['article_id' => 'required|integer|between:1,1000000']
            );
            if ($validation->fails()) { throw new Exception("不正なリクエストが送信されました。"); }
            $exists = RecordingStatus::where('article_id', $request->article_id)->exists();
            if ($on) {
                // ロックする場合
                if ($exists) { throw new Exception("文献ID {$request->article_id}：既にロックされています。"); }
                RecordingStatus::create([
                    'article_id' => $request->article_id,
                    'completed_at' => now()
                ]);
            } else {
                // ロック解除する場合
                if (!$exists) { throw new Exception("文献ID {$request->article_id}：ロックされていないレコードです。"); }
                RecordingStatus::where('article_id', $request->article_id)->delete();
            }
            return ['result' => true];
    
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return ['result' => false, 'message' => $e->getMessage()];
        }
    }

    public function search(){
        return 'search';
    }

    public function show(){
        return 'show';
    }
    
    public function showImport(){
        return view('kaikon::records.import');
    }
    
    public function import( Request $request ){
        return 'import';
    }

    public function showCreate( Request $request ){
        $municipalities = Municipality::all();
        $action_type='create';
        $article_id = @($request->article_id);
        if(isset($article_id)){


            $article_info = Article::join('journals', 'articles.journal_id', '=', 'journals.id')
                ->where('articles.id', '=', $article_id)
                ->select('articles.id AS aid')
                ->selectRaw( "CONCAT(author,',',year,'.',title,'.',journal_name_ja,'.',vol_no,':',page) AS summary" )
                ->firstOrFail()
                ->toArray();

            $status = RecordingStatus::where('article_id', '=', $article_id)->first();
            $locked = isset($status);
            if ($locked) {
                abort(423);
            }
                
        }

        return view('kaikon::records.form', [
            'municipalities' => $municipalities, 
            'article_id' => @($article_info['aid']), 
            'summary' => @($article_info['summary']), 
            'action_type'=>$action_type,
        ]);
    }

    public function create( Request $request ){
        $inputs = $request->all();
        $rules = [];

        $data = $inputs;
        $data['user_id'] = 1;
        $data['action_type'] = 'create';

        $status = RecordingStatus::where('article_id', '=', $data['article_id'])->first();
        $locked = isset($status);
        if ($locked) {
            abort(423);
        }
        
        //表示用
        $data['municipalities_array'] = Municipality::whereIn('municipality_code', $data['municipality_ids_array'])
            ->pluck('municipality_ja')
            ->toArray();
        $data['article_summary'] = Article::where('id','=', $data['article_id'])->SelectSummaryShort()->first()['summary_short'];
        $species_tmp = Species::where('id', '=', $data['species_id'])->first();
        $data['species'] = $species_tmp->species_ja . $species_tmp->species;
        
        if( $request->verified ){

            DB::beginTransaction();
            try {

                $municipality_list = Municipality::pluck('id', 'municipality_code')->toArray();

                foreach($data['municipality_ids_array'] as $code){
                    $new_record = Record::create([
                        'species_id' => $data['species_id'], 
                        'municipality_id' => $municipality_list[$code], //繰り返し
                        'article_id' => $data['article_id'], 
                        'memo' => $data['memo'], 
                        'user_id' => $data['user_id'], 
                    ]);
                }
                DB::commit();

            } catch (\Exception $e) {

                DB::rollback();
                return "error!";

            }

            $data['verified'] = $request->verified; 
            return view('kaikon::records.complete', ['data'=>$data]);

        }

        return view('kaikon::records.confirm', ['data'=>$data]);

    }

    public function showEdit($id){

        $record = Record::where('id','=',$id)->first();
        $municipalities = Municipality::all();
        $action_type = 'edit';
        
        $status = RecordingStatus::where('article_id', '=', $record->article_id)->first();
        $locked = isset($status);
        if ($locked) {
            abort(423);
        }

        //文献データ
        $article_info = Article::join('journals', 'articles.journal_id', '=', 'journals.id')
            ->where('articles.id', '=', $record->article_id)
            ->select('articles.id AS aid')
            ->selectRaw( "CONCAT(author,',',year,'.',title,'.',journal_name_ja,'.',vol_no,':',page) AS summary" )
            ->firstOrFail()
            ->toArray();
        
        //種データ
        $species_info = Record::join('speciess', 'records.species_id', '=', 'speciess.id')
            ->where('speciess.id', '=', $record->species_id)
            ->select('speciess.id AS sid')
            ->selectRaw( "CONCAT(species_ja,' ',species) AS species_all" )
            ->firstOrFail()
            ->toArray();

        //レコード
        $recorded_municipalities = Record::join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
            ->where('species_id', '=', $record->species_id)
            ->where('article_id', '=', $record->article_id)
            ->pluck('municipalities.municipality_code')
            ->toArray();
        
        return view('kaikon::records.form', [
            'species_id' => $record->species_id,
            'municipalities' => @($municipalities), 
            'recorded_municipalities' => $recorded_municipalities,
            'species_all' => $species_info['species_all'],
            'article_id' => @($article_info['aid']), 
            'summary' => @($article_info['summary']), 
            'action_type'=>$action_type,
        ]);
    }

    public function edit(){
        return 'edit';
    }

    public function delete(){
        return 'delete';
    }


}
