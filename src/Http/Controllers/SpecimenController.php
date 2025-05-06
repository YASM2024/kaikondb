<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Session;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Models\Article;
use Kaikon2\Kaikondb\Models\Document;
use Kaikon2\Kaikondb\Models\Order;
use Kaikon2\Kaikondb\Models\Journal;
use Kaikon2\Kaikondb\Models\ArticleOrder;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\RecordingStatus;
use Kaikon2\Kaikondb\Models\Species;

class SpecimenController extends Controller
{
    //
    public function showSearchMenu()
    {
        $orders = $this->getOrders();
        $journals = $this->getJournals();
        return view('kaikon::static.specimens', ['orders' => $orders, 'journals' => $journals]);
    }
    
    private function getOrders()
    {
        $query = Order::join('article_order', 'orders.id', '=', 'article_order.order_id')
            ->select('orders.id as order_id', 'orders.order_ja', 'orders.order')
            ->selectRaw('COUNT(article_order.order_id) as count_orderId')
            ->groupBy('orders.id', 'orders.order_ja', 'orders.order')
            ->orderBy('count_orderId', 'desc');
    
        if (!Auth::check() || !Auth::user()->roles->pluck('name')->contains('Admin')) {
            $query->having('count_orderId', '>', 20);
        }

        return $query->get();
    }
    
    private function getJournals()
    {
        $query = Journal::join('articles', 'journals.id', '=', 'articles.journal_id')
            ->join('article_order', 'articles.id', '=', 'article_order.article_id')
            ->select('journals.id as journal_id', 'journals.journal_name_ja', 'journals.journal_name_en', 'journals.journal_code')
            ->selectRaw('COUNT(journals.id) as count_journalId')
            ->groupBy('journals.id', 'journals.journal_name_ja', 'journals.journal_name_en', 'journals.journal_code');
    
        if (!Auth::check() || !Auth::user()->roles->pluck('name')->contains('Admin')) {
            $query->having('count_journalId', '>', 20);
        }
    
        return $query->get();
    }
    
    

