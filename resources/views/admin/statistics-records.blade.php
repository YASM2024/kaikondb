<x-kaikon::app-layout :useChartjs="true">
    <x-slot:header>{{ $pageTitle }}</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">{{ $pageTitle }}</h4>

        @include('kaikon::admin.partials.stat-section', [
            'title' => '分布記録 — 目別（上位15）',
            'canvasId' => 'chartOrders',
            'rows' => $orderStats,
        ])

        @include('kaikon::admin.partials.stat-section', [
            'title' => '分布記録 — 市町村別（上位15）',
            'canvasId' => 'chartMunicipalities',
            'rows' => $municipalityStats,
        ])

        @include('kaikon::admin.partials.stat-section', [
            'title' => 'ユーザー — 登録件数（分布記録）',
            'canvasId' => null,
            'rows' => $userRecordRank,
        ])

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
