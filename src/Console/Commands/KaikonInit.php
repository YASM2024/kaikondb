<?php

namespace Kaikon2\Kaikondb\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Role;
use Kaikon2\Kaikondb\Models\RoleUser;

use Mail;

class KaikonInit extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
        $admin_role = Role::where('name', 'Administrator')->value('id');
        if($admin_role == null){
            $this->error("ERROR: 管理者ロールが存在しません。先にマイグレーションを行ってください。");
            return;
        }
        $this->info("INFO: 管理者ユーザ初期化を行います。");

        if(RoleUser::where('role_id', $admin_role)->exists() && 
            !$this->confirm('既に登録されている管理者ユーザは削除されます。本当に実行してよろしいですか?')) {
            $this->info('INFO: キャンセルされました。');
            return;
        }

        $administrator = config('kaikon.Administrator');
        $email = config('kaikon.Email');
        $password = Str::password(12); //ランダムなパスワード生成

        DB::beginTransaction();

        try{
            $user = User::firstOrCreate(
                [   // 検索条件
                    'email' => $email
                ],
                [
                    'name'     => $administrator,
                    'password' => Hash::make($password),
                    'is_active'=>1,
                    'login_failed'=>0,
                ]
            );
            
            RoleUser::where('role_id', $admin_role)->delete();
            RoleUser::firstOrCreate(['user_id'=>$user->id, 'role_id'=>$admin_role]);

            //$emailにメールを送信
            event(new \Illuminate\Auth\Events\Registered($user));

            DB::commit();
            $this->info("INFO: 管理者ユーザの初期化に成功しました。メール認証のリンクが送信されましたので、メールを確認してください。");

        }catch( Exception $ex ){
            
            DB::rollback();
            $this->error("ERROR: 処理に失敗しました。エラーは以下のとおりです。");
            echo $ex; 

        }
        
    }
}
