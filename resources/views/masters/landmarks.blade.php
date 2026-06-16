<x-kaikon::masters.page
    header="登録地点マスタ"
    script="/js/components/masters/landmark.js"
    :script-version="filemtime(public_path('js/components/masters/landmark.js'))"
>
    <div class="container py-4">
        <x-kaikon::masters.toolbar
            title="登録地点マスタ"
            add-button-id="addLandmarkButton"
            add-button-label="地点を追加"
        />

        @if ($georefBounds)
            <div class="alert alert-info border-0 shadow-sm mb-3" role="status">
                <div class="fw-semibold mb-1">地図表示範囲（参考）</div>
                <div class="small mb-2">
                    緯度 {{ number_format($georefBounds['south'], 6) }} 〜 {{ number_format($georefBounds['north'], 6) }} /
                    経度 {{ number_format($georefBounds['west'], 6) }} 〜 {{ number_format($georefBounds['east'], 6) }}
                </div>
                <div class="small text-body-secondary mb-0">
                    登録地点が地図から大きく外れないよう、上記の範囲内で入力してください。
                    編集モーダルで地図上の位置を確認できます。
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mb-3" role="status">
                地図の表示範囲情報（georef）が取得できません。緯度・経度は慎重に入力してください。
            </div>
        @endif

        <x-kaikon::masters.search-card
            keyword-label="地点名 / コードで検索"
            keyword-placeholder="例: 富士山 / lm_fuji"
            :show-status-filter="false"
            keyword-col="col-12 col-md-9"
            search-col="col-12 col-md-3"
        />

        <x-kaikon::masters.list-card
            list-title="登録地点一覧"
            subtitle="地図に表示するランドマーク"
            empty-message="表示できる地点がありません。"
            tbody-id="landmarkTableBody"
            count-pill-initial="0 records"
        >
            <x-slot:head>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                        type="checkbox"
                        id="selectAllLandmarks"
                        class="form-check-input mt-0"
                        aria-label="表示中の地点をすべて選択"
                    >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 120px;">code</th>
                <th class="px-3 py-3">地点名</th>
                <th class="text-nowrap px-3 py-3" style="width: 180px;">緯度 / 経度</th>
                <th class="text-nowrap px-3 py-3 text-center" style="width: 80px;">アイコン</th>
                <th class="text-nowrap px-3 py-3" style="width: 80px;">表示順</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 90px;">actions</th>
            </x-slot:head>
        </x-kaikon::masters.list-card>

        <script>
            window.landmarkMapConfig = @json($mapConfig);
            window.landmarkGeoref = @json($georef);
            window.landmarkGeorefBounds = @json($georefBounds);
            window.landmarkPrefectureLabel = @json($mapConfig['prefecture_ja'] ?? null);
        </script>
    </div>

    @php
        $landmarkMapAspect = 1;
        if (!empty($georef['svg']['width']) && !empty($georef['svg']['height'])) {
            $landmarkMapAspect = round($georef['svg']['width'] / $georef['svg']['height'], 4);
        }
    @endphp

    @include('kaikon::masters.modals.landmark-form', ['landmarkMapAspect' => $landmarkMapAspect])
</x-kaikon::masters.page>
