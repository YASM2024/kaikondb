<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Session;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Literature;
use Kaikon2\Kaikondb\Models\LiteratureHistory;
use Kaikon2\Kaikondb\Models\Document;
use Kaikon2\Kaikondb\Models\Order;
use Kaikon2\Kaikondb\Models\Journal;
use Kaikon2\Kaikondb\Models\LiteratureOrder;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\RecordingStatus;
use Kaikon2\Kaikondb\Models\Species;

class LiteratureController extends Controller
{
    private function recordLiteratureHistory(Literature $literature, string $action, ?int $savedByUserId = null): void
    {
        LiteratureHistory::create([
            'literature_id' => $literature->id,
            'action' => $action,
            'saved_by_user_id' => $savedByUserId,
            'code' => $literature->code,
            'author' => $literature->author,
            'author_en' => $literature->author_en,
            'year' => $literature->year,
            'title' => $literature->title,
            'title_en' => $literature->title_en,
            'vol_no' => $literature->vol_no,
            'journal_id' => $literature->journal_id,
            'publisher' => $literature->publisher,
            'page' => $literature->page,
            'language_id' => $literature->language_id,
            'memo1' => $literature->memo1,
            'memo2' => $literature->memo2,
            'memo3' => $literature->memo3,
            'memo4' => $literature->memo4,
            'memo5' => $literature->memo5,
            'memo6' => $literature->memo6,
            'memo7' => $literature->memo7,
            'memo8' => $literature->memo8,
            'memo9' => $literature->memo9,
            'memo10' => $literature->memo10,
            'inventory' => $literature->inventory,
            'random_id' => $literature->random_id,
            'link' => $literature->link,
            'comment' => $literature->comment,
            'created_at' => $literature->created_at,
            'updated_at' => $literature->updated_at,
            'deleted_at' => $literature->deleted_at,
            'tag_id' => $literature->tag_id,
            'user_id' => $literature->user_id,
            'recorded_at' => now(),
        ]);
    }

    public function showSearchMenu()
    {
        $orders = $this->getOrders();
        $journals = $this->getJournals();
        return view('kaikon::literatures.index', ['orders' => $orders, 'journals' => $journals]);
    }
    
    private function getOrders()
    {
        $query = Order::join('literature_order', 'orders.id', '=', 'literature_order.order_id')
            ->select('orders.id as order_id', 'orders.order_ja', 'orders.order')
            ->selectRaw('COUNT(literature_order.order_id) as count_orderId')
            ->groupBy('orders.id', 'orders.order_ja', 'orders.order')
            ->orderBy('count_orderId', 'desc');
    
        if (!Auth::check() || !User::fromAppUser(Auth::user())->isAdmin()) {
            $query->having('count_orderId', '>', 20);
        }

        return $query->get();
    }
    
