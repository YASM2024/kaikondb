<x-kaikon::app-layout :useChartjs="true">
    <x-slot:header>{{ $pageTitle }}</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">{{ $pageTitle }}</h4>

        <section class="mb-5">
            <h5 class="mb-3">分布記録 — 目別（上位15）</h5>
            @if ($orderStats->isEmpty())
                <p class="text-muted small">データがありません。</p>
            @else
                <div class="row g-3 align-items-start">
                    <div class="col-lg-6">
                        <canvas id="chartOrders" height="200"></canvas>
                    </div>
                    <div class="col-lg-6">
                        <table class="table table-sm table-bordered bg-white mb-0">
                            <thead class="table-light">
                                <tr><th>項目</th><th class="text-end">件数</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($orderStats as $row)
                                    <tr>
                                        <td>{{ $row->label }}</td>
                                        <td class="text-end">{{ number_format($row->count) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        <section class="mb-5">
            <h5 class="mb-3">分布記録 — 市町村別（上位15）</h5>
            @if ($municipalityStats->isEmpty())
                <p class="text-muted small">データがありません。</p>
            @else
                <div class="row g-3 align-items-start">
                    <div class="col-lg-6">
                        <canvas id="chartMunicipalities" height="200"></canvas>
                    </div>
                    <div class="col-lg-6">
                        <table class="table table-sm table-bordered bg-white mb-0">
                            <thead class="table-light">
                                <tr><th>項目</th><th class="text-end">件数</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($municipalityStats as $row)
                                    <tr>
                                        <td>{{ $row->label }}</td>
                                        <td class="text-end">{{ number_format($row->count) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        <section class="mb-5">
            <h5 class="mb-3">ユーザー — 登録件数（分布記録）</h5>
            @if ($userRecordRank->isEmpty())
                <p class="text-muted small">データがありません。</p>
            @else
                <table class="table table-sm table-bordered bg-white mt-2">
                    <thead class="table-light">
                        <tr><th>項目</th><th class="text-end">件数</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($userRecordRank as $row)
                            <tr>
                                <td>{{ $row->label }}</td>
                                <td class="text-end">{{ number_format($row->count) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </section>

        <p class="mt-4"><a href="{{ route('dashboard') }}">管理者メニューへ戻る</a></p>
    </div>

    <x-slot:scripts>
        <script>
            window.statisticsData = {
                charts: [
                    { canvasId: 'chartOrders', rows: @json($orderStats), label: '件数' },
                    { canvasId: 'chartMunicipalities', rows: @json($municipalityStats), label: '件数' },
                ],
            };
        </script>
        <script src="{{ url('/js/components/admin/statistics.js') }}"></script>
    </x-slot:scripts>
</x-kaikon::app-layout>
