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
        <form id="{{$data['action_type']}}_form" method="post"
        @if( $data['action_type'] ==='delete' )
        onsubmit="return cancelsubmit();"
        @endif
        >
            <h4 class="my-3 px-3 px-md-0">
                    @if( $data['action_type'] ==='create' )
                    文献追加
                    @elseif( $data['action_type'] ==='edit' )
                    文献編集
                    @elseif( $data['action_type'] ==='delete' )
                    文献削除
                    @endif
            </h4>
        
            @csrf
        
            <p>
        記事ID：
                    @if( $data['action_type'] ==='create' )
                    （新規）
                    @else
                    {{ @$data['id'] }}
                    @endif
            <br>
            著　者：{{ $data['author'] }}<br>
            (英語)：{{ $data['author_en'] }}<br>
            発行年：{{ $data['year'] }}<br>
            表　題：{{ $data['title'] }}<br>
            (英語)：{{ $data['title_en'] }}<br>
            雑誌名：{{ $data['journal_code'] }}<br>
            出版者：{{ $data['publisher'] }}<br>
            巻号数：{{ $data['vol_no'] }}<br>
            頁　数：{{ $data['page'] }}<br>
            カテゴリ：{{ implode(',', $data['order_ids_array']) }}<br>
            リンク：{{ $data['link'] }}<br>
            コメント：{{ $data['comment'] }}<br>
            備　考：{{ $data['memo1'] }}</p>

            <input name="id" value="{{ @$data['id'] }}" class="d-none">
            <input name="author" value="{{ $data['author'] }}" class="d-none">
            <input name="author_en" value="{{ $data['author_en'] }}" class="d-none">
            <input name="year" value="{{ $data['year'] }}" class="d-none">
            <input name="title" value="{{ $data['title'] }}" class="d-none">
            <input name="title_en" value="{{ $data['title_en'] }}" class="d-none">
            <input name="journal_code" value="{{ $data['journal_code'] }}" class="d-none">
            <input name="publisher" value="{{ $data['publisher'] }}" class="d-none">
            <input name="vol_no" value="{{ $data['vol_no'] }}" class="d-none">
            <input name="page" value="{{ $data['page'] }}" class="d-none">
            @foreach($data['order_ids_array'] as $item)
            <input name="order_ids_array[]" value="{{ $item }}" checked class="d-none">
            @endforeach
            <input name="link" value="{{ $data['link'] }}" class="d-none">
            <input name="memo1" value="{{ $data['memo1'] }}" class="d-none">
            <input name="inventory" value="" class="d-none">
            <input name="comment" value="{{ $data['comment'] }}" class="d-none">
            <input name="verified" value="verified" class="d-none">

            <div class="center-button">
                <button id="back" type="button" class="btn btn-outline-primary" onclick="history.back(); return false;">戻る</button>
                <button id="submit" type="submit" class="btn btn-primary">確定</button>
            </div>

        </form>
    </div>


    @slot('scripts')
    <script>
    delete_form.addEventListener('submit', function(){
        res = confirm("本当に<< 削除 >>してよいですか？")
        if(!res){return false;}
    })
    </script>
    @endslot

</x-kaikon::app-layout>