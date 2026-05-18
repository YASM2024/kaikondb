@props([
    'tbodyId',
])

<div id="tableWrap" class="table-responsive d-none">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                {{ $head }}
            </tr>
        </thead>
        <tbody id="{{ $tbodyId }}"></tbody>
    </table>
</div>
