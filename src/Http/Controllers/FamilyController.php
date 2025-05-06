<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Kaikon2\Kaikondb\Models\Family;

class FamilyController extends Controller
{
    //
    public function showMaster(Request $request){
        if(!isset($request->order_id)){ return redirect()->route('top'); }
        $famlies = Family::where('order_id', '=', $request->order_id )
            ->orderBy('code', 'asc')->get();
        return $famlies;
    
    }

    public function downloadMaster(){
            
        $families = Family::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","code","family_ja","family_ja","order_id"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($families as $family) {
            $csvdata = array(
                $family->id,
                $family->code,
                $family->family_ja,
                $family->family_ja,
                $family->order_id
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=families.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    //
    public function importMaster(){
        return 'importMaster';
    }

    public function showEditMaster(){
        return view('kaikon::masters.family');
    }
    
    public function editMaster( Request $request ){

        $inputs = $request->all();
        $rules = [
            'id' => 'nullable | integer', 
            'family' => 'required | string | max:255', 
            'family_ja' => 'required | string | max:255', 
            'code' => 'required | string | max:6', 
            'order_id' => 'required | integer | between:1,30', 
        ];
      
        $validation = Validator::make($inputs,$rules);
        
        if( $validation->fails()){ return ['result'=>'error' ]; }       

        DB::beginTransaction();
        try {
            
            if( isset($request->id) ){
                $family = Family::find($inputs['id']);
                $family->family = $inputs['family'];
                $family->family_ja = $inputs['family_ja'];
                $family->code = $inputs['code'];
                $family->save();
            }else{

                //insert 非推奨
                Family::create([
                    'order_id' => $inputs['order_id'],
                    'family' => $inputs['family'],
                    'family_ja' => $inputs['family_ja'],
                    'code' => $inputs['code']
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

}
