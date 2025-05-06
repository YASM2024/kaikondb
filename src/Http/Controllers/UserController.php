<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

use Intervention\Image\ImageManager;
use Carbon\Carbon;

use App\Models\User as AppUser; // 変換用
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Role;
use Kaikon2\Kaikondb\Models\Profile;
use Kaikon2\Kaikondb\Models\UserLoginLog;

use Mail;

class UserController extends Controller
{

    public function showOpenProfile($id)
    {
        $profile = Profile::where('user_id', $id)
            ->select('id','show_name','icon','description')->firstOrFail();
        return $profile->toArray();
    }

    //
    public function showUsers(){
        $users = User::with('roles')->get()->map(function ($user) {
            // administrator は特別に処理する
            $user->admin = $user->isAdmin();
            $user->roles = $user->roles->pluck('name')->toArray();
            $user->last_login = $user->last_login();
            return $user;
        });
        return view('kaikon::users', ['users' => $users]);
    }

    public function show($id) {
        return User::with('profile','roles')->where('id', '=', $id)->get()->map(function ($data) {
            // administrator は特別に処理する
            $data->admin = $data->isAdmin();

            $data->show_name = $data->profile->show_name ?? null;
            $data->icon = $data->profile->icon ?? null;

            $tmp = collect($data->roles ?? [])->pluck('code')->toArray();
            unset($data->roles);
            $data->roles = implode(",", $tmp);

            $data->email_verified = isset($data->email_verified_at);

            $tmp = $data->is_active;
            unset($data->is_active);
            $data->is_active = $tmp == 1;

            $data->last_login = $data->last_login();

            unset($data->created_at);
            unset($data->updated_at);
            unset($data->profile);
            unset($data->email_verified_at);

            return $data;

        })->firstOrFail();
    }    

    public function update( $id, Request $request ) {

        if ( !preg_match("/^[0-9]+$/i", $id) ) { return['res' => 1 ];}
        // 入力バリデーション
        $inputs = $request->all();
        $rules = [
            'icon'=>'nullable|image|max:1024', //kbyte  // error
            'email' => 'nullable | string', 
            'show_name' => 'nullable | string', 
            'roles' => 'nullable | string', // error
            'is_active' => 'nullable | string', // error
        ];

        $validator = Validator::make($inputs, $rules);
        if($validator->fails()){ return['res' => 1];}

        $user = User::with('roles')->find($id);
        if( $user == null ){ return['res' => 1]; }

        $profile = Profile::firstOrCreate(
            ['user_id' => $id], // 検索条件
            ['show_name' => $user->name], // レコードがない場合の初期値
            ['icon' => 'anonymousIcon.svg'], // レコードがない場合の初期値
        );

        DB::beginTransaction();
        try {

            // アイコン
            if(isset($inputs['icon']) && $request->file('icon')){
                $photo = $request->file('icon');
                $img_file_name = now()->format('YmdHisu').CRC32($user->show_name).'.png';
                $path = $photo->storeAs('public/photos', $img_file_name);
                $img = ImageManager::imagick()->read($photo);
                $img->scaleDown(width: 200)//アスペクト比を維持
                    ->save(storage_path('app/public/profile/' . $img_file_name ) );
                $profile->icon = $img_file_name;
            }
            // メール(認証はリセットする)
            if(isset($inputs['email'])){
                $user->email = $inputs['email'];
                $user->email_verified_at = null;
                // メール認証用URLを送信する
                // ............
            }
            // 公開名
            if(isset($inputs['show_name'])){$profile->show_name = $inputs['show_name'];}            

            // 権限とステータスは管理者のみが操作可能
            if (Auth::check() && User::fromAppUser(Auth::user())->isAdmin()) {

                // 権限
                if(isset($inputs['roles'])){
                    // 対象が管理者自身の場合は編集不可
                    if ($user->isAdmin()){
                        throw new \Exception('Administratorの権限は変更できません');
                    }else{
                        $role_ids = json_decode($inputs['roles'], true);
                        $role_ids = Role::whereIn('code', $role_ids )->pluck('id')->toArray();
                        $role_ids = array_unique($role_ids);
                        $user->roles()->sync($role_ids);
                    }
                }

                // ステータス
                if(isset($inputs['is_active'])){
                    // 対象が管理者自身の場合は編集不可
                    if ($user->isAdmin()){
                        throw new \Exception('Administratorのステータスは変更できません');
                    }else{
                        // bool か null に変換
                        $is_active = filter_var($inputs['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                        if(isset($is_active)){
                            $user->is_active = (int) $is_active;  //bool値をint値に
                        }    
                    }
                }

            }

            $user->save();
            $profile->save();
            DB::commit();

            // ユーザ本人・管理者にメールを送信
            // ....

            return ['res' => 0 ];

        } catch (\Exception $e) {

            DB::rollback();
            return['res' => 1, 'errors' => $e->getMessage() ];

        }

    }

    /**
     * プロフィールエリア
     * 
     */

    
    /**
     * プロフィール編集画面表示
     */

    public function showProfile(Request $request)
    {
        // if (Auth::check() && !Auth::user()->hasVerifiedEmail()){
        //     return redirect()->route('verification.notice');
        // }

        $user = User::fromAppUser(Auth::user())->load('profile', 'roles');
        $user->last_login = $user->last_login();

        $profile = User::fromAppUser(Auth::user())->profile;
        if(!$profile) {
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->icon = 'anonymousIcon.svg';
            $profile->show_name = '未設定';
            $profile->description = '自己紹介文がありません';
            $profile->save();
        }
        return view('kaikon::profile.edit', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    public function updateProfile(Request $request){
        $inputs = $request->all();
        $rules = [
            'icon'=>'nullable|image|max:1024', //kbyte  // error
            'email' => 'nullable | string', 
            'show_name' => 'nullable | string', 
            'description' => 'nullable | string | max:400', 
        ];

        $validator = Validator::make($inputs, $rules);
        if ($validator->fails()) {
            return ['res' => 1, 'errors' => $validator->errors()];
        }
    
        DB::beginTransaction();
        try {
            $user = User::fromAppUser(Auth::user());
            if (!$user) { return ['res' => 1, 'errors' => 'User not found']; }    
                
            // ユーザーのプロフィールを取得（関係モデルを仮定）
            $profile = $user->profile ?? new Profile();
            if (isset($inputs['show_name'])) { $profile->show_name = $inputs['show_name']; }
            if (isset($inputs['description'])) { $profile->description = $inputs['description']; }
            if (isset($inputs['icon']) && $request->file('icon')){
                $photo = $request->file('icon');
                $img_file_name = now()->format('YmdHisu').CRC32($user->show_name).'.png';
                $path = $photo->storeAs('public/photos', $img_file_name);
                $img = ImageManager::imagick()->read($photo);
                $img->scaleDown(width: 200)//アスペクト比を維持
                    ->save(storage_path('app/public/profile/' . $img_file_name ) );
                $profile->icon = $img_file_name;
            }
            // メール(認証はリセットする)            
            if(isset($inputs['email'])){
                $user->email = $inputs['email'];
                $user->email_verified_at = null;
                // メール認証用URLを送信する
                // ............
            }
            $profile->user_id = $user->id; // 関係を確保

            $user->save();
            $profile->save();
            
            DB::commit();

            return ['res' => 0];

        } catch (\Exception $e) {
            DB::rollback();
            return['res' => 1, 'errors' => $e ];
        }
    }

    /**
     * プロフィール削除
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
