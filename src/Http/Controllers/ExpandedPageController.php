<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Kaikon2\Kaikondb\Models\ExpandedPage;

class ExpandedPageController extends Controller
{
    //
    public function index(){
        $expanded_pages = ExpandedPage::get();
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
        $seqs = ExpandedPage::pluck('seq')->toArray();

        return view('kaikon::expanded.form', ['page' => $page, 'seqs' => $seqs, 'action_type' => $action_type]);
    }

    public function update($route_name = null , Request $request){
        $inputs = $request->all();
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'seq' => 'required|integer',
            'open' => 'required|boolean',
        ];
        $validator = Validator::make($inputs, $rules);
        if($validator->fails()){ return['res' => 1];}

        try{
            if($route_name == null){
                ExpandedPage::create([
                    'route_name' => $request->input('route_name'),
                    'title' => $request->input('title'),
                    'body' => $request->input('body'),
                    'seq' => $request->input('seq'),
                    'open' => $request->input('open')
                ]);
            }else{
                ExpandedPage::where('route_name', '=', $route_name)->update([
                    'title' => $request->input('title'),
                    'body' => $request->input('body'),
                    'seq' => $request->input('seq'),
                    'open' => $request->input('open')
                ]);
            }
        }catch(\Exception $e){
            return ['res'=> 1];
        }

        return ['res'=> 0];
    }
}
