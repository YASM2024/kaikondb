@props([
    'title',
    'addButtonId',
    'addButtonLabel',
])

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <h1 class="h3 mb-1">{{ $title }}</h1>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-secondary" id="reloadButton">
            再読込
        </button>
        <button type="button" class="btn btn-primary" id="{{ $addButtonId }}">
            {{ $addButtonLabel }}
        </button>
    </div>
</div>
