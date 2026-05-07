<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
//use Illuminate\Support\Facades\Session;
//use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

use Kaikon2\Kaikondb\Models\Specimen;
use Kaikon2\Kaikondb\Models\License;

class SpecimenController extends Controller
{
    //
    public function showSearchMenu()
    {
        return view('kaikon::static.specimens');
    }

    //
    public function index(Request $request)
    {
        $query = Specimen::query()
            ->leftJoin('licenses', 'specimens.license_id', '=', 'licenses.id')
            ->select([
                'specimens.id',
                'specimens.species',
                'specimens.species_ja',
                'specimens.locality',
                'specimens.collection_date_text',
                'specimens.collected_by',
                'specimens.identified_by',
                'specimens.image_1',
                'licenses.name as license_name',
            ]);

        $q = trim((string) $request->query('q', ''));
        if ($q !== '') {
            $escaped = addcslashes($q, "%_\\");
            $like = "%{$escaped}%";
            $query->where(function ($w) use ($like) {
                $w->where('species', 'like', $like)
                ->orWhere('species_ja', 'like', $like)
                ->orWhere('locality', 'like', $like);
            });
        }

        $locality = trim((string) $request->query('locality', ''));
        if ($locality !== '') {
            $escaped = addcslashes($locality, "%_\\");
            $query->where('locality', 'like', "%{$escaped}%");
        }

        $collectedBy = trim((string) $request->query('collected_by', ''));
        if ($collectedBy !== '') {
            $collectedBy = preg_replace('/\s+/', ' ', $collectedBy);
            $escaped = addcslashes($collectedBy, "%_\\");
            $query->where('collected_by', 'like', "%{$escaped}%");
        }

        $identifiedBy = trim((string) $request->query('identified_by', ''));
        if ($identifiedBy !== '') {
            $identifiedBy = preg_replace('/\s+/', ' ', $identifiedBy);
            $escaped = addcslashes($identifiedBy, "%_\\");
            $query->where('identified_by', 'like', "%{$escaped}%");
        }

        $owner = trim((string) $request->query('owner', ''));
        if ($owner !== '') {
            $owner = preg_replace('/\s+/', ' ', $owner);
            $escaped = addcslashes($owner, "%_\\");
            $query->where('owner', 'like', "%{$escaped}%");
        }

        $query->where('is_public', true);
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min($perPage, 50)); // 念のため上限。後ほど修正するかも…

        $p = $query->orderBy('id', 'desc')->paginate($perPage)->withQueryString();

        // PhotoController と同じ感じで「余計なURL系キー」を落として返すか検討
        $json = $p->toArray();
        foreach (['links','first_page_url','last_page_url','next_page_url','prev_page_url','path'] as $k) {
            unset($json[$k]);
        }

        return response()->json($json);
    }
    
    public function show($id)
    {
        $specimen = Specimen::with('license:id,name')->findOrFail($id);
        $data = $specimen->toArray();
        unset($data['license_id']);
        $data['license'] = $specimen->license?->name ?? null; // or '-'
        return response()->json($data);
    }

    /**
     * 標本新規登録フォーム（管理：Moderator以上）
     */
    public function showCreate(Request $request)
    {
        $licenses = License::query()->select('id', 'name')->orderBy('id')->get();
        return view('kaikon::specimens.create', [
            'licenses' => $licenses,
        ]);
    }

    /**
     * 標本登録（管理：Moderator以上）
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'locality' => 'nullable|string|max:255',
            'decimal_latitude' => 'nullable|numeric|between:-90,90',
            'decimal_longitude' => 'nullable|numeric|between:-180,180',
            'collection_date_text' => 'nullable|string|max:255',
            'collected_by' => 'nullable|string|max:100',
            'owner' => 'nullable|string|max:100',
            'species' => 'nullable|string|max:255',
            'species_ja' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:20',
            'identified_by' => 'nullable|string|max:100',
            'type_status' => 'nullable|string|max:50',
            'image_1' => 'nullable|string|max:255',
            'image_2' => 'nullable|string|max:255',
            'image_3' => 'nullable|string|max:255',
            'preservation_method' => 'nullable|string|max:50',
            'repository_institution' => 'nullable|string|max:150',
            'repository_catalog_number' => 'nullable|string|max:100',
            'remarks' => 'nullable|string',
            'is_public' => 'nullable|boolean',
            'license_id' => 'required|integer|exists:licenses,id',
        ]);

        $validated['user_id'] = Auth::id() ?? 1;
        $validated['is_public'] = (bool) ($request->boolean('is_public'));

        Specimen::create($validated);

        return redirect()
            ->route('specimen.create')
            ->with('status', '標本情報を登録しました。');
    }
    
}
