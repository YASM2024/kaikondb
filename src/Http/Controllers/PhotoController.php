<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;


use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Photo;
use Kaikon2\Kaikondb\Models\PhotoHistory;
use Kaikon2\Kaikondb\Models\Profile;



class PhotoController extends Controller
{
    public function showSearchMenu(Request $request)
    {
        $user_id = $request->user_id ? $request->user_id : '%';

        $photos = Photo::join('users', 'photos.user_id', '=', 'users.id')
            ->leftJoin('profiles as p1', 'photos.user_id', '=', 'p1.user_id')
            ->leftJoin('profiles as p2', function ($join) {
                $join->on(DB::raw('-1'), '=', 'p2.user_id');
            })
            ->select(
                'photos.id',
                'thumbnail_url',
                'photo_title',
                'photos.user_id',
                'approved_at', 
                DB::raw('COALESCE(p1.show_name, p2.show_name) AS show_name')
            );

        $me = Auth::check() ? User::fromAppUser(Auth::user())->id : null;

        $photos = $photos->where(function ($query) use ($me) {
            if ($me) {
                // ログインユーザーは自分の写真と承認済みの他のユーザーの写真を表示
                $query->where('photos.user_id', $me)
                    ->orWhere(function ($q) use ($me) {
                        $q->where('photos.user_id', '!=', $me)
                            ->whereNotNull('approved_at');
                    });
            } else {
                // 未ログインユーザーは承認済みの写真のみを表示
                $query->whereNotNull('approved_at');
            }
        });

        $photos = $photos->where('photo_title','LIKE', "%{$request->keyword}%")
            ->where('photos.user_id','LIKE', $user_id)
            ->orderBy('photos.id','desc')
            ->paginate(12)->withQueryString();

        $photographers = Photo::join('profiles','profiles.user_id','=','photos.user_id')
            ->select('photos.user_id','show_name')
            ->selectRaw('COUNT(photos.user_id) as count')
            ->groupBy('photos.user_id','show_name')
            ->orderBy('count','desc')
            ->get(); 
        
        $data = ['user_id'=> $request->user_id, 'keyword'=> $request->keyword, 'place'=> $request->place, 'date'=> $request->date];

        return view('kaikon::photos.index', ['photos'=>$photos, 'photographers'=>$photographers, 'data'=>$data]);
    }
    
    public function search(Request $request)
    {
        $json = [];
        $validation = Validator::make($request->all(), [
            'keyword' => 'nullable|string',
            'user_id' => 'nullable|numeric',
            'place' => 'nullable|string',
            'date' => 'nullable|string',
        ]);
        
        if ($validation->fails()) {
            $json['error'] = true;
        } else {
            
            $json['error'] = false;

            $keywords = $request->filled('keyword') ? $request->keyword : '';
            $keywords_array = explode('　', str_replace(' ', '　', $keywords));
            // return $keywords_array;

            $photos_tmp = Photo::query();
            
            
            foreach (array_filter($keywords_array) as $kw) {
                $photos_tmp = $photos_tmp->where(function ($query) use ($kw) {
                        $query->where('photo_title', 'like', "%{$kw}%")
                            ->orWhere('memo', 'like', "%{$kw}%");
                    });
            }
            if ($request->filled('user_id')) {
                $photos_tmp = $photos_tmp->where('user_id', $request->user_id);
            }
            if ($request->filled('place')) {
                $photos_tmp = $photos_tmp->where('place', 'like', "%{$request->place}%");
            }
            if ($request->filled('date')) {
                $photos_tmp = $photos_tmp->where('date', 'like', "%{$request->date}%");
            }

            if (Auth::check() && User::fromAppUser(Auth::user())->isAdmin()) {
                // 管理者は全ての写真を表示
                $photos = $photos_tmp->orderBy('id', 'desc')
                    ->select("id", "code", "url", "thumbnail_url", "photo_title", "date", "place", "user_id", "memo", "approved_at")
                    ->paginate(12);
            } elseif (Auth::check()) {
                // ログインユーザーは自分の写真と承認済みの他のユーザーの写真を表示
                $photos_tmp = $photos_tmp->whereNotNull('approved_at')
                    ->orWhere('user_id', User::fromAppUser(Auth::user())->id);
                $photos = $photos_tmp->orderBy('id', 'desc')
                    ->select("id", "code", "url", "thumbnail_url", "photo_title", "date", "place", "user_id", "memo", "approved_at")
                    ->paginate(12);
            } else {
                // 未ログインユーザーは承認済みの写真のみを表示
                $photos_tmp = $photos_tmp->whereNotNull('approved_at');
                $photos = $photos_tmp->orderBy('id', 'desc')
                    ->select("id", "code", "url", "thumbnail_url", "photo_title", "date", "place", "user_id", "memo")
                    ->paginate(12);
            }

            $count = $photos_tmp->count();

            $json = array_merge($json, $photos->toArray());

            $del_keys = ['links', 'first_page_url', 'last_page_url', 'next_page_url', 'prev_page_url'];
            foreach ($del_keys as $del_key) {
                unset($json[$del_key]);
            }
        }
        return $json;
    }

