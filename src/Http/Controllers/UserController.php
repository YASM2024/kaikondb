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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;

use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Carbon\Carbon;

use App\Models\User as AppUser; // 変換用
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Role;
use Kaikon2\Kaikondb\Models\RoleUser;

use Kaikon2\Kaikondb\Models\Profile;
use Kaikon2\Kaikondb\Models\UserLoginLog;

use Kaikon2\Kaikondb\Models\Article;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Photo;
use Kaikon2\Kaikondb\Models\Specimen;

use Illuminate\Database\Eloquent\SoftDeletes;


class UserController extends Controller
{

    public function showOpenProfile($id)
    {
        $profile = Profile::where('user_id', $id)
            ->select('id', 'show_name', 'icon', 'description')
            ->first();

        if (!$profile) {
            $profile = Profile::where('user_id', -1)
                ->select('id', 'show_name', 'icon', 'description')
                ->firstOrFail();
        }
        return $profile->toArray();
    }

    //
    public function showUsers(){
        $users = User::with('roles')->get()->map(function ($user) {
            // administrator は特別に処理する
            $user->admin = $user->isAdmin();
            $user->roles = $user->roles->sortBy('code')->pluck('name')->toArray();
            $user->last_login = $user->last_login();
            return $user;
        });
        return view('kaikon::pages.users', ['users' => $users]);
    }

    public function show($id) {
        $anonymous = Profile::where('user_id', '=', '-1')->first();
        $user = User::with('profile', 'roles')->findOrFail($id);

        $user->admin = $user->isAdmin();
        $user->show_name = $user->profile->show_name ?? $anonymous->show_name;
        $user->icon      = $user->profile->icon ?? $anonymous->icon;

        $tmp = implode(",", collect($user->roles ?? [])->pluck('code')->toArray());

        $user->email_verified = isset($user->email_verified_at);
        $user->is_active      = $user->is_active == 1;
        $user->last_login     = $user->last_login();

        // 不要なプロパティの削除（モデルの$hiddenやAPI Resourceの使用を検討）
        unset($user->roles, $user->created_at, $user->updated_at, $user->profile, $user->email_verified_at);
        $user->roles = $tmp;

        return $user;
    }

    public function update($id, Request $request)
    {

        if (!ctype_digit((string) $id)) { return ['res' => 1, 'errors' => 'Invalid user id']; }

        $rules = [
            'icon' => 'sometimes|nullable|image|max:1024',
            'email' => 'sometimes|nullable|string|email',
            'show_name' => 'sometimes|nullable|string',
            'roles' => 'sometimes|nullable',
            'is_active' => 'sometimes|nullable',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ['res' => 1, 'errors' => $validator->errors()];
        }

        $inputs = $validator->validated();

        $user = User::with('roles')->find($id);
        if (!$user) {
            return ['res' => 1, 'errors' => 'User not found'];
        }

        $profile = Profile::firstOrCreate(
            ['user_id' => $id],
            [
                'show_name' => $user->name ?? '未設定',
                'description' => '自己紹介文がありません',
                'icon' => 'anonymousIcon.svg',
            ]
        );

        $currentUser = Auth::check() ? User::fromAppUser(Auth::user()) : null;
        $isAdmin = $currentUser ? $currentUser->isAdmin() : false;
        $isMe = $this->isMe((string) $id);

        // 実際に更新すべき入力だけを判定
        $hasEmail = $request->filled('email');
        $hasShowName = array_key_exists('show_name', $inputs) && $inputs['show_name'] !== null;
        $hasRoles = array_key_exists('roles', $inputs) && $inputs['roles'] !== null && $inputs['roles'] !== '';
        $hasIsActive = array_key_exists('is_active', $inputs) && $inputs['is_active'] !== null && $inputs['is_active'] !== '';

        // 管理者専用項目の事前チェック
        if ($hasRoles) {
            if (!$isAdmin) {
                return ['res' => 1, 'errors' => 'Only administrators can edit roles.'];
            }
            if ($isMe) {
                return ['res' => 1, 'errors' => 'You cannot edit your own roles.'];
            }
        }

        if ($hasIsActive) {
            if (!$isAdmin) {
                return ['res' => 1, 'errors' => 'Only administrators can edit status.'];
            }
            if ($isMe) {
                return ['res' => 1, 'errors' => 'You cannot edit your own status.'];
            }
        }

        // roles を先に解釈
        $roleCodes = null;
        if ($hasRoles) {
            $roleCodes = $this->parseRoleCodes($inputs['roles']);
            if ($roleCodes === null) {
                return ['res' => 1, 'errors' => 'Invalid roles value'];
            }
        }

        // is_active を先に解釈
        $isActive = null;
        if ($hasIsActive) {
            $isActive = $this->parseBoolean($inputs['is_active']);
            if ($isActive === null) {
                return ['res' => 1, 'errors' => 'Invalid is_active value'];
            }
        }

        DB::beginTransaction();

        try {
            // アイコン
            if ($request->hasFile('icon')) {
                $photo = $request->file('icon');
                $baseName = $profile->show_name ?? $user->name ?? 'user';
                $imgFileName = now()->format('YmdHisu') . crc32($baseName) . '.png';

                $imageManager = ImageManager::usingDriver(Driver::class);
                $img = $imageManager->decodeSplFileInfo($photo);
                $img->scaleDown(width: 200)
                    ->save(storage_path('app/public/profile/' . $imgFileName));

                $profile->icon = $imgFileName;
            }

            // メール
            // null / 空文字は更新しない
            if ($hasEmail) {
                $newEmail = trim((string) $request->input('email'));
                if ($newEmail !== '' && $newEmail !== $user->email) {
                    $user->email = $newEmail;
                    $user->email_verified_at = null;
                    // 必要ならここで認証メール送信
                }
            }

            // 公開名
            if ($hasShowName) {
                $profile->show_name = (string) $inputs['show_name'];
            }

            // 権限
            if ($hasRoles) {
                $roleIds = Role::whereIn('code', $roleCodes)
                    ->pluck('id')
                    ->unique()
                    ->toArray();

                $user->roles()->sync($roleIds);
            }

            // ステータス
            if ($hasIsActive) {
                $user->is_active = (int) $isActive;
            }

            $user->save();
            $profile->save();

            DB::commit();

            return ['res' => 0];
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return [
                'res' => 1,
                'errors' => $e->getMessage(),
            ];
        }
    }

