@php
    $isAdmin = ($variant ?? 'index') === 'admin';
    $iconsHref = $iconsHref ?? ($isAdmin ? '../svg/icons.svg' : './svg/icons.svg');
    $waitImage = $waitImage ?? url('/storage/img/wait.png');
@endphp

<div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="ModalLabel">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-1">
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="閉じる">
            </div>
            <div class="modal-body">
                @if ($isAdmin)
                    <div class="w-100">
                        <img src="{{ $waitImage }}" id="photo_url" class="w-100">
                    </div>
                @else
                    <div class="position-relative d-inline-block w-100">
                        <img src="{{ $waitImage }}" id="photo_url" class="w-100">
                        @if (\Illuminate\Support\Facades\Auth::check())
                            <div id="closed" class="position-absolute top-0 start-0 m-2 badge bg-secondary">承認待ち</div>
                            <div id="opened" class="position-absolute top-0 start-0 m-2 badge bg-danger">公開中</div>
                            <div class="position-absolute bottom-0 end-0 m-2" style="display: block;">
                                <div id="editAndDelete" style="float: right; padding-right: 1em;">
                                    <span id="editBtn" class="icon-btn" data-bs-toggle="modal" data-bs-target="#photoEditModal" data-bs-whatever="2">
                                        <svg class="bi cursor-pointer" width="1.2em" height="1.2em"><use xlink:href="{{ $iconsHref }}#edit"></use></svg>
                                    </span>
                                    <span id="delBtn" class="icon-btn" data-bs-whatever="2">
                                        <svg class="bi cursor-pointer" width="1.2em" height="1.2em"><use xlink:href="{{ $iconsHref }}#delete"></use></svg>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
                <div class="m-2">
                    <div id="ModalLabel" class="h4 m-2" style="clear: both;"></div>
                    <span name="photographer" class="view_data ms-2"></span>
                    <span class="ms-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="{{ $iconsHref }}#map"></use></svg>
                        <span name="place" class="view_data" value=""></span>
                    </span>
                    <span class="ms-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="{{ $iconsHref }}#calendar"></use></svg>
                        <span name="date" class="view_data" value=""></span>
                    </span>
                    <div class="m-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="{{ $iconsHref }}#memo"></use></svg>
                        <span name="memo" class="view_data" value=""></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer p-1 @if($isAdmin) container @endif">
                @if ($isAdmin)
                    <div class="row w-100">
                        <div id="acceptBtn" class="col mx-1 btn btn-danger">承認</div>
                        <div id="rejectBtn" class="col mx-1 btn btn-secondary">却下</div>
                        <div id="cancelBtn" class="col mx-1 btn btn-secondary">承認取消</div>
                    </div>
                @elseif (\Illuminate\Support\Facades\Auth::check())
                    <button id="deleteBtn" type="button" class="d-none btn btn-outline-danger">削除</button>
                @endif
            </div>
        </div>
    </div>
</div>
