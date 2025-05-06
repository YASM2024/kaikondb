<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Kaikon2\Kaikondb\Models\Journal;
use Kaikon2\Kaikondb\Models\Article;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class JournalController extends Controller
{
    
    protected static $rules = [
        'journal_code' => 'required | string | max:20', 
        'journal_name_ja' => 'required | string | max:255', 
        'journal_name_en' => 'required | string | max:255', 
        'url' => 'nullable | string | max:255', 
        'category' => 'required | integer', 
        'publisher' => 'required | string | max:255', 
        'provided_by' => 'nullable | string | max:255', 
    ];

    //
    public function showMaster(){
        $journals = Journal::get()->sortBy('journal_code')->all();
        return view('kaikon::masters.journals',['journals' => $journals]);
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
        if( $validation->fails()){ return ['res' => 1 ]; }

        $journal = Journal::where('id', '=', $id)->firstOrFail();
        $journal->journal_code = $inputs['journal_code'];
        $journal->journal_name_ja = $inputs['journal_name_ja'];
        $journal->journal_name_en = $inputs['journal_name_en'];
        $journal->url = $inputs['url'];
        $journal->category = $inputs['category'];
        $journal->publisher = $inputs['publisher'];
        $journal->provided_by = $inputs['provided_by'];

        $journal->save();

        return ['res' => 0 ];
    }
    
    public function create(Request $request){   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if( $validation->fails()){ return ['res' => 1 ]; }

        //insert 非推奨
        Journal::create([    
            'journal_code' => $inputs['journal_code'],
            'journal_name_ja' => $inputs['journal_name_ja'],
            'journal_name_en' => $inputs['journal_name_en'],
            'url' => $inputs['url'],
            'category' => $inputs['category'],
            'publisher' => $inputs['publisher'],
            'provided_by' => $inputs['provided_by'],
        ]);

        return ['res' => 0 ];
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
