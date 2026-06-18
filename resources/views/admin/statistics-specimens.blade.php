<x-kaikon::app-layout>
    <x-slot:header>{{ $pageTitle }}</x-slot:header>

    <div class="container py-4">
        <h4 class="mb-4">{{ $pageTitle }}</h4>

        <p class="text-muted">標本に関する統計は現在ありません。</p>

        <p class="mt-4"><a href="{{ route('dashboard') }}">管理者メニューへ戻る</a></p>
    </div>
</x-kaikon::app-layout>
