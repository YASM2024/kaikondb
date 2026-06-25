<?php



namespace Kaikon2\Kaikondb\Console\Commands;



use Illuminate\Console\Command;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Schema;

use Kaikon2\Kaikondb\Support\JisMesh;

use RuntimeException;



class GenerateMeshOverlay extends Command

{

    protected $signature = 'kaikon:generate-mesh-overlay

        {prefecture? : 都道府県 ID（1-47）。未指定時は config kaikon.PPREFECTURE}

        {--maps-dir= : maps ディレクトリ（既定: パッケージ public/maps）}

    ';



    protected $description = '都道府県 georef 範囲に交差する3次メッシュ JSON を生成する';



    public function handle(): int

    {

        $prefectureId = $this->argument('prefecture');

        if ($prefectureId === null || $prefectureId === '') {

            $raw = config('kaikon.PPREFECTURE');

            if ($raw === null || $raw === '') {

                $this->error('都道府県 ID を引数で指定するか、config kaikon.PPREFECTURE を設定してください。');



                return self::FAILURE;

            }

            $prefectureId = (int) $raw;

        } else {

            $prefectureId = (int) $prefectureId;

        }



        if ($prefectureId < 1 || $prefectureId > 47) {

            $this->error('都道府県 ID は 1-47 の範囲で指定してください。');



            return self::FAILURE;

        }



        $mapsDir = $this->option('maps-dir')

            ?: base_path('public/maps');



        $mapStem = $this->resolveMapStem($prefectureId);

        $georefPath = $mapsDir.DIRECTORY_SEPARATOR.$mapStem.'_georef.json';

        if (! is_file($georefPath)) {

            $this->error("georef が見つかりません: {$georefPath}");



            return self::FAILURE;

        }



        $georef = json_decode((string) file_get_contents($georefPath), true);

        if (! is_array($georef) || ! isset($georef['bounds']) || ! is_array($georef['bounds'])) {

            $this->error("georef の bounds が不正です: {$georefPath}");



            return self::FAILURE;

        }



        $cells = JisMesh::enumerateMesh3ForPrefecture($georef);



        $codeRange = JisMesh::resolveMesh3CodeRange($georef);

        $payload = [

            'order' => 3,

            'prefecture_id' => $prefectureId,

            'generated_at' => now()->toIso8601String(),

            'bounds_source' => $georefPath,

            'cell_count' => count($cells),

            'cells' => $cells,

        ];

        if ($codeRange !== null) {

            $payload['code_range'] = $codeRange;

        }



        $outputPath = $mapsDir.DIRECTORY_SEPARATOR.$mapStem.'_mesh3.json';

        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {

            $this->error('JSON のエンコードに失敗しました。');



            return self::FAILURE;

        }



        if (file_put_contents($outputPath, $json.PHP_EOL) === false) {

            $this->error("書き込みに失敗しました: {$outputPath}");



            return self::FAILURE;

        }



        $this->info("Generated {$outputPath} ({$payload['cell_count']} cells)");



        return self::SUCCESS;

    }



    private function resolveMapStem(int $prefectureId): string

    {

        if (! Schema::hasTable('prefectures')) {

            throw new RuntimeException('prefectures テーブルがありません。マイグレーション後に実行してください。');

        }



        $row = DB::table('prefectures')->where('id', $prefectureId)->first();

        if ($row === null || empty($row->file)) {

            throw new RuntimeException("prefecture id={$prefectureId} の file が見つかりません。");

        }



        return pathinfo((string) $row->file, PATHINFO_FILENAME);

    }

}


