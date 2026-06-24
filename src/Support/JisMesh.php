<?php

namespace Kaikon2\Kaikondb\Support;

use InvalidArgumentException;

/**
 * JIS X 0410 地域メッシュコード（統計局方式）。
 * 2次: 1次を 8×8 分割（緯5分×経7.5分）、3次: 2次を 10×10 分割（緯30秒×経45秒）。
 */
class JisMesh
{
    private const LAT_UNIT_1 = 2 / 3;

    private const LON_UNIT_1 = 1.0;

    private const LAT_UNIT_2 = self::LAT_UNIT_1 / 8;

    private const LON_UNIT_2 = self::LON_UNIT_1 / 8;

    private const LAT_UNIT_3 = self::LAT_UNIT_2 / 10;

    private const LON_UNIT_3 = self::LON_UNIT_2 / 10;

    /**
     * @return array{north: float, south: float, east: float, west: float}
     */
    public static function mesh3Bounds(string $code): array
    {
        if (! preg_match('/^\d{8}$/', $code)) {
            throw new InvalidArgumentException("Invalid mesh3 code: {$code}");
        }

        $p = (int) substr($code, 0, 2);
        $q = (int) substr($code, 2, 2);
        $r = (int) $code[4];
        $s = (int) $code[5];
        $t = (int) $code[6];
        $u = (int) $code[7];

        if ($r < 0 || $r > 7 || $s < 0 || $s > 7 || $t < 0 || $t > 9 || $u < 0 || $u > 9) {
            throw new InvalidArgumentException("Invalid mesh3 subdivision: {$code}");
        }

        $south = ($p * self::LAT_UNIT_1) + ($r * self::LAT_UNIT_2) + ($t * self::LAT_UNIT_3);
        $west = $q + 100 + ($s * self::LON_UNIT_2) + ($u * self::LON_UNIT_3);

        return [
            'north' => $south + self::LAT_UNIT_3,
            'south' => $south,
            'east' => $west + self::LON_UNIT_3,
            'west' => $west,
        ];
    }

    public static function latLonToMesh3Code(float $lat, float $lon): ?string
    {
        $tLat = $lat * 1.5;
        $tLon = $lon - 100.0;

        $m1Lat = (int) floor($tLat);
        $m1Lon = (int) floor($tLon);

        $tLat = ($tLat - $m1Lat) * 8;
        $tLon = ($tLon - $m1Lon) * 8;

        $m2Lat = (int) floor($tLat);
        $m2Lon = (int) floor($tLon);

        if ($m2Lat < 0 || $m2Lat > 7 || $m2Lon < 0 || $m2Lon > 7) {
            return null;
        }

        $tLat = ($tLat - $m2Lat) * 10;
        $tLon = ($tLon - $m2Lon) * 10;

        $m3Lat = (int) floor($tLat);
        $m3Lon = (int) floor($tLon);

        if ($m3Lat < 0 || $m3Lat > 9 || $m3Lon < 0 || $m3Lon > 9) {
            return null;
        }

        return sprintf('%02d%02d%d%d%d%d', $m1Lat, $m1Lon, $m2Lat, $m2Lon, $m3Lat, $m3Lon);
    }

