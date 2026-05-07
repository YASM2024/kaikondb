<x-kaikon::app-layout>
    @slot('header')
        システム起動状況
    @endslot

    <div class="container py-2">
        <div class="bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">システム起動状況</h2>

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">← 管理者メニューへ戻る</a>
            </div>

            <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">ジョブ</h5>
            <p class="text-muted small mb-2">
                Laravel のキュー設定：<code>{{ $queueDefault ?? '' }}</code>
                （driver: <code>{{ $queueDriver ?? '' }}</code>）
                — driver が <code>sync</code> のときは <code>jobs</code> テーブルに行は増えません（同一リクエスト内で処理されます）。
            </p>

            <details class="mb-3">
                <summary class="fw-semibold">ログイン通知の実行トレース（直近30行）</summary>
                <p class="text-muted small mb-1 mt-2">
                    ファイル：<code>storage/logs/kaikondb-login-trace.log</code>
                    （パッケージの <code>AuthenticatedSessionController@store</code> が実行されたときだけ増えます）
                </p>
                @if(isset($loginTraceTail) && count($loginTraceTail) > 0)
                <pre class="small bg-white border rounded p-2 mb-0" style="max-height: 240px; overflow: auto;">@foreach($loginTraceTail as $line){{ $line }}

@endforeach</pre>
                @else
                <p class="small text-muted mb-0">記録はまだありません。ログインしても増えない場合、POST <code>login</code> が別のコントローラに割り当てられている可能性があります。</p>
                @endif
            </details>

            <div class="table-responsive mb-4">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th class="width: 20%;">項目</th>
                            <th class="width: 50%;">説明</th>
                            <th class="width: 10%;">状態</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($statuses['jobs'] ?? []) as $row)
                            <tr title="{{ $row['key'] ?? '' }}">
                                <td class="fw-semibold">{{ $row['name'] ?? '' }}</td>
                                <td>{{ $row['description'] ?? '' }}</td>
                                <td>
                                    @if(($row['enabled'] ?? false) === true)
                                        <span class="badge bg-success">ON</span>
                                    @else
                                        <span class="badge bg-secondary">OFF</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">リスナー</h5>
            <div class="table-responsive">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 20%;">項目</th>
                            <th style="width: 50%;">説明</th>
                            <th style="width: 10%;">状態</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($statuses['listeners'] ?? []) as $row)
                            <tr title="{{ $row['key'] ?? '' }}">
                                <td class="fw-semibold">{{ $row['name'] ?? '' }}</td>
                                <td>{{ $row['description'] ?? '' }}</td>
                                <td>
                                    @if(($row['enabled'] ?? false) === true)
                                        <span class="badge bg-success">ON</span>
                                    @else
                                        <span class="badge bg-secondary">OFF</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-kaikon::app-layout>

