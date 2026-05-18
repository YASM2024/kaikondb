@props([
    'countPillInitial' => '0 records / 無効 0件',
    'importVariant' => 'label',
])

<div class="d-flex flex-wrap align-items-center gap-2">
    <button
        type="button"
        class="btn btn-outline-success btn-sm"
        id="csvDownloadButton"
        disabled
    >
        CSVダウンロード
    </button>

    @if ($importVariant === 'button')
        <button
            type="button"
            class="btn btn-outline-primary btn-sm"
            id="csvImportButton"
        >
            CSV取込
        </button>
    @else
        <label
            for="csvImportInput"
            class="btn btn-outline-primary btn-sm mb-0"
            id="csvImportButton"
            role="button"
        >
            CSV取込
        </label>
    @endif

    <input
        type="file"
        id="csvImportInput"
        accept=".csv,text/csv"
        class="d-none"
    >

    <span class="badge text-bg-primary rounded-pill" id="countPill">
        {{ $countPillInitial }}
    </span>
</div>
