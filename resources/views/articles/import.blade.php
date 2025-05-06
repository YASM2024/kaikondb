<x-kaikon::app-layout>
    
    <style>
    /* コンテナのカスタマイズ */
    @media (min-width: 768px) {  .container {    max-width: 736px;  }}
    /* アイコン（オンマウスで色づく） */
    .custom-border{
        margin-top: -1px;
        margin-left: -1px;
        border: 1px solid black;
    }
    .mb-2{ margin-bottom:0 !important;}
    .orderBtn, .familyBtn, .speciesBtn{ font-size:0.9em ; cursor: pointer;}
    .familyBtn, .speciesBtn { font-size: 0.76em ;}
    </style>

    <div class="container py-2">
        <h4 class="my-3 px-3 px-md-0">文献一括アップロード</h4>
        <p>取込みデータは、article_id、author、year、title、journal_id、publisher、vol、no、page_begin、page_end、order_ids、link、comment、memo1を含む14列のCSVデータとして下さい。
        文字コードはUTF-8、改行コードはLFとして下さい。
        <a href="">取込フォーマットはこちら</a><br>
        レコードの追加の際は、article_id列は空白としてください。<br>
        <span class="text-danger">現在、本ページではレコードの追加のみが可能です。上書きや削除はできません。</span>
        </p>
        <form action="" method="post" enctype="multipart/form-data" class="input-group mb-3">
            <input type="file" class="form-control" id="formFile" name="csvFile">
            <button type="submit" class="btn btn-primary">送　信</button>
        </form>
        <ul class="mt-3 mx-3">
            <li><a href="./download">現在の登録内容</a></li>
        </ul>
    </div>

</x-kaikon::app-layout>