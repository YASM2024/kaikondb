<x-kaikon::app-layout>
    @slot('additionalStyles')
    <link rel="stylesheet" href="{{ url('/css/masters.css') }}">
    @endslot

    @slot('header')
    雑誌情報マスタ
    @endslot

    <style>
      /* code列が隠れる mobile breakpoint に合わせて非表示 */
      @media (max-width: 575.98px) {
        .journal-col-urlprov {
          display: none !important;
        }
      }
    </style>

    <div class="container py-4">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
          <h1 class="h3 mb-1">雑誌情報マスタ</h1>
        </div>

        <div class="d-flex gap-2">
          <button type="button" class="btn btn-outline-secondary" id="reloadButton">
            再読込
          </button>
          <button type="button" class="btn btn-primary" id="addJournalButton">
            雑誌を追加
          </button>
        </div>
      </div>

      <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
          <div class="row g-3 align-items-end">
            <div class="col-12 col-md-6">
              <label for="keywordInput" class="form-label">雑誌名で検索</label>
              <input
                id="keywordInput"
                type="text"
                class="form-control"
                placeholder="例: 昆虫分類学報 / Japanese Journal of Entomology / 000123"
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
            <h2 class="h5 mb-1">雑誌情報マスタ</h2>
            <div class="text-secondary small">
              登録済みの雑誌一覧
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
              0 records / 無効 0件
            </span>
          </div>
        </div>

        <div class="card-body p-0">
          <div id="loadingBox" class="p-3 text-secondary">読み込み中...</div>

          <div id="errorBox" class="alert alert-danger rounded-0 border-0 m-0 d-none"></div>

          <div id="emptyBox" class="p-3 text-secondary d-none">
            表示できる雑誌がありません。
          </div>

          <div id="tableWrap" class="table-responsive d-none">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                      type="checkbox"
                      id="selectAllJournals"
                      class="form-check-input mt-0"
                      aria-label="表示中の雑誌をすべて選択"
                    >
                  </th>
                  <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
                  <th class="px-3 py-3">雑誌名</th>
                  <th class="px-3 py-3 journal-col-urlprov" style="min-width: 9rem;">URL<span class="text-body-secondary fw-normal"> / </span>提供</th>
                  <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                  <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
                </tr>
              </thead>
              <tbody id="journalTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade" id="journalCreateAndEditModal" tabindex="-1" aria-labelledby="journalCreateAndEditModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
          <form id="journalCreateAndEditForm">
            <div class="modal-header">
              <h5 class="modal-title" id="journalCreateAndEditModalLabel">雑誌の編集</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>

            <div class="modal-body">
              <div class="alert alert-danger d-none" id="journalEditErrorBox"></div>

              <input type="hidden" id="editJournalId" name="id" value="">

              <div class="row compactRow g-3">
                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalEn" class="form-label mb-1">雑誌名（英）</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editJournalEn"
                    name="journal_name_en"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalJa" class="form-label mb-1">雑誌名（和）</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editJournalJa"
                    name="journal_name_ja"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalCode" class="form-label mb-1">雑誌コード</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editJournalCode"
                    name="journal_code"
                    inputmode="numeric"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalCategory" class="form-label mb-1">category</label>
                  <input
                    type="number"
                    class="form-control"
                    id="editJournalCategory"
                    name="category"
                    inputmode="numeric"
                    step="1"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalPublisher" class="form-label mb-1">出版者</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editJournalPublisher"
                    name="publisher"
                    maxlength="30"
                    autocomplete="organization"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalProvidedBy" class="form-label mb-1">提供</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editJournalProvidedBy"
                    name="provided_by"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalUrl" class="form-label mb-1">URL</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editJournalUrl"
                    name="url"
                    placeholder="https:// から始まるURL"
                    autocomplete="off"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editJournalStatus" class="form-label mb-1">ステータス</label>
                  <select
                    class="form-select"
                    id="editJournalStatus"
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
              <button type="button" class="btn btn-primary btn-sm" id="saveJournalButton">
                保存
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    @slot('scripts')
    <script src="{{ url('/js/components/masters/journal.js') }}" type="module"></script>
    @endslot
</x-kaikon::app-layout>