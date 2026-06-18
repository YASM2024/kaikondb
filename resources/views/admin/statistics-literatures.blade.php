<x-kaikon::app-layout :useChartjs="true">
    <x-slot:header>{{ $pageTitle }}</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">{{ $pageTitle }}</h4>

        @include('kaikon::admin.partials.stat-section', [
            'title' => '文献 — 雑誌別（上位15）',
            'canvasId' => 'chartJournals',
            'rows' => $journalStats,
        ])

        <p class="mt-4"><a href="{{ route('dashboard') }}">管理者メニューへ戻る</a></p>
    </div>

    <x-slot:scripts>
        <script>
            window.statisticsData = {
                charts: [
                    { canvasId: 'chartJournals', rows: @json($journalStats), label: '件数' },
                ],
            };
        </script>
        <script src="{{ url('/js/components/admin/statistics.js') }}"></script>
    </x-slot:scripts>
</x-kaikon::app-layout>
