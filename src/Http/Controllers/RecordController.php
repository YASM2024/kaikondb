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

    /* レコード作成
    */
    public function create(Request $request)
    {
        $inputs = $this->validateRequest($request);
        $inputs['user_id'] = 1;
        $inputs['action_type'] = 'create';

        if ($this->isArticleLocked($inputs['article_id'])) {
            abort(423);
        }

        $data = $this->prepareDisplayData($inputs);
        if ($request->verified) {
            if (!$this->createRecords($inputs)) {
                return "error!";
            }

            $data['verified'] = true;
            return view('kaikon::records.complete', ['data' => $data]);
        }
        
        return view('kaikon::records.confirm', ['data' => $data]);
    }

    /* バリデーションルール
    */  
    protected function validateRequest(Request $request)
    {
        return $request->validate([
            '_token' => 'required|string',
            'article_id' => 'required|integer',
            'species_id' => 'required|integer',
            'municipality_ids_array' => 'required|array',
            'rdb' => 'nullable|string',
            'memo' => 'nullable|string',
            'verified' => 'boolean',
        ]);
    }

    /* 文献IDがロックされているか確認
    */
    protected function isArticleLocked($articleId)
    {
        return RecordingStatus::where('article_id', $articleId)->exists();
    }


    /* 表示用データの準備
    */
    protected function prepareDisplayData(array $data)
    {
        $data['municipalities_array'] = Municipality::whereIn('municipality_code', $data['municipality_ids_array'])
            ->pluck('municipality_ja')
            ->toArray();

        $data['article_summary'] = Article::where('id', $data['article_id'])->SelectSummaryShort()->value('summary_short');

        $species = Species::find($data['species_id']);
        $data['species'] = $species->species_ja . $species->species;

        return $data;
    }

    /* レコードの作成
    */
    protected function createRecords(array $data)
    {
        DB::beginTransaction();
        try {
            $municipalityList = Municipality::pluck('id', 'municipality_code')->toArray();

            foreach ($data['municipality_ids_array'] as $code) {
                Record::create([
                    'species_id' => $data['species_id'],
                    'municipality_id' => $municipalityList[$code],
                    'article_id' => $data['article_id'],
                    'memo' => $data['memo'],
                    'user_id' => $data['user_id'],
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    protected function updateRecords(array $data)
    {
        DB::beginTransaction();
        try {
            $municipalityList = Municipality::pluck('id', 'municipality_code')->toArray();

            // 既存のレコードを削除してから新規作成
            Record::where('article_id', $data['article_id'])
                ->where('species_id', $data['species_id'])->delete(); 
            foreach ($data['municipality_ids_array'] as $code) {
                Record::create([
                    'species_id' => $data['species_id'],
                    'municipality_id' => $municipalityList[$code],
                    'article_id' => $data['article_id'],
                    'memo' => $data['memo'],
                    'user_id' => $data['user_id'],
                ]);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }
    }

    public function showEdit($article_species){
        $municipalities = Municipality::all();
        $action_type = 'edit';
        
        $article_random_id = explode("_", $article_species)[0];
        $article_id = Article::where('random_id', $article_random_id)->value('id');
        $species_id = explode("_", $article_species)[1];

        $status = RecordingStatus::where('article_id', $article_id)->first();
        $locked = isset($status);
        if ($locked) {
            abort(423);
        }

        //文献データ
        $article_info = Article::join('journals', 'articles.journal_id', '=', 'journals.id')
            ->where('articles.id', '=', $article_id)
            ->select('articles.id AS aid')
            ->selectRaw( "CONCAT(author,',',year,'.',title,'.',journal_name_ja,'.',vol_no,':',page) AS summary" )
            ->firstOrFail()
            ->toArray();
        
        //種データ
        $species_info = Record::join('speciess', 'records.species_id', '=', 'speciess.id')
            ->where('speciess.id', '=', $species_id)
            ->select('speciess.id AS sid')
            ->selectRaw( "CONCAT(species_ja,' ',species) AS species_all" )
            ->firstOrFail()
            ->toArray();

        //レコード
        $recorded_municipalities = Record::join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
            ->where('species_id', '=', $species_id)
            ->where('article_id', '=', $article_id)
            ->pluck('municipalities.municipality_code')
            ->toArray();
        
        return view('kaikon::records.form', [
            'species_id' => $species_id,
            'municipalities' => @($municipalities), 
            'recorded_municipalities' => $recorded_municipalities,
            'species_all' => $species_info['species_all'],
            'article_id' => @($article_info['aid']), 
            'summary' => @($article_info['summary']), 
            'action_type'=>$action_type,
        ]);
    }

    

    /* レコード編集 */
    public function edit(Request $request)
    {
        $inputs = $this->validateRequest($request);
        $inputs['user_id'] = 1;
        $inputs['action_type'] = 'edit';

        // 記事がロックされている場合は編集不可
        if ($this->isArticleLocked($inputs['article_id'])) {
            abort(423);
        }

        $data = $this->prepareDisplayData($inputs);


        if ($request->verified) {
            if (!$this->updateRecords($inputs)) {
                return "error!";
            }

            $data['verified'] = true;
            return view('kaikon::records.complete', ['data' => $data]);
        }

        return view('kaikon::records.confirm', ['data' => $data]);
    }


    /* レコード編集 */
    public function delete(Request $request)
    {
        return $request;
    }

}