    public function search(Request $request){
        $json = [];
        $validation = Validator::make($request->all(), [
            'keyword' => 'nullable|string',
            'year' => 'nullable|integer|between:1000,2050',
            'journal_code' => 'nullable|numeric',
            'order_id' => 'nullable|numeric'
        ]);

        if ($validation->fails()) {
            $json['error'] = true;
        } else {
            $json['error'] = false;

            $keyword = $request->filled('keyword') ? $request->keyword : '';
            $keyword_array = explode('　', str_replace(' ', '　', $keyword));
            $year = $request->filled('year') ? $request->year : '%';
            $journal_code = $request->filled('journal_code') ? $request->journal_code : '%';
            $author = $request->filled('author') ? $request->author : '';
            $order_id = $request->filled('order_id') ? $request->order_id : '%';
            
            $documents_tmp = Document::select('article_id')
                ->groupBy('article_id')
                ->pluck('article_id')
                ->toArray() ?: [];
    
            $records_tmp = Record::select('article_id')
                ->groupBy('article_id')
                ->pluck('article_id')
                ->toArray() ?: [];

            $articles_tmp = ArticleOrder::join('articles', 'article_order.article_id', '=', 'articles.id')
            ->join('journals', 'articles.journal_id', '=', 'journals.id')
            ->whereNull('articles.deleted_at');

            $articles_tmp = $articles_tmp->where(function ($query) use ($year, $journal_code, $order_id) {
                    $query->where('year', 'like', $year)
                        ->where('journals.journal_code', 'like', $journal_code)
                        ->where('article_order.order_id', 'like', $order_id);
                });

            $articles_tmp = $articles_tmp->where(function ($query) use ($author) {
                    $query->where('author', 'like', "%{$author}%")
                        ->orWhere('author_en', 'like', "%{$author}%");
                });

            foreach (array_filter($keyword_array) as $kw) {
                $articles_tmp = $articles_tmp->where(function ($query) use ($kw) {
                        $query->where('title', 'like', "%{$kw}%")
                            ->orWhere('title_en', 'like', "%{$kw}%")
                            ->orWhere('comment', 'like', "%{$kw}%")
                            ->orWhere('memo1', 'like', "%{$kw}%");
                    });
            }

    
            $locale = session('locale') == 'ja' ? '' : '_en';
            $articles_tmp = $articles_tmp->select('random_id', "title{$locale} as title")
                ->selectRaw("CONCAT(author{$locale}, ',', year, '.', journal_name{$locale}, '.', vol_no, ':', page) AS summary");
            
    
            if (Auth::check()) {
                $articles_tmp = $articles_tmp->addSelect('articles.id as id');
            }

            $count = $articles_tmp->count();
    
            $articles_tmp = $articles_tmp->groupBy('articles.id')
                ->orderBy('year', 'desc')
                ->orderBy('journal_id', 'asc')
                ->orderBy('vol_no', 'desc')
                ->orderBy('page', 'asc');
                
            $articles_tmp = $articles_tmp
                ->select('random_id', 'title', DB::raw("CONCAT(author, ',', year, '.', journal_name_ja, '.', vol_no, ':', page) AS summary"), 'articles.id as id')
                ->groupBy('articles.id', 'title', 'author', 'year', 'journal_name_ja', 'vol_no', 'page','random_id');
        
            if (Auth::check() && Auth::user()->roles->pluck('name')->contains('Admin')) {
                $json['too_many'] = false;
                $articles = $articles_tmp->paginate(10);
                $json = array_merge($json, $articles->toArray());
            } else {
                $json['too_many'] = $count > 100;
                if (!$json['too_many']) {
                    $articles = $articles_tmp->paginate(10);
                    $json = array_merge($json, $articles->toArray());
                }
            }
        
            
        }
    
        $json['search_option'] = [
            'order_id' => Order::where('id', $request->order_id)->pluck('order'),
            'keyword' => $request->keyword,
            'year' => $request->year,
            'journal_ja' => Journal::where('journal_code', $request->journal_code)->pluck('journal_name_ja'),
            'author' => $request->author
        ];
    
        $del_keys = ['links', 'first_page_url', 'last_page_url', 'next_page_url', 'prev_page_url'];
        foreach ($del_keys as $del_key) {
            unset($json[$del_key]);
        }
    
        if (Auth::check() && isset($json['data'])) {
            foreach ($json['data'] as &$data) {
                $data['document'] = in_array($data['id'], $documents_tmp) ? 1 : 0;
                $data['inventory'] = in_array($data['id'], $records_tmp) ? 1 : 0;
            }
        }
    
        return $json;
    }
    

    //
    public function show($id){
        $article = Article::where('random_id', $id)
            ->select(
                'id',
                session('locale') == 'en' ? 'author_en as author' : 'author',
                'year',
                session('locale') == 'en' ? 'title_en as title' : 'title',
                'journal_id',
                'publisher',
                'vol_no',
                'page',
                'link',
                'created_at',
                'comment'
            )
            ->firstOrFail();
    
        $journal = Journal::findOrFail($article->journal_id);
    
        $article->journal_name = session('locale') == 'en' ? $journal->journal_name_en : $journal->journal_name_ja;
        if ($article->publisher) {
            $article->journal_name = $article->publisher;
        }
    
        $article->order_ids = $article->orders->pluck('id')->implode(';');
        $article->order_names = $article->orders->pluck('order')->implode('; ');
    
        if (Auth::check()) {
            $article->documents = Document::where('article_id', $article->id)
                ->select('display_title', 'file_name')
                ->get()
                ->toArray();
    
            $recording_status = RecordingStatus::where('article_id', $article->id)->exists();
            $article->is_recorded = (bool) $recording_status;
        }
    
        if (empty($article->link)) {
            $article->link = '';
        }
        if (!empty($journal->provided_by) && !empty($article->link)) {
            $article->provided_by = '（' . $journal->provided_by . '）';
        }
    
        return $article->toArray();
    }
    


