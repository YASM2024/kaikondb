<x-kaikon::app-layout>
    <x-slot:header>文献に紐づく種一覧</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">種リスト</h4>

        @if ($groupedSpecies->isEmpty())
            <p class="text-muted">登録されている種はありません。</p>
        @else
            <div class="literature-species-list">
                @foreach ($groupedSpecies as $orderId => $families)
                    @php
                        $order = $families->first()?->first()?->order;
                    @endphp
                    <section class="mb-3" data-order-id="{{ $orderId }}">
                        <div class="fw-bold">■ {{ $order?->order_ja ?? '（目不明）' }}</div>

                        @foreach ($families as $familyId => $speciesInFamily)
                            @php
                                $family = $speciesInFamily->first()?->family;
                            @endphp
                            <div class="ms-3" data-family-id="{{ $familyId }}">
                                <div class="text-muted">
                                    └─ {{ $family?->family_ja ?? '（科不明）' }}@if ($family?->family)（{{ $family->family }}）@endif
                                </div>
                                <ul class="list-unstyled ms-3 mb-2">
                                    @foreach ($speciesInFamily as $species)
                                        <li>
                                            ・<a href="{{ url('/records/' . $random_id . '_' . $species->id . '/edit') }}"
                                                 target="_blank"
                                                 rel="noopener">{{ $species->species_ja }}@if ($species->species)（{{ $species->species }}）@endif</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </section>
                @endforeach
            </div>
        @endif

        @if (!$locked)
            <p class="mt-4">
                <a href="{{ url('/records/create?literature_id=' . $literature_id) }}"
                   target="_blank"
                   rel="noopener">追加</a>
            </p>
        @endif
    </div>
</x-kaikon::app-layout>