    private function getJournals()
    {
        $query = Journal::join('literatures', 'journals.id', '=', 'literatures.journal_id')
            ->join('literature_order', 'literatures.id', '=', 'literature_order.literature_id')
            ->select('journals.id as journal_id', 'journals.journal_name_ja', 'journals.journal_name_en', 'journals.journal_code')
            ->selectRaw('COUNT(journals.id) as count_journalId')
            ->groupBy('journals.id', 'journals.journal_name_ja', 'journals.journal_name_en', 'journals.journal_code');
    
        if (!Auth::check() || !User::fromAppUser(Auth::user())->isAdmin()) {
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
            
            $documents_tmp = Document::select('literature_id')
                ->groupBy('literature_id')
                ->pluck('literature_id')
                ->toArray() ?: [];
    
            $records_tmp = Record::select('literature_id')
                ->groupBy('literature_id')
                ->pluck('literature_id')
                ->toArray() ?: [];

            $literatures_tmp = LiteratureOrder::join('literatures', 'literature_order.literature_id', '=', 'literatures.id')
            ->join('journals', 'literatures.journal_id', '=', 'journals.id')
            ->whereNull('literatures.deleted_at');

            $literatures_tmp = $literatures_tmp->where(function ($query) use ($year, $journal_code, $order_id) {
                    $query->where('year', 'like', $year)
                        ->where('journals.journal_code', 'like', $journal_code)
                        ->where('literature_order.order_id', 'like', $order_id);
                });

            $literatures_tmp = $literatures_tmp->where(function ($query) use ($author) {
                    $query->where('author', 'like', "%{$author}%")
                        ->orWhere('author_en', 'like', "%{$author}%");
                });

            foreach (array_filter($keyword_array) as $kw) {
                $literatures_tmp = $literatures_tmp->where(function ($query) use ($kw) {
                        $query->where('title', 'like', "%{$kw}%")
                            ->orWhere('title_en', 'like', "%{$kw}%")
                            ->orWhere('comment', 'like', "%{$kw}%")
                            ->orWhere('memo1', 'like', "%{$kw}%");
                    });
            }

    
            $locale = session('locale') == 'en' ? '_en' : '';
            $literatures_tmp = $literatures_tmp->select('random_id', "title{$locale} as title")
                ->selectRaw("CONCAT(author{$locale}, ',', year, '.', journal_name{$locale}, '.', vol_no, ':', page) AS summary");
            
    
            if (Auth::check()) {
                $literatures_tmp = $literatures_tmp->addSelect('literatures.id as id');
            }

            $count = $literatures_tmp->count();
    
            $literatures_tmp = $literatures_tmp->groupBy('literatures.id')
                ->orderBy('year', 'desc')
                ->orderBy('journal_id', 'asc')
                ->orderBy('vol_no', 'desc')
                ->orderBy('page', 'asc');
                
            if (session('locale') == 'en'){
                $literatures_tmp = $literatures_tmp
                    ->select(
                        'random_id',
                        'literatures.title as title_ja',
                        'literatures.title_en as title_en',
                        'literatures.title_en as title',
                        DB::raw("CONCAT(author_en, ',', year, '.', journal_name_en, '.', vol_no, ':', page) AS summary"),
                        'literatures.id as id'
                    )
                    ->groupBy('literatures.id', 'literatures.title', 'literatures.title_en', 'author', 'year', 'journal_name_ja', 'vol_no', 'page', 'random_id');
            }else{
                $literatures_tmp = $literatures_tmp
                    ->select(
                        'random_id',
                        'literatures.title as title_ja',
                        'literatures.title_en as title_en',
                        'literatures.title as title',
                        DB::raw("CONCAT(author, ',', year, '.', journal_name_ja, '.', vol_no, ':', page) AS summary"),
                        'literatures.id as id'
                    )
                    ->groupBy('literatures.id', 'literatures.title', 'literatures.title_en', 'author', 'year', 'journal_name_ja', 'vol_no', 'page', 'random_id');
            }
        
            if (Auth::check() && User::fromAppUser(Auth::user())->isAdmin()) {
                $json['too_many'] = false;
                $literatures = $literatures_tmp->paginate(10);
                $json = array_merge($json, $literatures->toArray());
            } else {
                $json['too_many'] = $count > 100;
                if (!$json['too_many']) {
                    $literatures = $literatures_tmp->paginate(10);
                    $json = array_merge($json, $literatures->toArray());
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
    public function show(string $id){
        $literature = Literature::where('random_id', $id)
            ->join('users', 'literatures.user_id', '=', 'users.id')
            ->select(
                'literatures.id',
                session('locale') == 'en' ? 'literatures.author_en as author' : 'literatures.author',
                'literatures.year',
                'literatures.title as title_ja',
                'literatures.title_en as title_en',
                session('locale') == 'en' ? 'literatures.title_en as title' : 'literatures.title as title',
                'literatures.journal_id',
                'literatures.publisher',
                'literatures.vol_no',
                'literatures.page',
                'literatures.link',
                'literatures.created_at',
                'literatures.comment',
                'users.name as user_name'
            )
            ->firstOrFail();
    
        $journal = Journal::findOrFail($literature->journal_id);
    
        $literature->journal_name = session('locale') == 'en' ? $journal->journal_name_en : $journal->journal_name_ja;
        if ($literature->publisher) {
            $literature->journal_name = $literature->publisher;
        }
    
        $literature->order_ids = $literature->orders->pluck('id')->implode(';');
        $literature->order_names = $literature->orders->pluck('order')->implode('; ');
    
        if (Auth::check()) {
            $literature->documents = Document::where('literature_id', $literature->id)
                ->select('display_title', 'file_name')
                ->get()
                ->toArray();
    
            $recording_status = RecordingStatus::where('literature_id', $literature->id)->exists();
            $literature->is_recorded = (bool) $recording_status;
        }
    
        if (empty($literature->link)) {
            $literature->link = '';
        }
        if (!empty($journal->provided_by) && !empty($literature->link)) {
            $literature->provided_by = '（' . $journal->provided_by . '）';
        }

        $this->recordLiteratureHistory(
            Literature::where('random_id', $id)->firstOrFail(),
            'show',
            Auth::id()
        );
    
        return $literature->toArray();
    }
    


    private function getOrdersForForm()
    {
        return Order::where('status', true)
            ->orderBy('code')
            ->get(['id', 'order_ja', 'order', 'code']);
    }

    private function getSelectedOrderIds(?Literature $literature = null): array
    {
        $old = old('order_ids_array');
        if (is_array($old) && $old !== []) {
            return array_map('intval', $old);
        }

        if ($literature && $literature->relationLoaded('orders')) {
            return $literature->orders->pluck('id')->map(fn ($id) => (int) $id)->all();
        }

        if ($literature && ! empty($literature->order_ids)) {
            return array_values(array_filter(array_map('intval', explode(';', $literature->order_ids))));
        }

        return [];
    }

    private function formatOrderLabels(array $orderIds): string
    {
        if ($orderIds === []) {
            return '';
        }

        return Order::whereIn('id', $orderIds)
            ->orderBy('code')
            ->get()
            ->map(fn (Order $order) => $order->order_ja . '（' . $order->order . '）')
            ->implode('、');
    }

    private function buildLiteratureFormConfig($orders, array $selectedOrderIds): array
    {
        return [
            'orders' => collect($orders)->map(fn (Order $order) => [
                'id' => $order->id,
                'order_ja' => $order->order_ja,
                'order' => $order->order,
                'code' => $order->code,
            ])->values()->all(),
            'selectedOrderIds' => $selectedOrderIds,
        ];
    }

    public function showCreate(){
        $journals = Journal::get()->sortBy('journal_code')->all();
        $action_type = 'create';
        $orders = $this->getOrdersForForm();
        $selectedOrderIds = $this->getSelectedOrderIds();

        return view('kaikon::literatures.form', [
            'journals' => $journals,
            'action_type' => $action_type,
            'orders' => $orders,
            'selectedOrderIds' => $selectedOrderIds,
            'formConfig' => $this->buildLiteratureFormConfig($orders, $selectedOrderIds),
        ]);
    } 

    public function showEdit(string $id){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Literature::where('random_id', $id)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }
        
        $journals = Journal::orderBy('journal_code')->get();
        $action_type = 'edit';
        
        $literature = Literature::where('random_id', $id)
            ->with(['orders','journal'])
            ->select('random_id', 'literatures.id', 'title', 'title_en', 'author', 'author_en', 'year', 'literatures.publisher', 'journal_id', 'vol_no', 'page', 'comment', 'link', 'memo1')
            ->firstOrFail();

        $literature->order_ids = $literature->orders->pluck('id')->implode(';');
        $literature->journal_code = $literature->journal->journal_code;
        $documents = Document::where('literature_id', $literature->id)->get();
        
        $orders = $this->getOrdersForForm();
        $selectedOrderIds = $this->getSelectedOrderIds($literature);

        return view('kaikon::literatures.form', [
            'literature' => $literature,
            'journals' => $journals,
            'action_type' => $action_type,
            'documents' => $documents,
            'orders' => $orders,
            'selectedOrderIds' => $selectedOrderIds,
            'formConfig' => $this->buildLiteratureFormConfig($orders, $selectedOrderIds),
        ]);
    }
    

    
    public function showDelete(string $id) {
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Literature::where('random_id', $id)->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }
            
        $action_type = 'delete';
        $literature = Literature::where('random_id', $id)
            ->with('orders', 'journal')
            ->select('literatures.id', 'title', 'title_en', 'author', 'author_en', 'year', 'literatures.publisher', 'journal_code', 'vol_no', 'page', 'comment', 'link', 'memo1')
            ->firstOrFail()
            ->toArray();
    
        $literature['order_ids'] = $literature['orders']->pluck('id')->implode(';');
        $literature['order_ids_array'] = array_values(array_filter(
            array_map('intval', explode(';', $literature['order_ids']))
        ));
        $literature['order_labels'] = $this->formatOrderLabels($literature['order_ids_array']);
    
        return view('kaikon::literatures.confirm', [
            'data' => $literature,
            'action_type' => $action_type
        ]);
    }
    

    public function showImport(){
        return view('kaikon::literatures.import');
    }

    public function import( Request $request ){
        return "import";
    }

    public function download(){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        if (!Auth::check()){
                abort(403, 'Unauthorized action.');
            }
        if (User::fromAppUser(Auth::user())->isAdmin()){
            $literatures = Literature::all();
        }
        elseif (User::fromAppUser(Auth::user())->isModerator()){
            $tags = User::fromAppUser(Auth::user())->tags->pluck('id')->toArray();
            $literatures = Literature::whereIn('tag_id', $tags)->get();
        }else{
            abort(403, 'Unauthorized action.');
        }
        // CSVデータ生成
        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","code","author","author_en","year","title","title_en","vol_no","journal_id","publisher","page","language_id","memo1","memo2","memo3","memo4","memo5","memo6","memo7","memo8","memo9","memo10","inventory","random_id","link","comment","created_at","updated_at","deleted_at","user_id"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($literatures as $literature) {
            $csvdata = array(
                $literature->id,
                $literature->code,
                $literature->author,
                $literature->author_en,
                $literature->year,
                $literature->title,
                $literature->title_en,
                $literature->vol_no,
                $literature->journal_id,
                $literature->publisher,
                $literature->page,
                $literature->language_id,
                $literature->memo1,
                $literature->memo2,
                $literature->memo3,
                $literature->memo4,
                $literature->memo5,
                $literature->memo6,
                $literature->memo7,
                $literature->memo8,
                $literature->memo9,
                $literature->memo10,
                $literature->inventory,
                $literature->random_id,
                $literature->link,
                $literature->comment,
                $literature->created_at,
                $literature->updated_at,
                $literature->deleted_at,
                $literature->user_id
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=literatures.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    public function create(Request $request){
        $inputs = $request->all();
    
        $rules = [
            'author' => 'required|string|max:255',
            'author_en' => 'nullable|string|max:255',
            'order_ids_array' => 'required',
            'year' => 'required|integer|between:1000,2050',
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
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
        $data['inventory'] = 0;
    
        if ($request->verified) {
            $journal = Journal::where('journal_code', $data['journal_code'])->firstOrFail();
    
            DB::beginTransaction();
            try {
                $new_literature = Literature::create([
                    'code' => 0,
                    'author' => $data['author'],
                    'author_en' => $data['author_en'] ?? '',
                    'year' => $data['year'],
                    'title' => $data['title'],
                    'title_en' => $data['title_en'] ?? '',
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
                    'user_id' => Auth::id(),
                ]);
    
                $new_literature->orders()->attach($data['order_ids_array']);
    
                DB::commit();

                $this->recordLiteratureHistory($new_literature->fresh(), 'create', Auth::id());
    
                return view('kaikon::literatures.complete', ['data' => $data]);
            } catch (\Exception $e) {
                DB::rollback();
                return 'error';
            }
        }
    
        $data['order_labels'] = $this->formatOrderLabels(
            array_map('intval', (array) ($data['order_ids_array'] ?? []))
        );

        return view('kaikon::literatures.confirm', ['data' => $data]);
    }
    

    public function edit(Request $request){
        $inputs = $request->all();
        $rules = [
            'id' => 'required|integer',
            'author' => 'required|string|max:255',
            'author_en' => 'nullable|string|max:255',
            'order_ids_array' => 'required',
            'year' => 'required|integer|between:1000,2050',
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
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

        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $required_tag_id = Literature::where('id', $inputs['id'])->first()->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        $data = $inputs;
        $data['action_type'] = 'edit';
        $data['inventory'] = 0;
    
        if ($request->verified) {
            $journal = Journal::where('journal_code', $data['journal_code'])->firstOrFail();
    
            DB::beginTransaction();
            try {
                $literature = Literature::findOrFail($data['id']);
                $literature->update([
                    'code' => 0,
                    'author' => $data['author'],
                    'author_en' => $data['author_en'] ?? '',
                    'year' => $data['year'],
                    'title' => $data['title'],
                    'title_en' => $data['title_en'] ?? '',
                    'vol_no' => $data['vol_no'] ?? '',
                    'journal_id' => $journal->id,
                    'publisher' => $data['publisher'],
                    'page' => $data['page'] ?? '',
                    'memo1' => $data['memo1'] ?? '',
                    'inventory' => $data['inventory'],
                    'link' => $data['link'] ?? '',
                    'comment' => $data['comment'] ?? '',
                ]);
    
                $literature->orders()->sync($data['order_ids_array']);
    
                DB::commit();

                $this->recordLiteratureHistory($literature->fresh(), 'edit', Auth::id());
    
                return view('kaikon::literatures.complete', ['data' => $data]);
            } catch (\Exception $e) {
                DB::rollback();
                return "error!";
            }
        }
    
        $data['order_labels'] = $this->formatOrderLabels(
            array_map('intval', (array) ($data['order_ids_array'] ?? []))
        );

        return view('kaikon::literatures.confirm', ['data' => $data]);
    }
    

    public function delete(string $id){
        // [編集タグをもつModerator] or [Administrator] のみアクセス可能
        $literature = Literature::where('random_id', $id)->firstOrFail();
        $required_tag_id = $literature->tag_id;
        if (!Auth::check() || 
                (!User::fromAppUser(Auth::user())->isAdmin() && !User::fromAppUser(Auth::user())->hasTag($required_tag_id))
            ) {
                abort(403, 'Unauthorized action.');
            }

        $this->recordLiteratureHistory($literature, 'delete', Auth::id());

        // まだ実装しない
        return "delete";
    }

    public function showSpecies(string $id){
        
        $literature = Literature::where('random_id', $id)->select('id')->firstOrFail();
        $literature_id = $literature['id'];

        $status = RecordingStatus::where('literature_id', $literature_id)->first();
        $locked = isset($status);
        
        $species_ids = Record::where('literature_id', $literature_id)
            ->pluck('species_id')
            ->unique()
            ->values()
            ->all();

        $groupedSpecies = Species::whereIn('id', $species_ids)
            ->with(['order', 'family'])
            ->get()
            ->sortBy([
                fn ($species) => $species->order?->code ?? '',
                fn ($species) => $species->family?->code ?? '',
                fn ($species) => $species->species_ja ?? '',
            ])
            ->groupBy('order_id')
            ->map(fn ($speciesInOrder) => $speciesInOrder->groupBy('family_id'));

        return view('kaikon::literatures.species', [
            'random_id' => $id,
            'literature_id' => $literature_id,
            'locked' => $locked,
            'groupedSpecies' => $groupedSpecies,
        ]);
    }
    /*
    public function useApi(Request $request){

        $query = $request->only(['author','journal_id','keyword','order_id','year','page']);
        $items = Literature::query()->orderBy('id', 'desc')->where('author', 'like', '%泰雄%')->paginate(10);

        dd($items);
    }
    */

}
