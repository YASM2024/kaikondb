<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Literatures') }}
    @endslot
  <style>
    /* 文献検索結果のスタイル */
    #component-search-articles .custom-border{ margin-top: -1px; margin-left: -1px; border: 1px solid gray; word-wrap: break-word;}
    #component-search-articles .mb-2{margin-bottom:0 !important;}
    #component-search-articles .article_title{ font-size: 1.2em;}
    #component-search-articles .break-word{ overflow-wrap:break-word; word-break:break-all; }
    #component-search-articles hr { margin: 0.8rem 0;}

    /*検索条件の横線スタイル*/
    #component-search-articles td{
      border-width: 0 0 1px 0; /* 上下だけ引く */
      border-color: silver;
      border-style: solid;
      padding: 1em 0;        /* セル内側の余白 */
    }
    #component-search-articles table{border-spacing: 0 0.8em;}
  </style>
  <div id="component-search-articles" class="container mt-4 py-2">
    <form id="form" name="search" action="" method="get" target="result">
          <h4 class="ssj my-3 px-3 px-md-0">{{ __('messages.Literatures') }}</h4>
            <noscript>
              <div class="container pt-3 py-2">
                <p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p>
              </div>
            </noscript>
            <div class="container pt-3 py-2">学術出版物、商業誌、同好会誌および図鑑等の単行本の情報を検索できます。</div>
            <div class="container text-center py-2">
          <table style="width:100%; ">
            <tbody>
              <tr>
                <td style="width: 5.2em; text-align-last: justify; border-top: 1px solid silver; font-weight:440; letter-spacing: 0;">{{__('messages.Keywords')}}</td>
                <td style="border-top: 1px solid silver;">
                  <div class="ms-3 me-1">
                    <input name="keyword" type="text" class="form-control" placeholder="論文の表題、種名、地名など">
                  </div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.Taxon')}}</td>
                <td>
                  <div class="ms-3 me-1">
                    <select name="order_id" class="form-select" title="order_id">
                      <option value="">{{ session('locale') == 'en' ? 'select order...' : '目(order)を選択...' }}</option>
                      @foreach ( $orders as $order )
                      <option value="{{ $order->order_id }}">{{ session('locale') == 'en' ? '' : $order->order_ja }} {{ $order->order }}</option>
                      @endforeach
                    </select>
                  </div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.JournalName')}}</td>
                <td>
                  <div class="ms-3 me-1">
                    <select name="journal_code" class="form-select" title="journal_code">
                      <option value="">{{ session('locale') == 'en' ? 'select journal...' : '雑誌名を選択...' }}</option>
                      @foreach ( $journals as $journal )
                      <option value="{{ $journal->journal_code }}">{{ session('locale') == 'en' ? $journal->journal_name_en : $journal->journal_name_ja }}</option>
                      @endforeach
                    </select>
                  </div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.PublishedAt')}}</td>
                <td>
                  <div class="ms-3 me-1"><input name="year" type="text" class="form-control" placeholder="20XX"></div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.Author')}}</td>
                <td>
                  <div class="ms-3 me-1"><input name="author" type="text" class="form-control" placeholder="山梨太郎"></div>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="text-center mt-4 mb-4">
            <button id="searchBtn" class="btn btn-secondary mb-2">{{__('messages.ArticleSearch')}}</button>　　
            <button class="btn btn-secondary mb-2" type="reset">{{__('messages.ArticleReset')}}</button>
          </div>
        </div>
    </form>
    <form class="d-none">
      <label for="httpquery">httpquery</label><input name="httpquery" id="httpquery"></input>
    </form>
    <div id="app" style="text-align: start;"></div>
    <div id="number_of_show"></div>
    <div id="next_page_loader"></div>
  </div>





  @slot('modal')
  <div class="modal fade" id="ModalItemDetail" tabindex="-1" aria-labelledby="ModalArticle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg z-3">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            <span id="title">題名</span>
            @if (Auth::check())
            <div class="d-inline row" style="margin-right: 12px;">
              <a id="editArticleBtn" target="_blank" rel="noopener">
                <svg class="bi ms-1 me-2" width="1.2em" height="1.2em"><use xlink:href="./svg/article_symbols.svg#edit"></use></svg>
              </a>
            </div>
            @endif
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>
          
        <div class="modal-body">
          <div class="mb-3">
            <table class="table mb-0">
              <tbody>
                <tr><th colspan="2">詳細情報</th></tr>
                <tr><td style="width:5em !important;">{{__('messages.Author')}}</td><td id="author" class="text-break"></td></tr>
                <tr><td>{{__('messages.PublishedAt')}}</td><td id="year"></td></tr>
                <tr><td>{{__('messages.JournalName')}}</td><td id="journal_name" class="text-break"></td></tr>
                <tr><td>{{__('messages.VolumeNumber')}}</td><td id="volno"></td></tr>
                <tr><td>{{__('messages.Page')}}</td><td id="page"></td></tr>
                <tr><td>カテゴリ</td><td id="category" class="text-break"></td></tr>
                <tr><td>{{__('messages.Link')}}</td><td id="link" class="text-break"></td></tr>
                <tr><td>{{__('messages.Comment')}}</td><td id="comment" class="text-break"></td></tr>
                @if (Auth::check())
                <tr><td>ファイル</td>
                  <td class="break-word"><span id="fileInfo"></span></td>
                </tr>
                <tr>
                  <td>種データ</td>
                  <td class="break-word">
                    <div class="text-left">
                      <div class="d-inline row" style="margin-left: 12px; margin-right: 12px;">
                        <a id="openSpeciesListBtn" class="col btn btn-secondary d-inline" target="_blank" rel="noopener">リスト</a>
                        <div id="inputLockBtn" class="col btn btn-danger d-inline">完了</div>
                        <div id="unLockBtn" class="col btn btn-danger d-inline">ロック解除</div>
                      </div>
                    </div>
                  </td>
                </tr>
                @endif

              </tbody>
            </table>
            <div class="text-end">
              <small>登録日：<span id="created_at">[yyyy/mm/dd]</span>　</small>
              @if (Auth::check())
              <small>登録者：<span id="username">[username]</span></small>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endslot

  
  @slot('scripts')
    <script src ="./js/nextPageLoader.js"></script>
    <script>
      window.home_url = `{{url('/')}}`;
      window.authenticated = {{ Auth::check() ? 'true' : 'false' }};
      window.searchResult = `{{ __('messages.SearchResult') }}`;
    </script>
    <script type="module" src="./js/components/articles/main.js"></script>
  @endslot
  
</x-kaikon::app-layout>