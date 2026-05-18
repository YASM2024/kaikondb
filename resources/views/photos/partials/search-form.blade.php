<form id="searchPhotos" class="mb-3" name="search" method="get" action="">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-8">
            <label class="form-label mb-1" for="keyword">キーワード</label>
            <input id="keyword" name="keyword" type="text" class="form-control" placeholder="例: Papilio / アゲハ">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label mb-1" for="place">採集地</label>
            <input id="place" type="text" name="place" class="form-control" placeholder="例: 市区町村名">
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label mb-1" for="date">採集日（テキスト）</label>
            <input id="date" type="text" name="date" class="form-control" placeholder="例: 2025 / 2025-06 / 2025-06-14 / 期間">
        </div>

        <div class="col-12 col-md-4">
            <label for="user_id_selectbox" class="form-label mb-1">投稿者（撮影者）</label>
            <select id="user_id_selectbox" name="user_id" class="form-select">
                <option value="" selected>全員</option>
                @foreach ($photographers as $photographer)
                    <option value="{{ $photographer->user_id }}" @selected($photographer->user_id == $data['user_id'])>
                        {{ $photographer->show_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-4 d-flex gap-2">
            <button type="submit" id="searchBtn" class="btn btn-secondary w-100">検索</button>
            <button type="button" id="cancelBtn" class="btn btn-outline-secondary w-100">クリア</button>
        </div>

        <div class="col-12">
            <div id="activeFilters" class="mt-2"></div>
            <div class="small text-body-secondary mt-1" id="resultMeta"></div>
        </div>
    </div>
</form>
