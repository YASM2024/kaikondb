<x-kaikon::masters.page
    header="分類マスタ - Familyマスタ"
    script="/js/components/masters/family.js"
>
    <div class="container py-4">
        <x-kaikon::masters.toolbar
            title="分類マスタ - Familyマスタ"
            add-button-id="addFamilyButton"
            add-button-label="Familyを追加"
        />

        <x-kaikon::masters.search-card
            keyword-label="Family名で検索"
            keyword-placeholder="例: カマアシムシ科 / Acerentomidae / 010"
        />

        <x-kaikon::masters.list-card
            list-title="Familyマスタ"
            subtitle-id="currentOrderLabel"
            subtitle-initial="読み込み中... >> 登録済みの Families 一覧"
            empty-message="表示できる Family がありません。"
            tbody-id="familiesTableBody"
            import-variant="button"
        >
            <x-slot:head>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                        type="checkbox"
                        id="selectAllFamilies"
                        class="form-check-input mt-0"
                        aria-label="表示中のFamilyをすべて選択"
                    >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
                <th class="px-3 py-3">family</th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
            </x-slot:head>
        </x-kaikon::masters.list-card>
    </div>

    @include('kaikon::masters.modals.family-form')
</x-kaikon::masters.page>
