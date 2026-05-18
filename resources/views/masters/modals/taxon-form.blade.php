<div class="modal fade" id="orderCreateAndEditModal" tabindex="-1" aria-labelledby="orderCreateAndEditModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="orderCreateAndEditForm">
        <div class="modal-header">
          <h5 class="modal-title" id="orderCreateAndEditModalLabel">Orderを編集</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>

        <div class="modal-body">
          <div class="alert alert-danger d-none" id="orderEditErrorBox"></div>

          <div class="container-fluid px-0">
            <div class="row mb-3 align-items-center">
              <label for="editOrderId" class="col-12 col-lg-3 col-form-label">id</label>
              <div class="col-12 col-lg-9">
                <input
                  type="text"
                  class="form-control"
                  id="editOrderId"
                  name="id"
                  disabled
                >
              </div>
            </div>

            <div class="row mb-3 align-items-center">
              <label for="editOrderCode" class="col-12 col-lg-3 col-form-label">code</label>
              <div class="col-12 col-lg-9">
                <input
                  type="text"
                  class="form-control"
                  id="editOrderCode"
                  name="code"
                >
              </div>
            </div>

            <div class="row mb-3 align-items-center">
              <label for="editOrderLatin" class="col-12 col-lg-3 col-form-label">order</label>
              <div class="col-12 col-lg-9">
                <input
                  type="text"
                  class="form-control"
                  id="editOrderLatin"
                  name="order"
                >
              </div>
            </div>

            <div class="row mb-3 align-items-center">
              <label for="editOrderJa" class="col-12 col-lg-3 col-form-label">order_ja</label>
              <div class="col-12 col-lg-9">
                <input
                  type="text"
                  class="form-control"
                  id="editOrderJa"
                  name="order_ja"
                >
              </div>
            </div>

            <div class="row mb-3 align-items-center">
              <label for="editOrderStatus" class="col-12 col-lg-3 col-form-label">status</label>
              <div class="col-12 col-lg-9">
                <select
                  class="form-select"
                  id="editOrderStatus"
                  name="status"
                >
                  <option value="1">有効</option>
                  <option value="0">無効</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">キャンセル</button>
          <button type="button" class="btn btn-secondary" id="saveOrderButton">保存</button>
        </div>
      </form>
    </div>
  </div>
</div>