    public function showCreate(){
        $journals = Journal::get()->sortBy('journal_code')->all();
        $action_type = 'create';
        return view('kaikon::articles.form', ['journals'=>$journals, 'action_type'=>$action_type]);
    } 

    public function showEdit($id){
        $journals = Journal::orderBy('journal_code')->get();
        $action_type = 'edit';
        
        $article = Article::where('random_id', $id)
            ->with(['orders','journal'])
            ->select('articles.id', 'title', 'author', 'year', 'articles.publisher', 'journal_id', 'vol_no', 'page', 'comment', 'link', 'memo1')
            ->firstOrFail();

        $article->order_ids = $article->orders->pluck('id')->implode(';');
        $article->journal_code = $article->journal->journal_code;
        $documents = Document::where('article_id', $article->id)->get();
        
        return view('kaikon::articles.form', [
            'article' => $article,
            'journals' => $journals,
            'action_type' => $action_type,
            'documents' => $documents
        ]);
    }
    

    
    public function showDelete($id) {
        $action_type = 'delete';
    
        $article = Article::where('random_id', $id)
            ->with('orders', 'journal')
            ->select('articles.id', 'title', 'author', 'year', 'articles.publisher', 'journal_code', 'vol_no', 'page', 'comment', 'link', 'memo1')
            ->firstOrFail()
            ->toArray();
    
        $article['order_ids'] = $article['orders']->pluck('id')->implode(';');
        $article['order_ids_array'] = explode(';', $article['order_ids']);
    
        return view('kaikon::articles.confirm', [
            'data' => $article,
            'action_type' => $action_type
        ]);
    }
    

    public function showImport(){
        return view('kaikon::articles.import');
    }

    public function import( Request $request ){
        return "import";
    }

