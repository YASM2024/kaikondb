@php
    $queryBase = array_merge(
        ['status' => $status],
        array_filter($filters ?? []),
        ['sort' => $sort ?? 'created_at', 'dir' => $dir ?? 'desc']
    );

    $sortLink = function (string $column) use ($queryBase, $sort, $dir) {
        $nextDir = ($sort === $column && $dir === 'asc') ? 'desc' : 'asc';
        $params = array_merge($queryBase, ['sort' => $column, 'dir' => $nextDir]);
        return '?' . http_build_query($params);
    };

    $sortIndicator = function (string $column) use ($sort, $dir) {
        if ($sort !== $column) {
            return '';
        }
        return $dir === 'asc' ? ' ▲' : ' ▼';
    };
@endphp

<x-kaikon::app-layout>
    <x-slot:additionalStyles>
        <link rel="stylesheet" href="{{ url('/css/photos.css') }}">
    </x-slot:additionalStyles>

    <x-slot:header>昆虫写真承認・差戻し</x-slot:header>

    <div class="container mt-4 py-2">
        @include('kaikon::photos.partials.header-section', [
            'title' => '昆虫写真承認・差戻し',
        ])

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $status === 'pending' ? 'active' : '' }}"
                   href="?{{ http_build_query(array_merge(array_filter($filters ?? []), ['status' => 'pending', 'sort' => $sort, 'dir' => $dir])) }}">
                    承認待ち
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $status === 'published' ? 'active' : '' }}"
                   href="?{{ http_build_query(array_merge(array_filter($filters ?? []), ['status' => 'published', 'sort' => $sort, 'dir' => $dir])) }}">
                    公開中
                </a>
            </li>
        </ul>

        <form method="get" class="bg-white border rounded p-3 mb-3">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="dir" value="{{ $dir }}">
            <div class="row g-2 align-items-end">
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small mb-0" for="author">投稿者名</label>
                    <input type="text" class="form-control form-control-sm" id="author" name="author"
                           value="{{ $filters['author'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small mb-0" for="species">種名</label>
                    <input type="text" class="form-control form-control-sm" id="species" name="species"
                           value="{{ $filters['species'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small mb-0" for="place">市町村</label>
                    <input type="text" class="form-control form-control-sm" id="place" name="place"
                           value="{{ $filters['place'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small mb-0" for="created_at">投稿日</label>
                    <input type="text" class="form-control form-control-sm" id="created_at" name="created_at"
                           value="{{ $filters['created_at'] ?? '' }}" placeholder="2026-05">
                </div>
                <div class="col-md-4 col-lg-2">
                    <label class="form-label small mb-0" for="date">撮影日</label>
                    <input type="text" class="form-control form-control-sm" id="date" name="date"
                           value="{{ $filters['date'] ?? '' }}">
                </div>
                <div class="col-md-4 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-secondary btn-sm w-100">検索</button>
                    <a href="{{ route('photos.admin', ['status' => $status]) }}" class="btn btn-outline-secondary btn-sm w-100">クリア</a>
                </div>
            </div>
        </form>

        @if ($photos->total() === 0)
            <p class="text-muted">0 件</p>
        @else
            <div class="table-responsive bg-white border rounded">
                <table class="table table-sm table-hover align-middle mb-0" id="photoAdminTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width:5rem;">画像</th>
                            <th><a href="{{ $sortLink('photo_title') }}" class="text-decoration-none text-dark">種名{!! $sortIndicator('photo_title') !!}</a></th>
                            <th><a href="{{ $sortLink('show_name') }}" class="text-decoration-none text-dark">投稿者{!! $sortIndicator('show_name') !!}</a></th>
                            <th>市町村</th>
                            <th>撮影日</th>
                            <th><a href="{{ $sortLink('created_at') }}" class="text-decoration-none text-dark">投稿日時{!! $sortIndicator('created_at') !!}</a></th>
                            <th>規程同意</th>
                            @if ($status === 'published')
                                <th><a href="{{ $sortLink('approved_at') }}" class="text-decoration-none text-dark">承認日時{!! $sortIndicator('approved_at') !!}</a></th>
                            @endif
                            <th style="width:7rem;">操作</th>
                        </tr>
                    </thead>
                    <tbody id="photo_admin_body">
                        @include('kaikon::photos.partials.admin-rows', [
                            'photos' => $photos,
                            'status' => $status,
                        ])
                    </tbody>
                </table>
            </div>
            <div id="number_of_show" class="mt-3 text-muted small"></div>
            <div id="next_page_loader"></div>
        @endif
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="photoAdminToast" class="toast align-items-center text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body" id="photoAdminToastBody"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    @if ($photos->total() > 0)
        <x-slot:scripts>
            <script src="{{ url('/js/paginationMessage.js') }}"></script>
            <script src="{{ url('/js/nextPageLoader.js') }}"></script>
            <script>
                window.photoAdminConfig = {
                    approveUrlTemplate: @json(url('/photos/__ID__/approve')),
                    unapproveUrlTemplate: @json(url('/photos/__ID__/unapprove')),
                    csrfToken: @json(csrf_token()),
                    currentStatus: @json($status),
                    sort: @json($sort),
                    dir: @json($dir),
                    filters: @json($filters ?? []),
                    entriesUrl: @json(route('photos.admin.entries')),
                    pagination: {
                        current_page: {{ $photos->currentPage() }},
                        last_page: {{ $photos->lastPage() }},
                        per_page: {{ $photos->perPage() }},
                        total: {{ $photos->total() }},
                    },
                };
            </script>
            <script src="{{ url('/js/components/photos/admin.js') }}"></script>
        </x-slot:scripts>
    @endif
</x-kaikon::app-layout>
