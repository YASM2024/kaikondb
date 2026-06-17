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
      /* 検索結果 */
      #component-search-species .item {
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

  <div id="component-search-species" class="container mt-4 py-2">
    <h4 class="ssj my-3 px-3 px-md-0">{{ __('messages.Inventory') }}</h4>
        <noscript>
          <div class="container pt-3 py-2">
            <p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p>
          </div>
        </noscript>
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
          @if (Auth::check() && \Kaikon2\Kaikondb\Models\User::fromAppUser(Auth::user())->isModerator())
          <li class="nav-item" role="presentation">
            <a class="nav-link px-3" id="tab-post" href="{{ route('record.create') }}">
              登録
            </a>
          </li>
          @endif
          @if (config('kaikon.SupportFormUrl'))
          <li class="nav-item" role="presentation">
            <a class="nav-link px-3" href="{{ config('kaikon.SupportFormUrl') }}" target="_blank" rel="noopener">
              情報提供 <i class="bi bi-box-arrow-up-right small"></i>
            </a>
          </li>
          @endif
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
                        <button id="searchBtn" class="btn btn-secondary w-100" type="button">{{ __('kaikon::messages.LiteratureSearch') }}</button>
                        <a id="cancelBtn" class="btn btn-outline-secondary w-100" href="" type="reset">{{ __('kaikon::messages.LiteratureReset') }}</a>
                    </div>
                </div>
              </form>
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
      <div class="modal fade" id="ModalItemDetail" tabindex="-1" aria-labelledby="ModalLiterature" aria-hidden="true">
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
                  <style>
                    .species-photo-wrap { display: block; max-width: 100%; }
                    .species-photo-frame {
                      /* 縦：横＝3:4（高さ3・幅4の横長）。aspect-ratio は 幅/高さ の順 */
                      --species-photo-width: 4;
                      --species-photo-height: 3;
                      aspect-ratio: var(--species-photo-width) / var(--species-photo-height);
                      position: relative;
                      width: 100%;
                      overflow: hidden;
                      border-radius: var(--bs-border-radius);
                      border: 1px solid var(--bs-border-color);
                      background: #f8f9fa;
                    }
                    .species-photo-admin-overlay {
                      position: absolute;
                      inset: 0;
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      gap: 0.75rem;
                      background: rgba(33, 37, 41, 0.35);
                      pointer-events: none;
                    }
                    .species-photo-admin-overlay-empty {
                      background: rgba(33, 37, 41, 0.12);
                    }
                    .species-photo-admin-btn {
                      pointer-events: auto;
                      display: inline-flex;
                      align-items: center;
                      justify-content: center;
                      width: 2.5rem;
                      height: 2.5rem;
                      padding: 0;
                      border: 1px solid rgba(255, 255, 255, 0.85);
                      border-radius: 50%;
                      background: rgba(255, 255, 255, 0.95);
                      color: var(--bs-body-color);
                      cursor: pointer;
                      line-height: 1;
                      transition: background-color 0.15s ease, color 0.15s ease;
                    }
                    .species-photo-admin-btn .bi {
                      font-size: 1.2rem;
                      line-height: 1;
                    }
                    .species-photo-admin-btn:hover,
                    .species-photo-admin-btn:focus-visible {
                      background: #fff;
                      color: var(--bs-primary);
                      outline: none;
                    }
                    .species-photo-admin-btn-danger:hover,
                    .species-photo-admin-btn-danger:focus-visible {
                      color: var(--bs-danger);
                    }
                    .species-photo-slot-active .species-photo-frame {
                      border-color: var(--bs-primary);
                      box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.28);
                    }
                    .species-photo-slot-active .species-photo-placeholder {
                      background: #dce9f8;
                      border-color: var(--bs-primary);
                    }
                    .species-photo-img {
                      width: 100%;
                      height: 100%;
                      object-fit: cover;
                      object-position: center;
                      display: block;
                    }
                    .species-photo-placeholder {
                      display: flex;
                      align-items: center;
                      justify-content: center;
                      background: #e9ecef;
                      color: #6c757d;
                      font-size: 0.875rem;
                    }
                    .species-photo-caption {
                      position: absolute;
                      right: 0.5rem;
                      bottom: 0.5rem;
                      color: #fff;
                      font-weight: bold;
                      font-size: 0.875rem;
                      line-height: 1.2;
                      text-shadow: 0 1px 3px rgba(0, 0, 0, 0.85);
                      pointer-events: none;
                    }
                    .species-photo-admin-save {
                      display: inline-flex;
                      align-items: center;
                      gap: 0.35rem;
                      padding: 0.35rem 0.9rem;
                      font-size: 0.8125rem;
                      font-weight: 500;
                      line-height: 1;
                      border: 1px solid var(--bs-border-color);
                      border-radius: 2rem;
                      background: #fff;
                      color: var(--bs-body-color);
                      cursor: pointer;
                      transition: border-color 0.15s ease, color 0.15s ease, opacity 0.15s ease;
                    }
                    .species-photo-admin-save .bi {
                      font-size: 1rem;
                      line-height: 1;
                    }
                    .species-photo-admin-save:hover:not(:disabled),
                    .species-photo-admin-save:focus-visible:not(:disabled) {
                      border-color: var(--bs-primary);
                      color: var(--bs-primary);
                      outline: none;
                    }
                    .species-photo-admin-save:disabled {
                      opacity: 0.55;
                      cursor: not-allowed;
                    }
                    .species-photos-carousel {
                      position: relative;
                    }
                    .species-photos-carousel .carousel-inner {
                      border-radius: var(--bs-border-radius);
                    }
                    .species-photos-carousel .carousel-item {
                      padding: 0;
                    }
                    .species-photos-carousel .carousel-indicators {
                      margin-bottom: 0.35rem;
                    }
                    .species-photos-carousel .carousel-indicators [data-bs-target] {
                      width: 0.5rem;
                      height: 0.5rem;
                      border-radius: 50%;
                    }
                    .species-photos-carousel .carousel-control-prev,
                    .species-photos-carousel .carousel-control-next {
                      width: 2.25rem;
                    }
                    .species-photos-carousel .carousel-control-prev-icon,
                    .species-photos-carousel .carousel-control-next-icon {
                      filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.65));
                    }
                  </style>
                  @php
                    $speciesPhotoLinkIsAdministrator = false;
                    if (\Illuminate\Support\Facades\Auth::check()) {
                        $speciesPhotoLinkIsAdministrator = \Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->isAdmin();
                    }
                  @endphp
                  <div id="species_photos" class="mb-2" aria-label="種の写真"></div>
                  @if ($speciesPhotoLinkIsAdministrator && config('kaikon.PHOTOS') == 1)
                  <div id="species_photo_admin_panel" class="d-none mb-2">
                    <div id="species_photo_picker" class="d-none border rounded p-2 mb-2 bg-light">
                      <div class="d-flex justify-content-between align-items-center mb-2">
                        <span id="species_photo_picker_slot_label" class="small fw-semibold"></span>
                        <button type="button" class="btn-close btn-sm" id="species_photo_picker_close" aria-label="閉じる"></button>
                      </div>
                      <input type="search" id="species_photo_picker_keyword" class="form-control form-control-sm mb-2" placeholder="種名・場所などで検索">
                      <div id="species_photo_picker_results" class="list-group list-group-flush" style="max-height: 240px; overflow-y: auto;"></div>
                    </div>
                    <div class="d-flex justify-content-end align-items-center gap-2">
                      <span id="species_photo_admin_status" class="small" role="status"></span>
                      <button type="button" class="species-photo-admin-save" id="species_photo_admin_save" aria-label="写真の紐付けを保存">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                        <span>写真を保存</span>
                      </button>
                    </div>
                  </div>
                  @endif
                  <div class="row mb-3">
                    <div class="col-lg-6">
                      <div class="table">
                        <div>
                            <div><div class="bg-secondary text-light border-bottom">分布情報</div><div><div id="distribution_info" class="break-word"></div><div id="distribution_memo" class="small">※要件を満たす採集記録を伴わなない場合は参考とします。</div></div></div>
                            <div><div class="bg-secondary text-light border-bottom">関連文献</div><div class="break-word"><ol id="literatures_info" class="list_parentheses"></ol></div></div>
                            <div><div class="bg-secondary text-light border-bottom">備考</div><div id="memo" class="break-word"></div></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-6">
                      <style>
                      #map {
                        margin: 0 8%;
                        height: 300px;
                        overflow: hidden;
                      }
                      #map svg {
                        display: block;
                        width: 100%;
                        height: 100%;
                      }
                      @media screen and (min-width: 992px) {
                        #map { margin: 0 10%; }
                      }
                      </style>
                      <div id="map" class="border rounded bg-light overflow-hidden"></div>
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
  <script src ="{{url('/')}}/js/paginationMessage.js"></script>
  <script src ="{{url('/')}}/js/components/records/drawMap.js"></script>
  <script src ="{{url('/')}}/js/components/records/pagination.js"></script>
  <script>
    window.authenticated = {{ Auth::check() ? 'true' : 'false' }};
    window.isAdministrator = @json($speciesPhotoLinkIsAdministrator ?? false);
    window.photosEnabled = @json(config('kaikon.PHOTOS') == 1);
    window.kaikonPrefectureMap = @json(\Kaikon2\Kaikondb\Support\PrefectureMapConfig::resolve());
    window.homeUrl = @json(url('/'));
    window.waitImg = @json(url('/storage/img/wait.png'));
  </script>
  <script type="module" src="{{url('/')}}/js/components/records/main.js"></script>
  @endslot
</x-kaikon::app-layout>
