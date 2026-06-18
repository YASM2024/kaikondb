<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\Record;
use Kaikon2\Kaikondb\Models\Literature;
use Kaikon2\Kaikondb\Models\Photo;

class StatisticsController extends Controller
{
    public function records(Request $request): View
    {
        $this->ensureModerator();

        return view('kaikon::admin.statistics-records', [
            'pageTitle' => '分布記録 — 統計',
            'orderStats' => $this->orderStats(),
            'municipalityStats' => $this->municipalityStats(),
            'userRecordRank' => $this->userRecordRank(),
        ]);
    }

    public function literatures(Request $request): View
    {
        $this->ensureModerator();

        return view('kaikon::admin.statistics-literatures', [
            'pageTitle' => '文献 — 統計',
            'journalStats' => $this->journalStats(),
        ]);
    }

    public function photos(Request $request): View
    {
        $this->ensureModerator();

        return view('kaikon::admin.statistics-photos', [
            'pageTitle' => '写真 — 統計',
            'userPhotoRank' => $this->userPhotoRank(),
        ]);
    }

    public function specimens(Request $request): View
    {
        $this->ensureModerator();

        return view('kaikon::admin.statistics-specimens', [
            'pageTitle' => '標本 — 統計',
        ]);
    }

    private function orderStats(): Collection
    {
        return Record::query()
            ->join('speciess', 'records.species_id', '=', 'speciess.id')
            ->join('orders', 'speciess.order_id', '=', 'orders.id')
            ->select('orders.order_ja as label', DB::raw('COUNT(*) as count'))
            ->groupBy('orders.id', 'orders.order_ja')
            ->orderByDesc('count')
            ->limit(15)
            ->get();
    }

    private function journalStats(): Collection
    {
        return Literature::query()
            ->join('journals', 'literatures.journal_id', '=', 'journals.id')
            ->select('journals.journal_name_ja as label', DB::raw('COUNT(*) as count'))
            ->groupBy('journals.id', 'journals.journal_name_ja')
            ->orderByDesc('count')
            ->limit(15)
            ->get();
    }

    private function municipalityStats(): Collection
    {
        return Record::query()
            ->join('municipalities', 'records.municipality_id', '=', 'municipalities.id')
            ->select('municipalities.municipality_ja as label', DB::raw('COUNT(*) as count'))
            ->groupBy('municipalities.id', 'municipalities.municipality_ja')
            ->orderByDesc('count')
            ->limit(15)
            ->get();
    }

    private function userRecordRank(): Collection
    {
        return Record::query()
            ->join('users', 'records.user_id', '=', 'users.id')
            ->select('users.name as label', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
    }

    private function userPhotoRank(): Collection
    {
        return Photo::query()
            ->join('users', 'photos.user_id', '=', 'users.id')
            ->select('users.name as label', DB::raw('COUNT(*) as count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
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
