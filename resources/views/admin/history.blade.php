<x-kaikon::app-layout>
    <x-slot:header>履歴 — {{ $typeLabel }}</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-3">履歴（{{ $typeLabel }}）</h4>

        <ul class="nav nav-pills mb-3 flex-wrap gap-1">
            @foreach ($allowedTypes as $t)
                <li class="nav-item">
                    <a class="nav-link {{ $t === $type ? 'active' : '' }}"
                       href="{{ route('admin.history', ['type' => $t, 'days' => $days]) }}">
                        {{ $typeLabels[$t] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <form method="get" class="mb-3 d-flex flex-wrap align-items-center gap-2">
            <label class="form-label mb-0" for="days">期間</label>
            <select name="days" id="days" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                @foreach ($allowedDays as $d)
                    <option value="{{ $d }}" @selected($days === $d)>過去 {{ $d }} 日</option>
                @endforeach
            </select>
        </form>

        @if ($entries->isEmpty())
            <p class="text-muted">該当する履歴はありません。</p>
        @else
            <div class="table-responsive bg-white border rounded">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>日時</th>
                            <th>ユーザー</th>
                            <th>操作</th>
                            <th>内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($entries as $entry)
                            <tr>
                                <td class="text-nowrap">{{ $entry->recorded_at?->format('Y-m-d H:i') }}</td>
                                <td>{{ $entry->savedByUser?->name ?? '—' }}</td>
                                <td>{{ $entry->action }}</td>
                                <td class="text-break">
                                    @if ($type === 'records')
                                        {{ $entry->species?->species_ja ?? '—' }}
                                        @if ($entry->species?->species)
                                            <span class="text-muted fst-italic ms-1">{{ $entry->species->species }}</span>
                                        @endif
                                    @else
                                        {{ $entry->{$summaryColumn} ?? '—' }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-3">{{ $entries->links() }}</div>
        @endif

        <p class="mt-3">
            <a href="{{ route('dashboard') }}">管理者メニューへ戻る</a>
        </p>
    </div>
</x-kaikon::app-layout>
