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

    public function update(Request $request, $route_name = null){

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

            if($route_name !== null){
                $record = ExpandedPage::where('route_name', '=', $request->input('route_name'))->first();
                $oldSeq = $record->seq;
                if ($newSeq > $oldSeq) {
                    // 例: seq 2 -> seq 5 の場合、3～5 のレコードは前詰め（seqを1減算）
                    ExpandedPage::whereBetween('seq', [$oldSeq + 1, $newSeq])->decrement('seq');
                } elseif ($newSeq < $oldSeq) {
                    // 例: seq 5 -> seq 2 の場合、2～4 のレコードは後ろシフト（seqを1加算）
                    ExpandedPage::whereBetween('seq', [$newSeq, $oldSeq - 1])->increment('seq');
                }
                $record->title = $request->input('title');
                $record->title_en = $request->input('title_en') ?? $request->input('title');
                $record->body = $request->input('body');
                $record->body_en = $request->input('body_en') ?? $request->input('body');
                $record->seq = $newSeq;
                $record->open = $request->input('open');
                $record->save();
            }else{
                ExpandedPage::where('seq', '>=', $newSeq)->increment('seq');
                ExpandedPage::create([
                    'route_name' => $request->input('route_name'),
                    'title' => $request->input('title'),
                    'title_en' => $request->input('title_en') ?? $request->input('title'),
                    'body' => $request->input('body'),
                    'body_en' => $request->input('body_en') ?? $request->input('body'),
                    'seq' => $newSeq,
                    'open' => 0
                ]);
            }

            DB::commit();
            
        }catch(\Exception $e){

            DB::rollback();
            return ['res'=> 1];

        }
        
        return ['res'=> 0];
    }
}
