<x-kaikon::app-layout>
    @slot('header')
        {{ __('messages.Specimens') }}
    @endslot

    @php
        // license 表示名（必要に応じて調整）
        $licenseLabels = [
            1 => 'CC BY',
            2 => 'CC BY-SA',
            3 => 'CC BY-NC',
            4 => 'CC BY-NC-SA',
            5 => 'CC0',
            6 => 'Public Domain',
            7 => 'All Rights Reserved',
        ];
    @endphp

    <style>
        .specimen-thumb {
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
            background: #f5f5f5;
        }
        .break-word { word-break: break-word; overflow-wrap: anywhere; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    </style>

    <div class="container mt-4 py-2">
        <h4 class="my-3 px-3 px-md-0">{{ __('messages.Specimens') }}</h4>

        <noscript>
            <div class="container pt-3 py-2">
                <p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p>
            </div>
        </noscript>

        <div class="container pt-3 py-2">
            県内で採集された標本の情報を検索できます。
        </div>

        {{-- 検索フォーム（GET） --}}
        <form id="specimenSearchForm" class="card p-3 mb-3" method="get" action="">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label mb-1" for="keyword">学名 / 和名</label>
                    <input id="keyword" type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="例: Papilio / アゲハ">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label mb-1" for="locality">採集地</label>
                    <input id="locality" type="text" name="locality" class="form-control" value="{{ request('locality') }}" placeholder="例: 高尾山 / 八王子">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label mb-1" for="date">採集日（テキスト）</label>
                    <input id="date" type="text" name="date" class="form-control" value="{{ request('date') }}" placeholder="例: 2025 / 2025-06 / 2025-06-14 / 期間">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label mb-1" for="collectedBy">採集者</label>
                    <input id="collectedBy" type="text" name="collected_by" class="form-control" value="{{ request('collected_by') }}" placeholder="例: T. Sato">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label mb-1" for="identifiedBy">同定者</label>
                    <input id="identifiedBy" type="text" name="identified_by" class="form-control" value="{{ request('identified_by') }}" placeholder="例: K. Tanaka">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label mb-1" for="owner">所蔵</label>
                    <input id="owner" type="text" name="owner" class="form-control" value="" placeholder="例: Private Collection">
                </div>

                <div class="col-12 col-md-3 d-flex gap-2">
                    <button id="searchBtn" class="btn btn-secondary w-100" type="submit">検　　索</button>
                    <a id="cancelBtn" class="btn btn-outline-secondary w-100" href="">取り消し</a>
                </div>
            </div>
        </form>

        {{-- 件数表示 --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div id="resultCount" class="text-muted small"></div>
        </div>

        {{-- 一覧（カード） --}}
        <div id="app"></div>

        {{-- ページネーション --}}
        <div id="pagination" class="mt-4"></div>
    </div>

    @slot('modal')
        {{-- 詳細モーダル（1つだけ） --}}
        <div class="modal fade" id="ModalSpecimenDetail" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <span id="m-title">Specimen</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                    </div>

                    <div class="modal-body">
                        {{-- 画像 --}}
                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-4"><img id="m-img1" class="img-fluid rounded border d-none" alt=""></div>
                            <div class="col-12 col-md-4"><img id="m-img2" class="img-fluid rounded border d-none" alt=""></div>
                            <div class="col-12 col-md-4"><img id="m-img3" class="img-fluid rounded border d-none" alt=""></div>
                        </div>

                        <table class="table table-sm">
                            <tbody>
                                <tr><th style="width: 10rem;">和名</th><td id="m-species-ja" class="break-word"></td></tr>
                                <tr><th>学名</th><td id="m-species" class="break-word fst-italic"></td></tr>
                                <tr><th>性</th><td id="m-sex"></td></tr>

                                <tr><th>採集地</th><td id="m-locality" class="break-word"></td></tr>
                                <tr><th>採集日</th><td id="m-date" class="break-word"></td></tr>
                                <tr><th>採集者</th><td id="m-collected-by" class="break-word"></td></tr>

                                <tr><th>同定者</th><td id="m-identified-by" class="break-word"></td></tr>
                                <tr><th>所蔵</th><td id="m-owner" class="break-word"></td></tr>

                                <tr><th>Type status</th><td id="m-type-status" class="break-word"></td></tr>

                                <tr>
                                    <th>座標</th>
                                    <td class="break-word">
                                        <span class="mono" id="m-coord"></span>
                                        <span id="m-map"></span>
                                    </td>
                                </tr>

                                <tr><th>保存方法</th><td id="m-preservation" class="break-word"></td></tr>

                                <tr><th>収蔵機関</th><td id="m-repo" class="break-word"></td></tr>
                                <tr><th>収蔵番号</th><td id="m-catalog" class="break-word"></td></tr>

                                <tr><th>License</th><td id="m-license"></td></tr>
                                <tr><th>備考</th><td id="m-remarks" class="break-word"></td></tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    @endslot

    @slot('scripts')
        <script type="module" src="/database/js/components/specimen/index.js"></script>
    @endslot
</x-kaikon::app-layout>