    public function showCreate()
    {
        return view('kaikon::ja.photo.form');
    }

    public function create( Request $request ){
        $inputs = $request->all();
        $rules = [
            'name' => 'required | string',
            'place' => 'nullable | string',
            'date' => 'nullable | string',
            'memo' => 'nullable | string',
            'terms_agreed' => 'required|accepted',
        ];
        $validation = Validator::make($inputs, $rules);
        if ($validation->fails()) {
            return response()->json(['message' => '利用規約への同意が必要です'], 422);
        }
        $data = $inputs;
        $data['action_type'] = 'create';

        if( $request->verified ){
            try {
                $photo = $request->file('image_file');
                if(isset($photo)){
                    $img_file_name = now()->format('YmdHisu').CRC32($request->photographer).'.png';
                    $code = sha1(now()->format('YmdHisu').$request->photographer);
                    $place = isset($request->place) ? $request->place : '';
                    $date = isset($request->date) ? $request->date : '';
                    $memo = isset($request->memo) ? $request->memo : '';
                    $random_sp_id = 0;
                    
                    $imageManager = ImageManager::usingDriver(Driver::class);
                    $img1 = $imageManager->decodeSplFileInfo($photo);
                    $img2 = $imageManager->decodeSplFileInfo($photo);
                    $img1->scaleDown(width: 800)//アスペクト比を維持
                        ->save(storage_path('app/public/photos/' . $img_file_name ) );
                    $img2->scaleDown(width: 200)//アスペクト比を維持
                        ->save(storage_path('app/public/photos/' . 'thumbnailphoto'.$img_file_name ) );

                    $result = Photo::create([
                        'code' => $code,
                        'url' => $img_file_name,
                        'thumbnail_url' => 'thumbnailphoto'.$img_file_name,
                        'photo_title' => $request->name, 
                        'date' => $date,
                        'place' => $place,
                        'photographer' => $request->photographer,
                        'user_id' => User::fromAppUser(Auth::user())->id,
                        'memo' => $memo,
                        'heart' => 0,
                        'random_sp_id' => 0,//$random_sp_id,
                        'approved_at' => null,
                        'agreed_at' => now(),
                        'delpass' => "1",//Hash::make($request->password), 
                        'error_count' => 0
                    ]);

                    PhotoHistory::recordFrom($result, 'create', User::fromAppUser(Auth::user())->id);

                    $data = ['photographer'=>User::fromAppUser(Auth::user())->name];
                    //管理者にメールを送信
                    Mail::send('kaikon::emails.photo-create', $data, function($message){
                        $message->to(config('kaikon.Email'), config('kaikon.Administrator'))->subject('kai-kon: 写真投稿通知');
                    });

                    return ['result'=>'success'];
                    
                }
            } catch (\Exception $e) {
                abort(500);
            }
        }
        abort(400);
    }

