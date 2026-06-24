<?php

namespace Kaikon2\Kaikondb;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

use Kaikon2\Kaikondb\Auth\SoftDeleteAwareUserProvider;
use Kaikon2\Kaikondb\Listeners\LogFailedLogin;
use Kaikon2\Kaikondb\Listeners\LogUserLogin;
use Kaikon2\Kaikondb\Listeners\LogUserLogout;
use Kaikon2\Kaikondb\Support\KaikonLoginNotificationContext;

class KaikonServiceProvider extends ServiceProvider
{
    public const QUEUE_HEARTBEAT_FILE = 'kaikon/queue-worker-heartbeat.txt';
    public const QUEUE_WORKER_PID_FILE = 'kaikon/queue-worker.pid';

    /**
     * Register any application services.
     */
    
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Failed::class => [
            LogFailedLogin::class,
        ],
        Login::class => [
            LogUserLogin::class,
        ],
        Logout::class => [
            LogUserLogout::class,
        ],
    ];

    public function register(): void
    {
        //       
        $this->mergeConfigFrom(
            __DIR__.'/../config/kaikon.php', // パッケージの設定ファイルパス
            'kaikon'                         // 親プロジェクトでの設定キー
        );

        // KaikonUser を強制する（config:cache と両立させるため、Auth 解決前の register で確定させる）
        config(['auth.providers.users' => [
            'driver' => 'softdelete',
            'model' => \Kaikon2\Kaikondb\Models\User::class,
        ]]);

        // AuthManager が解決されたタイミングで必ず provider を登録する
        $this->app->afterResolving('auth', function ($auth): void {
            if (method_exists($auth, 'provider')) {
                $auth->provider('softdelete', function ($app, array $config) {
                    return new SoftDeleteAwareUserProvider($app['hash'], $config['model']);
                });
            }
        });

        $this->app->scoped(KaikonLoginNotificationContext::class, fn (): KaikonLoginNotificationContext => new KaikonLoginNotificationContext);

        // $this->commands([
        //     \Kaikon\Console\Commands\CustomCommand::class,
        // ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * composer.jsonのautoload->psr4 には src/ と seeders/ のほか、 factories/ が入る
         * その他のディレクトリ（config/ routes/ views/ migrations/ など）は、 
         * Laravel の組み込みメソッド (loadMigrationsFrom() など) でロードできるため、 
         * autoload には含めていない。2025.05.01
         */


        // パッケージroute/databaseなどを読み込み
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'kaikon');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kaikon');

        // コンポーネントをプレフィックス付きで登録（View\Components 配下のクラス → x-kaikon::*）
        Blade::componentNamespace('Kaikon2\\Kaikondb\\View\\Components', 'kaikon');

        // イベントリスナーの登録（設定でON/OFF可能）
        if ((int) config('kaikon.FEATURES.listeners.log_failed_login', 1) === 1) {
            Event::listen(Failed::class, LogFailedLogin::class);
        }
        // ログイン通知用ペイロードは LogUserLogin が scoped の KaikonLoginNotificationContext に載せるため、
        // リスナー自体は常に登録する。DB への user_login_logs 書き込みだけ FEATURES で抑止する。
        Event::listen(Login::class, LogUserLogin::class);
        if ((int) config('kaikon.FEATURES.listeners.log_user_logout', 1) === 1) {
            Event::listen(Logout::class, LogUserLogout::class);
        }

        // queue worker の生存確認用ハートビート（queue:work プロセス内で更新される）
        // 可能なイベントを掴んで Cache に時刻を書き込む（存在しないクラスは無視）
        $heartbeat = function (): void {
            $path = storage_path('app/' . self::QUEUE_HEARTBEAT_FILE);
            $dir = \dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @file_put_contents($path, (string) time(), LOCK_EX);
        };
        $queueEvents = [
            \Illuminate\Queue\Events\Looping::class, // worker のループ毎に発火（ジョブが無くても発火する想定）
            \Illuminate\Queue\Events\JobProcessed::class,
            \Illuminate\Queue\Events\JobFailed::class,
        ];
        foreach ($queueEvents as $evt) {
            if (class_exists($evt)) {
                Event::listen($evt, $heartbeat);
            }
        }

        // Auth provider / auth.providers.users の強制は register() で行う

        // ルーターインスタンスを取得
        $router = $this->app['router'];

        // webミドルウェアグループに登録（bootstrap/app.php を上書き）
        $router->pushMiddlewareToGroup('web', \Kaikon2\Kaikondb\Http\Middleware\SetLocale::class);
        $router->pushMiddlewareToGroup('web', \Kaikon2\Kaikondb\Http\Middleware\BlockBadUserAgent::class);
        if (class_exists(\Kaikon2\Kaikondb\Http\Middleware\EnforceIdleTimeout::class)) {
            $router->pushMiddlewareToGroup('web', \Kaikon2\Kaikondb\Http\Middleware\EnforceIdleTimeout::class);
        }
        
        // aliasミドルウェアグループ登録（bootstrap/app.php を上書き）
        $router->aliasMiddleware('filterIp', \Kaikon2\Kaikondb\Http\Middleware\FilterByWhitelistIp::class);
        $router->aliasMiddleware('isUser', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsTheUser::class);
        $router->aliasMiddleware('isModerator', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsModerator::class);
        $router->aliasMiddleware('isDeveloper', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsDeveloper::class);
        $router->aliasMiddleware('isAdministrator', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsAdministrator::class);
        $router->aliasMiddleware('sectionAvailable', \Kaikon2\Kaikondb\Http\Middleware\EnsureSectionAvailable::class);

        // 各フォルダ・ファイルを作成
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kaikon.php' => config_path('kaikon.php'),
            ], 'kaikon-config');

            $this->publishes([
                __DIR__.'/../lang' => resource_path('/../lang'),
            ], 'kaikon-lang');

            $this->publishes([
                __DIR__.'/../public' => public_path('/'),
            ], 'kaikon-public');

            $this->publishes([
                __DIR__.'/../storage' => storage_path('/'),
            ], 'kaikon-storage');

        }

        // カスタムコマンドの登録
        // - `kaikon:queue-work` は Web から Artisan::call() でも使うため常に登録する
        $this->commands([
            \Kaikon2\Kaikondb\Console\Commands\KaikonQueueWork::class,
        ]);
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Kaikon2\KaikondbSeeders\RunSeederCommand::class,
                \Kaikon2\Kaikondb\Console\Commands\GenerateMeshOverlay::class,
            ]);
        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
