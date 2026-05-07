<?php

namespace Kaikon2\Kaikondb\Support;

use Illuminate\Support\Facades\File;

class KaikonLoginTrace
{
    public static function logFilePath(): string
    {
        return storage_path('logs/kaikondb-login-trace.log');
    }

    /**
     * LOG_LEVEL で捨てられないよう、ログイン処理の経路だけを専用ファイルへ追記する。
     */
    public static function append(string $event, array $context = []): void
    {
        try {
            $line = '['.now()->toIso8601String()."] {$event} ".json_encode($context, JSON_UNESCAPED_UNICODE).PHP_EOL;
            File::append(self::logFilePath(), $line);
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * @return array<int, string>
     */
    public static function tail(int $lines = 30): array
    {
        $path = self::logFilePath();
        if (! is_readable($path)) {
            return [];
        }
        $all = preg_split('/\r\n|\r|\n/', trim((string) File::get($path)));
        $all = array_values(array_filter($all, fn ($l) => is_string($l) && $l !== ''));

        return array_slice($all, max(0, count($all) - $lines));
    }
}
