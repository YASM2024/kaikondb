<script src="{{ url('/js/paginationMessage.js') }}"></script>
<script src="{{ url('/js/nextPageLoader.js') }}"></script>
<script>
    window.adminHistory = {
        type: @json($type),
        days: {{ $days }},
        entriesUrl: @json($entriesUrl),
        pagination: {
            current_page: {{ $entries->currentPage() }},
            last_page: {{ $entries->lastPage() }},
            per_page: {{ $entries->perPage() }},
            total: {{ $entries->total() }},
        },
    };
</script>
<script src="{{ url('/js/components/admin/history.js') }}"></script>
