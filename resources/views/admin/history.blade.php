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
                    <tbody id="history_entries_body">
                        @include('kaikon::admin.partials.history-rows', [
                            'entries' => $entries,
                            'type' => $type,
                            'summaryColumn' => $summaryColumn,
                        ])
                    </tbody>
                </table>
            </div>
            <div id="number_of_show" class="mt-3 text-muted small"></div>
            <div id="next_page_loader"></div>
        @endif

        <p class="mt-3">
            <a href="{{ route('dashboard') }}">管理者メニューへ戻る</a>
        </p>
    </div>

    @if (!$entries->isEmpty())
        <x-slot:scripts>
            <script src="{{ url('/js/paginationMessage.js') }}"></script>
            <script src="{{ url('/js/nextPageLoader.js') }}"></script>
            <script>
                window.adminHistory = {
                    type: @json($type),
                    days: {{ $days }},
                    entriesUrl: @json(route('admin.history.entries', ['type' => $type])),
                    pagination: {
                        current_page: {{ $entries->currentPage() }},
                        last_page: {{ $entries->lastPage() }},
                        per_page: {{ $entries->perPage() }},
                        total: {{ $entries->total() }},
                    },
                };
            </script>
            <script src="{{ url('/js/components/admin/history.js') }}"></script>
        </x-slot:scripts>
    @endif
</x-kaikon::app-layout>
