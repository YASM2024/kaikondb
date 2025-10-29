<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Exception;

use Kaikon2\Kaikondb\Http\Controllers\Controller;

use Kaikon2\Kaikondb\Models\User;
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
            'is_collected' => 'required|integer|in:0,1',
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
                    'is_collected' => $data['is_collected'],
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
                    'is_collected' => $data['is_collected'],
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

    protected function devideArticleAndSpeciesId(string $article_species)
    {
        $article_random_id = explode("_", $article_species)[0];
        return [
            'article_random_id' => $article_random_id,
            'article_id' => Article::where('random_id', $article_random_id)->value('id'),
            'species_id' => explode("_", $article_species)[1],
        ];
    }

    public function showEdit($article_species){
        try{
            $article_id = $this->devideArticleAndSpeciesId($article_species)['article_id'];
            $species_id = $this->devideArticleAndSpeciesId($article_species)['species_id'];
        }catch(\Exception $e){
            abort(404);
        }
        
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Article::where('id', $article_id)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        $municipalities = Municipality::all();
        $action_type = 'edit';

        $status = RecordingStatus::where('article_id', $article_id)->first();
        $locked = isset($status);
        if ($locked) {
            abort(423, 'Article is locked.');
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
        $record = Record::join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
            ->where('species_id', '=', $species_id)
            ->where('article_id', '=', $article_id);
        $recorded_municipalities = $record->pluck('municipalities.municipality_code')->toArray();
        $recorded_is_collected = $record->first()->is_collected;

        return view('kaikon::records.form', [
            'species_id' => $species_id,
            'municipalities' => @($municipalities), 
            'recorded_municipalities' => $recorded_municipalities,
            'recorded_is_collected' => $recorded_is_collected,
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
        $inputs['user_id'] = Auth::id() ?? 1;
        $inputs['action_type'] = 'edit';


        // 記事がロックされている場合は編集不可
        if ($this->isArticleLocked($inputs['article_id'])) {
            abort(423, 'Article is locked.');
        }
        
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Article::where('id', $inputs['article_id'])->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
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
        $article_id = $request->article_id;
        $species_id = $request->species_id;

        if ($this->isArticleLocked($article_id)) {
            abort(423, 'Article is locked.');
        }
        
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Article::where('id', $article_id)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        DB::beginTransaction();
        if (!$this->deleteRecords($article_id, $species_id)) {
            DB::rollback();
            return "error!";
        }
        DB::commit();
        return redirect()->route('record.create', ['article_id' => $article_id]);
    }

    protected function deleteRecords($article_id, $species_id)
    {
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Article::where('id', $article_id)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }
        
        try {
            Record::where('article_id', $article_id)
                ->where('species_id', $species_id)
                ->delete(); 
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function download(){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        if (!Auth::check()){
                abort(403, 'Unauthorized action.');
            }
        if (User::fromAppUser(Auth::user())->isAdmin()){
            $records = Record::all();
        }
        elseif (User::fromAppUser(Auth::user())->isModerator()){
            $tags = User::fromAppUser(Auth::user())->tags->pluck('id')->toArray();
            $records = Record::whereIn('tag_id', $tags)->get();
        }else{
            abort(403, 'Unauthorized action.');
        }
        // CSVデータ生成
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","article_id","species_id","municipality_id","memo","user_id","created_at","updated_at","deleted_at","is_collected"'."\n";
        fwrite($stream, $csvheader);
        foreach ($records as $record) {
            $csvdata = array(
                $record->id,
                $record->article_id,
                $record->species_id,
                $record->municipality_id,
                $record->memo,
                $record->user_id,
                $record->created_at,
                $record->updated_at,
                $record->deleted_at,
                $record->is_collected,
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=records.csv'
        );

        return Response::make($csv, 200, $headers);
    }

}
