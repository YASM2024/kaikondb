<?php

namespace Kaikon2\Kaikondb\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
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

    private const PER_PAGE = 50;

    public function index(Request $request, string $type)
    {
        $this->ensureModerator();
        $this->ensureAllowedType($type);

        $days = $this->resolveDays($request);
        $entries = $this->buildEntriesQuery($type, $days)->paginate(self::PER_PAGE);

        return view('kaikon::admin.history', [
            'type' => $type,
            'typeLabel' => $this->typeLabels()[$type],
            'days' => $days,
            'entries' => $entries,
            'summaryColumn' => $this->summaryColumn($type),
            'allowedDays' => self::ALLOWED_DAYS,
            'allowedTypes' => self::ALLOWED_TYPES,
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function entries(Request $request, string $type)
    {
        $this->ensureModerator();
        $this->ensureAllowedType($type);

        $days = $this->resolveDays($request);
        $paginator = $this->buildEntriesQuery($type, $days)
            ->paginate(self::PER_PAGE, ['*'], 'page', (int) $request->query('page', 1));

        return response()->json([
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'data' => $paginator->getCollection()
                ->map(fn ($entry) => $this->formatEntry($entry, $type))
                ->values(),
        ]);
    }

    private function buildEntriesQuery(string $type, int $days): Builder
    {
        $since = now()->subDays($days);

        $modelClass = match ($type) {
            'records' => RecordHistory::class,
            'literatures' => LiteratureHistory::class,
            'specimens' => SpecimenHistory::class,
            'photos' => PhotoHistory::class,
        };

        $eagerLoad = ['savedByUser'];
        if ($type === 'records') {
            $eagerLoad[] = 'species';
        }

        return $modelClass::query()
            ->with($eagerLoad)
            ->where('recorded_at', '>=', $since)
            ->orderByDesc('recorded_at');
    }

    private function formatEntry(object $entry, string $type): array
    {
        $content = match ($type) {
            'records' => [
                'species_ja' => $entry->species?->species_ja,
                'species' => $entry->species?->species,
            ],
            default => [
                'text' => $entry->{$this->summaryColumn($type)},
            ],
        };

        return [
            'recorded_at' => $entry->recorded_at?->format('Y-m-d H:i'),
            'user_name' => $entry->savedByUser?->name,
            'action' => $entry->action,
            'content' => $content,
        ];
    }

    private function resolveDays(Request $request): int
    {
        $days = (int) $request->query('days', 7);

        return in_array($days, self::ALLOWED_DAYS, true) ? $days : 7;
    }

    private function ensureAllowedType(string $type): void
    {
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            abort(404);
        }
    }

    private function summaryColumn(string $type): ?string
    {
        return match ($type) {
            'records' => null,
            'literatures' => 'title',
            'specimens' => 'species_ja',
            'photos' => 'photo_title',
        };
    }

    private function typeLabels(): array
    {
        return [
            'records' => '分布記録',
            'literatures' => '文献',
            'specimens' => '標本',
            'photos' => '写真',
        ];
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
