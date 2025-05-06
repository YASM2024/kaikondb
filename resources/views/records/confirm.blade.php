<x-kaikon::app-layout>
    <style>
    /* プロジェクト名の高さをナビゲーションと同じにする */
    header h3 {  line-height: 3rem;}
    /* コンテナのカスタマイズ */
    @media (min-width: 768px) {  .container {    max-width: 736px;  }}
    /* アイコン（オンマウスで色づく） */
    .icon-articles, .icon-species { box-sizing: border-box; border: 1px solid #ffffff; fill:#222222;}
    .icon-articles:hover, .icon-species:hover { box-sizing: border-box; border: 1px solid #333399; color:#333399; fill:#333399;}
    .convey-icon-color{ fill: inherit;}
    .st0{ fill:inherit; } .st0:hover{ fill:#333399; }
    .custom-border{
        margin-top: -1px;
        margin-left: -1px;
        border: 1px solid black;
    }
    .mb-2{ margin-bottom:0 !important;}
    </style>

    <div class="container py-2">
        <form name="search" method="post"
        @if( $data['action_type']==='delete' )
        onsubmit="return cancelsubmit();"
        @endif
        >
            <h4 class="my-3 px-3 px-md-0">
                    @if( $data['action_type']==='create' )
                    レコード追加
                    @elseif( $data['action_type']==='edit' )
                    レコード編集
                    @elseif( $data['action_type']==='delete' )
                    レコード削除
                    @endif
            </h4>
        
            @csrf
        
            <p>
        記事ID：
                    @if( $data['action_type']==='create' )
                    （新規）
                    @endif
            <br>
            学名・和名：{{ $data['species'] }}<br>
            分　布：
            @foreach($data['municipalities_array'] as $item)
            {{ $item }}; 
            @endforeach<br>
            関連文献：{{ $data['article_summary'] }}<br>
            ＲＤＢ区分：{{ $data['rdb'] }}<br>
            備　考：{{ $data['memo'] }}</p>

            <input name="record_id" value="" class="d-none">
            <input name="species_id" value="{{ $data['species_id'] }}" class="d-none">
            <input name="article_id" value="{{ $data['article_id'] }}" class="d-none">
            @foreach($data['municipality_ids_array'] as $item)
            <input name="municipality_ids_array[]" value="{{ $item }}" checked class="d-none">
            @endforeach
            <input name="rdb" value="{{ $data['rdb'] }}" class="d-none">
            <input name="memo" value="{{ $data['memo'] }}" class="d-none">
            <input name="verified" value="verified" class="d-none">

            <div class="center-button">
                <button id="back" type="button" class="btn btn-outline-primary" onclick="history.back(); return false;">戻る</button>
                <button id="submit" type="submit" class="btn btn-primary">確定</button>
            </div>

        </form>
    </div>

</x-kaikon::app-layout>