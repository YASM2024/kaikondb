<x-kaikon::app-layout :useChartjs="true">
    <x-slot:header>統計</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">統計ダッシュボード</h4>

        <div class="row g-4">
            <div class="col-lg-6">
                <h5>分布記録 — 目別（上位15）</h5>
                <canvas id="chartOrders" height="200"></canvas>
                @include('kaikon::admin.partials.stat-table', ['rows' => $orderStats])
            </div>
            <div class="col-lg-6">
                <h5>文献 — 雑誌別（上位15）</h5>
                <canvas id="chartJournals" height="200"></canvas>
                @include('kaikon::admin.partials.stat-table', ['rows' => $journalStats])
            </div>
            <div class="col-lg-6">
                <h5>分布記録 — 市町村別（上位15）</h5>
                <canvas id="chartMunicipalities" height="200"></canvas>
                @include('kaikon::admin.partials.stat-table', ['rows' => $municipalityStats])
            </div>
            <div class="col-lg-6">
                <h5>ユーザー — 登録件数（分布記録）</h5>
                @include('kaikon::admin.partials.stat-table', ['rows' => $userRecordRank])
                <h5 class="mt-4">ユーザー — 投稿数（写真）</h5>
                @include('kaikon::admin.partials.stat-table', ['rows' => $userPhotoRank])
            </div>
        </div>

        <p class="mt-4"><a href="{{ route('dashboard') }}">管理者メニューへ戻る</a></p>
    </div>

    <x-slot:scripts>
        <script>
            window.statisticsData = {
                orders: @json($orderStats),
                journals: @json($journalStats),
                municipalities: @json($municipalityStats),
            };
        </script>
        <script type="module" src="{{ url('/js/components/admin/statistics.js') }}"></script>
    </x-slot:scripts>
</x-kaikon::app-layout>
