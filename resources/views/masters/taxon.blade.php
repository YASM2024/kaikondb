<x-kaikon::app-layout>
    @slot('additionalStyles')
    <link rel="stylesheet" href="{{ url('/css/masters.css') }}">
    @endslot

    @slot('header')
    分類マスタ - Orderマスタ
    @endslot

  <div class="container py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
      <h1 class="h3 mb-1">分類マスタ - Orderマスタ</h1>
      <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" id="reloadButton">
          再読込
        </button>
        <button type="button" class="btn btn-primary" id="addOrderButton">
          Orderを追加
        </button>
      </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-12 col-md-6">
            <label for="keywordInput" class="form-label">Order名で検索</label>
            <input
              id="keywordInput"
              type="text"
              class="form-control"
              placeholder="例: カマアシムシ / Protura / 010"
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
          <h2 class="h5 mb-1">Orderマスタ</h2>
          <div class="text-secondary small">登録済みの Orders 一覧</div>
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


    </div>

    <div class="card-body p-0">
      <div id="loadingBox" class="p-3 text-secondary">読み込み中...</div>

      <div id="errorBox" class="alert alert-danger rounded-0 border-0 m-0 d-none"></div>

      <div id="emptyBox" class="p-3 text-secondary d-none">
        表示できる Order がありません。
      </div>

      <div id="tableWrap" class="table-responsive d-none">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                <input
                  type="checkbox"
                  id="selectAllOrders"
                  class="form-check-input mt-0"
                  aria-label="表示中のOrderをすべて選択"
                >
              </th>
              <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
              <th class="px-3 py-3">order</th>
              <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
              <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
            </tr>
          </thead>
          <tbody id="ordersTableBody"></tbody>
        </table>
      </div>
    </div>

  </div>
</div>
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

    @slot('scripts')
    <script src="{{ url('/js/components/masters/taxon.js') }}" type="module"></script>
    @endslot

</x-kaikon::app-layout>