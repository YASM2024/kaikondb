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

    /** textarea 高さ可変対応 */
    .FlexTextarea {
      position: relative;
      font-size: 1rem;
      line-height: 1.8;
    }

    .FlexTextarea__dummy {
      overflow: hidden;
      visibility: hidden;
      box-sizing: border-box;
      padding: 5px 15px;
      min-height: 120px;
      white-space: pre-wrap;
      word-wrap: break-word;
      overflow-wrap: break-word;
      border: 1px solid;
    }

    .FlexTextarea__textarea {
      position: absolute;
      top: 0;
      left: 0;
      box-sizing: border-box;
      padding: 5px 15px;
      width: 100%;
      height: 100%;
      background-color: #ffffff;
      border: 1px solid #b6c3c6;
      border-radius: 4px;
      color: inherit;
      font: inherit;
      letter-spacing: inherit;
      resize: none;
    }
    .FlexTextarea__textarea:focus {
      box-shadow: 0 0 0 4px rgba(35, 167, 195, 0.3);
      outline: 0;
    }
    </style>

    <!-- アイコンの設定 -->
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">

      <symbol id="x" viewBox="0 0 512 512"><style>.x{fill:#4B4B4B;}</style>
        <g>
          <polygon class="x" points="512,89.75 422.256,0.005 256.004,166.256 89.754,0.005 0,89.75 166.255,256 0,422.25 89.754,511.995 
            256.004,345.745 422.26,511.995 512,422.25 345.744,256"></polygon>
        </g>
      </symbol>

      <symbol id="upload" viewBox="0 0 512 512"><style>.uploadicon{fill:#4B4B4B;}</style>
        <g>
          <path class="uploadicon" d="M427.258,244.249c0.204-2.604,0.338-5.228,0.338-7.885c0-55.233-44.775-100.008-100.008-100.008
            c-17.021,0-33.042,4.264-47.072,11.764c-15.136-42.633-55.81-73.172-103.633-73.172c-60.729,0-109.96,49.231-109.96,109.96
            c0,11.416,1.741,22.425,4.97,32.778C29.804,234.254,0,275.238,0,323.21c0,62.627,50.769,113.396,113.396,113.396h292.642
            c3.021,0.284,6.079,0.445,9.175,0.445c53.454,0,96.788-43.333,96.788-96.788C512,290.891,475.024,250.183,427.258,244.249z
            M311.709,296.227h-20.452c-6.044,0-10.989,4.945-10.989,10.99v58.074c0,6.044-4.946,10.99-10.989,10.99h-26.558
            c-6.044,0-10.989-4.946-10.989-10.99v-58.074c0-6.044-4.945-10.99-10.989-10.99h-20.452c-6.044,0-8-3.94-4.347-8.755l53.414-70.405
            c3.652-4.816,9.631-4.816,13.284,0l53.414,70.405C319.709,292.288,317.753,296.227,311.709,296.227z"></path>
        </g>
      </symbol>

    </svg>

    <div class="container mt-4 py-2">
      <h4 class="my-3 px-0 mx-2">
      @if( $action_type ==='create' )
      文献情報追加
      @elseif( $action_type ==='edit' )
      文献情報編集
      @elseif( $action_type ==='delete' )
      文献情報削除
      @endif
      </h4>
      <form action="" method="post" id="main">
        @csrf
        <input class="d-none" type="text" name="id" value="{{ @$article->id }}">
        <input class="d-none" type="text" name="entered" value="1">
        <input name="random_id" value="{{ @$article->random_id }}" class="d-none">
        <div class="row mb-0">
          <label for="author" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-center">
              <span>著者</span>
              <a data-bs-toggle="collapse" href="#author_en" role="button" aria-expanded="false" aria-controls="author_en" class="btn btn-sm btn-secondary collapsed">
              +English
              </a>
          </label>
          <div class="col-sm-9 custom-border col-form-label"><input type="text" name="author" class="form-control
          @if($errors->first('author'))
          bg-danger bg-opacity-25
          @endif
          " value="{{ old('author', @$article->author) }}"></div>
        </div>


        <div id="author_en" class="row mb-0 collapse">
          <label for="author_en" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-center">　</label>
          <div class="col-sm-9 custom-border col-form-label"><input type="text" name="author_en" class="form-control
          @if($errors->first('author_en'))
          bg-danger bg-opacity-25
          @endif
          " value="{{ old('author_en', @$article->author_en) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="year" class="col-sm-3 custom-border col-form-label text-danger">発行年</label>
          <div class="col-sm-9 custom-border col-form-label"><input type="text" name="year" class="form-control
          @if($errors->first('year'))
          bg-danger bg-opacity-25
          @endif
          " value="{{ old('year', @$article->year) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="title" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-start">
              <span>表題</span>
              <a data-bs-toggle="collapse" href="#title_en" role="button" aria-expanded="false" aria-controls="author_en" class="btn btn-sm btn-secondary collapsed">
              +English
              </a>
          </label>
          <div class="col-sm-9 custom-border col-form-label">
            <div class="FlexTextarea">
              <div class="FlexTextarea__dummy" aria-hidden="true"></div>
              <textarea name="title" id="FlexTextarea_1" class="FlexTextarea__textarea overflow-hidden form-control
              @if($errors->first('title'))
              bg-danger bg-opacity-25
              @endif
              " >{{ old('title', @$article->title) }}</textarea>
            </div>
          </div>
        </div>

        <div id="title_en" class="row mb-0 collapse">
          <label for="title_en" class="col-sm-3 custom-border col-form-label">　</label>
          <div class="col-sm-9 custom-border col-form-label">
            <div class="FlexTextarea">
              <div class="FlexTextarea__dummy" aria-hidden="true"></div>
              <textarea name="title_en" id="FlexTextarea_1" class="FlexTextarea__textarea overflow-hidden form-control
              @if($errors->first('title_en'))
              bg-danger bg-opacity-25
              @endif
              " >{{ old('title_en', @$article->title_en) }}</textarea>
            </div>
          </div>
        </div>

        <div class="row mb-0">
          <label for="journal" class="col-sm-3 custom-border col-form-label text-danger">雑誌名</label>
          <div class="col-sm-9 custom-border col-form-label">
            <select class="form-select
            @if($errors->first('journal_code'))
            bg-danger bg-opacity-25
            @endif
            " id="exampleFormSelect1" name="journal_code">
              <option value="" class="placeholder">雑誌名を選択</option>
              @foreach($journals as $journal)
              <option value="{{$journal->journal_code}}"
              @if( old('journal_code', @$article->journal_code) == $journal->journal_code )
              selected
              @endif
              >{{$journal->journal_name_ja}}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="row mb-0">
          <label for="publisher" class="col-sm-3 custom-border col-form-label">出版者</label>
          <div class="col-sm-9 custom-border col-form-label"><input name="publisher" type="text" class="form-control
          @if($errors->first('publisher'))
          bg-danger bg-opacity-25
          @endif
          " value="{{ old('publisher', @$article->publisher) }}">{{$errors->first('publisher')}}</div>
        </div>
      
        <div class="row mb-0">
          <label for="vol_no" class="col-sm-3 custom-border col-form-label">巻号数</label>
          <div class="col-sm-9 custom-border col-form-label"><input name="vol_no" type="text" class="form-control" value="{{ old('vol_no', @$article->vol_no) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="page" class="col-sm-3 custom-border col-form-label">頁数</label>
          <div class="col-sm-9 custom-border col-form-label"><input name="page" type="text" class="form-control" value="{{ old('page', @$article->page) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="order_names" class="col-sm-3 custom-border col-form-label">カテゴリ</label>
          <div class="col-sm-9 custom-border col-form-label" id="order_ids_array"></div>
        </div>
      
        <div class="row mb-0">
          <label for="link" class="col-sm-3 custom-border col-form-label">リンク</label>
          <div class="col-sm-9 custom-border col-form-label"><input name="link" type="text" class="form-control
          @if($errors->first('link'))
          bg-danger bg-opacity-25
          @endif
          " value="{{ old('link', @$article->link) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="comment" class="col-sm-3 custom-border col-form-label">コメント</label>
          <div class="col-sm-9 custom-border col-form-label">
            <div class="FlexTextarea">
              <div class="FlexTextarea__dummy" aria-hidden="true"></div>
              <textarea name="comment" id="FlexTextarea_2" class="FlexTextarea__textarea overflow-hidden form-control
              @if($errors->first('comment'))
              bg-danger bg-opacity-25
              @endif
              " >{{ old('comment', @$article->comment) }}</textarea>
            </div>
          </div>
        </div>

        <div class="row mb-0">
          <label for="memo1" class="col-sm-3 custom-border col-form-label">備考</label>
          <div class="col-sm-9 custom-border col-form-label">
            <div class="FlexTextarea">
              <div class="FlexTextarea__dummy" aria-hidden="true"></div>
              <textarea name="memo1" id="FlexTextarea_3" class="FlexTextarea__textarea overflow-hidden form-control
              @if($errors->first('memo1'))
              bg-danger bg-opacity-25
              @endif
              " >{{ old('memo1', @$article->memo1) }}</textarea>
            </div>
          </div>
        </div>
      </form>
        @if( $action_type ==='edit' || $action_type ==='delete')
        <div class="row mb-0">
          <label for="documents" class="col-sm-3 custom-border col-form-label">文献</label>
          <div class="col-sm-9 custom-border col-form-label" id="documents">
            @foreach($documents as $item)
            <div class="m-2">
              <a href="../documents/{{ $item->file_name }}" target="_blank" rel="noopener"><span class="badge bg-danger me-2">PDF</span></a>
              <input class="document_name_input" data-docid="{{$item->id}}" name="document_name" type="text" value="{{$item->display_title}}"></input>
              <a class="me-3" style="cursor: pointer;" onclick="deleteDocument('{{ $item->file_name }}');">削除</a>
            </div>
            @endforeach
            <iframe id="iframe" name="iframe" class="d-none"></iframe>
            <form action="../{{$article->id}}/documents/upload" id="file_upload_form" class="d-inline ms-3" method="post" target="iframe" enctype="multipart/form-data">
              @csrf
              <label class="d-inline">
                <span style="cursor: pointer;">
                  <a target="_blank" rel="noopener">
                    <svg class="bi ms-1 me-2" width="2.4em" height="2.4em"><use xlink:href="#upload"></use></svg>
                  </a>
                  <input type="file" id="document_file" name="document_file" form="file_upload_form" style="display:none">
                </span>
              </label>
            </form>
          </div>
        </div>
        @endif
        <br>

      <div class="center-button">
        <button class="btn btn-primary" form="main" type="submit">確認</button>
      </div>

    </div>
  

    @slot('scripts')
    <script>
    function flexTextarea(el) {
      const dummy = el.querySelector('.FlexTextarea__dummy')
      el.querySelector('.FlexTextarea__textarea').addEventListener('input', e => {
        dummy.textContent = e.target.value + '\u200b'
      })
      el.querySelector('.FlexTextarea__textarea').addEventListener('focus', e => {
        dummy.textContent = e.target.value + '\u200b'
      })
    }
    document.querySelectorAll('.FlexTextarea').forEach(flexTextarea)

    function getOrderListButton(insertElementId, selected_order_ids = [] ){
        let checkgroup = '';
        let url = "{{route('orderMaster')}}";
        if(selected_order_ids === null){ selected_order_ids = []; }
        selected_order_ids = selected_order_ids.map( str => parseInt(str, 10) );
        fetch(url)
        .then(function (response) {
            return response.json();
        })
        .then(function (jsonx) { 
            for (let i = 0; i < jsonx.length; i++) {
                checkgroup = checkgroup + '<span class="d-inline-block m-1"><input type="checkbox" class="btn-check" id="btn-check-' + jsonx[i].id + '" autocomplete="off" name="order_ids_array[]" value="' + jsonx[i].id + '"';
                if(selected_order_ids.includes(jsonx[i].id)){checkgroup = checkgroup + ' checked';}
                checkgroup = checkgroup + '><label for="btn-check-' + jsonx[i].id + '" class="btn btn-outline-primary btn-sm">' + jsonx[i].order + '</label></span>';
            }
        })
        .then(function (){
            document.getElementById(insertElementId).innerHTML = checkgroup;
        })
    }

    @if( $action_type ==='edit')
    const docEditUrl = '{{route("document.edit", ["id" => $article->random_id])}}';
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const documentNameInputs = document.querySelectorAll('.document_name_input');
    documentNameInputs.forEach(documentNameInput => {
        let documentId = documentNameInput.dataset.docid;
        documentNameInput.addEventListener("blur", (event) => {
            let documentName = documentNameInput.value; // ここで再度取得
            if(documentName.length){
                let data = { document_id: documentId, document_name: documentName };
                fetch(docEditUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data),
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                })
                .catch((error) => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        });
    });
    const file_upload_form = document.getElementById('file_upload_form');
    document_file.addEventListener("change", (event) => {
        if(document_file.value.length){
            file_upload_form.submit();
            alert('アップロードが完了しました。');
        }
    });
    @endif

    window.addEventListener('DOMContentLoaded', function() {
        getOrderListButton('order_ids_array', {!! json_encode(old('order_ids_array[]', explode (";", @$article->order_ids))) !!});
    })

    function deleteDocument(filename){
        res = confirm(filename + "：本当に削除してよいですか？削除すると元に戻せません。")
        if(!res){return false;}
        let url = '../documents/' + filename + '/delete';
        //location.href = url;
        fetch(url).then(function (response) { alert('削除完了しました。')})
    }

    </script>
    @endslot
</x-kaikon::app-layout>