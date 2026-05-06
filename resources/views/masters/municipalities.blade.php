<x-kaikon::app-layout>
  @slot('additionalStyles')
  <link rel="stylesheet" href="{{ url('/css/masters.css') }}">
  @endslot

  @slot('header')
  市町村マスタ
  @endslot

  <div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <div>
        <h1 class="h3 mb-1">市町村マスタ</h1>
      </div>

      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" id="reloadButton">
          再読込
        </button>
        <button type="button" class="btn btn-primary" id="addMunicipalityButton">
          市町村を追加
        </button>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-12 col-md-9">
            <label for="keywordInput" class="form-label">市町村名 / コードで検索</label>
            <input
              id="keywordInput"
              type="text"
              class="form-control"
              placeholder="例: 192015 / 甲府市 / Kofu-city"
            />
          </div>

          <div class="col-12 col-md-3">
            <label for="statusFilter" class="form-label">表示条件</label>
            <select id="statusFilter" class="form-select">
              <option value="all">すべて表示</option>
              <option value="1">有効のみ</option>
              <option value="0">無効のみ</option>
            </select>
          </div>

          <div class="col-12 col-md-3">
            <button type="button" class="btn btn-outline-primary w-100" id="searchButton">
              検索
            </button>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0">
      <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
          <h2 class="h5 mb-1">市町村マスタ</h2>
          <div class="text-secondary small">登録済みの市町村一覧</div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
          <button
            type="button"
            class="btn btn-outline-success btn-sm"
            id="csvDownloadButton"
            disabled
          >
            CSVダウンロード
          </button>

          <label
            for="csvImportInput"
            class="btn btn-outline-primary btn-sm mb-0"
            id="csvImportButton"
          >
            CSV取込
          </label>

          <input
            type="file"
            id="csvImportInput"
            accept=".csv,text/csv"
            class="d-none"
          >

          <span class="badge text-bg-primary rounded-pill" id="countPill">
            0 records
          </span>
        </div>
      </div>

      <div class="card-body p-0">
        <div id="loadingBox" class="p-3 text-secondary">読み込み中...</div>
        <div id="errorBox" class="alert alert-danger rounded-0 border-0 m-0 d-none"></div>
        <div id="emptyBox" class="p-3 text-secondary d-none">表示できる市町村がありません。</div>

        <div id="tableWrap" class="table-responsive d-none">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                  <input
                    type="checkbox"
                    id="selectAllMunicipalities"
                    class="form-check-input mt-0"
                    aria-label="表示中の市町村をすべて選択"
                  >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 110px;">code</th>
                <th class="px-3 py-3">市町村名</th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 130px;">actions</th>
              </tr>
            </thead>
            <tbody id="municipalityTableBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="municipalityCreateAndEditModal" tabindex="-1" aria-labelledby="municipalityCreateAndEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="municipalityCreateAndEditForm">
          <div class="modal-header">
            <h5 class="modal-title" id="municipalityCreateAndEditModalLabel">市町村の編集</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
          </div>

          <div class="modal-body">
            <div class="alert alert-danger d-none" id="municipalityEditErrorBox"></div>

            <input type="hidden" id="editMunicipalityId" name="id" value="">

            <div class="row g-3">
              <div class="col-12">
                <label for="editMunicipalityCode" class="form-label mb-1">市町村コード</label>
                <input
                  type="text"
                  class="form-control"
                  id="editMunicipalityCode"
                  name="municipality_code"
                  autocomplete="off"
                >
              </div>

              <div class="col-12">
                <label for="editMunicipalityJa" class="form-label mb-1">市町村名（和）</label>
                <input
                  type="text"
                  class="form-control"
                  id="editMunicipalityJa"
                  name="municipality_ja"
                  autocomplete="off"
                >
              </div>

              <div class="col-12">
                <label for="editMunicipalityEn" class="form-label mb-1">市町村名（英）</label>
                <input
                  type="text"
                  class="form-control"
                  id="editMunicipalityEn"
                  name="municipality_en"
                  autocomplete="off"
                >
              </div>

              <div class="col-12">
                <label for="editMunicipalityStatus" class="form-label mb-1">ステータス</label>
                <select class="form-select" id="editMunicipalityStatus" name="status">
                  <option value="1">有効</option>
                  <option value="0">無効</option>
                </select>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
              キャンセル
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="saveMunicipalityButton">
              保存
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  @slot('scripts')
  <script
    src="{{ url('/js/components/masters/municipality.js') }}?v={{ @filemtime(public_path('js/components/masters/municipality.js')) }}"
    type="module"
  ></script>
  @endslot

</x-kaikon::app-layout>