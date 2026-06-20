<x-kaikon::app-layout :useChartjs="true">
    <x-slot:header>{{ $pageTitle }}</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">{{ $pageTitle }}</h4>

        <section class="mb-5">
            <h5 class="mb-3">ユーザー — 投稿数（写真）</h5>
            @if ($userPhotoRank->isEmpty())
                <p class="text-muted small">データがありません。</p>
            @else
                <div class="row g-3 align-items-start">
                    <div class="col-lg-6">
                        <canvas id="chartUserPhotos" height="200"></canvas>
                    </div>
                    <div class="col-lg-6">
                        <table class="table table-sm table-bordered bg-white mb-0">
                            <thead class="table-light">
                                <tr><th>項目</th><th class="text-end">件数</th></tr>
                            </thead>
                            <tbody>
                                @foreach ($userPhotoRank as $row)
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

        <p class="mt-4"><a href="{{ route('dashboard') }}">管理者メニューへ戻る</a></p>
    </div>

    <x-slot:scripts>
        <script>
            window.statisticsData = {
                charts: [
                    { canvasId: 'chartUserPhotos', rows: @json($userPhotoRank), label: '件数' },
                ],
            };
        </script>
        <script src="{{ url('/js/components/admin/statistics.js') }}"></script>
    </x-slot:scripts>
</x-kaikon::app-layout>
