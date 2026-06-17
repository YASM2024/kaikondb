<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Kaikon2\Kaikondb\Models\Landmark;
use Kaikon2\Kaikondb\Support\PrefectureMapConfig;

class LandmarkController extends Controller
{
    /**
     * @var array<string, string>
     */
    private static array $editRules = [
        'label' => 'required|string|max:255',
        'lat' => 'required|numeric|between:-90,90',
        'lon' => 'required|numeric|between:-180,180',
        'pattern' => 'required|string|in:mountain,urban',
        'sort_order' => 'nullable|integer|min:0|max:999',
    ];

    public function showMaster()
    {
        $mapConfig = PrefectureMapConfig::resolve();
        $georef = self::loadGeoref($mapConfig);

        return view('kaikon::masters.landmarks', [
            'mapConfig' => $mapConfig,
            'georef' => $georef,
        ]);
    }

    /**
     * 管理画面用ランドマーク一覧。
     */
    public function all(): JsonResponse
    {
        $prefectureId = self::resolvePrefectureId();
        if ($prefectureId === null) {
            return response()->json([]);
        }

        $rows = Landmark::query()
            ->where('prefecture_id', $prefectureId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json($rows);
    }

    /**
     * 地図描画用ランドマーク一覧（landmarks.json 互換形式）。
     */
    public function index(): JsonResponse
    {
        $prefectureId = self::resolvePrefectureId();
        if ($prefectureId === null) {
            return response()->json(['points' => []]);
        }

        $points = Landmark::query()
            ->where('prefecture_id', $prefectureId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(static fn (Landmark $row): array => [
                'id' => $row->code,
                'label' => $row->label,
                'lat' => $row->lat,
                'lon' => $row->lon,
                'pattern' => $row->pattern,
            ])
            ->values()
            ->all();

        return response()->json(['points' => $points]);
    }

    public function create(Request $request): array|JsonResponse
    {
        $prefectureId = self::resolvePrefectureId();
        if ($prefectureId === null) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => ['prefecture' => ['都道府県が設定されていません。']],
            ], 422);
        }

        $inputs = $request->all();
        $validation = Validator::make($inputs, self::createValidationRules($prefectureId));
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        $sortOrder = isset($inputs['sort_order']) && $inputs['sort_order'] !== ''
            ? (int) $inputs['sort_order']
            : ((int) (Landmark::query()
                ->where('prefecture_id', $prefectureId)
                ->max('sort_order') ?? -1) + 1);

        Landmark::create([
            'prefecture_id' => $prefectureId,
            'code' => (string) $inputs['code'],
            'label' => (string) $inputs['label'],
            'lat' => (float) $inputs['lat'],
            'lon' => (float) $inputs['lon'],
            'pattern' => (string) $inputs['pattern'],
            'sort_order' => $sortOrder,
        ]);

        return ['result' => 'ok'];
    }

    public function edit(int $id, Request $request): array|JsonResponse
    {
        $prefectureId = self::resolvePrefectureId();
        if ($prefectureId === null) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => ['prefecture' => ['都道府県が設定されていません。']],
            ], 422);
        }

        $landmark = Landmark::query()
            ->where('prefecture_id', $prefectureId)
            ->where('id', $id)
            ->firstOrFail();

        $inputs = $request->all();
        if (isset($inputs['code']) && (string) $inputs['code'] !== $landmark->code) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => ['code' => ['コードは変更できません。']],
            ], 422);
        }

        $validation = Validator::make($inputs, self::$editRules);
        if ($validation->fails()) {
            return response()->json([
                'result' => 'validation_error',
                'errors' => $validation->errors(),
            ], 422);
        }

        $landmark->label = (string) $inputs['label'];
        $landmark->lat = (float) $inputs['lat'];
        $landmark->lon = (float) $inputs['lon'];
        $landmark->pattern = (string) $inputs['pattern'];
        if (isset($inputs['sort_order']) && $inputs['sort_order'] !== '') {
            $landmark->sort_order = (int) $inputs['sort_order'];
        }
        $landmark->save();

        return ['result' => 'ok'];
    }

    public function delete(int $id): array
    {
        $prefectureId = self::resolvePrefectureId();
        if ($prefectureId === null) {
            return ['result' => 'error', 'message' => 'prefecture not configured'];
        }

        $landmark = Landmark::query()
            ->where('prefecture_id', $prefectureId)
            ->where('id', $id)
            ->firstOrFail();

        $landmark->delete();

        return ['result' => 'ok'];
    }

    /**
     * @return array<string, mixed>
     */
    private static function createValidationRules(int $prefectureId): array
    {
        return array_merge(self::$editRules, [
            'code' => [
                'required',
                'string',
                'max:16',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('landmarks', 'code')->where(
                    static fn ($query) => $query->where('prefecture_id', $prefectureId)
                ),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function loadGeoref(?array $mapConfig): ?array
    {
        if ($mapConfig === null || ! isset($mapConfig['map_file_stem'])) {
            return null;
        }

        $path = public_path('maps/'.$mapConfig['map_file_stem'].'_georef.json');
        if (! is_file($path)) {
            return null;
        }

        $data = json_decode((string) file_get_contents($path), true);
        if (! is_array($data) || ! is_array($data['bounds'] ?? null)) {
            return null;
        }

        $bounds = $data['bounds'];
        foreach (['north', 'south', 'east', 'west'] as $key) {
            if (! isset($bounds[$key]) || ! is_numeric($bounds[$key])) {
                return null;
            }
        }

        return $data;
    }

    private static function resolvePrefectureId(): ?int
    {
        $raw = config('kaikon.PPREFECTURE');
        if ($raw === null || $raw === '') {
            return null;
        }

        $prefectureId = (int) $raw;
        if ($prefectureId < 1 || $prefectureId > 47) {
            return null;
        }

        return $prefectureId;
    }
}
