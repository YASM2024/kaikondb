@php
    $expandedFormConfig = [
        'isEdit' => request()->routeIs('expanded_page.showEdit'),
        'createUrl' => route('expanded_page.create'),
        'updateUrl' => route('expanded_page.update'),
        'deleteUrl' => route('expanded_page.delete'),
        'indexUrl' => route('expanded_page.index'),
    ];
@endphp
<script>
  window.__EXPANDED_FORM__ = @json($expandedFormConfig);
</script>
