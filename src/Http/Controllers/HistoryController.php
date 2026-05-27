<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kaikon2\Kaikondb\Models\User;
use Kaikon2\Kaikondb\Models\RecordHistory;
use Kaikon2\Kaikondb\Models\LiteratureHistory;
use Kaikon2\Kaikondb\Models\SpecimenHistory;
use Kaikon2\Kaikondb\Models\PhotoHistory;

class HistoryController extends Controller
{
    private const ALLOWED_TYPES = ['records', 'literatures', 'specimens', 'photos'];

    private const ALLOWED_DAYS = [1, 3, 7, 30];

    public function index(Request $request, string $type)
    {
        $this->ensureModerator();

        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            abort(404);
        }

        $days = (int) $request->query('days', 7);
        if (!in_array($days, self::ALLOWED_DAYS, true)) {
            $days = 7;
        }

        $since = now()->subDays($days);

        [$modelClass, $summaryColumn] = match ($type) {
            'records' => [RecordHistory::class, null],
            'literatures' => [LiteratureHistory::class, 'title'],
            'specimens' => [SpecimenHistory::class, 'species_ja'],
            'photos' => [PhotoHistory::class, 'photo_title'],
        };

        $eagerLoad = ['savedByUser'];
        if ($type === 'records') {
            $eagerLoad[] = 'species';
        }

        $entries = $modelClass::query()
            ->with($eagerLoad)
            ->where('recorded_at', '>=', $since)
            ->orderByDesc('recorded_at')
            ->paginate(50)
            ->withQueryString();

        $typeLabels = [
            'records' => '分布記録',
            'literatures' => '文献',
            'specimens' => '標本',
            'photos' => '写真',
        ];

        return view('kaikon::admin.history', [
            'type' => $type,
            'typeLabel' => $typeLabels[$type],
            'days' => $days,
            'entries' => $entries,
            'summaryColumn' => $summaryColumn,
            'allowedDays' => self::ALLOWED_DAYS,
            'allowedTypes' => self::ALLOWED_TYPES,
            'typeLabels' => $typeLabels,
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
