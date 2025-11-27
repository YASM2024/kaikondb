<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Photos') }}
    @endslot
    <style>
    #component-serch-photos .cursor-pointer,
    #photoRegisterModal .cursor-pointer { cursor: pointer; }
    #component-serch-photos .modal-body, 
    #photoRegisterModal .modal-body { padding: 0; }
    #component-serch-photos .image-container,
    #photoRegisterModal .image-container {
      position: relative;
      display: inline-block;
    }
    #component-serch-photos .image-overlay,
    #photoRegisterModal .image-overlay {
      cursor: pointer;
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      opacity: 0;
      transition: opacity 0.3s ease;
      opacity: 1;
    }
    #component-serch-photos .image-overlay-content,
    #photoRegisterModal .image-overlay-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      text-align: center;
    }
    #component-serch-photos .custom-carousel,
    #photoRegisterModal .custom-carousel
     { object-fit: cover; }
    .icon-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background-color: white;
      border-radius: 50%;
      width: 2.5em;
      height: 2.5em;
      border: 1px solid #ccc;
      cursor: pointer;
    }
    .icon-btn svg {
      width: 1.2em;
      height: 1.2em;
    }
    </style>
    <!-- ページアイコン -->
    <!-- /ページアイコン -->

    <div id="component-serch-photos" >
        <div class="container mt-4 py-2">
            <h4 class="my-3 px-3 px-md-0">{{ __('messages.Photos') }}</h4>
            <noscript><div class="container pt-3 py-2"><p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p></div></noscript>
            <div class="container pt-3 py-2">
              県内で撮影された昆虫写真を掲載・検索できます。<br>
              ※著作権は撮影者に帰属します。無断転用はお控えください。
            </div>
            <div class="text-left bg-light px-1 px-sm-4">
                <div class="marketing">
                    <div class="row">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="search-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">
                                詳細検索
                                </button>
                            </li>
                            @if(\Illuminate\Support\Facades\Auth::check())
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="post-tab" data-bs-toggle="tab" data-bs-target="#post" type="button" role="tab" aria-controls="itsupport" aria-selected="false" tabindex="-1">
                                写真投稿
                                </button>
                            </li>
                            @endif
                        </ul>
                        <div class="tab-content px-1" id="myTabContent">
                            <div class="tab-pane fade mt-4 px-4 active show" id="home" role="tabpanel" aria-labelledby="search-tab">
                            <form class="m-1" id="searchPhotos" name="search">
                                <div class="row">
                                  <input name="keyword" type="text" class="form-control col-6" placeholder="キーワード" value="{{$data['keyword']}}"></input>
                                  <select id="user_id_selectbox" name="user_id" class="form-control my-2 col-6">
                                    <option value="" selected>投稿者を選択</option>
                                    @foreach($photographers as $photographer)
                                    <option value="{{$photographer->user_id}}" 
                                    @if( $photographer->user_id == $data['user_id'] )
                                    selected
                                    @endif
                                    >{{$photographer->show_name}}</option>
                                    @endforeach
                                  </select>
                                </div>
                            </form>
                            </div>
                        </div>
                        
                        @if(\Illuminate\Support\Facades\Auth::check())
                        <div class="tab-content px-1" id="myTabContent">
                            <div class="tab-pane mt-4 mb-3 px-4 fade" id="post" role="tabpanel" aria-labelledby="post-tab">
                                <div class="tab-pane mt-4 mb-3 px-4 fade active show" id="post" role="tabpanel" aria-labelledby="post-tab">
                                  <div id="rules" class="collapse show" style="">
                                    <div class="mb-3">以下の注意に同意のうえ、投稿してください。</div><h6>基本ルール</h6>
                                    <div class="mb-1">
                                      <div>【著作権の尊重】</div>
                                      <div>他人が撮影した画像を無断で投稿しない。</div>
                                    </div>
                                    <div class="mb-3">
                                      <div>【内容の制限】</div>
                                      <div>昆虫に関する画像のみ投稿可能（セールスやアダルトの投稿は禁止）</div>
                                    </div>

                                    <h6>投稿のフォーマット</h6>
                                    <div><u>タイトル</u>：種名など、画像の特徴を簡潔に。無理に同定する必要はありません（例： 「オオクワガタの交尾」 ）</div>
                                    <div><u>撮影場所</u>：原則、市町村まで。希少種の場合には撮影場所がピンポイントで分かる情報は記載不可。</div>
                                    <div><u>コメント</u>：遭遇した時の状況や、写真にかけた想いなど自由に記載してください。</div>
                                  </div>
                                  <div class="d-flex justify-content-center mt-3">
                                    <!-- ボタン -->
                                    <button class="btn btn-secondary" id="agreementButton" data-bs-toggle="collapse" data-bs-target="#rules" aria-expanded="true">
                                      上記に同意
                                    </button>
                                    <!-- 投稿ボタン（初期では非表示） -->
                                    <button class="btn btn-primary d-none" id="postButton" data-bs-toggle="modal" data-bs-target="#photoRegisterModal">
                                      投稿
                                    </button>
                                  </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    </div><!-- /.row -->
                </div><!--//marketing-->
            </div>
            <hr>
        </div>
    </div>

    <form class="d-none">
      <label for="httpquery">httpquery</label><input name="httpquery" id="httpquery"></input>
    </form>
    <div id="app" class="py-2 px-1 px-sm-4 mx-1 row"></div>
    <div id="number_of_show" class="m-1 px-1 px-sm-4"></div>
    <div id="next_page_loader" class="px-1 px-sm-4 mx-1 row"></div>
