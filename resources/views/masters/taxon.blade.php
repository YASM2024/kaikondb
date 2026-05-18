<x-kaikon::masters.page
    header="分類マスタ - Orderマスタ"
    script="/js/components/masters/taxon.js"
>
    <div class="container py-4">
        <x-kaikon::masters.toolbar
            title="分類マスタ - Orderマスタ"
            add-button-id="addOrderButton"
            add-button-label="Orderを追加"
        />

        <x-kaikon::masters.search-card
            keyword-label="Order名で検索"
            keyword-placeholder="例: カマアシムシ / Protura / 010"
        />

        <x-kaikon::masters.list-card
            list-title="Orderマスタ"
            subtitle="登録済みの Orders 一覧"
            empty-message="表示できる Order がありません。"
            tbody-id="ordersTableBody"
        >
            <x-slot:head>
                <th class="text-center px-2 py-3 tableCheckboxCell" style="width: 48px;">
                    <input
                        type="checkbox"
                        id="selectAllOrders"
                        class="form-check-input mt-0"
                        aria-label="表示中のOrderをすべて選択"
                    >
                </th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">code</th>
                <th class="px-3 py-3">order</th>
                <th class="text-nowrap px-3 py-3" style="width: 90px;">status</th>
                <th class="text-center text-nowrap px-3 py-3" style="width: 110px;">actions</th>
            </x-slot:head>
        </x-kaikon::masters.list-card>
    </div>

    @include('kaikon::masters.modals.taxon-form')
</x-kaikon::masters.page>
