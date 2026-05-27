<div class="text-left bg-light px-1 px-sm-4">
    <div class="marketing">
        <div class="row">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link active"
                        id="search-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#home"
                        type="button"
                        role="tab"
                        aria-controls="home"
                        aria-selected="true">
                        検索
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button
                        class="nav-link"
                        id="post-tab"
                        type="button"
                        role="tab"
                        @if (\Illuminate\Support\Facades\Auth::check())
                            data-bs-toggle="modal"
                            data-bs-target="#photoRegisterModal"
                        @else
                            disabled
                            title="投稿にはログインが必要です"
                        @endif>
                        投稿
                    </button>
                </li>
            </ul>
            <div class="tab-content px-1 bg-white border-bottom">
                <div
                    class="tab-pane fade mt-4 px-4 active show"
                    id="home"
                    role="tabpanel"
                    aria-labelledby="search-tab">
                    @include('kaikon::photos.partials.search-form', [
                        'photographers' => $photographers,
                        'data' => $data,
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
