@foreach ($photos as $photo)
    <tr data-photo-id="{{ $photo->id }}" data-status="{{ $status }}">
        <td>
            <img src="{{ url('/storage/photos/' . $photo->thumbnail_url) }}"
                 alt="" class="img-fluid rounded" style="max-height:3.5rem;">
        </td>
        <td class="text-break">{{ $photo->photo_title }}</td>
        <td>{{ $photo->show_name }}</td>
        <td>{{ $photo->place }}</td>
        <td>{{ $photo->date }}</td>
        <td>{{ $photo->created_at?->format('Y-m-d H:i') }}</td>
        <td>{{ $photo->agreed_at?->format('Y-m-d H:i') ?? '—' }}</td>
        @if ($status === 'published')
            <td>{{ $photo->approved_at?->format('Y-m-d H:i') }}</td>
        @endif
        <td>
            @if ($status === 'pending')
                <button type="button" class="btn btn-danger btn-sm w-100 btn-approve">承認</button>
            @else
                <button type="button" class="btn btn-secondary btn-sm w-100 btn-unapprove">取消</button>
            @endif
        </td>
    </tr>
@endforeach
