<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use Kaikon2\Kaikondb\Models\ExpandedPage;

class ExpandedPageController extends Controller
{
    //
    public function index(){
        $expanded_pages = ExpandedPage::orderBy('seq', 'asc')->get();
        return view('kaikon::expanded.index', ['expanded_pages' => $expanded_pages]);
    }

    public function show($route_name){
        $expanded_page = ExpandedPage::where('route_name', '=', $route_name)
            ->where('open', '=', 1)->firstOrFail();
        $header = $expanded_page->title;
        $body = $expanded_page->body;
        return view('kaikon::expanded.show', ['header' => $header, 'body' => $body]);
    }

    public function showForm($route_name = null){
        if($route_name == null){
            $action_type = 'create';
            $page = null;
        }else{
            $action_type = 'edit';
            $page = ExpandedPage::where('route_name', '=', $route_name)->firstOrFail();
        }
        $seqs = ExpandedPage::orderBy('seq', 'asc')->pluck('seq')->toArray();

        return view('kaikon::expanded.form', ['page' => $page, 'seqs' => $seqs, 'action_type' => $action_type]);
    }

    public function create(Request $request){
        // バリデーションルール
        $inputs = $request->all();
        $rules = [
            'route_name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'seq' => 'required|integer'
        ];
        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) { return ['res' => 1]; }

        $newSeq = (int)$request->input('seq');
        DB::beginTransaction();
        try{
            ExpandedPage::where('seq', '>=', $newSeq)->increment('seq');
            ExpandedPage::create([
                'route_name' => $request->input('route_name'),
                'title' => $request->input('title'),
                'title_en' => $request->input('title_en') ?? $request->input('title'),
                'body' => $request->input('body'),
                'body_en' => $request->input('body_en') ?? $request->input('body'),
                'open' => 0,
                'seq' => $newSeq
            ]);
            DB::commit();
            return ['res' => 0];
        }catch(\Exception $e){
            DB::rollback();
            return ['res'=> 1];
        }
    }

    public function update(Request $request){
        // バリデーションルール
        $inputs = $request->all();
        $rules = [
            'id' => 'required|integer',
            'route_name' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'body' => 'nullable|string',
            'body_en' => 'nullable|string',
            'open' => 'nullable|integer',
            'seq' => 'nullable|integer'
        ];
        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) { return ['res' => 1]; }
        $id = $request->input('id');
        if($id == null) return ['res' => 1];
        $newSeq = $request->input('seq') !== null ? (int)$request->input('seq') : null;

        DB::beginTransaction();
        try{
            $record = ExpandedPage::where('id', $id)->first();
            $oldSeq = (int) $record->seq;

            if($newSeq !== null){
                if ($newSeq > $oldSeq) {
                    // 例: seq 2 -> seq 5 の場合、3～5 のレコードは前詰め（seqを1減算）
                    ExpandedPage::whereBetween('seq', [$oldSeq + 1, $newSeq])->decrement('seq');
                } elseif ($newSeq < $oldSeq) {
                    // 例: seq 5 -> seq 2 の場合、2～4 のレコードは後ろシフト（seqを1加算）
                    ExpandedPage::whereBetween('seq', [$newSeq, $oldSeq - 1])->increment('seq');
                }
            }
            if($request->input('title') !== null) { $record->title = $request->input('title'); }
            if($request->input('title') !== null) { $record->title_en = $request->input('title_en') ?? $request->input('title'); }
            if($request->input('body') !== null) { $record->body = $request->input('body'); }
            if($request->input('body') !== null) { $record->body_en = $request->input('body_en') ?? $request->input('body'); }
            if($newSeq !== null ) { $record->seq = $newSeq; }
            if($request->input('open') !== null) { $record->open = $request->input('open'); }
            $record->save();

            DB::commit();
            
        }catch(\Exception $e){

            DB::rollback();
            return ['res'=> 1];

        }
        
        return ['res'=> 0];
    }
    
    public function delete(Request $request){
        try{
            $id = $request->input('id');
            if($id == null) return ['res' => 1];
            $expanded_page = ExpandedPage::where('id', $id)->firstOrFail();
            $seq = $expanded_page->seq;
            $expanded_page->delete();
            ExpandedPage::where('seq', '>', $seq)->decrement('seq');
            return ['res' => 0];
        }catch(\Exception $e){
            return ['res' => 1];
        }
    }
}
