<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

use Intervention\Image\ImageManager;

use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Photo;
use Kaikon2\Kaikondb\Models\Profile;

use Mail;


class PhotoController extends Controller
{
    public function showSearchMenu(Request $request)
    {
        if($request->user_id){$user_id = $request->user_id; }else{$user_id = "%"; }
            $photos = Photo::join('profiles', 'photos.user_id', '=', 'profiles.user_id')
                ->select('photos.id','thumbnail_url','photo_title','show_name','approved_at','photos.user_id')
                ->where('approved_at','!=', null)
                ->where('photo_title','LIKE', "%{$request->keyword}%")
                ->where('photos.user_id','LIKE', $user_id)
                ->orderBy('photos.id','desc')
                ->paginate(12)->withQueryString();

        $photographers = Photo::join('profiles','profiles.user_id','=','photos.user_id')
            ->select('photos.user_id','show_name')
            ->selectRaw('COUNT(photos.user_id) as count')
            ->groupBy('photos.user_id','show_name')
            ->orderBy('count','desc')
            ->get(); 
        
        $data = ['user_id'=> $request->user_id, 'keyword'=> $request->keyword];

        return view('kaikon::static.photos', ['photos'=>$photos, 'photographers'=>$photographers, 'data'=>$data]);
    }
    
    public function search()
    {
        return 'search';
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
            'photographer' => 'required | string',
            'memo' => 'nullable | string', 
            'image_file'=>'required|image|max:2048', //kbyte
        ];
        $validation = Validator::make($inputs, $rules);
        if($validation->fails()){
            return ['result'=>'failed'];
        }
        $data = $inputs;
        $data['action_type'] = 'create';

        if( $request->verified ){
            
            $photo = $request->file('image_file');
            if(isset($photo)){
                $img_file_name = now()->format('YmdHisu').CRC32($request->photographer).'.png';
                $code = sha1(now()->format('YmdHisu').$request->photographer);
                $place = isset($request->place) ? $request->place : '';
                $date = isset($request->date) ? $request->date : '';
                $memo = isset($request->memo) ? $request->memo : '';
                $random_sp_id = 0;
                $path = $photo->storeAs('public/photos', $img_file_name);

                $img1 = $img2 = ImageManager::imagick()->read($photo);
                //$img1 = $img2 = $manager->read($photo);
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
                    'delpass' => "1",//Hash::make($request->password), 
                    'error_count' => 0
                ]);

                $data = ['photographer'=>User::fromAppUser(Auth::user())->name];
                //管理者にメールを送信
                Mail::send('emails.photoCreate', $data, function($message){
                    $message->to('ymiyazaki713@gmail.com', 'YASUO MIYAZAKI')->subject('kai-kon: 写真投稿通知');
                });

                if($result){return ['result'=>'success'];}
                
            }
        }
        return ['result'=>'failed'];
    }

    public function show(Request $request, $id)
    {
        if (Auth::check()){
            $data = Photo::join('profiles', 'photos.user_id', '=', 'profiles.user_id')
                ->select('photos.id','url','photo_title','date','place','show_name','memo','photos.user_id','icon')
                ->where('photos.id', '=', $id)
                ->first();
        }else{
            $data = Photo::join('profiles', 'photos.user_id', '=', 'profiles.user_id')
                ->select('photos.id','url','photo_title','date','place','show_name','memo','photos.user_id','icon')
                ->where('photos.id', '=', $id)
                ->where('approved_at','!=', null)
                ->firstOrFail();
        }
        return $data;
    }

    public function download(){
        
        if (Auth::check() && User::fromAppUser(Auth::user())->isAdmin()){
            $photos = Photo::all()->get();
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
        if( $photo->user_id != User::fromAppUser(Auth::user())->id ) { return ['result'=>'error']; }
        if( $photo == null ){ return ['result'=>'error']; }

        DB::beginTransaction();
        try {
            $photo->delete();
            DB::commit();
            return ['result'=>'success'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['result'=>'error'];
        }
    } 

    public function showAdmin()
    {
        $photos = Photo::where('approved_at','=', null)->get();
        return view('kaikon::ja.photo.admin', ['photos'=>$photos]);
    }

    public function approve($id)
    {
        if ( !preg_match("/^[0-9]+$/i", $id) ) {
            return ['result'=>'error'];
        }
        $photo = Photo::find($id);
        if( $photo == null ){
            return ['result'=>'error'];
        }

        DB::beginTransaction();
        try {
            $photo->approved_at = now(); 
            $photo->save();
            DB::commit();
            return ['result'=>'success'];
        } catch (\Exception $e) {
            DB::rollback();
            return ['result'=>'error'];
        }
    }


}