    /**
     * @param  array{north: float, south: float, east: float, west: float}  $prefectureBounds
     * @return list<array{code: string, bounds: array{north: float, south: float, east: float, west: float}}>
     */
    public static function enumerateMesh3InCodeRange(string $southCode, string $northCode, array $prefectureBounds): array
    {
        self::assertBounds($prefectureBounds);

        if (! preg_match('/^\d{8}$/', $southCode) || ! preg_match('/^\d{8}$/', $northCode) || $southCode > $northCode) {
            throw new InvalidArgumentException('Invalid mesh3 code range.');
        }

        $southP = (int) substr($southCode, 0, 2);
        $southQ = (int) substr($southCode, 2, 2);
        $northP = (int) substr($northCode, 0, 2);
        $northQ = (int) substr($northCode, 2, 2);
        $pMin = min($southP, $northP) - 1;
        $pMax = max($southP, $northP) + 1;
        $qMin = min($southQ, $northQ) - 1;
        $qMax = max($southQ, $northQ) + 1;

        $cells = [];
        for ($p = $pMin; $p <= $pMax; $p++) {
            for ($q = $qMin; $q <= $qMax; $q++) {
                for ($r = 0; $r <= 7; $r++) {
                    for ($s = 0; $s <= 7; $s++) {
                        for ($t = 0; $t <= 9; $t++) {
                            for ($u = 0; $u <= 9; $u++) {
                                $code = sprintf('%02d%02d%d%d%d%d', $p, $q, $r, $s, $t, $u);
                                if ($code < $southCode || $code > $northCode) {
                                    continue;
                                }
                                $cellBounds = self::mesh3Bounds($code);
                                if (self::boundsIntersect($prefectureBounds, $cellBounds)) {
                                    $cells[] = [
                                        'code' => $code,
                                        'bounds' => $cellBounds,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        usort($cells, fn (array $a, array $b): int => strcmp($a['code'], $b['code']));

        return $cells;
    }

    /**
     * @return list<array{code: string, bounds: array{north: float, south: float, east: float, west: float}}>
     */
    public static function enumerateMesh3ForPrefecture(array $georef): array
    {
        if (! isset($georef['bounds']) || ! is_array($georef['bounds'])) {
            throw new InvalidArgumentException('georef bounds is required.');
        }

        $range = self::resolveMesh3CodeRange($georef);
        if ($range !== null) {
            return self::enumerateMesh3InCodeRange($range['south'], $range['north'], $georef['bounds']);
        }

        return self::enumerateMesh3InBounds($georef['bounds']);
    }

    /**
     * @return array{south: string, north: string}|null
     */
    public static function resolveMesh3CodeRange(array $georef): ?array
    {
        $mesh3 = $georef['mesh']['3'] ?? $georef['mesh'][3] ?? null;
        if (! is_array($mesh3) || ! isset($mesh3['code_range']) || ! is_array($mesh3['code_range'])) {
            return null;
        }

        $south = trim((string) ($mesh3['code_range']['south'] ?? ''));
        $north = trim((string) ($mesh3['code_range']['north'] ?? ''));
        if (! preg_match('/^\d{8}$/', $south) || ! preg_match('/^\d{8}$/', $north) || $south > $north) {
            return null;
        }

        return ['south' => $south, 'north' => $north];
    }

    /**
     * @param  array{north: float, south: float, east: float, west: float}  $bounds
     * @return list<array{code: string, bounds: array{north: float, south: float, east: float, west: float}}>
     */
    public static function enumerateMesh3InBounds(array $bounds): array
    {
        self::assertBounds($bounds);

        $cells = [];
        $pMin = (int) floor($bounds['south'] / self::LAT_UNIT_1);
        $pMax = (int) floor($bounds['north'] / self::LAT_UNIT_1);
        $qMin = (int) floor($bounds['west'] - 100);
        $qMax = (int) floor($bounds['east'] - 100);

        for ($p = $pMin; $p <= $pMax; $p++) {
            for ($q = $qMin; $q <= $qMax; $q++) {
                for ($r = 0; $r <= 7; $r++) {
                    for ($s = 0; $s <= 7; $s++) {
                        for ($t = 0; $t <= 9; $t++) {
                            for ($u = 0; $u <= 9; $u++) {
                                $code = sprintf('%02d%02d%d%d%d%d', $p, $q, $r, $s, $t, $u);
                                $cellBounds = self::mesh3Bounds($code);
                                if (self::boundsIntersect($bounds, $cellBounds)) {
                                    $cells[] = [
                                        'code' => $code,
                                        'bounds' => $cellBounds,
                                    ];
                                }
                            }
                        }
                    }
                }
            }
        }

        usort($cells, fn (array $a, array $b): int => strcmp($a['code'], $b['code']));

        return $cells;
    }

    /**
     * @param  array{north: float, south: float, east: float, west: float}  $bounds
     */
    private static function assertBounds(array $bounds): void
    {
        foreach (['north', 'south', 'east', 'west'] as $key) {
            if (! isset($bounds[$key]) || ! is_numeric($bounds[$key])) {
                throw new InvalidArgumentException("Bounds missing or invalid key: {$key}");
            }
        }

        if ($bounds['north'] <= $bounds['south'] || $bounds['east'] <= $bounds['west']) {
            throw new InvalidArgumentException('Bounds north/south or east/west are inverted.');
        }
    }

    /**
     * @param  array{north: float, south: float, east: float, west: float}  $a
     * @param  array{north: float, south: float, east: float, west: float}  $b
     */
    private static function boundsIntersect(array $a, array $b): bool
    {
        return $a['south'] < $b['north']
            && $a['north'] > $b['south']
            && $a['west'] < $b['east']
            && $a['east'] > $b['west'];
    }
}
