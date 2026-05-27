<x-kaikon::app-layout>
    @slot('header')
        設定値一覧
    @endslot

    <div class="container py-2">
        <div class="bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">設定値一覧</h2>

            <div class="mb-4">
                <a href="{{ route('dashboard') }}">&larr; 管理者メニューへ戻る</a>
            </div>

            <p class="text-muted small mb-4">
                <code>config/kaikon.php</code> および <code>.env</code> に定義された設定値の一覧です。変更はファイルを直接編集してください。
            </p>

            @foreach ($sections as $sectionName => $rows)
                <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">{{ $sectionName }}</h5>
                <div class="table-responsive mb-4">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th class="d-none d-md-table-cell" style="width: 25%;">項目</th>
                                <th class="d-none d-md-table-cell" style="width: 20%;">キー</th>
                                <th class="d-md-none">項目（キー）</th>
                                <th>値</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="d-none d-md-table-cell fw-semibold">{{ $row['label'] }}</td>
                                    <td class="d-none d-md-table-cell"><code class="small">{{ $row['key'] }}</code></td>
                                    <td class="d-md-none">
                                        <span class="fw-semibold">{{ $row['label'] }}</span><br>
                                        <code class="small">（{{ $row['key'] }}）</code>
                                    </td>
                                    <td class="text-break" style="white-space: pre-line;">{{ $row['value'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
    </div>
</x-kaikon::app-layout>
