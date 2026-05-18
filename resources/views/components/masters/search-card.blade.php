@props([
    'keywordLabel',
    'keywordPlaceholder' => '',
    'keywordCol' => 'col-12 col-md-6',
    'statusCol' => 'col-12 col-md-3',
    'searchCol' => 'col-12 col-md-3',
    'showSearchButton' => true,
])

<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="{{ $keywordCol }}">
                <label for="keywordInput" class="form-label">{{ $keywordLabel }}</label>
                <input
                    id="keywordInput"
                    type="text"
                    class="form-control"
                    placeholder="{{ $keywordPlaceholder }}"
                />
            </div>

            <div class="{{ $statusCol }}">
                <label for="statusFilter" class="form-label">表示条件</label>
                <x-kaikon::masters.status-filter />
            </div>

            @if ($showSearchButton)
                <div class="{{ $searchCol }}">
                    <button type="button" class="btn btn-outline-primary w-100" id="searchButton">
                        検索
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
