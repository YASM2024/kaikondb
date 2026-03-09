<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Inventory') }}
    @endslot
  <style>
      <style>
      /* プロジェクト名の高さをナビゲーションと同じにする */
      #component-search-species header h3 {  line-height: 3rem;}
      /* コンテナのカスタマイズ */
      @media (min-width: 768px) {
        #component-search-species .container { max-width: 736px;  }
      }
      /* アイコン（オンマウスで色づく） */
      #component-search-species .icon-articles, 
      #component-search-species .icon-species { box-sizing: border-box; border: 1px solid #ffffff; fill:#222222;}
      #component-search-species .icon-articles:hover, 
      #component-search-species .icon-species:hover { box-sizing: border-box; border: 1px solid #333399; color:#333399; fill:#333399;}
      #component-search-species .convey-icon-color{ fill: inherit;}
      #component-search-species .st0{ fill:inherit; }
      #component-search-species .st0:hover{ fill:#333399; }
      #component-search-species .item {
        color: #444444;
        text-decoration: none;
        letter-spacing: -0.2px;
        padding: 0.4em 0;
      }
      #component-search-species div.zebra > .item:nth-child(2n){
        background-color: white;
      }
      #component-search-species div.zebra > .item:nth-child(2n+1) {
        background-color: #e0e0e0;
      }
      #component-search-species .item:hover {
        cursor: pointer;
        text-decoration: none;
        font-weight: bold;
        background-color: #d0e2be !important;
        letter-spacing: -0.2px;
      }

      ol.list_parentheses {
        padding-left: 0; /* ol自体の余白をなくす */
      }

      ol.list_parentheses li {
        list-style-type: none;
        counter-increment: cnt;
        position: relative;
        left: 0; /* 左にずらす。必要に応じて調整 */
      }

      ol.list_parentheses li:before {
        content: counter(cnt)") ";
        position: absolute;
        left: 0; /* 番号をさらに左に配置 */
      }

  </style>
<!-- アイコンの設定 -->
<svg xmlns="http://www.w3.org/2000/svg" class="d-none">
  <symbol id="edit" viewBox="0 0 512 512">
    <g>
      <path class="st0" d="M500.11,71.074l-59.202-59.181c-15.856-15.848-41.53-15.862-57.386-0.014l-38.378,38.378L57.252,338.185
        c-7.775,7.775-13.721,17.158-17.44,27.498L1.799,471.479c-3.957,11.032-1.206,23.36,7.092,31.656
        c8.288,8.288,20.627,11.046,31.662,7.075l105.774-38.024c10.339-3.714,19.729-9.674,27.501-17.435l277.883-277.893l0.006,0.014
        l10.028-10.048l38.364-38.37l0.014-0.021C515.91,112.598,515.965,86.943,500.11,71.074z M77.321,358.254L363.814,71.732
        l33.208,33.208L107.66,394.296l-33.035-33.028C75.491,360.236,76.364,359.209,77.321,358.254z M136.734,445.472L69.33,469.699
        l-27.019-27.02l24.223-67.392c0.17-0.492,0.412-0.957,0.602-1.442l71.028,71.024C137.68,445.064,137.218,445.299,136.734,445.472z
        M153.757,434.682c-0.956,0.95-1.982,1.83-3.014,2.696l-33.045-33.042l289.359-289.362l33.194,33.201L153.757,434.682z
        M480.034,108.384l-28.322,28.336l-1.42,1.421l-76.443-76.442l29.743-29.75c4.768-4.733,12.474-4.74,17.248,0.014l59.195,59.174
        c4.761,4.754,4.767,12.46-0.021,17.269L480.034,108.384z"></path>
    </g>
  </symbol>
