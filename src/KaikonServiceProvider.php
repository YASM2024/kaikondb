<?php

namespace Kaikon2\Kaikondb;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Blade;
use Kaikon2\Kaikondb\Auth\SoftDeleteAwareUserProvider;
use Kaikon2\Kaikondb\Listeners\LogFailedLogin;
use Kaikon2\Kaikondb\Listeners\LogUserLogin;
use Kaikon2\Kaikondb\Listeners\LogUserLogout;
use Kaikon2\Kaikondb\Support\KaikonLoginNotificationContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;

class KaikonServiceProvider extends ServiceProvider
{
    public const QUEUE_HEARTBEAT_FILE = 'kaikon/queue-worker-heartbeat.txt';

    public const QUEUE_WORKER_PID_FILE = 'kaikon/queue-worker.pid';

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
        $this->mergeConfigFrom(
            __DIR__.'/../config/kaikon.php',
            'kaikon'
        );

        config(['auth.providers.users' => [
            'driver' => 'softdelete',
            'model' => \Kaikon2\Kaikondb\Models\User::class,
        ]]);

        $this->app->afterResolving('auth', function ($auth): void {
            if (method_exists($auth, 'provider')) {
                $auth->provider('softdelete', function ($app, array $config) {
                    return new SoftDeleteAwareUserProvider($app['hash'], $config['model']);
                });
            }
        });

        $this->app->scoped(
            KaikonLoginNotificationContext::class,
            fn (): KaikonLoginNotificationContext => new KaikonLoginNotificationContext
        );
    }

    public function boot(): void
    {
        $this->resolveAppPathPrefix();

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/console.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'kaikon');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'kaikon');

        Blade::componentNamespace('Kaikon2\\Kaikondb\\View\\Components', 'kaikon');

        if ((int) config('kaikon.FEATURES.listeners.log_failed_login', 1) === 1) {
            Event::listen(Failed::class, LogFailedLogin::class);
        }
        Event::listen(Login::class, LogUserLogin::class);
        if ((int) config('kaikon.FEATURES.listeners.log_user_logout', 1) === 1) {
            Event::listen(Logout::class, LogUserLogout::class);
        }

        $heartbeat = function (): void {
            $path = storage_path('app/'.self::QUEUE_HEARTBEAT_FILE);
            $dir = \dirname($path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            @file_put_contents($path, (string) time(), LOCK_EX);
        };
        $queueEvents = [
            \Illuminate\Queue\Events\Looping::class,
            \Illuminate\Queue\Events\JobProcessed::class,
            \Illuminate\Queue\Events\JobFailed::class,
        ];
        foreach ($queueEvents as $evt) {
            if (class_exists($evt)) {
                Event::listen($evt, $heartbeat);
            }
        }

        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', \Kaikon2\Kaikondb\Http\Middleware\SetLocale::class);
        if (class_exists(\Kaikon2\Kaikondb\Http\Middleware\EnforceIdleTimeout::class)) {
            $router->pushMiddlewareToGroup('web', \Kaikon2\Kaikondb\Http\Middleware\EnforceIdleTimeout::class);
        }

        $router->aliasMiddleware('filterIp', \Kaikon2\Kaikondb\Http\Middleware\FilterByWhitelistIp::class);
        $router->aliasMiddleware('isUser', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsTheUser::class);
        $router->aliasMiddleware('isModerator', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsModerator::class);
        $router->aliasMiddleware('isDeveloper', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsDeveloper::class);
        $router->aliasMiddleware('isAdministrator', \Kaikon2\Kaikondb\Http\Middleware\EnsureUserIsAdministrator::class);

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

        $this->commands([
            \Kaikon2\Kaikondb\Console\Commands\KaikonQueueWork::class,
            \Kaikon2\Kaikondb\Console\Commands\KaikonImportLegacySql::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Kaikon2\KaikondbSeeders\RunSeederCommand::class,
            ]);
        }
    }

    private function normalizeAppPathPrefix(string $raw): string
    {
        $t = trim($raw);
        if ($t === '') {
            return '';
        }
        $t = trim($t, '/');

        return $t === '' ? '' : '/'.$t;
    }

    private function resolveAppPathPrefix(): void
    {
        $explicit = config('kaikon.APP_PATH_PREFIX');
        if (is_string($explicit) && $explicit !== '') {
            config(['kaikon.APP_PATH_PREFIX' => $this->normalizeAppPathPrefix($explicit)]);

            return;
        }
        if ($explicit === '') {
            config(['kaikon.APP_PATH_PREFIX' => '']);

            return;
        }

        $path = parse_url((string) config('app.url'), PHP_URL_PATH) ?: '';
        if ($path === '' || $path === '/') {
            config(['kaikon.APP_PATH_PREFIX' => '']);
        } else {
            config(['kaikon.APP_PATH_PREFIX' => $this->normalizeAppPathPrefix($path)]);
        }
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
