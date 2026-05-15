<?php

namespace Kaikon2\Kaikondb\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class KaikonImportLegacySql extends Command
{
    protected $signature = 'kaikon:import-legacy-sql
        {path : 旧 articles 形式の SQL ダンプ（.sql）へのパス}
        {--output= : 変換後 SQL の出力先（指定時はインポートせず変換のみ）}
        {--skip-ddl : CREATE/DROP/ALTER TABLE 行を除外（migrate 済み DB 向けデータのみ投入）}';

    protected $description = '旧バックアップ（articles / article_id）を literature 用スキーマ向けに変換して投入する';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_readable($path)) {
            $this->error("SQL ファイルを読み込めません: {$path}");
            return self::FAILURE;
        }

        $this->info('旧形式 SQL を変換しています…');
        $sql = $this->transformSql(File::get($path), (bool) $this->option('skip-ddl'));

        $output = $this->option('output');
        if ($output) {
            File::put($output, $sql);
            $this->info("変換済み SQL を保存しました: {$output}");
            $this->line('投入例: mysql -u ... -p データベース名 < '.$output);
            return self::SUCCESS;
        }

        if (! $this->confirm('現在の DB に SQL を実行します。migrate:fresh 済みで空の literature スキーマであることを確認しましたか?', false)) {
            $this->warn('キャンセルしました。');
            return self::SUCCESS;
        }

        $originalMode = DB::selectOne('SELECT @@SESSION.sql_mode AS mode')->mode ?? '';
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET SESSION sql_mode = ""');

        try {
            foreach ($this->splitStatements($sql) as $statement) {
                $trimmed = trim($statement);
                if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                    continue;
                }
                DB::unprepared($trimmed);
            }
        } catch (\Throwable $e) {
            $this->error('インポート中にエラー: '.$e->getMessage());
            return self::FAILURE;
        } finally {
            if ($originalMode !== '') {
                DB::statement('SET SESSION sql_mode = ?', [$originalMode]);
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->info('インポートが完了しました。');
        return self::SUCCESS;
    }

    public function transformSql(string $sql, bool $skipDdl = false): string
    {
        if ($skipDdl) {
            $lines = preg_split('/\R/', $sql) ?: [];
            $lines = array_filter($lines, function (string $line): bool {
                $upper = strtoupper(ltrim($line));
                return ! preg_match('/^(CREATE|DROP|ALTER)\s+TABLE/i', $upper);
            });
            $sql = implode("\n", $lines);
        }

        $replacements = [
            ['`article_order`', '`literature_order`'],
            ['article_order', 'literature_order'],
            ['`articles`', '`literatures`'],
            ['INSERT INTO articles', 'INSERT INTO literatures'],
            ['INTO articles ', 'INTO literatures '],
            ['`article_id`', '`literature_id`'],
            ['article_id', 'literature_id'],
            ["'0000-00-00 00:00:00'", "'1970-01-01 00:00:01'"],
            ["'0000-00-00'", 'NULL'],
        ];

        foreach ($replacements as [$from, $to]) {
            $sql = str_replace($from, $to, $sql);
        }

        return $sql;
    }

    /**
     * @return iterable<string>
     */
    private function splitStatements(string $sql): iterable
    {
        $buffer = '';
        $inString = false;
        $stringChar = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $buffer .= $char;

            if ($inString) {
                if ($char === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $inString = false;
                }
                continue;
            }

            if ($char === "'" || $char === '"') {
                $inString = true;
                $stringChar = $char;
                continue;
            }

            if ($char === ';') {
                yield $buffer;
                $buffer = '';
            }
        }

        if (trim($buffer) !== '') {
            yield $buffer;
        }
    }
}