</svg>

  <!-- /アイコンの設定 -->
  <div id="component-search-species" class="container mt-4 py-2">
    <h4 class="ssj my-3 px-3 px-md-0">{{ __('messages.Inventory') }}</h4>
          <p>文献にて正式に記録された昆虫を分類に基づき整理し、検索できるようにしています。</p>
          <a href="{{ url('/summary') }}"><p>目・科の種数一覧</p></a>

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
          <li class="nav-item" role="presentation">
            <button class="nav-link px-3 disabled" id="tab-post"
                    data-bs-toggle="tab" data-bs-target="#pane-post"
                    type="button" role="tab" aria-controls="pane-post" aria-selected="false">
              登録
            </button>
          </li>
        </ul>
      </div>

      <div class="">
        <div class="tab-content" id="searchPostTabsContent">

          <div class="tab-pane fade show active" id="pane-search" role="tabpanel" aria-labelledby="tab-search">
              <form id="form" name="search" class="bg-white p-3 mb-3 border-bottom" method="get" action="" target="result">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-6">
                        <label class="form-label mb-1" for="keyword">キーワード</label>
                        <input id="keyword" name="keyword" type="text" class="form-control" placeholder="キーワード"></input>
                    </div>

                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1" for="literature">文献</label>
                        <input id="literature" name="literature" type="text" class="form-control" placeholder="文献ID" disabled></input>
                    </div>

                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button id="searchBtn" class="btn btn-secondary w-100" type="button">{{__('messages.ArticleSearch')}}</button>
                        <a id="cancelBtn" class="btn btn-outline-secondary w-100" href="" type="reset">{{__('messages.ArticleReset')}}</a>
                    </div>
                </div>
              </form>
          </div>

          <!-- Post pane（登録フォーム） -->
          <div class="tab-pane fade" id="pane-post" role="tabpanel" aria-labelledby="tab-post">

            <div id="postLoginRequired" class="alert alert-warning mt-3 d-none">
              投稿するにはログインが必要です。
              <a href="https://kai-kon.com/database/login" class="alert-link">ログイン</a>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="mb-3">
      <div class="h5"><a id="by_category" class="text-dark text-decoration-none">分類から探す</a></div>
      <span class="mb-3">下表から選択してください。</span>
    </div>

          <div id="app" class="px-3" style="text-align: start;">          
            <div class="zebra mb-5 mx-0 mx-sm-3">
              <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
                  <div class="col-1">#</div>
                  <div class="col-8 col-md-4 ps-4">目</div>
                  <div class="col d-none d-md-block">Order</div>
                  <div class="col-3 col-md-2 text-end">種数</div>
              </div>
              @foreach( $orders as $key => $order )
              <div class="item row searchable" data-order-id="{{ $order->order_id }}">
                  <div class="col-1">{{ $key + 1 }}</div>
                  <div class="col-8 col-md-4 ps-4">{{ $order->order_ja }}</div>
                  <div class="col d-none d-md-block">{{ $order->order }}</div>
                  <div class="col-3 col-md-2 pe-4 text-end">{{ number_format($order->count) }}</div>
              </div>
              @endforeach
              <div class="row" style="background-color: #e0e0e0; padding: 0.4em 0; font-weight: bold;">
                  <div class="col-9 ps-5">合計</div>
                  <div class="col-3 pe-4 text-end">{{ number_format($species_count) }}</div>
              </div>
          </div><!--zebra 終わり-->
        </div><!--app 終わり-->
        <div id ="number_of_show"></div>
        <div id ="pagination"></div>
        <form class="d-none"><input class="d-none" name="httpquery" id="httpquery"></form>
  </div>




  @slot('modal')
      <!---ModalDetail--->
      <div class="modal fade" id="ModalItemDetail" tabindex="-1" aria-labelledby="ModalArticle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h6 class="modal-title">
                        <div colspan="2">
                            <p id="species_ja" class="d-inline" style="font-size: larger;margin-bottom: 0.3em;">和名</p>
                            <p id="species_en" class="m-0" style="font-size: inherit;font-style: italic;">学名</p>
                        </div>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
                </div>
                
                <div class="modal-body">
                  <div class="row mb-3">
                    <div class="col-lg-6">
                      <div class="table">
                        <div>
                            <div><div class="bg-secondary text-light border-bottom">分布情報</div><div><div id="distribution_info" class="break-word"></div><div id="distribution_memo" class="small">※要件を満たす採集記録を伴わない場合は参考とします。</div></div></div>
                            <div><div class="bg-secondary text-light border-bottom">関連文献</div><div class="break-word"><ol id="articles_info" class="list_parentheses"></ol></div></div>
                            <div><div class="bg-secondary text-light border-bottom">備考</div><div id="memo" class="break-word"></div></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <style>
                      #map{margin: 0 8%; height: 300px;} svg.outline{border: 1px solid #e0e0e0;}
                      @media screen and (min-width: 992px) {
                        #map{margin: 0 10%; height: 300px;} svg.outline{border: 1px solid #e0e0e0;}
                      }
                      </style>
                      <div id="map" class="border"></div>
                    </div>
                    <div class="px-4 py-2">
                      <div class="small">
                        <span style="color:#4db56a;">●</span><span class="fw-bold">：記録　</span><span style="color:#aaeeaa;">●</span><span>：参考</span>
                      </div>
                      <small>※地図は、本種の記録地域を市町村の区画に基づき可視化したものであり、本種の分布を正確に示しているとは限りません。</small>
                    </div>
                  </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                  @if (Auth::check())
                  <div class="ms-3"><small>登録者：<span id="usernames">[usernames]</span></small></div>
                  @endif
                </div>

            </div>
        </div>
      </div><!---ModalDetail終わり--->
  @endslot

  @slot('scripts')
  <script src ="{{url('/')}}/js/components/records/drawMap.js"></script>
  <script src ="{{url('/')}}/js/components/records/pagination.js"></script>
  <script>
    window.authenticated = {{ Auth::check() ? 'true' : 'false' }};
  </script>
  <script type="module" src="{{url('/')}}/js/components/records/main.js"></script>
  @endslot
</x-kaikon::app-layout>