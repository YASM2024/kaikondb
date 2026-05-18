<div class="container mt-4 py-2">
    <h4 class="my-3 px-0 mx-2">
        @if ($action_type === 'create')
            ページ追加
        @elseif ($action_type === 'edit')
            ページ編集
        @endif
    </h4>

    <div>
        @csrf
        @if ($action_type === 'edit')
            <input id="id" class="d-none" type="text" name="id" value="{{ $page->id }}">
        @endif
        <input id="entered" class="d-none" type="text" name="entered" value="1">

        <div class="row mb-0">
            <label for="route_name" class="col-sm-3 custom-border col-form-label text-danger">
                <span>識別子</span>
            </label>
            <div class="col-sm-9 custom-border col-form-label">
                <input id="route_name" type="text" name="route_name" class="form-control"
                    required
                    @if ($action_type === 'edit') disabled readonly @endif
                    value="{{ old('route_name', $page->route_name ?? '') }}">
            </div>
        </div>
        <div class="row mb-0">
            <div class="col-sm-3 custom-border"></div>
            <div class="col-sm-9 custom-border col-form-label">
                <small class="text-muted">予約語：{{ implode('、', $expandedPageReservedRouteNames ?? []) }} は使用できません。</small>
            </div>
        </div>

        <div class="row mb-0">
            <label for="title" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-center">
                <span>表題</span>
                <a data-bs-toggle="collapse" href="#title_en_area" role="button" aria-expanded="false" aria-controls="title_en" class="btn btn-sm btn-secondary collapsed">
                    +English
                </a>
            </label>
            <div class="col-sm-9 custom-border col-form-label">
                <input id="title" type="text" name="title" class="form-control" required value="{{ old('title', $page->title ?? '') }}">
            </div>
        </div>

        <div id="title_en_area" class="row mb-0 collapse">
            <label for="title_en" class="col-sm-3 custom-border col-form-label">
                <span>(TITLE)</span>
            </label>
            <div class="col-sm-9 custom-border col-form-label">
                <input id="title_en" type="text" name="title_en" class="form-control" value="{{ old('title_en', $page->title_en ?? '') }}">
            </div>
        </div>

        <div class="row mb-0">
            <label for="body" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-start">
                <span>本文</span>
                <a data-bs-toggle="collapse" href="#body_en_area" role="button" aria-expanded="false" aria-controls="body_en" class="btn btn-sm btn-secondary collapsed">
                    +English
                </a>
            </label>
            <div class="col-sm-9 custom-border col-form-label">
                <textarea id="body" name="body" class="form-control FlexTextarea" required>{{ old('body', $page->body ?? '') }}</textarea>
            </div>
        </div>

        <div id="body_en_area" class="row mb-0 collapse">
            <label for="body_en" class="col-sm-3 custom-border col-form-label">
                <span>(BODY)</span>
            </label>
            <div class="col-sm-9 custom-border col-form-label">
                <textarea id="body_en" name="body_en" class="form-control FlexTextarea">{{ old('body_en', $page->body_en ?? '') }}</textarea>
            </div>
        </div>

        <div class="row mb-0">
            <label for="open" class="col-sm-3 custom-border col-form-label">公開設定</label>
            @if ($action_type === 'create')
                <div class="col-sm-9 custom-border col-form-label">
                    <span>下書き（非公開）</span>
                    <input class="d-none" type="radio" name="open" id="radioOn" value="0" checked>
                </div>
            @elseif ($action_type === 'edit')
                <div class="col-sm-9 custom-border col-form-label">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input cursor-pointer" type="radio" name="open" id="radioOn" value="1"
                            @checked(old('open', $page->open ?? '') == 1)>
                        <label class="form-check-label cursor-pointer" for="radioOn">ON</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input cursor-pointer" type="radio" name="open" id="radioOff" value="0"
                            @checked(old('open', $page->open ?? '') == 0)>
                        <label class="form-check-label cursor-pointer" for="radioOff">OFF</label>
                    </div>
                </div>
            @endif
        </div>

        <div class="row mb-0">
            <label for="seq" class="col-sm-3 custom-border col-form-label">公開順</label>
            <div class="col-sm-9 custom-border col-form-label">
                <select name="seq" class="form-select" aria-label="公開順">
                    @foreach ($seqs as $p)
                        <option value="{{ $p }}" @selected(old('seq', $page->seq ?? -1) == $p)>{{ $p }}</option>
                    @endforeach
                    @if ($action_type === 'create')
                        <option value="{{ max($seqs) + 1 }}" @selected(old('seq', $page->seq ?? -1) == -1)>末尾</option>
                    @endif
                </select>
            </div>
        </div>
    </div>
</div>
