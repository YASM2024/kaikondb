    <div class="modal fade" id="speciesCreateAndEditModal" tabindex="-1" aria-labelledby="speciesCreateAndEditModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <form id="speciesCreateAndEditForm">
            <div class="modal-header">
              <h5 class="modal-title" id="speciesCreateAndEditModalLabel">Speciesを編集</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
            </div>

            <div class="modal-body">
              <div class="alert alert-danger d-none" id="speciesEditErrorBox"></div>

              <div class="compactMeta">
                <div class="row align-items-center">
                  <div class="d-none">
                    <div class="row align-items-center">
                      <div class="col-8">
                        <input type="text" class="form-control form-control-sm" id="editSpeciesId" name="id" disabled>
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
                    
                  <div class="col-12 col-md-6">
                    <div class="row align-items-center">
                      <label for="editFamilyId" class="col-2 col-form-label">family</label>
                      <div class="col-10">
                        <select class="form-select form-select-sm" id="editFamilyId" name="family_id">
                          <option value="">選択してください</option>
                        </select>
                      </div>
                    </div>
                  </div>

                </div>
              </div>

              <div class="row compactRow">
                <div class="col-12 col-md-6 compactBlock">
                  <label for="editSpeciesLatin" class="form-label mb-1">species</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editSpeciesLatin"
                    name="species"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editSpeciesJa" class="form-label mb-1">species_ja</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editSpeciesJa"
                    name="species_ja"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editSpeciesCode" class="form-label mb-1">code</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editSpeciesCode"
                    name="code"
                  >
                </div>

                <div class="col-12 col-md-6 compactBlock">
                  <label for="editSpeciesStatus" class="form-label mb-1">status</label>
                  <select
                    class="form-select"
                    id="editSpeciesStatus"
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
              <button type="button" class="btn btn-primary btn-sm" id="saveSpeciesButton">
                保存
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
