<?php

namespace Kaikon2\Kaikondb\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrefectureMapConfig
{
    private const CACHE_TTL_SECONDS = 86400;

    /**
     * @return array{
     *   id: int,
     *   prefecture_ja: string,
     *   prefecture_en: string,
     *   file: string,
     *   map_file_stem: string,
     *   map_shapes_id: string,
     *   maps_url: string,
     *   landmarks_url: string
     * }|null
     */
    public static function resolve(): ?array
    {
        $raw = config('kaikon.PPREFECTURE');
        if ($raw === null || $raw === '') {
            return null;
        }

        $id = (int) $raw;
        if ($id < 1 || $id > 47) {
            return null;
        }

        $key = self::cacheKey($id);
        $cached = Cache::get($key);
        if (is_array($cached)) {
            $cached['maps_url'] = self::mapsUrl();
            $cached['landmarks_url'] = self::landmarksUrl();

            return $cached;
        }

        $loaded = self::load($id);
        if ($loaded === null) {
            return null;
        }

        Cache::put($key, $loaded, self::CACHE_TTL_SECONDS);
        $loaded['maps_url'] = self::mapsUrl();
        $loaded['landmarks_url'] = self::landmarksUrl();

        return $loaded;
    }

    public static function forget(?int $id = null): void
    {
        if ($id === null) {
            $raw = config('kaikon.PPREFECTURE');
            if ($raw === null || $raw === '') {
                return;
            }
            $id = (int) $raw;
        }
        Cache::forget(self::cacheKey($id));
    }

    private static function mapsUrl(): string
    {
        return rtrim(url('/maps'), '/');
    }

    private static function landmarksUrl(): string
    {
        return rtrim(url('/landmarks'), '/');
    }

    private static function cacheKey(int $id): string
    {
        return 'kaikon.prefecture_map.'.$id;
    }

    private static function load(int $id): ?array
    {
        if (! Schema::hasTable('prefectures')) {
            return null;
        }

        $row = DB::table('prefectures')->where('id', $id)->first();
        if ($row === null) {
            return null;
        }

        return self::format($row);
    }

    private static function format(object $row): array
    {
        $stem = pathinfo((string) $row->file, PATHINFO_FILENAME);

        return [
            'id' => (int) $row->id,
            'prefecture_ja' => (string) $row->prefecture_ja,
            'prefecture_en' => (string) $row->prefecture_en,
            'file' => (string) $row->file,
            'map_file_stem' => $stem,
            'map_shapes_id' => $row->prefecture_en.'-map-shapes',
            'maps_url' => self::mapsUrl(),
            'landmarks_url' => self::landmarksUrl(),
        ];
    }
}
