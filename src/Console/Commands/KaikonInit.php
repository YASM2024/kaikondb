<?php

namespace Kaikon2\Kaikondb\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\Notifications\VerifyEmail;

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
        $roleNames = ['Administrator', 'Developer', 'Moderator', 'User'];
        $roles = Role::whereIn('name', $roleNames)->pluck('id', 'name');

        $missingRoles = array_diff($roleNames, $roles->keys()->toArray());

        if (!empty($missingRoles)) {
            $this->error("ERROR: 以下のロールが存在しません: " . implode(', ', $missingRoles) . "。先にマイグレーションを行ってください。");
            return;
        }

        $this->info("INFO: 管理者ユーザ初期化を行います。");

        $administrator = config('kaikon.Administrator');
        $email = config('kaikon.Email');
        $password = Str::password(12);

        if (RoleUser::where('role_id', $roles['Administrator'])->exists() &&
            ! $this->confirm('既に登録されている管理者ユーザは削除されます。本当に実行してよろしいですか?')) {
            $this->info('INFO: キャンセルされました。');
            return;
        }

        DB::beginTransaction();

        try {
            // 既存管理者がいる場合、ユーザーを削除して作り直す（確認済み）
            $existing = User::where('email', $email)->first();
            if ($existing !== null) {
                RoleUser::where('user_id', $existing->id)->delete();
                $existing->forceDelete();
            }

            $user = User::create([
                'name' => $administrator,
                'email' => $email,
                'password' => Hash::make($password),
                'is_active' => 1,
                'login_failed' => 0,
                // 再実行でも必ず未認証スタートにする（認証メールを毎回出すため）
                'email_verified_at' => null,
            ]);

            // 関連ロールの削除
            RoleUser::where('user_id', $user->id)->delete();

            // 関連ロールの登録
            foreach ($roles as $roleId) {
                RoleUser::create([
                    'user_id' => $user->id,
                    'role_id' => $roleId,
                ]);
            }

            DB::commit();
            $this->info("INFO: 管理者ユーザ [{$administrator}] の初期化に成功しました。\n      初期パスワードは [{$password}] です。\nログインのうえ、パスワード変更を行ってください。");

            // 初期設定（再実行含む）では認証メールを即時送信する（email_queue=1 でもキューに積まない）
            try {
                if ($user instanceof MustVerifyEmail) {
                    $user->notifyNow(new VerifyEmail());
                    $this->info('INFO: 認証メールを即時送信しました（初期設定）。');
                }
            } catch (\Throwable $mailEx) {
                $this->warn('WARN: 認証メールの即時送信に失敗しました。メール設定をご確認ください。');
                $this->line($mailEx->getMessage());
            }

        } catch (\Exception $ex) {
            DB::rollBack();
            $this->error("ERROR: 処理に失敗しました。エラーは以下のとおりです。");
            $this->line($ex->getMessage());
        }
    }
}
