        <h4 class="mb-3">{{ $pageTitle }}</h4>

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