    private function parseRoleCodes($value): ?array
    {
        if (is_array($value)) {
            $roleCodes = $value;
        } elseif (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return null;
            }

            $decoded = json_decode($trimmed, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $roleCodes = $decoded;
            } else {
                // "010,090" のような文字列にも対応
                $roleCodes = array_map('trim', explode(',', $trimmed));
            }
        } else {
            return null;
        }

        $roleCodes = array_map(function ($item) {
            if (is_string($item)) {
                return trim($item);
            }
            if (is_int($item)) {
                return (string) $item;
            }
            return '';
        }, $roleCodes);

        $roleCodes = array_values(array_unique(array_filter($roleCodes, function ($item) {
            return $item !== '';
        })));

        return $roleCodes;
    }

    private function parseBoolean($value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    private function isMe(string $user_id): bool
    {
        return Auth::id() === (int) $user_id;
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
            $user_edit_flag = false;
            if (!$user) { return ['res' => 1, 'errors' => 'User not found']; }    
            // ユーザーのプロフィールを取得（関係モデルを仮定）
            $profile = $user->profile ?? new Profile();
            $profile_edit_flag = false;
            if (isset($inputs['show_name'])) { 
                $profile->show_name = $inputs['show_name'];
                $profile_edit_flag = true;
            }
            if (isset($inputs['description'])) {
                $profile->description = $inputs['description'];
                $profile_edit_flag = true;
            }
            if (isset($inputs['icon']) && $request->file('icon')){
                $photo = $request->file('icon');
                $img_file_name = now()->format('YmdHisu').CRC32($user->show_name).'.png';
                $path = $photo->storeAs('public/photos', $img_file_name);
                $imageManager = ImageManager::usingDriver(Driver::class);
                $img = $imageManager->decodeSplFileInfo($photo);
                $img->scaleDown(width: 200)//アスペクト比を維持
                    ->save(storage_path('app/public/profile/' . $img_file_name ) );
                $profile->icon = $img_file_name;
                $profile_edit_flag = true;
            }
            // メール(認証はリセットする)            
            if(isset($inputs['email'])){
                $user->email = $inputs['email'];
                $user->email_verified_at = null;
                $user_edit_flag = true;
                // メール認証用URLを送信する
                // ............
            }
            $profile->user_id = $user->id; // 関係を確保
            if($user_edit_flag){ $user->save(); }
            if($profile_edit_flag){ $profile->save(); }
            DB::commit();
            return ['res' => 0];

        } catch (\Exception $e) {
            DB::rollback();
            return['res' => 1, 'errors' => $e, 'e' => $error, 'user' => $user, 'profile' => $profile];
        }
    }

    /**
     * 文献・記録・写真・標本に user_id が残っていれば削除不可（ソフト削除済み行も含む）
     *
     * @return list<string>
     */
    private function contentDependencyLabelsForUser(int $userId): array
    {
        $defs = [
            [Article::class, '文献（articles）'],
            [Record::class, '観察記録（records）'],
            [Photo::class, '写真（photos）'],
            [Specimen::class, '標本（specimens）'],
        ];

        $labels = [];
        foreach ($defs as [$class, $label]) {
            $q = $class::query()->where('user_id', $userId);
            if (in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
                $q->withTrashed();
            }
            if ($q->exists()) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * 管理画面：ユーザ削除（JSON）
     */
    public function adminDestroyUser(string $id, Request $request)
    {
        if (! ctype_digit((string) $id)) {
            return response()->json(['res' => 1, 'errors' => 'Invalid user id']);
        }

        $actor = Auth::check() ? User::with('roles')->find(Auth::id()) : null;
        if (! $actor || ! $actor->isAdmin()) {
            return response()->json(['res' => 1, 'errors' => 'Forbidden'], 403);
        }

        if ((int) $id === (int) Auth::id()) {
            return response()->json(['res' => 1, 'errors' => 'You cannot delete your own account.']);
        }

        $user = User::with('roles')->find($id);
        if (! $user) {
            return response()->json(['res' => 1, 'errors' => 'User not found']);
        }

        if ($user->isAdmin()) {
            $otherAdmins = User::where('id', '!=', $user->id)
                ->whereHas('roles', function ($q) {
                    $q->where('name', 'Administrator');
                })
                ->count();
            if ($otherAdmins < 1) {
                return response()->json(['res' => 1, 'errors' => 'Cannot delete the last administrator.']);
            }
        }

        $blockedLabels = $this->contentDependencyLabelsForUser((int) $id);
        if ($blockedLabels !== []) {
            return response()->json([
                'res' => 1,
                'code' => 'in_use',
                'message' => '次のコンテンツで使用されているため削除できません。',
                'labels' => $blockedLabels,
            ]);
        }

        DB::beginTransaction();

        try {
            if ($user->profile) {
                $user->profile->delete();
            }
            $user->roles()->detach();
            $user->user_login_logs()->delete();
            $user->delete();

            DB::commit();

            return response()->json(['res' => 0]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json(['res' => 1, 'errors' => $e->getMessage()]);
        }
    }

    /**
     * プロフィール（マイページ）からの自身のアカウント削除
     */
    public function destroyProfile(Request $request): RedirectResponse
    {
        $blockedLabels = $this->contentDependencyLabelsForUser((int) $request->user()->id);
        if ($blockedLabels !== []) {
            return redirect()->back()->withErrors(
                [
                    'password' => '次のコンテンツで使用中のためアカウントを削除できません：'.implode('、', $blockedLabels),
                ],
                'userDeletion'
            );
        }

        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);
        $user = User::fromAppUser($request->user());
        // これは不要かもしれない...　$user = $request->user();

        Auth::logout();
        try {
            $user->profile()->delete();
            $user->roles()->detach();
            $user->user_login_logs()->delete();
            $user->delete();
        } catch (\Exception $e) {
            dd($e->getMessage());
        }


        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * `destroy` で参照されるルート互換（route:cache／Resource／ホスト側の旧定義向け）。
     * DELETE …/admin/users/{id} が誤ってここに来ても管理者削除へ回し、パスワードは不要。
     * マイページからの削除は POST profile.destroy → {@see destroyProfile}（パスワード必須）。
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->isMethod('DELETE') && preg_match('#admin/users/(\d+)(?:/purge)?$#', $request->path(), $m)) {
            return $this->adminDestroyUser($m[1], $request);
        }

        return $this->destroyProfile($request);
    }

    /**     * 権限エリア
     * 
     */

    function assignRoleIfNotExists(User $user, string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            // ロールが存在しない場合はとりあえず何もしない cf. 例外を投げる
            return;
        }

        $exists = RoleUser::where('user_id', $user->id)
                        ->where('role_id', $role->id)
                        ->exists();

        if (!$exists) {
            RoleUser::create([
                'user_id' => $user->id,
                'role_id' => $role->id,
            ]);
        }
    }

}
