<div class="modal fade" id="landmarkCreateAndEditModal" tabindex="-1" aria-labelledby="landmarkCreateAndEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="landmarkCreateAndEditForm">
        <div class="modal-header">
          <h5 class="modal-title" id="landmarkCreateAndEditModalLabel">地点の編集</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-danger d-none" id="landmarkEditErrorBox"></div>

          <input type="hidden" id="editLandmarkId" name="id" value="">

          <div
            class="row g-3 landmark-modal-layout"
            style="--landmark-map-aspect: {{ $landmarkMapAspect ?? 1 }};"
          >
            <div class="col-12 col-md-6 landmark-modal-fields">
              <div class="row g-2">
                <div class="col-5 landmark-field-code">
                  <label for="editLandmarkCode" class="form-label mb-1">コード</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editLandmarkCode"
                    name="code"
                    autocomplete="off"
                    pattern="[a-z0-9_]+"
                    maxlength="16"
                    placeholder="例: lm_fuji"
                  >
                  <div class="form-text" id="landmarkCodeHint">半角16文字まで</div>
                </div>

                <div class="col-7 landmark-field-label">
                  <label for="editLandmarkLabel" class="form-label mb-1">地点名</label>
                  <input
                    type="text"
                    class="form-control"
                    id="editLandmarkLabel"
                    name="label"
                    autocomplete="off"
                    placeholder="例: 富士山"
                  >
                </div>

                <div class="col-6 landmark-field-lat">
                  <label for="editLandmarkLat" class="form-label mb-1">緯度</label>
                  <input
                    type="number"
                    step="0.000001"
                    class="form-control"
                    id="editLandmarkLat"
                    name="lat"
                    autocomplete="off"
                  >
                </div>

                <div class="col-6 landmark-field-lon">
                  <label for="editLandmarkLon" class="form-label mb-1">経度</label>
                  <input
                    type="number"
                    step="0.000001"
                    class="form-control"
                    id="editLandmarkLon"
                    name="lon"
                    autocomplete="off"
                  >
                </div>

                <div class="col-auto landmark-field-sort">
                  <label for="editLandmarkSortOrder" class="form-label mb-1">表示順</label>
                  <input
                    type="number"
                    min="0"
                    max="999"
                    step="1"
                    class="form-control"
                    id="editLandmarkSortOrder"
                    name="sort_order"
                    inputmode="numeric"
                    autocomplete="off"
                  >
                </div>

                <div class="col-auto landmark-field-pattern">
                  <label for="editLandmarkPattern" class="form-label mb-1">アイコン</label>
                  <select class="form-select" id="editLandmarkPattern" name="pattern">
                    <option value="mountain">▲</option>
                    <option value="urban">◎</option>
                  </select>
                </div>

                <div class="col-12">
                  <div class="small text-body-secondary" id="landmarkBoundsHint">
                    地図表示範囲の情報を読み込んでいます…
                  </div>
                </div>
              </div>
            </div>

            <div class="col-12 col-md-6 landmark-modal-preview-column">
              <label class="form-label mb-1">地図プレビュー</label>
              <div id="landmarkMapPreview" class="landmark-map-preview border rounded bg-light">
                <div class="text-body-secondary small p-3">緯度・経度を入力するとプレビューが表示されます。</div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
            キャンセル
          </button>
          <div class="ms-auto d-flex gap-2">
            <button type="button" class="btn btn-outline-danger btn-sm" id="deleteLandmarkButton" hidden>
              削除
            </button>
            <button type="button" class="btn btn-primary btn-sm" id="saveLandmarkButton">
              保存
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
