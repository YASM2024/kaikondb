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
