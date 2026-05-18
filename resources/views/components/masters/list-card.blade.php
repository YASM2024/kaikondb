@props([
    'listTitle',
    'subtitle' => null,
    'subtitleId' => null,
    'subtitleInitial' => null,
    'countPillInitial' => '0 records / 無効 0件',
    'importVariant' => 'label',
    'emptyMessage',
    'tbodyId',
])

<div class="card shadow-sm border-0">
    <x-kaikon::masters.list-header
        :list-title="$listTitle"
        :subtitle="$subtitle"
        :subtitle-id="$subtitleId"
        :subtitle-initial="$subtitleInitial"
        :count-pill-initial="$countPillInitial"
        :import-variant="$importVariant"
    />

    <div class="card-body p-0">
        <x-kaikon::masters.list-states :empty-message="$emptyMessage" />

        <x-kaikon::masters.table-shell :tbody-id="$tbodyId">
            <x-slot:head>
                {{ $head }}
            </x-slot:head>
        </x-kaikon::masters.table-shell>
    </div>
</div>
