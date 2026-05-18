<x-kaikon::masters.page
    header="雑誌情報マスタ"
    script="/js/components/masters/journal.js"
>
    <x-slot:styles>
        <style>
            @media (max-width: 575.98px) {
                .journal-col-urlprov {
                    display: none !important;
                }
            }
        </style>
    </x-slot:styles>

    <div class="container py-4">
        <x-kaikon::masters.toolbar
            title="雑誌情報マスタ"
            add-button-id="addJournalButton"
            add-button-label="雑誌を追加"
        />

        <x-kaikon::masters.search-card
            keyword-label="雑誌名で検索"
            keyword-placeholder="例: 昆虫分類学報 / Japanese Journal of Entomology / 000123"
        />

        <x-kaikon::masters.list-card
            list-title="雑誌情報マスタ"
            subtitle="登録済みの雑誌一覧"
            empty-message="表示できる雑誌がありません。"
            tbody-id="journalTableBody"
        >
            <x-slot:head>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                        type="checkbox"
                        id="selectAllJournals"
                        class="form-check-input mt-0"
                        aria-label="表示中の雑誌をすべて選択"
                    >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
                <th class="px-3 py-3">雑誌名</th>
                <th class="px-3 py-3 journal-col-urlprov" style="min-width: 9rem;">URL<span class="text-body-secondary fw-normal"> / </span>提供</th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
            </x-slot:head>
        </x-kaikon::masters.list-card>
    </div>

    @include('kaikon::masters.modals.journal-form')
</x-kaikon::masters.page>
