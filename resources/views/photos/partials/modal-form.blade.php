@php
    $isRegister = ($mode ?? 'register') === 'register';
    $modalId = $isRegister ? 'photoRegisterModal' : 'photoEditModal';
    $labelId = $isRegister ? 'ModalLabel-form' : 'ModalLabel-edit';
    $iconsHref = $iconsHref ?? './svg/icons.svg';
    $waitImage = $waitImage ?? url('/storage/img/wait.png');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $labelId }}">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-1">
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="閉じる">
            </div>
            <div class="modal-body">
                <div class="w-100 image-container">
                    @if ($isRegister)
                        <label>
                            <input type="file" accept="image/*" class="d-none" id="new_image_file">
                            <img id="preview" class="custom-carousel w-100" src="{{ $waitImage }}">
                            <div class="image-overlay">
                                <div class="image-overlay-content w-75">
                                    <div class="h5">画像アップロード</div>
                                    <svg class="bi ms-1" width="2em" height="2em"><use xlink:href="{{ $iconsHref }}#upload"></use></svg>
                                    <div class="mt-3 small">アップロード可能サイズ：最大2MB<br>
                                    フォーマット：jpg、png、bmp</div>
                                </div>
                            </div>
                        </label>
                    @else
                        <img id="photo_editForm" class="custom-carousel w-100" src="{{ $waitImage }}">
                    @endif
                </div>
                <div class="m-2">
                    <div id="{{ $labelId }}" class="h4 m-2" style="clear: both;"></div>
                    @if ($isRegister)
                        <span class="my-2 fw-bold">撮影者：{{ \Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->name }}</span>
                        <input type="text" id="new_photo_title" class="form-control my-2" placeholder="種名など" value="">
                        <small class="ms-2">例）トノサマバッタ、ガの一種、脚の長い虫など</small>
                        <input type="text" id="new_place" class="form-control my-2" placeholder="撮影場所" value="">
                        <small class="ms-2">例）甲府市、北岳、大弛峠　など</small>
                        <input type="text" id="new_date" class="form-control my-2" placeholder="撮影日" value="">
                        <small class="ms-2">例）2022年XX月XX日、令和４年○月　など</small>
                        <textarea id="new_memo" class="form-control my-2" placeholder="コメント"></textarea>
                        <small class="ms-2">確認状況の詳細など、自由に記述。</small>
                    @else
                        <span id="show_name_editForm" class="my-2 fw-bold"></span>
                        <input type="hidden" id="id_editForm">
                        <input type="text" id="photo_title_editForm" class="form-control my-2" placeholder="種名など">
                        <input type="text" id="place_editForm" class="form-control my-2" placeholder="撮影場所">
                        <input type="text" id="date_editForm" class="form-control my-2" placeholder="撮影日">
                        <textarea id="memo_editForm" class="form-control my-2" placeholder="コメント"></textarea>
                    @endif
                </div>
            </div>
            <div class="modal-footer p-1">
                <button type="button" id="{{ $isRegister ? 'create_submit' : 'edit_submit' }}" class="btn btn-primary">確定</button>
            </div>
        </div>
    </div>
</div>
