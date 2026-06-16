<?php

namespace Kaikon2\KaikondbSeeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KaikonLandmarkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonPath = __DIR__.'/data/landmarks.json';
        if (! is_file($jsonPath)) {
            return;
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);
        if (! is_array($data) || ! is_array($data['points'] ?? null)) {
            return;
        }

        $prefectureId = 19;
        $sortOrder = 0;

        foreach ($data['points'] as $point) {
            if (! is_array($point)) {
                continue;
            }

            DB::table('landmarks')->insert([
                'prefecture_id' => $prefectureId,
                'code' => (string) ($point['id'] ?? ''),
                'label' => (string) ($point['label'] ?? ''),
                'lat' => (float) ($point['lat'] ?? 0),
                'lon' => (float) ($point['lon'] ?? 0),
                'pattern' => (string) ($point['pattern'] ?? 'mountain'),
                'sort_order' => $sortOrder++,
            ]);
        }
    }
}
