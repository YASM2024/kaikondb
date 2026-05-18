<x-kaikon::masters.page
    header="分類マスタ - Speciesマスタ"
    script="/js/components/masters/species.js"
>
    <x-slot:styles>
        <style>
            .modalCompactLabel {
                min-width: 0;
            }
        </style>
    </x-slot:styles>

    <div class="container py-4">
        <x-kaikon::masters.toolbar
            title="分類マスタ - Speciesマスタ"
            add-button-id="addSpeciesButton"
            add-button-label="Speciesを追加"
        />

        <x-kaikon::masters.search-card
            keyword-label="Species名で検索"
            keyword-placeholder="例: クロオオアリ / Camponotus japonicus / 010"
        />

        <x-kaikon::masters.list-card
            list-title="Speciesマスタ"
            subtitle-id="currentTaxonomyLabel"
            subtitle-initial="読み込み中... >> 読み込み中... >> 登録済みの Species 一覧"
            empty-message="表示できる Species がありません。"
            tbody-id="speciesTableBody"
            import-variant="button"
        >
            <x-slot:head>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                        type="checkbox"
                        id="selectAllSpecies"
                        class="form-check-input mt-0"
                        aria-label="表示中のSpeciesをすべて選択"
                    >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
                <th class="px-3 py-3">species</th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
            </x-slot:head>
        </x-kaikon::masters.list-card>
    </div>

    @include('kaikon::masters.modals.species-form')
</x-kaikon::masters.page>