    public function download(){
        $articles = Article::all();
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","code","author","author_en","year","title","title_en","vol_no","journal_id","publisher","page","language_id","memo1","memo2","memo3","memo4","memo5","memo6","memo7","memo8","memo9","memo10","inventory","random_id","link","comment","created_at","updated_at","deleted_at","pdf","user_id"';
        fwrite($stream, $csvheader);
        
        foreach ($articles as $article) {
            $csvdata = array(
                $article->id,
                $article->code,
                $article->author,
                $article->author_en,
                $article->year,
                $article->title,
                $article->title_en,
                $article->vol_no,
                $article->journal_id,
                $article->publisher,
                $article->page,
                $article->language_id,
                $article->memo1,
                $article->memo2,
                $article->memo3,
                $article->memo4,
                $article->memo5,
                $article->memo6,
                $article->memo7,
                $article->memo8,
                $article->memo9,
                $article->memo10,
                $article->inventory,
                $article->random_id,
                $article->link,
                $article->comment,
                $article->created_at,
                $article->updated_at,
                $article->deleted_at,
                $article->pdf,
                $article->user_id
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=articles.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    public function create(Request $request){
        $inputs = $request->all();
    
        $rules = [
            'author' => 'required|string|max:255',
            'order_ids_array' => 'required',
            'year' => 'required|integer|between:1000,2050',
            'title' => 'required|string|max:255',
            'journal_code' => 'required|integer',
            'page' => 'nullable|string',
            'publisher' => 'nullable|string|max:255',
            'vol_no' => 'nullable|string',
            'link' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'memo1' => 'nullable|string|max:255',
        ];
    
        foreach ($request->all() as $key => $value) {
            session()->put($key, $value);
        }
    
        $validation = Validator::make($inputs, $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation->errors())->withInput();
        }
    
        $data = $inputs;
        $data['action_type'] = 'create';
        $data['user_id'] = Auth::id();
        $data['inventory'] = 0;
    
        if ($request->verified) {
            $journal = Journal::where('journal_code', $data['journal_code'])->firstOrFail();
    
            DB::beginTransaction();
            try {
                $new_article = Article::create([
                    'code' => 0,
                    'author' => $data['author'],
                    'author_en' => '',
                    'year' => $data['year'],
                    'title' => $data['title'],
                    'title_en' => '',
                    'vol_no' => $data['vol_no'] ?? '',
                    'journal_id' => $journal->id,
                    'publisher' => $data['publisher'],
                    'page' => $data['page'] ?? '',
                    'language_id' => 1,
                    'memo1' => $data['memo1'] ?? '',
                    'memo2' => '',
                    'memo3' => '',
                    'memo4' => '',
                    'memo5' => '',
                    'memo6' => '',
                    'memo7' => '',
                    'memo8' => '',
                    'memo9' => '',
                    'memo10' => '',
                    'inventory' => 0,
                    'random_id' => hash('sha256', uniqid("", true)),
                    'link' => $data['link'] ?? '',
                    'comment' => $data['comment'] ?? '',
                    'pdf' => 0,
                    'user_id' => Auth::id(),
                ]);
    
                $new_article->orders()->attach($data['order_ids_array']);
    
                DB::commit();
    
                return view('kaikon::articles.complete', ['data' => $data]);
            } catch (\Exception $e) {
                DB::rollback();
                return 'error';
            }
        }
    
        return view('kaikon::articles.confirm', ['data' => $data]);
    }
    

    public function edit(Request $request){
        $inputs = $request->all();
    
        $rules = [
            'id' => 'required|integer',
            'author' => 'required|string|max:255',
            'order_ids_array' => 'required',
            'year' => 'required|integer|between:1000,2050',
            'title' => 'required|string|max:255',
            'journal_code' => 'required|integer',
            'page' => 'nullable|string',
            'publisher' => 'nullable|string|max:255',
            'vol_no' => 'nullable|string',
            'link' => 'nullable|string|max:255',
            'comment' => 'nullable|string|max:255',
            'memo1' => 'nullable|string|max:255',
        ];
    
        foreach ($request->all() as $key => $value) {
            session()->put($key, $value);
        }
    
        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return redirect()->back()->withErrors($validation->errors())->withInput();
        }
    
        $data = $inputs;
        $data['action_type'] = 'edit';
        $data['user_id'] = Auth::id();
        $data['inventory'] = 0;
    
        if ($request->verified) {
            $journal = Journal::where('journal_code', $data['journal_code'])->firstOrFail();
    
            DB::beginTransaction();
            try {
                $article = Article::findOrFail($data['id']);
                $article->update([
                    'code' => 0,
                    'author' => $data['author'],
                    'year' => $data['year'],
                    'title' => $data['title'],
                    'vol_no' => $data['vol_no'] ?? '',
                    'journal_id' => $journal->id,
                    'publisher' => $data['publisher'],
                    'page' => $data['page'] ?? '',
                    'memo1' => $data['memo1'] ?? '',
                    'inventory' => $data['inventory'],
                    'link' => $data['link'] ?? '',
                    'comment' => $data['comment'] ?? '',
                ]);
    
                $article->orders()->sync($data['order_ids_array']);
    
                DB::commit();
    
                return view('kaikon::articles.complete', ['data' => $data]);
            } catch (\Exception $e) {
                DB::rollback();
                return "error!";
            }
        }
    
        return view('kaikon::articles.confirm', ['data' => $data]);
    }
    

    public function delete(){
        return "delete";
    }

    public function showSpecies($id){
        $article = Article::where( 'random_id', '=', $id )
            ->select('id')->firstOrFail();
        $article_id = $article['id'];
        $records = Record::where('article_id', '=', $article_id)->select('species_id')->get();
        $records = $records->toArray();
        $species_ids = array_column($records, 'species_id');
        $speciess = Species::whereIn('id', $species_ids)->get();
        $return ='';
        foreach ($speciess as $species) {
            $return .= $species->species_ja.': '.$species->species.'<br>';
        }
        $return .='<br><br>';
        $return .='<a href="'."../../records/create?article_id=".$article_id.'" target="_blank" rel="noopener">追加</a>';
        return $return;
    }
    /*
    public function useApi(Request $request){

        $query = $request->only(['author','journal_id','keyword','order_id','year','page']);
        $items = Article::showArticlesInfo()->orderBy('id', 'desc')->where('author', 'like', '%泰雄%')->paginate(10);

        dd($items);
    }
    */

}
