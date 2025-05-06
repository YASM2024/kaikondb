@php
    $roles = \Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->roles->pluck('name')->toArray();
@endphp
<x-kaikon::app-layout>
    @slot('header')
    管理者メニュー
    @endslot
    <div class="container py-2">
        <div class="text-left bg-light p-3 p-sm-5 mb-4 rounded">
            <h2 class="mb-4">管理者メニュー</h2>

            <div class="row">
                @if (isset($roles) && is_null($roles))
                <div class="col border-top border-left">
                <!-- ロール未設定 -->
                <a href="./">ユーザ認証を行ってください。</a>
                </div>
                @else
                <!-- ロール設定済 -->
                @if (isset($roles) && is_array($roles) && (in_array('Administrator', $roles) || in_array('Coleoptera-Admin', $roles) || in_array('Lepidoptera-Admin', $roles)))
                @if(env('LITERATURES')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">文献データ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="text-muted d-flex align-items-start mb-1"><a href="{{route('article.create')}}">データ追加</a></li>
                        <li class="text-muted d-flex align-items-start mb-1"><a href="./articles/create">データ編集・削除</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="{{route('article.import')}}">データ一括管理</a></li>
                    </ul>
                </div>
                @endif

                @if (env('SPECIMENS')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">標本情報管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="./specimens/download">データ追加</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="./specimens/download">データ編集・削除</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="./specimens/download">データ一括管理</a></li>
                    </ul>
                </div>
                @endif

                @if(env('INVENTORY')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">分布データ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="text-muted d-flex align-items-start mb-1"><a href="{{route('record.create')}}">データ追加</a></li>
                        <li class="text-muted d-flex align-items-start mb-1"><a href="./records/create">データ編集・削除</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="{{route('record.import')}}">データ一括管理</a></li>
                    </ul>
                </div>
                @endif
                @endif

                @if(env('PHOTOS')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">昆虫写真管理</h5>
                    <ul class="icon-list ps-3">
                        @if (isset($roles) && is_array($roles) && in_array('Administrator', $roles))
                        <li class="d-flex align-items-start mb-1"><a href="./photos/admin">投稿承認・取下げ</a></li>
                        @endif
                        <li class="d-flex align-items-start mb-1"><a href="./photos/download">メタデータダウンロード</a></li>
                    </ul>
                </div>
                @endif

                @if (isset($roles) && is_array($roles) && in_array('Administrator', $roles))
                <!-- ゆくゆくは moderator に開放する予定 -->
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">マスタ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="./master/taxon">分類マスタ(目/科/種)</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="./master/municipality/show">市町村マスタ</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="./master/journal/show">雑誌マスタ</a></li>
                    </ul>
                </div>
                @endif

                @if (isset($roles) && is_array($roles) && in_array('Administrator', $roles))
                
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">ページ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="{{route('expanded_page.index')}}">運営情報管理</a></li>
                    </ul>
                </div>

                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">システム管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="{{route('admin.showUsers')}}">ユーザ管理</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="{{route('admin.backup')}}">バックアップ</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="{{route('admin.developers')}}">開発・ヘルプ</a></li>
                    </ul>
                </div>
                @endif
                @endif

            </div>

        </div>
    </div>
</x-kaikon::app-layout>
