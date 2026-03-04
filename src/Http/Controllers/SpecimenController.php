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
        $query = Specimen::query()->select([
            'id',
            'species',
            'species_ja',
            'locality',
            'collection_date_text',
            'collected_by',
            'identified_by',
            'image_1',
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

        // 採集地
        $locality = trim((string) $request->query('locality', ''));
        if ($locality !== '') {
            $escaped = addcslashes($locality, "%_\\");
            $like = "%{$escaped}%";
            $query->where('locality', 'like', $like);
        }

        // 採集者
        $collectedBy = trim((string) $request->query('collected_by', ''));
        if ($collectedBy !== '') {

        $collectedBy = preg_replace('/\s+/', ' ', $collectedBy);

            $escaped = addcslashes($collectedBy, "%_\\");
            $like = "%{$escaped}%";

            $query->where('collected_by', 'like', $like);
        }

        // 同定者
        $identifiedBy = trim((string) $request->query('identified_by', ''));
        if ($identifiedBy !== '') {

        $identifiedBy = preg_replace('/\s+/', ' ', $identifiedBy);

            $escaped = addcslashes($identifiedBy, "%_\\");
            $like = "%{$escaped}%";

            $query->where('identified_by', 'like', $like);
        }

        // 所蔵者
        $owner = trim((string) $request->query('owner', ''));
        if ($owner !== '') {
            $owner = preg_replace('/\s+/', ' ', $owner);

            $escaped = addcslashes($owner, "%_\\");
            $query->where('owner', 'like', "%{$escaped}%");
        }

        
        $specimens = $query->get();

        return response()->json($specimens);
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
