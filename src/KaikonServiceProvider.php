<?php

namespace Kaikon2\Kaikondb;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Failed;

use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

use Kaikon2\Kaikondb\Events\UserLoggedIn;
use Kaikon2\Kaikondb\Events\UserLoggedOut;
use Kaikon2\Kaikondb\Listeners\LogUserLogin;
use Kaikon2\Kaikondb\Listeners\LogUserLogout;
use Kaikon2\Kaikondb\Listeners\LogFailedLogin;

class KaikonServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        UserLoggedIn::class => [
            LogUserLogin::class,
        ],
        UserLoggedOut::class => [
            LogUserLogout::class,
        ],
        Failed::class => [
            LogFailedLogin::class,
        ],
    ];

    public function register(): void
    {
        //       
        $this->mergeConfigFrom(
            __DIR__.'/../config/kaikon.php', // パッケージの設定ファイルパス
            'kaikon'                         // 親プロジェクトでの設定キー
        );
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

        // コンポーネントをプレフィックス付きで登録
        Blade::componentNamespace('Kaikondb\\View\\Components', 'kaikon');

        // イベントリスナーの登録
        Event::listen(UserLoggedIn::class, LogUserLogin::class);
        Event::listen(UserLoggedOut::class, LogUserLogout::class);
        Event::listen(Failed::class, LogFailedLogin::class);

        // ルーターインスタンスを取得
        $router = $this->app['router'];

        // ミドルウェアをwebミドルウェアグループに追記する必要がある
        $router->pushMiddlewareToGroup('web', \Kaikon2\Kaikondb\Http\Middleware\SetLocale::class);
        
        // エイリアス登録 (withMiddleware での alias() 相当)
        $router->aliasMiddleware('isUser', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsTheUser::class);
        $router->aliasMiddleware('isModerator', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsModerator::class);
        $router->aliasMiddleware('isAdministrator', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsAdministrator::class);

        //
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/kaikon.php' => config_path('kaikon.php'),
            ], 'kaikon-config');

            $this->publishes([
                __DIR__.'/../lang' => resource_path('/../lang'),
            ], 'kaikon-lang');

            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('/seeders/kaikon'),
            ], 'kaikon-seeders');

            $this->publishes([
                __DIR__.'/../public' => public_path('/'),
            ], 'kaikon-public');

            $this->publishes([
                __DIR__.'/../storage' => storage_path('/'),
            ], 'kaikon-storage');

        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
