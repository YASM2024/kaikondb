<x-kaikon::app-layout>
    @slot('header')
    文献情報編集
    @endslot
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
        <h4 class="my-3 px-3 px-md-0">
            @if( $data['action_type']==='create' )
            登録完了
            @elseif( $data['action_type']==='edit' )
            編集完了
            @elseif( $data['action_type']==='delete' )
            削除完了
            @endif
        </h4>

        @csrf

        <p>記事ID：
            @if( $data['action_type']==='create' )
            （取得ID）
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

        <div class="center-button">
            <button type="button" class="btn btn-primary mx-2" onClick="window.location = location.href;">続けて登録</button>
            <a href="{{url('admin')}}" id="back" type="button" class="btn btn-outline-primary mx-2">管理メニュー</a>
        </div>

    </div>

    @slot('scripts')
    <script>
    function getOrderListButton(insertElementId, selected_order_ids = ''){
        let checkgroup = '';
        let selected_order_ids_arr = selected_order_ids.split(';');
        let url = '{{ route("orderMaster") }}';
        fetch(url)
        .then(function (response) {
            return response.json();
        })
        .then(function (jsonx) { 
            for (let i = 0; i < jsonx.length; i++) {
                checkgroup = checkgroup + '<span class="d-inline-block m-1"><input type="checkbox" class="btn-check" id="btn-check-' + jsonx[i].id + '" autocomplete="off" name="order_ids_array[]" value="' + jsonx[i].order_id + '"';
                if(selected_order_ids_arr.includes(jsonx[i].id)){checkgroup = checkgroup + ' checked';}
                checkgroup = checkgroup + '><label for="btn-check-' + jsonx[i].id + '" class="btn btn-outline-primary btn-sm">' + jsonx[i].order + '</label></span>';
            }
        })
        .then(function (){
            document.getElementById(insertElementId).innerHTML = checkgroup;
        })
    }
    window.addEventListener('DOMContentLoaded', function() {
        getOrderListButton('order_ids_input', "");
    })
    </script>
    @endslot
</x-kaikon::app-layout>