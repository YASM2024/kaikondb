<x-kaikon::app-layout>
    @slot('additionalStyles')
    <link rel="stylesheet" href="{{ url('/css/masters.css') }}">
    @endslot

    @slot('header')
    分類マスタ - Familyマスタ
    @endslot

    <div class="container py-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="h3 mb-1">Familyマスタ</h1>
          <p class="text-secondary mb-0">
            初期表示は Families のみ。order_id を付けて取得します。
          </p>
        </div>

        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" id="reloadButton">
            再読込
          </button>
          <button type="button" class="btn btn-primary" id="addFamilyButton">
            Familyを追加
          </button>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
              <label for="keywordInput" class="form-label">Family名で検索</label>
              <input
                id="keywordInput"
                type="text"
                class="form-control"
                placeholder="例: カマアシムシ科 / Acerentomidae / 010"
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
            <h2 class="h5 mb-1">Familyマスタ</h2>
            <div class="text-secondary small" id="currentOrderLabel">
              読み込み中... >> 登録済みの Families 一覧
            </div>
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

            <button
              type="button"
              class="btn btn-outline-primary btn-sm"
              id="csvImportButton"
            >
              CSV取込
            </button>

            <input
              type="file"
              id="csvImportInput"
              accept=".csv,text/csv"
              class="d-none"
            >

            <span class="badge text-bg-primary rounded-pill" id="countPill">
              0 records / 無効 0件
            </span>
          </div>
        </div>

        <div class="card-body p-0">
          <div id="loadingBox" class="p-3 text-secondary">読み込み中...</div>

          <div id="errorBox" class="alert alert-danger rounded-0 border-0 m-0 d-none"></div>

          <div id="emptyBox" class="p-3 text-secondary d-none">
            表示できる Family がありません。
          </div>

          <div id="tableWrap" class="table-responsive d-none">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                      type="checkbox"
                      id="selectAllFamilies"
                      class="form-check-input mt-0"
                      aria-label="表示中のFamilyをすべて選択"
                    >
                  </th>
                  <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
                  <th class="px-3 py-3">family</th>
                  <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                  <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
                </tr>
              </thead>
              <tbody id="familiesTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="familyCreateAndEditModal" tabindex="-1" aria-labelledby="familyCreateAndEditModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <form id="familyCreateAndEditForm">
            <div class="modal-header">
              <h5 class="modal-title" id="familyCreateAndEditModalLabel">Familyを編集</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>

            <div class="modal-body">
              <div class="alert alert-danger d-none" id="familyEditErrorBox"></div>

              <div class="compactMeta">
                <div class="row align-items-center">
                  <div class="d-none">
                    <div class="row align-items-center">
                      <div class="col-8">
                        <input type="text" class="form-control form-control-sm" id="editFamilyId" name="id" disabled>
                      </div>
                    </div>
                  </div>

                  <div class="col-12 col-md-6">
                    <div class="row align-items-center">
                      <label for="editOrderId" class="col-2 col-form-label">order</label>
                      <div class="col-10">
                        <select class="form-select form-select-sm" id="editOrderId" name="order_id">
                          <option value="">選択してください</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row compactRow">
                <div class="col-12 col-md-6 compactBlock">
                  <label for="editFamilyLatin" class="form-label mb-1">family</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editFamilyLatin"
                    name="family"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editFamilyJa" class="form-label mb-1">family_ja</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editFamilyJa"
                    name="family_ja"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editFamilyCode" class="form-label mb-1">code</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editFamilyCode"
                    name="code"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editFamilyStatus" class="form-label mb-1">status</label>
                  <select
                    class="form-select"
                    id="editFamilyStatus"
                    name="status"
                  >
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
              <button type="button" class="btn btn-primary btn-sm" id="saveFamilyButton">
                保存
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    @slot('scripts')
    <script src="{{ url('/js/components/masters/family.js') }}" type="module"></script>
    @endslot
</x-kaikon::app-layout>