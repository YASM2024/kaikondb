@foreach ($entries as $entry)
    <tr>
        <td class="text-nowrap">{{ $entry->recorded_at?->format('Y-m-d H:i') }}</td>
        <td>{{ $entry->savedByUser?->name ?? '—' }}</td>
        <td>{{ $entry->action }}</td>
        <td class="text-break">
            @if ($type === 'records')
                {{ $entry->species?->species_ja ?? '—' }}
                @if ($entry->species?->species)
                    <span class="text-muted fst-italic ms-1">{{ $entry->species->species }}</span>
                @endif
            @else
                {{ $entry->{$summaryColumn} ?? '—' }}
            @endif
        </td>
    </tr>
@endforeach
