<section class="mb-5">
    <h5 class="mb-3">{{ $title }}</h5>
    @if (!empty($canvasId))
        <canvas id="{{ $canvasId }}" height="200"></canvas>
    @endif
    @include('kaikon::admin.partials.stat-table', ['rows' => $rows])
</section>
