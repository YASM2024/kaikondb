@if ($rows->isEmpty())
    <p class="text-muted small">データがありません。</p>
@else
    <table class="table table-sm table-bordered bg-white mt-2">
        <thead class="table-light">
            <tr><th>項目</th><th class="text-end">件数</th></tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td>{{ $row->label }}</td>
                    <td class="text-end">{{ number_format($row->count) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
