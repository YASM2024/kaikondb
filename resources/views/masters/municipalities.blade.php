<x-kaikon::masters.page
    header="市町村マスタ"
    script="/js/components/masters/municipality.js"
    :script-version="filemtime(public_path('js/components/masters/municipality.js'))"
>
    <div class="container py-4">
        <x-kaikon::masters.toolbar
            title="市町村マスタ"
            add-button-id="addMunicipalityButton"
            add-button-label="市町村を追加"
        />

        <x-kaikon::masters.search-card
            keyword-label="市町村名 / コードで検索"
            keyword-placeholder="例: 192015 / 甲府市 / Kofu-city"
        />

        <x-kaikon::masters.list-card
            list-title="市町村マスタ"
            subtitle="登録済みの市町村一覧"
            empty-message="表示できる市町村がありません。"
            tbody-id="municipalityTableBody"
            count-pill-initial="0 records"
        >
            <x-slot:head>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                        type="checkbox"
                        id="selectAllMunicipalities"
                        class="form-check-input mt-0"
                        aria-label="表示中の市町村をすべて選択"
                    >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 110px;">code</th>
                <th class="px-3 py-3">市町村名</th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 130px;">actions</th>
            </x-slot:head>
        </x-kaikon::masters.list-card>
    </div>

    @include('kaikon::masters.modals.municipality-form')
</x-kaikon::masters.page>
