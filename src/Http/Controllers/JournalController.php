<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Kaikon2\Kaikondb\Models\Journal;
use Kaikon2\Kaikondb\Models\Article;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class JournalController extends Controller
{
    
    protected static $rules = [
        'journal_code' => 'required | integer',
        'journal_name_ja' => 'required | string | max:255',
        'journal_name_en' => 'required | string | max:255',
        'url' => 'nullable | string | max:255',
        'category' => 'required | integer',
        'publisher' => 'required | string | max:255',
        'provided_by' => 'nullable | string | max:255',
        'status' => 'required | integer | in:0,1',
    ];

    //
    public function showMaster(){
        $journals = Journal::get()->sortBy('journal_code')->all();
        return view('kaikon::masters.journals',['journals' => $journals]);
    }

    //
    public function all(){
        return Journal::orderBy('journal_code')->get();
    }

    public function show($id)
    {   
        $journal = Journal::where('id', '=', $id)->firstOrFail();
        return $journal;
    }
    
    public function edit($id, Request $request)
    {   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        $journal = Journal::where('id', '=', $id)->firstOrFail();
        $journal->journal_code = $inputs['journal_code'];
        $journal->journal_name_ja = $inputs['journal_name_ja'];
        $journal->journal_name_en = $inputs['journal_name_en'];
        $journal->url = $inputs['url'] ?? '';
        $journal->category = $inputs['category'];
        $journal->publisher = $inputs['publisher'];
        $journal->provided_by = $inputs['provided_by'] ?? '';
        $journal->status = (bool) $inputs['status'];

        $journal->save();

        return ['result' => 'ok'];
    }
    
    public function create(Request $request){   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        //insert 非推奨
        Journal::create([    
            'journal_code' => $inputs['journal_code'],
            'journal_name_ja' => $inputs['journal_name_ja'],
            'journal_name_en' => $inputs['journal_name_en'],
            'url' => $inputs['url'] ?? '',
            'category' => $inputs['category'],
            'publisher' => $inputs['publisher'],
            'provided_by' => $inputs['provided_by'] ?? '',
            'status' => (bool) $inputs['status'],
        ]);

        return ['result' => 'ok'];
    }

    public function editStatus(Request $request)
    {
        $inputs = $request->all();
        $rules = [
            'id' => 'required',
            'status' => 'required | integer | in:0,1',
        ];

        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        $journal = Journal::find($inputs['id']);
        if (!$journal) {
            return ['result' => 'error', 'message' => 'not found'];
        }

        $journal->status = (bool) $inputs['status'];
        $journal->save();

        return ['result' => 'ok'];
    }

    
    public function delete($id)
    {
        if (!$this->isDeletable($id)) { return ['res' => 1]; }// 削除不可
        $journal = Journal::find($id);
        if (!$journal) { return ['res' => 1]; }// 該当なし
        try {
            $journal->delete();
            return ['res' => 0];
        } catch (\Exception $e) {
            return ['res' => 1];// その他のエラー
        }
    }

    
    public function isDeletable($id): bool
    {
        return !Article::where('journal_id', $id)->exists();
    }
    
    public function screeningDelete($id)
    {
        return response()->json(['deletable' => $this->isDeletable($id)]);
    }



    //
    public function downloadMaster(){
            
        $journals = Journal::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","journal_name_ja","journal_name_en","journal_code","category","publisher","url","provided_by"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($journals as $journal) {
            $csvdata = array(
                $journal->id,
                $journal->journal_name_ja,
                $journal->journal_name_en,
                $journal->journal_code,
                $journal->category,
                $journal->publisher,
                $journal->url,
                $journal->provided_by
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=journals.csv'
        );

        return Response::make($csv, 200, $headers);
    }
    
    //
    public function importMaster(){
        return 'importMaster';
    }

}
