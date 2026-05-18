<div class="container pb-4">
    <div class="center-button">
        <button class="btn btn-primary" id="main" type="button">保存</button>
        @if ($action_type === 'edit')
            <button class="btn btn-danger" id="deleteBtn" type="button">削除</button>
            <a class="btn btn-secondary" id="previewBtn" href="{{ route('expanded_page.preview', ['route_name' => $page->route_name]) }}" target="_blank" rel="noopener">プレビュー</a>
        @endif
    </div>
</div>
