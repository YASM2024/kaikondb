<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Literature;
use Kaikon2\Kaikondb\Models\Photo;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        $this->ensureModerator();

        $orderStats = Record::query()
            ->join('speciess', 'records.species_id', '=', 'speciess.id')
            ->join('orders', 'speciess.order_id', '=', 'orders.id')
            ->select('orders.order_ja as label', DB::raw('COUNT(*) as count'))
            ->groupBy('orders.id', 'orders.order_ja')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        $journalStats = Literature::query()
            ->join('journals', 'literatures.journal_id', '=', 'journals.id')
            ->select('journals.journal_name_ja as label', DB::raw('COUNT(*) as count'))
            ->groupBy('journals.id', 'journals.journal_name_ja')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        $municipalityStats = Record::query()
            ->join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
            ->select('municipalities.municipality_ja as label', DB::raw('COUNT(*) as count'))
            ->groupBy('municipalities.id', 'municipalities.municipality_ja')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        $userRecordRank = Record::query()
            ->join('users', 'records.user_id', '=', 'users.id')
            ->select('users.name as label', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $userPhotoRank = Photo::query()
            ->join('users', 'photos.user_id', '=', 'users.id')
            ->select('users.name as label', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return view('kaikon::admin.statistics', [
            'orderStats' => $orderStats,
            'journalStats' => $journalStats,
            'municipalityStats' => $municipalityStats,
            'userRecordRank' => $userRecordRank,
            'userPhotoRank' => $userPhotoRank,
        ]);
    }

    private function ensureModerator(): User
    {
        if (!Auth::check()) {
            abort(403);
        }

        $user = User::fromAppUser(Auth::user());
        if (!$user->isAdmin() && !$user->isModerator()) {
            abort(403);
        }

        return $user;
    }
}
