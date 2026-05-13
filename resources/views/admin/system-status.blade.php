<x-kaikon::app-layout>
    @php
        $hasDrainNow = \Illuminate\Support\Facades\Route::has('admin.system.status.queue_worker.drain_now');
    @endphp
    @slot('header')
        システム起動状況
    @endslot

    <div class="container py-2">
        <div class="bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">システム起動状況</h2>

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">← 管理者メニューへ戻る</a>
            </div>

            @if(session('status'))
                <div class="alert alert-info py-2 small mb-3">
                    状態：<code>{{ session('status') }}</code>
                </div>
            @endif

            <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">メール送信</h5>
            <div class="table-responsive mb-4">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 20%;">項目</th>
                            <th style="width: 50%;">説明</th>
                            <th style="width: 10%;">状態</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($statuses['email_sending'] ?? []) as $row)
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

            <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">ジョブ</h5>
            <div class="table-responsive mb-3">
                <table class="table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width: 20%;">項目</th>
                            <th style="width: 50%;">説明</th>
                            <th style="width: 10%;">状態</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($statuses['jobs'] ?? []) as $row)
                            <tr title="{{ $row['key'] ?? '' }}"
                                @if(!empty($row['dimmed']))
                                    class="text-muted bg-light bg-opacity-50"
                                    style="opacity: 0.65;"
                                @endif
                            >
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

            @if(($emailQueueConfigured ?? false) === true && ($queueDriver ?? '') !== 'sync')
                @if($hasDrainNow)
                    <div class="d-flex flex-wrap gap-2 align-items-center mb-4">
                        <form method="POST" action="{{ route('admin.system.status.queue_worker.drain_now') }}" class="mb-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">
                                今すぐ実行（最大20秒）
                            </button>
                        </form>
                    </div>
                @endif
            @elseif(($emailQueueConfigured ?? false) === true && ($queueDriver ?? '') === 'sync')
                <p class="text-muted small mb-4">キュー driver が <code>sync</code> のため、メール送信ワーカーの起動は不要です。</p>
            @else
                <p class="text-muted small mb-4">遅延送信が OFF のため、メール送信ワーカーの操作はありません。</p>
            @endif

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
