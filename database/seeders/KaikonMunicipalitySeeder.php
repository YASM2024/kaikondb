<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Seeder;
use DB;
use RuntimeException;

class KaikonMunicipalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = $this->resolveLatestDataFile(__DIR__ . '/data');

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            throw new RuntimeException("Failed to open CSV: {$csvPath}");
        }

        if (fgetcsv($handle) === false) {
            fclose($handle);
            throw new RuntimeException("CSV is empty: {$csvPath}");
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            $municipalityCode = $row[1] ?? '';
            $municipalityJa = $row[3] ?? '';
            if ($municipalityJa === '') {
                $municipalityJa = $row[2] ?? '';
            }

            if ($municipalityJa === '') {
                continue;
            }

            $rows[] = [
                'municipality_code' => $municipalityCode,
                'municipality_ja' => $municipalityJa,
                'municipality_en' => '',
                'status' => '1',
            ];
        }

        fclose($handle);

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('municipalities')->insert($chunk);
        }
    }

    private function resolveLatestDataFile(string $dataDir): string
    {
        $files = array_filter(glob($dataDir . '/*') ?: [], 'is_file');

        if ($files === []) {
            throw new RuntimeException("No data files found in: {$dataDir}");
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return $files[0];
    }
}