    public function show(Request $request, int $id)
    {
        $query = Photo::join('users', 'photos.user_id', '=', 'users.id')
            ->leftJoin('profiles as p1', 'photos.user_id', '=', 'p1.user_id')
            ->leftJoin('profiles as p2', function ($join) {
                $join->on(DB::raw('-1'), '=', 'p2.user_id');
            });

        $selects = [
            'photos.id',
            'url',
            'photo_title',
            'date',
            'place',
            'memo',
            'users.name',
            'photos.user_id',
            DB::raw('COALESCE(p1.icon, p2.icon) AS icon'),
            DB::raw('COALESCE(p1.show_name, p2.show_name) AS show_name'),
        ];

        // ログインしていれば approved_at を追加
        if (Auth::check()) {
            $selects[] = 'approved_at';
        }

        $query->select($selects)->where('photos.id', '=', $id);
        $data = Auth::check() ? $query->first() : $query->where('approved_at', '!=', null)->firstOrFail();

        return $data;
    }

    public function download(){
        
        if (Auth::check() && User::fromAppUser(Auth::user())->isAdmin()){
            $photos = Photo::all();
        }else{
            $photos = Photo::where('photos.user_id', User::fromAppUser(Auth::user())->id )->get();
        }

        $stream = fopen('php://temp', 'w');
        $csvheader = '"id","photo_title","date","place","user","memo","created_at","updated_at"'."\n";
        fwrite($stream, $csvheader);
        
        foreach ($photos as $photo) {
            $csvdata = array(
                $photo->id,
                $photo->photo_title,
                $photo->date,
                $photo->place,
                $photo->show_name,
                $photo->memo,
                $photo->created_at,
                $photo->updated_at
            );
            fwrite($stream, "\"" . implode("\",\"", $csvdata) . "\"\n");
        }

        rewind($stream);                      //ファイルポインタを先頭に戻す
        $csv = stream_get_contents($stream);
        $csv = mb_convert_encoding($csv,'UTF-8');

        fclose($stream);

        $headers = array(
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=photos.csv'
        );

        return Response::make($csv, 200, $headers);
    }

