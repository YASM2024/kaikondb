<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Literatures') }}
    @endslot
  <style>
    /* 文献検索結果のスタイル */
    #component-search-literatures .custom-border{ margin-top: -1px; margin-left: -1px; border: 1px solid gray; word-wrap: break-word;}
    #component-search-literatures .mb-2{margin-bottom:0 !important;}
    #component-search-literatures .literature_title{ font-size: 1.2em;}
    #component-search-literatures .break-word{ overflow-wrap:break-word; word-break:break-all; }
    #component-search-literatures hr { margin: 0.8rem 0;}

    /*検索条件の横線スタイル*/
    #component-search-literatures td{
      border-width: 0 0 1px 0; /* 上下だけ引く */
      border-color: silver;
      border-style: solid;
      padding: 1em 0;        /* セル内側の余白 */
    }
    #component-search-literatures table{border-spacing: 0 0.8em;}
  </style>
  <div id="component-search-literatures" class="container mt-4 py-2">
          <h4 class="ssj my-3 px-3 px-md-0">{{ __('messages.Literatures') }}</h4>
            <noscript>
              <div class="container pt-3 py-2">
                <p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p>
              </div>
            </noscript>
            <div class="container pt-3 py-2">学術出版物、商業誌、同好会誌および図鑑等の単行本の情報を検索できます。</div>



    <div class="mb-3">
      <!-- Tabs -->
      <div class="card-header p-0">
        <ul class="nav nav-tabs" id="searchPostTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active px-3" id="tab-search"
                    data-bs-toggle="tab" data-bs-target="#pane-search"
                    type="button" role="tab" aria-controls="pane-search" aria-selected="true">
              検索
            </button>
          </li>
          @if (Auth::check() && \Kaikon2\Kaikondb\Models\User::fromAppUser(Auth::user())->isModerator())
          <li class="nav-item" role="presentation">
            <a class="nav-link px-3" id="tab-post" href="{{ route('literature.create') }}">
              登録
            </a>
          </li>
          @endif
          <li class="nav-item" role="presentation">
            <button class="nav-link px-3 disabled" id="tab-info"
                    data-bs-toggle="tab" data-bs-target="#pane-info"
                    type="button" role="tab" aria-controls="pane-info" aria-selected="false">
              情報提供
            </button>
          </li>
        </ul>
      </div>

      <div class="">
        <div class="tab-content" id="searchPostTabsContent">

          <div class="tab-pane fade show active" id="pane-search" role="tabpanel" aria-labelledby="tab-search">
              <form id="form" name="search" class="bg-white p-3 mb-3 border-bottom" method="get" action="" target="result">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-8">
                        <label class="form-label mb-1" for="keyword">キーワード</label>
                        <input name="keyword" type="text" class="form-control" placeholder="論文の表題、種名、地名など">
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label mb-1" for="locality">{{__('messages.Taxon')}}</label>
                        <select name="order_id" class="form-select" title="order_id">
                          <option value="">{{ session('locale') == 'en' ? 'select order...' : '目(order)を選択...' }}</option>
                          @foreach ( $orders as $order )
                          <option value="{{ $order->order_id }}">{{ session('locale') == 'en' ? '' : $order->order_ja }} {{ $order->order }}</option>
                          @endforeach
                        </select>

                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1" for="collectedBy">{{__('messages.JournalName')}}</label>
                        <select name="journal_code" class="form-select" title="journal_code">
                          <option value="">{{ session('locale') == 'en' ? 'select journal...' : '雑誌名を選択...' }}</option>
                          @foreach ( $journals as $journal )
                          <option value="{{ $journal->journal_code }}">{{ session('locale') == 'en' ? $journal->journal_name_en : $journal->journal_name_ja }}</option>
                          @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1" for="identifiedBy">{{__('messages.PublishedAt')}}</label>
                        <input name="year" type="text" class="form-control" placeholder="20XX">
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1" for="owner">{{__('messages.Author')}}</label>
                        <input name="author" type="text" class="form-control" placeholder="山梨太郎">
                    </div>

                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button id="searchBtn" class="btn btn-secondary w-100" type="submit">{{ __('kaikon::messages.LiteratureSearch') }}</button>
                        <a id="cancelBtn" class="btn btn-outline-secondary w-100" href="" type="reset">{{ __('kaikon::messages.LiteratureReset') }}</a>
                    </div>
                </div>
              </form>
          </div>
          <div class="tab-pane fade" id="pane-info" role="tabpanel" aria-labelledby="tab-info">
          </div>

        </div>
      </div>
    </div>


    <form class="d-none">
      <label for="httpquery">httpquery</label><input name="httpquery" id="httpquery"></input>
    </form>
    <div id="app" style="text-align: start;"></div>
    <div id="number_of_show"></div>
    <div id="next_page_loader"></div>
  </div>

  @slot('modal')
  <div class="modal fade" id="ModalItemDetail" tabindex="-1" aria-labelledby="ModalLiterature" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg z-3">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            <span id="title">題名</span>
            @if (Auth::check())
            <a id="editLiteratureBtn"
              class="ms-3 text-primary"
              target="_blank"
              rel="noopener"
              href="https://kai-kon.com/database/literatures/f82033ba2672a2d1749a7b7e646715a0ab742a31fe099b090890c975012734e5/edit">
              <i class="bi bi-pencil-square text-primary"></i>
            </a>
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
    <script type="module" src="./js/components/literatures/main.js"></script>
  @endslot
  
</x-kaikon::app-layout>
