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
                @if (isset($roles) && is_array($roles) && (in_array('Administrator', $roles) || in_array('Moderator', $roles)))
                @if(config('kaikon.LITERATURES')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">文献データ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="text-muted d-flex align-items-start mb-1"><a href="{{route('literature.create')}}">データ追加</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="">データ入出力／チェック</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="">履歴・統計</a></li>
                    </ul>
                </div>
                @endif

                @if (config('kaikon.SPECIMENS')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">標本情報管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="{{ route('specimen.create') }}">データ追加</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="">データ入出力／チェック</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="">履歴・統計</a></li>
                    </ul>
                </div>
                @endif

                @if(config('kaikon.INVENTORY')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">分布データ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="text-muted d-flex align-items-start mb-1"><a href="{{route('record.create')}}">データ追加</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="">データ入出力／チェック</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="">履歴・統計</a></li>
                    </ul>
                </div>
                @endif
                @endif

                @if(config('kaikon.PHOTOS')==1)
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">昆虫写真管理</h5>
                    <ul class="icon-list ps-3">
                        @if (isset($roles) && is_array($roles) && in_array('Administrator', $roles))
                        <li class="d-flex align-items-start mb-1"><a href="./admin/photos">投稿承認・取下げ</a></li>
                        @endif
                        <li class="d-flex align-items-start mb-1"><a href="./photos/download">メタデータダウンロード</a></li>
                    </ul>
                </div>
                @endif

                @if (isset($roles) && is_array($roles) && ( in_array('Administrator', $roles) || in_array('Developer', $roles)  ))
                <!-- ゆくゆくは moderator に開放する予定 -->
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">マスタ管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="./master/taxon">分類マスタ(目/科/種)</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="./master/municipality/show">市町村マスタ</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="./master/journal/show">雑誌情報マスタ</a></li>
                    </ul>
                </div>
                @endif

                @if (isset($roles) && is_array($roles) && ( in_array('Administrator', $roles) || in_array('Developer', $roles) ))
                <div class="col-sm-6 col-md-4">
                    <h5 class="my-1 px-0 ps-3 py-3 me-3 bg-secondary text-light">システム管理</h5>
                    <ul class="icon-list ps-3">
                        <li class="d-flex align-items-start mb-1"><a href="{{route('expanded_page.index')}}">拡張ページ管理</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="{{route('admin.showUsers')}}">ユーザ管理</a></li>
                        <li class="d-flex align-items-start mb-1"><a href="{{route('admin.system.status')}}">ジョブ／リスナー起動状況</a></li>
                    </ul>
                </div>
                @endif
                @endif

            </div>

        </div>
    </div>
</x-kaikon::app-layout>