    public function edit( Request $request )
    {
        $id =  $request->id;
        if ( !preg_match("/^[0-9]+$/i", $id) ) {
            return ['result'=>'error'];
        }
        $inputs = $request->all();
        $rules = [
            'photo_title' => 'required | string',
            'place' => 'nullable | string',
            'date' => 'nullable | string', 
            'memo' => 'nullable | string', 
        ];
        
        $validation = Validator::make($inputs, $rules);
        if($validation->fails()){ return ['result'=>'error'];}

        $photo = Photo::find($id);
        if( $photo == null ){ return ['result'=>'error'];}
        if( User::fromAppUser(Auth::user())->id != $photo->user_id ){ return ['result'=>'error'];}

        DB::beginTransaction();
        try {
            if(isset($inputs['photo_title'])){$photo->photo_title = $inputs['photo_title'];}
            if(isset($inputs['place'])){$photo->place = $inputs['place'];}
            if(isset($inputs['date'])){$photo->date = $inputs['date'];}
            if(isset($inputs['memo'])){$photo->memo = $inputs['memo'];}
            $photo->save();
            PhotoHistory::recordFrom($photo, 'edit', User::fromAppUser(Auth::user())->id);
            DB::commit();
            return ['result'=>'success'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['result'=>'error_final'];
        }
    } 

    public function delete( Request $request )
    {
        $id =  $request->id;
        if( !preg_match("/^[0-9]+$/i", $id) ) { return ['result'=>'error']; }
        $photo = Photo::find($id);
        if( $photo == null ){ return ['result'=>'error']; }
        if( User::fromAppUser(Auth::user())->id != $photo->user_id ){ return ['result'=>'error']; }

        DB::beginTransaction();
        try {
            PhotoHistory::recordFrom($photo, 'delete', User::fromAppUser(Auth::user())->id);
            $this->destroyPhoto($photo);
            DB::commit();
            return ['result'=>'success'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['result'=>'error'];
        }
    }

    private function destroyPhoto(Photo $photo): void
    {
        $photo->species()->detach();

        $disk = Storage::disk('public');
        foreach (['url', 'thumbnail_url'] as $field) {
            $filename = basename((string) $photo->{$field});
            if ($filename === '') {
                continue;
            }
            $path = 'photos/' . $filename;
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }

        $photo->forceDelete();
    }

    public function admin(Request $request)
    {
        $this->ensurePhotoModerator();

        $status = $request->query('status', 'pending') === 'published' ? 'published' : 'pending';

        $query = Photo::query()
            ->join('users', 'photos.user_id', '=', 'users.id')
            ->leftJoin('profiles', 'photos.user_id', '=', 'profiles.user_id')
            ->select(
                'photos.id',
                'photos.thumbnail_url',
                'photos.photo_title',
                'photos.place',
                'photos.date',
                'photos.created_at',
                'photos.approved_at',
                'photos.agreed_at',
                DB::raw('COALESCE(profiles.show_name, users.name) AS show_name')
            );

        if ($status === 'published') {
            $query->whereNotNull('photos.approved_at');
        } else {
            $query->whereNull('photos.approved_at');
        }

        if ($request->filled('author')) {
            $author = $request->input('author');
            $query->where(function ($q) use ($author) {
                $q->where('profiles.show_name', 'like', "%{$author}%")
                    ->orWhere('users.name', 'like', "%{$author}%");
            });
        }
        if ($request->filled('species')) {
            $query->where('photos.photo_title', 'like', '%' . $request->input('species') . '%');
        }
        if ($request->filled('place')) {
            $query->where('photos.place', 'like', '%' . $request->input('place') . '%');
        }
        if ($request->filled('created_at')) {
            $query->where('photos.created_at', 'like', '%' . $request->input('created_at') . '%');
        }
        if ($request->filled('date')) {
            $query->where('photos.date', 'like', '%' . $request->input('date') . '%');
        }

        $sort = $request->query('sort', 'created_at');
        $dir = strtolower($request->query('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $sortColumn = match ($sort) {
            'show_name' => 'show_name',
            'photo_title' => 'photos.photo_title',
            'approved_at' => 'photos.approved_at',
            default => 'photos.created_at',
        };

        $photos = $query->orderBy($sortColumn, $dir)->paginate(24)->withQueryString();

        return view('kaikon::photos.admin', [
            'photos' => $photos,
            'status' => $status,
            'filters' => $request->only(['author', 'species', 'place', 'created_at', 'date']),
            'sort' => $sort,
            'dir' => $dir,
        ]);
    }

    public function approve(int $id)
    {
        return $this->setPhotoApproval($id, true);
    }

    public function unapprove(int $id)
    {
        return $this->setPhotoApproval($id, false);
    }

    private function setPhotoApproval(int $id, bool $approve)
    {
        $this->ensurePhotoModerator();

        $photo = Photo::find($id);
        if ($photo === null) {
            abort(404);
        }

        $photo->approved_at = $approve ? now() : null;
        $photo->save();

        PhotoHistory::recordFrom(
            $photo,
            $approve ? 'approve' : 'unapprove',
            User::fromAppUser(Auth::user())->id
        );

        return response()->json([
            'success' => true,
            'approved' => $approve,
            'approved_at' => $photo->approved_at?->toIso8601String(),
        ]);
    }

    private function ensurePhotoModerator(): User
    {
        if (!Auth::check()) {
            abort(404);
        }

        $user = User::fromAppUser(Auth::user());
        if (!$user->isAdmin() && !$user->isModerator()) {
            abort(404);
        }

        return $user;
    }

    /** @deprecated 旧 API。approve / unapprove を使用してください。 */
    public function accept(Request $request) {
        $data = json_decode($request->getContent(), true);
        $id =  $data['id'];
        $acceptOrReject = $data['acceptOrReject'];
        if( $acceptOrReject == null ){
            return ['result'=>'error'];
        }
        if ( !preg_match("/^[0-9]+$/i", $id) ) {
            return ['result'=>'error'];
        }
        $photo = Photo::find($id);
        if( $photo == null ){
            return ['result'=>'error'];
        }
        if( User::fromAppUser(Auth::user())->isAdmin() == false && !User::fromAppUser(Auth::user())->isModerator() ){
            return ['result'=>'error'];
        }
        DB::beginTransaction();
        try {
            if( $acceptOrReject == 'accept' ){
                $photo->approved_at = now(); 
                $photo->save();
            }
            elseif( $acceptOrReject == 'reject' ){
                $this->destroyPhoto($photo);
            }
            DB::commit();
            return ['result'=>'success'];
        
        } catch (\Exception $e) {
            DB::rollback();
            return ['result'=>'error'];
        }
    }

}
