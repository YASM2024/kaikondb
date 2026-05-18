@props([
    'emptyMessage',
])

<div id="loadingBox" class="p-3 text-secondary">読み込み中...</div>

<div id="errorBox" class="alert alert-danger rounded-0 border-0 m-0 d-none"></div>

<div id="emptyBox" class="p-3 text-secondary d-none">
    {{ $emptyMessage }}
</div>
