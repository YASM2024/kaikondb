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
