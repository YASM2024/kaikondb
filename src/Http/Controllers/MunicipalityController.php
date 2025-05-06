<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

use Kaikon2\Kaikondb\Models\Municipality;
use Kaikon2\Kaikondb\Models\Record;

class MunicipalityController extends Controller
{
    protected static $rules = [
        'municipality_code' => 'required | string | max:20', 
        'municipality_ja' => 'required | string | max:20', 
        'municipality_en' => 'required| string | max:20'
    ];
    
    
    public function showMaster()
    {   $municipalities = Municipality::all();
        return view('kaikon::masters.municipalities', ['municipalities' => $municipalities]);
    }

    public function show($id)
    {   
        $municipality = Municipality::where('id', '=', $id)->firstOrFail();
        return $municipality;
    }
    
    public function edit($id, Request $request)
    {   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if( $validation->fails()){ return ['res' => 1 ]; }

        $municipality = Municipality::where('id', '=', $id)->firstOrFail();
        $municipality->municipality_code = $inputs['municipality_code'];
        $municipality->municipality_ja = $inputs['municipality_ja'];
        $municipality->municipality_en = $inputs['municipality_en'];
        $municipality->save();

        return ['res' => 0 ];
    }
    
    public function create(Request $request){   
        $inputs = $request->all();
        $validation = Validator::make($inputs, self::$rules);
        if( $validation->fails()){ return ['res' => 1 ]; }

        //insert 非推奨
        Municipality::create([
            'municipality_code' => $inputs['municipality_code'],
            'municipality_ja' => $inputs['municipality_ja'],
            'municipality_en' => $inputs['municipality_en'],
        ]);

        return ['res' => 0 ];
    }

    
    public function delete($id)
    {
        if (!$this->isDeletable($id)) { return ['res' => 1]; }// 削除不可
        $municipality = Municipality::find($id);
        if (!$municipality) { return ['res' => 1]; }// 該当なし
        try {
            $municipality->delete();
            return ['res' => 0];
        } catch (\Exception $e) {
            return ['res' => 1];// その他のエラー
        }
    }

    
    public function isDeletable($id): bool
    {
        return !Record::where('municipality_id', $id)->exists();
    }
    
    public function screeningDelete($id)
    {
        return response()->json(['deletable' => $this->isDeletable($id)]);
    }
    

    public function downloadMaster(){
            
        $municipalities = Municipality::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","municipality_code","municipality_ja","municipality_en"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($municipalities as $municipality) {
            $csvdata = array(
                $municipality->id,
                $municipality->municipality_code,
                $municipality->municipality_ja,
                $municipality->municipality_en
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=municipalities.csv'
        );

        return Response::make($csv, 200, $headers);
    }
    
    public function importMaster()
    {
        return 'importMaster';
    }
}
