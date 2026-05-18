@props([
    'listTitle',
    'subtitle' => null,
    'subtitleId' => null,
    'subtitleInitial' => null,
    'countPillInitial' => '0 records / 無効 0件',
    'importVariant' => 'label',
])

<div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h2 class="h5 mb-1">{{ $listTitle }}</h2>
        @if ($subtitleId)
            <div class="text-secondary small" id="{{ $subtitleId }}">
                {{ $subtitleInitial }}
            </div>
        @elseif ($subtitle)
            <div class="text-secondary small">{{ $subtitle }}</div>
        @endif
    </div>

    @isset($csv)
        {{ $csv }}
    @else
        <x-kaikon::masters.csv-toolbar
            :count-pill-initial="$countPillInitial"
            :import-variant="$importVariant"
        />
    @endisset
</div>
