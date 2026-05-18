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
