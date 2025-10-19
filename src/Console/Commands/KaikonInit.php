<?php

namespace Kaikon2\Kaikondb\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

use Illuminate\Auth\Events\Registered;

use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Role;
use Kaikon2\Kaikondb\Models\RoleUser;


class KaikonInit extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleNames = ['Administrator', 'Moderator', 'User'];
        $roles = Role::whereIn('name', $roleNames)->pluck('id', 'name');

        $missingRoles = array_diff($roleNames, $roles->keys()->toArray());

        if (!empty($missingRoles)) {
            $this->error("ERROR: 以下のロールが存在しません: " . implode(', ', $missingRoles) . "。先にマイグレーションを行ってください。");
            return;
        }

        $this->info("INFO: 管理者ユーザ初期化を行います。");

        if (RoleUser::where('role_id', $roles['Administrator'])->exists() &&
            !$this->confirm('既に登録されている管理者ユーザは削除されます。本当に実行してよろしいですか?')) {
                $this->info('INFO: キャンセルされました。');
                return;
        }

        $administrator = config('kaikon.Administrator');
        $email = config('kaikon.Email');
        $password = Str::password(12);

        DB::beginTransaction();

        try {
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $administrator,
                    'password' => Hash::make($password),
                    'is_active' => 1,
                    'login_failed' => 0,
                ]
            );

            // 関連ロールの削除
            RoleUser::where('user_id', $user->id)->delete();
            RoleUser::whereIn('role_id', $roles->values())->delete();

            // 関連ロールの登録
            foreach ($roles as $roleId) {
                RoleUser::create([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ]);
            }

            // event(new Registered($user)); // メール送信（必要なら）

            DB::commit();
            $this->info("INFO: 管理者ユーザ [{$administrator}] の初期化に成功しました。\n      初期パスワードは [{$password}] です。\nログインのうえ、パスワード変更を行ってください。");

        } catch (\Exception $ex) {
            DB::rollBack();
            $this->error("ERROR: 処理に失敗しました。エラーは以下のとおりです。");
            $this->line($ex->getMessage());
        }
    }
}
