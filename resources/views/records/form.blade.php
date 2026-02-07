<x-kaikon::app-layout>
  <style>
  /* コンテナのカスタマイズ */
  @media (min-width: 768px) {  .container {max-width: 736px;}}
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

  <div class="container mt-4 py-2">
      <div class="row mx-2 mx-md-0">
          <div>
              <h4 class="my-3 px-0 mx-2">分類・分布情報</h4>

              @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif

              <div class="row">
                <label for="species" class="col-sm-3 custom-border col-form-label text-danger">学名・和名</label>
                @if( $action_type ==='create' )
                <div class="col-sm-9 custom-border col-form-label pb-4">
                    <div class="input-group mb-3">
                      <input id="keyword" type="test" class="form-control" style="z-index: 1;" placeholder="キーワード" tabindex="1">
                      <button class="btn btn-outline-secondary" style="z-index: 1;" type="button" id="keyword_search_btn" tabindex="2">検索</button>
                    </div>
                    <div class="mb-3">
                        <div id="selectorBar" class="d-flex flex-wrap align-items-center gap-2">
                            <button type="button"
                                    id="select_order_id"
                                    class="btn btn-outline-secondary btn-sm w-auto"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title="クリックで選択し直す">
                            すべての目
                            </button>

                            <span id="sep_order_family" class="selector-sep" aria-hidden="true">>></span>

                            <button type="button"
                                    id="select_family_id"
                                    class="btn btn-outline-secondary btn-sm w-auto"
                                    hidden
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title="クリックで選択し直す">
                            <!-- family -->
                            </button>

                            <span id="sep_family_species" class="selector-sep" aria-hidden="true">>></span>

                            <button type="button"
                                    id="select_species_id"
                                    class="btn btn-outline-secondary btn-sm w-auto"
                                    hidden
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title="クリックで選択し直す">
                            <!-- species -->
                            </button>

                            <a class="btn btn-sm btn-danger ms-1" href="../master/species/edit" target="_blank" rel="noopener">
                            種追加
                            </a>
                        </div>

                    </div>
                    <div class="container text-center px-0 mb-2">
                        <div class="row g-2 taxonBtnGroup" id="orderList"></div>
                    </div>
                    
                    <div class="container text-center px-0 mb-2">
                        <div class="row g-2 taxonBtnGroup" id="familyList"></div>
                    </div>
                    
                    <div class="container text-center px-0 mb-2">
                        <div class="row g-2 taxonBtnGroup" id="speciesList"></div>
                    </div>

                </div>
                @elseif( $action_type ==='edit' )
                <div class="col-sm-9 custom-border col-form-label">
                  {{$species_all}}
                </div>
                @endif
              </div>

              <div class="row">
                <label class="col-sm-3 custom-border col-form-label text-danger">分布情報登録</label>
                <div id="municipalities_input"
                    class="col-sm-9 custom-border col-form-label"
                    data-municipalities='@json($municipalities)'
                    data-recorded='@json($recorded_municipalities ?? [])'
                    data-is-collected='@json($recorded_is_collected ?? null)'
                >
                </div>
              </div>

              <div class="row">
                  <label for="memo" class="col-sm-3 custom-border col-form-label text-danger">関連文献</label>
                  <div class="col-sm-9 custom-border col-form-label">
                      @if(isset($article_id))
                      {{ $summary; }}
                      <input type="text" name="article_id" class="d-none" value="{{ $article_id }}" form="registerRecord">
                      @else
                      <input type="text" name="article_id" class="form-control" value="" form="registerRecord">
                      @endif
                  </div>
                </div>
              
              <div class="row">
                  <label for="rdb" class="col-sm-3 custom-border col-form-label">ＲＤＢ区分</label>
                  <div class="col-sm-9 custom-border col-form-label">
                      <input type="text" name="rdb" class="form-control" value="" form="registerRecord">
                  </div>
              </div>
              
              <div class="row">
                  <label for="link" class="col-sm-3 custom-border col-form-label">備考</label>
                  <div class="col-sm-9 custom-border col-form-label">
                      <input type="text" name="memo" class="form-control" value="" form="registerRecord">
                  </div>
              </div>
              
              <input type="text" class="d-none" id="input_order_id" value="">
              <input type="text" class="d-none" id="input_family_id" value="">
              <input type="text" name="species_id" class="d-none" id="input_species_id" value="{{@$species_id}}" form="registerRecord">
              <div class="center-button">
                  <form id="registerRecord" action="" method="POST">
                        @csrf
                        <button type="submit" id="submitBtn" class="btn btn-primary">確認</button>
                        <button type="button" id="deleteBtn" class="btn btn-danger">削除</button>
                  </form>
              </div>
              
          </div>
      </div>
  </div>
  @slot('scripts')
    @if( $action_type ==='create' )
        <script>
            const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
            const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        </script>
        <script type="module" src="{{ asset('js/components/records/main.js') }}"></script>
    @elseif( $action_type ==='edit' )
        <script type="module" src="{{ asset('js/components/records/edit.js') }}"></script>
    @endif
  @endslot
</x-kaikon::app-layout>