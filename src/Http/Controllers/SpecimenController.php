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
    
}
