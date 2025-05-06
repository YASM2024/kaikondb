<x-kaikon::app-layout>
    @slot('header')
    開発・ヘルプ
    @endslot
    <style>
    @font-face {
        font-family: 'Anton';
        src: url({{ url('/fonts/Anton/Anton-Regular.ttf')}}) format('truetype');
    }
    </style>
    <div class="container py-2">
        <div class="text-left bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">開発・ヘルプ</h2>

            <div class="h5 mt-3 mb-2">システム情報</div>
            <div class="ps-3 d-inline-flex align-items-center">
                <div style="width: 5em; height: 5em;">
                    <x-kaikon::application-logo class="w-20 h-20 fill-current text-gray-500" />
                </div>
                <div class="py-3 ps-3" style="color: #494949;">
                    <div style="font-size: 3em;font-family: Anton; font-weight: 400;">{{ Kaikon2\Kaikondb\Constants\SystemInfo::SERVICE_NAME }}</div>
                    <p class="h5">v{{ Kaikon2\Kaikondb\Constants\SystemInfo::VERSION }}</p><small> (Released at {{ Kaikon2\Kaikondb\Constants\SystemInfo::RELEASED_AT }})</small>
                </div>
            </div>
            <div class="h5 mt-3 mb-2">公開名称</div>
            <div class="h6 mb-2 ps-3">{{ config('app.name', 'Laravel') }}</div>
            <small class="mb-4 ps-3">※変更したい場合は、ベースディレクトリの.envファイルを書き換えてください。</small>
            
            <div class="h5 mt-3 mb-2">ベースシステム</div>
            <ul>
                <li>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</li>
                <li>{{ Illuminate\Support\Facades\DB::connection()->getDriverName() }}</li>
            </ul>
            
            <div class="h5 mt-3 mb-2">開発リポジトリ</div>
            <div class="mb-4 ps-3"><a href="https://github.io/hogehoge/">https://github.io/hogehoge/</a></div>

            <div class="h5 mt-3 mb-2">使用説明書</div>
            <div class="mb-4 ps-3"><a href="">説明書</a></div>
            
        </div>
    </div>
</x-kaikon::app-layout>