</div>
<div>
        <!-- モーダルの設定 -->
        <div class="modal fade" id="photoModal" tabindex="-1" aria-labelledby="ModalLabel">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-1">
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="閉じる">
              </div>
              <div class="modal-body">
                <div class="position-relative d-inline-block w-100">
                  <img src="{{ url('/storage/img/wait.png') }}" id="photo_url" class="w-100">
                  @if ( \Illuminate\Support\Facades\Auth::check() )
                  <div id="closed" class="position-absolute top-0 start-0 m-2 badge bg-secondary">承認待ち</div>
                  <div id="opened" class="position-absolute top-0 start-0 m-2 badge bg-danger">公開中</div>
                  <div class="position-absolute bottom-0 end-0 m-2" style="display: block;">
                    <div id="editAndDelete" class="" style="float: right; padding-right: 1em;">
                      <span id="editBtn" class="icon-btn" data-bs-toggle="modal" data-bs-target="#photoEditModal" data-bs-whatever="2">
                          <svg class="bi cursor-pointer" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#edit"></use></svg>
                      </span>
                      <span id="delBtn" class="icon-btn" data-bs-whatever="2">
                          <svg class="bi cursor-pointer" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#delete"></use></svg>
                      </span>
                    </div>
                  </div>
                  @endif
                </div>
                <div class="m-2">
                    <div id="ModalLabel" class="h4 m-2" style="clear: both;"></div>
                    <span name="photographer" class="view_data ms-2"></span>
                    <span class="ms-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#map"></use></svg>
                        <span name="place" class="view_data" value=""></span>
                    </span>
                    <span class="ms-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#calendar"></use></svg>
                        <span name="date" class="view_data" value=""></span>
                    </span>
                    <div class="m-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#memo"></use></svg>
                        <span name="memo" class="view_data" value=""></span>
                    </div>
                </div>

              </div>
              <div class="modal-footer p-1">
                @if ( \Illuminate\Support\Facades\Auth::check() )
                    <button id="deleteBtn" type="button" class="d-none btn btn-outline-danger">削除</button>
                @endif
              </div><!-- /.modal-footer -->
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->


        <div class="modal fade" id="profileModal" aria-hidden="true" aria-labelledby="profile_show_name" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h1 class="modal-title fs-5" id="profile_show_name"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <div class="row px-2">
                  <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                      <div class="image-container">
                          <img id="profile_icon" src="" class="image-gradient" style="width: 7em;">
                      </div>
                  </div>
                  <div class="col ps-sm-0">
                      <div id="profile_description" class="row pe-2"></div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button id="backBtn" class="btn btn-primary" data-bs-target="#photoModal" data-bs-whatever="" data-bs-toggle="modal">戻る</button>
              </div>
            </div>
          </div>
        </div>

    @if (\Illuminate\Support\Facades\Auth::check())
        <!-- 登録フォーム-->
        <div class="modal fade" id="photoRegisterModal" tabindex="-1" aria-labelledby="ModalLabel-form">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-1">
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="閉じる">
              </div>
              <div class="modal-body">
                <div class="w-100 image-container">
                  <label>
                    <input type="file" accept="image/*" class="d-none" id="new_image_file">
                    <img id="preview" class="custom-carousel w-100" src="{{ url('/storage/img/wait.png') }}">
                    <div class="image-overlay">
                      <div class="image-overlay-content w-75">
                        <div class="h5">画像アップロード</div>
                        <svg class="bi ms-1" width="2em" height="2em"><use xlink:href="./svg/icons.svg#upload"></use></svg>
                        <div class="mt-3 small">アップロード可能サイズ：最大2MB<br>
                        フォーマット：jpg、png、bmp</div>
                      </div>
                    </div>
                  </label>
                </div>
                <div class="m-2">
                    <div id="ModalLabel-form" class="h4 m-2" style="clear: both;"></div>
                    <span class="my-2 fw-bold">撮影者：{{\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->name}}</span>
                    <input type="text" id="new_photo_title" class="form-control my-2" placeholder="種名など" value=""></input>
                    <small class="ms-2">例）トノサマバッタ、ガの一種、脚の長い虫など</small>
                    <input type="text" id="new_place" class="form-control my-2" placeholder="撮影場所" value=""></input>
                    <small class="ms-2">例）甲府市、北岳、大弛峠　など</small>
                    <input type="text" id="new_date" class="form-control my-2" placeholder="撮影日" value=""></input>
                    <small class="ms-2">例）2022年XX月XX日、令和４年○月　など</small>
                    <textarea id="new_memo" class="form-control my-2" placeholder="コメント"></textarea>
                    <small class="ms-2">確認状況の詳細など、自由に記述。</small>
                </div>
              </div>
              <div class="modal-footer p-1"><button type="button" id="create_submit" class="btn btn-primary">確定</button>
              </div><!-- /.modal-footer -->
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <!-- /登録フォーム-->

        <!-- 編集フォーム-->
        <div class="modal fade" id="photoEditModal" tabindex="-1" aria-labelledby="ModalLabel-edit">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-1">
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="閉じる">
              </div>
              <div class="modal-body">
                <div class="w-100 image-container">
                  <img id="photo_editForm" class="custom-carousel w-100" src="{{ url('/storage/img/wait.png') }}">
                </div>
                <div class="m-2">
                    <div id="ModalLabel-edit" class="h4 m-2" style="clear: both;"></div>
                    <span id="show_name_editForm" class="my-2 fw-bold"></span>
                    <input type="id" id="id_editForm" class="d-none"></input>
                    <input type="text" id="photo_title_editForm" class="form-control my-2" placeholder="種名など"></input>
                    <input type="text" id="place_editForm" class="form-control my-2" placeholder="撮影場所"></input>
                    <input type="text" id="date_editForm" class="form-control my-2" placeholder="撮影日"></input>
                    <textarea id="memo_editForm" class="form-control my-2" placeholder="コメント"></textarea>
                </div>
              </div>
              <div class="modal-footer p-1"><button type="button" id="edit_submit" class="btn btn-primary">確定</button>
              </div><!-- /.modal-footer -->
            </div><!-- /.modal-content -->
          </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
        <!-- /編集フォーム-->
    @endif


    @slot('scripts')
    <script src ="{{url('/')}}/js/nextPageLoader.js"></script>
    <script>

      // ==============================
      // 定数・DOM参照
      // ==============================
      window.xCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
      window.authenticated = {{ \Illuminate\Support\Facades\Auth::check() ? 'true' : 'false' }};
      window.userId = {{ \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->id : 'null' }};
      window.isEventListenerSet = false;
      window.homeUrl = "{{ url('/') }}"; 
      window.photoBaseUrl="{{ route('photos') }}"; 
      window.profileUrl = "{{ url('/storage/profile') }}"; 
      window.agreeUrl = "{{ route('agree') }}"; 
      window.waitImg = "{{ url('/storage/img/wait.png') }}";

    </script>
    <script type="module" src="{{url('/')}}/js/components/photos/init.js"></script>
    @endslot
</x-kaikon::app-layout>