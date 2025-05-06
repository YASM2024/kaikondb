<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Photos') }}
    @endslot
    <style>
    #component-serch-photos .cursor-pointer,
    #Modal-register-form .cursor-pointer { cursor: pointer; }
    #component-serch-photos .modal-body, 
    #Modal-register-form .modal-body { padding: 0; }
    #component-serch-photos .image-container,
    #Modal-register-form .image-container {
      position: relative;
      display: inline-block;
    }
    #component-serch-photos .image-overlay,
    #Modal-register-form .image-overlay {
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
    #Modal-register-form .image-overlay-content {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      color: white;
      text-align: center;
    }
    #component-serch-photos .custom-carousel,
    #Modal-register-form .custom-carousel
     { object-fit: cover; }
    </style>
    <!-- ページアイコン -->
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">

      <symbol id="enter" viewBox="0 0 26 26">
        <g>
            <path class="enterIcon" d="M 22.566406 4.730469 L 20.773438 3.511719 C 20.277344 3.175781 19.597656 3.304688 19.265625 3.796875 
                L 10.476563 16.757813 L 6.4375 12.71875 C 6.015625 12.296875 5.328125 12.296875 4.90625 12.71875 L 3.371094 14.253906 C 
                2.949219 14.675781 2.949219 15.363281 3.371094 15.789063 L 9.582031 22 C 9.929688 22.347656 10.476563 22.613281 10.96875 
                22.613281 C 11.460938 22.613281 11.957031 22.304688 12.277344 21.839844 L 22.855469 6.234375 C 23.191406 5.742188 23.0625 
                5.066406 22.566406 4.730469 Z"/>
        </g>
      </symbol>

      <symbol id="edit" viewBox="0 0 24 24">
        <g>
            <path class="editIcon" d="M 19.171875 2 C 18.448125 2 17.724375 2.275625 17.171875 2.828125 L 16 4 L 20 8 L 21.171875 6.828125 
            C 22.275875 5.724125 22.275875 3.933125 21.171875 2.828125 C 20.619375 2.275625 19.895625 2 19.171875 2 z M 14.5 5.5 L 3 17 
            L 3 21 L 7 21 L 18.5 9.5 L 14.5 5.5 z"></path>
        </g>
      </symbol>

      <symbol id="delete" viewBox="0 0 512 512">
        <g>
          <path class="deleteIcon" d="M439.114,69.747c0,0,2.977,2.1-43.339-11.966c-41.52-12.604-80.795-15.309-80.795-15.309l-2.722-19.297
            C310.387,9.857,299.484,0,286.642,0h-30.651h-30.651c-12.825,0-23.729,9.857-25.616,23.175l-2.722,19.297
            c0,0-39.258,2.705-80.778,15.309C69.891,71.848,72.868,69.747,72.868,69.747c-10.324,2.849-17.536,12.655-17.536,23.864v16.695
            h200.66h200.677V93.611C456.669,82.402,449.456,72.596,439.114,69.747z" style="fill: rgb(75, 75, 75);"></path>
          <path class="deleteIcon" d="M88.593,464.731C90.957,491.486,113.367,512,140.234,512h231.524c26.857,0,49.276-20.514,51.64-47.269
            l25.642-327.21H62.952L88.593,464.731z M342.016,209.904c0.51-8.402,7.731-14.807,16.134-14.296
            c8.402,0.51,14.798,7.731,14.296,16.134l-14.492,239.493c-0.51,8.402-7.731,14.798-16.133,14.288
            c-8.403-0.51-14.806-7.722-14.296-16.125L342.016,209.904z M240.751,210.823c0-8.42,6.821-15.241,15.24-15.241
            c8.42,0,15.24,6.821,15.24,15.241v239.492c0,8.42-6.821,15.24-15.24,15.24c-8.42,0-15.24-6.821-15.24-15.24V210.823z
            M153.833,195.608c8.403-0.51,15.624,5.894,16.134,14.296l14.509,239.492c0.51,8.403-5.894,15.615-14.296,16.125
            c-8.403,0.51-15.624-5.886-16.134-14.288l-14.509-239.493C139.026,203.339,145.43,196.118,153.833,195.608z" style="fill: rgb(75, 75, 75);"></path>
        </g>
      </symbol>

      <symbol id="back" viewBox="0 0 512 512">
        <g>
            <path class="backIcon" d="M452.421,155.539c-36.6-36.713-87.877-59.612-143.839-59.579h-87.179V9.203L5.513,228.805h215.889h87.179
                c19.702,0.033,36.924,7.8,49.898,20.659c12.876,12.99,20.644,30.212,20.676,49.914c-0.032,19.703-7.8,36.924-20.676,49.898
                c-12.974,12.876-30.196,20.642-49.898,20.676H0v132.844h308.582c55.962,0.033,107.239-22.866,143.839-59.579
                c36.715-36.6,59.612-87.877,59.579-143.84C512.033,243.416,489.136,192.14,452.421,155.539z"></path>
        </g>
      </symbol>

      <symbol id="calendar" viewBox="0 0 512 512">
        <g>
          <path class="calendarIcon" d="M149.193,103.525c15.994,0,28.964-12.97,28.964-28.972V28.964C178.157,12.97,165.187,0,149.193,0
            C133.19,0,120.22,12.97,120.22,28.964v45.589C120.22,90.556,133.19,103.525,149.193,103.525z"></path>
          <path class="calendarIcon" d="M362.815,103.525c15.995,0,28.964-12.97,28.964-28.972V28.964C391.78,12.97,378.81,0,362.815,0
            c-16.002,0-28.972,12.97-28.972,28.964v45.589C333.843,90.556,346.813,103.525,362.815,103.525z"></path>
          <path class="calendarIcon" d="M435.164,41.288h-17.925v33.265c0,30.017-24.414,54.431-54.423,54.431c-30.017,0-54.431-24.414-54.431-54.431
            V41.288H203.616v33.265c0,30.017-24.415,54.431-54.423,54.431c-30.016,0-54.432-24.414-54.432-54.431V41.288H76.836
            c-38.528,0-69.763,31.234-69.763,69.763v331.186C7.073,480.766,38.309,512,76.836,512h358.328
            c38.528,0,69.763-31.234,69.763-69.763V111.051C504.927,72.522,473.692,41.288,435.164,41.288z M450.023,429.989
            c0,17.826-14.503,32.328-32.329,32.328H94.306c-17.826,0-32.329-14.502-32.329-32.328V170.877h388.047V429.989z"></path>
          <rect class="calendarIcon" x="220.58" y="334.908" width="70.806" height="70.798"></rect>
          <rect class="calendarIcon" x="110.839" y="334.908" width="70.808" height="70.798"></rect>
          <rect class="calendarIcon" x="330.338" y="225.151" width="70.824" height="70.807"></rect>
          <rect class="calendarIcon" x="330.338" y="334.908" width="70.824" height="70.798"></rect>
          <rect class="calendarIcon" x="220.58" y="225.151" width="70.806" height="70.807"></rect>
          <rect class="calendarIcon" x="110.839" y="225.151" width="70.808" height="70.807"></rect>
        </g>
      </symbol>

      <symbol id="map" viewBox="0 0 512 512">
        <g>
          <path class="mapIcon" d="M256.016,0C158.797,0.031,80.094,78.781,80.063,175.953c0.063,14.297,3.031,28.641,7.563,43.797
            c7.969,26.438,21.094,55.328,36.281,84.547c45.563,87.359,110.328,177.391,110.688,177.891L256.016,512l21.391-29.813
            c0.25-0.313,37.969-52.844,76.016-116.266c19.016-31.766,38.141-66.25,52.828-98.859c7.344-16.313,13.578-32.172,18.156-47.313
            c4.531-15.156,7.469-29.5,7.531-43.797C431.906,78.781,353.203,0.031,256.016,0z M373.938,204.594
            c-6.344,21.156-18.25,47.906-32.594,75.359c-21.484,41.266-48.281,84.375-69.625,116.953c-5.719,8.719-10.969,16.609-15.703,23.594
            c-14.891-22-35.594-53.594-55.844-87.75c-17.719-29.906-35.063-61.75-47.656-90.25c-6.297-14.188-11.391-27.547-14.781-39.094
            c-3.422-11.5-5-21.281-4.953-27.453c0.016-34.109,13.75-64.734,36.078-87.156c22.391-22.328,53.016-36.063,87.156-36.094
            c34.109,0.031,64.75,13.766,87.125,36.094c22.359,22.422,36.078,53.047,36.094,87.156
            C379.281,182.344,377.594,192.563,373.938,204.594z"></path>
          <path class="mapIcon" d="M256.016,118.719c-31.594,0-57.219,25.641-57.219,57.234c0,31.609,25.625,57.219,57.219,57.219
            c31.578,0,57.219-25.609,57.219-57.219C313.234,144.359,287.594,118.719,256.016,118.719z"></path>
        </g>
      </symbol>

      <symbol id="memo" viewBox="0 0 512 512">
        <g>
          <path class="memoIcon" d="M447.139,16H64.859C29.188,16,0,45.729,0,82.063v268.519c0,36.334,29.188,66.063,64.859,66.063h155.192
            l74.68,76.064c3.156,3.213,7.902,4.174,12.024,2.436c4.121-1.74,6.808-5.836,6.808-10.381v-68.119h133.576
            c35.674,0,64.861-29.729,64.861-66.063V82.063C512,45.729,482.812,16,447.139,16z M96,132v-32h320v32H96z M96,232v-32h320v32H96z
            M324,300v32H96v-32H324z" style="fill: rgb(75, 75, 75);"></path>
        </g>
      </symbol>

      <symbol id="upload" viewBox="0 0 512 512"><style>.uploadicon{fill:#dddddd;}</style>
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
                            <form class="m-1" id="searchPhotos">
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
                                <div class="mb-3">以下の注意に同意のうえ、投稿してください。</div>
                                <div>
                                  <h6>基本ルール</h6>
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
                                    <div class="btn btn-secondary float-end me-3 mt-3" data-bs-toggle="modal" data-bs-target="#Modal-register-form">
                                      上記に同意して投稿
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
    <div class="container">
        <div class="px-2">{{ $photos->firstItem() }}～{{ $photos->lastItem() }}件／全{{ $photos->total() }}件</div>
    </div>
</div>
<div>
    <div>
        <div class="py-2 mx-1 row">
            @foreach($photos as $photo)
            <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer">
                <div class="d-block" data-bs-toggle="modal" data-bs-target="#Modal" data-bs-whatever="{{$photo->id}}">
                    @if ( \Illuminate\Support\Facades\Auth::check() && $photo->approved_at == null )
                    <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('./storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;">
                      <div style="float: right;">
                        <div class="m-3 badge bg-danger">承認待ち</div>
                      </div>
                    </div>
                    @else
                    <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('./storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;"></div>
                    @endif
                    <div class="d-flex align-items-center justify-content-center text-decoration-none">{{$photo->photo_title}}</div>
                </div>
            </div>
            @endforeach
        </div>
        <div id="pagination" class="container pt-3 py-2">{{$photos->links('kaikon::vendor.pagination.original')}}</div>
    </div>

    
        <!-- モーダルの設定 -->
        <div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header p-1">
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal" aria-label="閉じる">
              </div>
              <div class="modal-body">
                <div class="w-100">
                  <img src="{{ url('/storage/img/wait.png') }}" id="photo_url" class="w-100">
                </div>
                <div class="m-2">
                      @if (\Illuminate\Support\Facades\Auth::check())
                          <div id="editAndDelete" class="d-none" style="float: right; padding-right: 1em;">
                              <span id="editBtn" data-bs-toggle="modal" data-bs-target="#Modal-edit">
                                  <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#edit"></use></svg>
                              </span>
                              <span id="delBtn">
                                  <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#delete"></use></svg>
                              </span>
                          </div>
                      @endif
                    <div id="ModalLabel" class="h4 m-2" style="clear: both;"></div>
                    <span name="photographer" class="view_data ms-2"></span>
                    <span class="ms-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#map"></use></svg>
                        <span name="place" class="view_data" value=""></span>
                    </span>
                    <span class="ms-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#calendar"></use></svg>
                        <span name="date" class="view_data" value=""></span>
                    </span>
                    <div class="m-2">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#memo"></use></svg>
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
                <button id="backBtn" class="btn btn-primary" data-bs-target="#Modal" data-bs-whatever="" data-bs-toggle="modal">戻る</button>
              </div>
            </div>
          </div>
        </div>

    @if (\Illuminate\Support\Facades\Auth::check())
        <!-- 登録フォーム-->
        <div class="modal fade" id="Modal-register-form" tabindex="-1" aria-labelledby="ModalLabel-form">
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
                        <svg class="bi ms-1" width="2em" height="2em"><use xlink:href="#upload"></use></svg>
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
        <div class="modal fade" id="Modal-edit" tabindex="-1" aria-labelledby="ModalLabel-edit">
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


    @slot('kaikon::scripts')
    <script>
      const thisUrl = location.href
      const useridEle = document.getElementById('user_id_selectbox')
      useridEle.addEventListener('change', function() {
        const searchPhotos = document.getElementById('searchPhotos')
        searchPhotos.submit();
      });

      const Modal = document.getElementById('Modal')
      const profileModal = document.getElementById('profileModal')
      const editBtn = document.getElementById('editBtn')
      const delBtn = document.getElementById('delBtn')

      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
      const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

      const viewDataPlace = document.querySelector('.view_data[name="place"]')
      const viewDataDate = document.querySelector('.view_data[name="date"]')
      const viewDataPhotographer = document.querySelector('.view_data[name="photographer"]')
      const viewDataMemo = document.querySelector('.view_data[name="memo"]')
      const photo_url = document.getElementById('photo_url')
      @if (\Illuminate\Support\Facades\Auth::check())
      const editDeleteIcons = document.getElementById('editAndDelete')
      @endif

      // 写真モーダル表示
      Modal.addEventListener('show.bs.modal', () => {
        const button = event.relatedTarget
        const id = button.getAttribute('data-bs-whatever')
        
        let url=`${CONFIG.baseUrl}/photos/${id}/show`;
        fetch(url)
        .then((response) => {
          return response.json();
        })
        .then((json) => {
          console.log(json);
          ModalLabel.innerText = json.photo_title;
          viewDataPlace.innerText = json.place;
          viewDataDate.innerText = json.date;
          viewDataPhotographer.innerHTML = `
          <div id="openProfileBtn" class="d-inline cursor-pointer" data-bs-target="#profileModal" data-bs-whatever="${json.user_id}" data-bs-toggle="modal">
              <img src="./storage/profile/${json.icon}" class="round img-fluid" style="width:1.4em; height:1.4em; border-radius:50%;">
              <u>${json.show_name}</u>
          </div>`;
          viewDataMemo.innerText = json.memo;

          viewDataPlace.setAttribute('value', json.place);
          viewDataDate.setAttribute('value', json.date);
          viewDataMemo.setAttribute('value', json.memo);

          photo_url.setAttribute('src', `${CONFIG.baseUrl}/storage/photos/${json.url}`);
          Modal.setAttribute('code', json.id);

          @if (\Illuminate\Support\Facades\Auth::check())
          if( json.user_id === {{\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->id}} ) editDeleteIcons.classList.remove('d-none');
          editBtn.setAttribute( 'data-bs-whatever', json.id)
          delBtn.setAttribute( 'data-bs-whatever', json.id)
          @endif

          profileModal.addEventListener('show.bs.modal', () => {
            const openProfileBtn = document.getElementById('openProfileBtn');
            const profileId = openProfileBtn.getAttribute('data-bs-whatever');
            let url=`${CONFIG.baseUrl}/users/${profileId}`;
            fetch(url)
            .then((response) => {
              return response.json();
            })
            .then((data) => {
              document.getElementById('profile_show_name').innerText = data.show_name;  
              document.getElementById('profile_icon').src = `{{url('/storage/profile')}}/${data.icon}`;  
              document.getElementById('profile_description').innerText = data.description;  
              document.getElementById('backBtn').setAttribute('data-bs-whatever', id);  
            })
          })


        })
      })

      // 写真モーダル非表示
      Modal.addEventListener('hidden.bs.modal', () => {
        ModalLabel.innerText = 'title';
        viewDataMemo.innerText = 'memo';
        viewDataPhotographer.innerText = 'photographer';
        viewDataPlace.innerText = 'place';
        viewDataDate.innerText = 'date';
        photo_url.setAttribute('src', `${CONFIG.baseUrl}/storage/img/wait.png`);
        document.querySelectorAll('.view_data').forEach(function(ele){ele.removeAttribute('value')})
        Modal.setAttribute('code','')
        })


        @if (\Illuminate\Support\Facades\Auth::check())

        //新規登録モーダル
        const Modal_form = document.getElementById('Modal-form')
        const new_photo_title_Ele = document.getElementById('new_photo_title')
        const new_place_Ele = document.getElementById('new_place')
        const new_date_Ele = document.getElementById('new_date')
        const new_memo_Ele = document.getElementById('new_memo')
        const new_image_file_Ele = document.getElementById('new_image_file')

        //写真プレビュー
        new_image_file_Ele.addEventListener('change', function() {
            if (!this.files.length) {
                return;
            }
            const file = this.files[0];
            const fr = new FileReader();
            const previewElement = document.getElementById('preview');
            previewElement.src = "{{ url('/storage/img/wait.png') }}";
            fr.onload = function() {
              previewElement.src = this.result;
            }
            fr.readAsDataURL(file);
        });

        //投稿アクション
        const createSubmitBtn = document.getElementById('create_submit')
        createSubmitBtn.addEventListener('click', function() {

            const url = "{{ url('/photos/create') }}";
            let body = new FormData()

            body.append('photographer', 'newPost')
            body.append('name', new_photo_title_Ele.value)
            body.append('place', new_place_Ele.value)
            body.append('date', new_date_Ele.value)
            body.append('memo', new_memo_Ele.value)
            body.append('image_file', new_image_file_Ele.files[0])
            body.append('verified', '1')

            fetch(url, {
                method: "POST", // *GET, POST, PUT, DELETE, etc.
                mode: "cors", 
                cache: "no-cache",
                credentials: "same-origin",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body
            })
            .then(() => {
                alert("投稿されました。\n承認をお待ちください。");
                location.href = thisUrl;
            })

        }, false);

        //編集モーダル表示
        const Modal_edit = document.getElementById('Modal-edit')
        Modal_edit.addEventListener('show.bs.modal', () => {
          const button = event.relatedTarget
          const id_edit = button.getAttribute('data-bs-whatever')
          const show_url=`${CONFIG.baseUrl}/photos/${id_edit}/show`;

          fetch(show_url)
          .then((response) => {
            return response.json();
          })
          .then((json) => {

            let previewElement = document.getElementById('photo_editForm');
            show_name_editForm.innerText = '投稿者：' + json.show_name
            id_editForm.value = json.id
            photo_title_editForm.value = json.photo_title
            place_editForm.value = json.place
            date_editForm.value = json.date
            memo_editForm.value = json.memo
            previewElement.src = `${CONFIG.baseUrl}/storage/photos/${json.url}`;
            const photo_id = json.id
            
            const edit_submit = document.getElementById('edit_submit');
            
            edit_submit.addEventListener('click', () => {
          
                edit_submit.disabled = true;
                const edit_url = `${CONFIG.baseUrl}/photos/edit`;
                let body = new FormData()
                body.append('id', id_editForm.value)
                body.append('photo_title', photo_title_editForm.value)
                body.append('place', place_editForm.value)
                body.append('date', date_editForm.value)
                body.append('memo', memo_editForm.value)

                fetch(edit_url, {
                    method: "POST", // *GET, POST, PUT, DELETE, etc.
                    mode: "cors", 
                    cache: "no-cache",
                    credentials: "same-origin",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    redirect: "follow",
                    referrerPolicy: "no-referrer",
                    body
                })
                .then((response) => {
                  return response.json();
                })
                .then((json) => {
                    if( json.result == 'success' ){
                      alert('編集を完了しました。');
                      location.href = thisUrl;
                    }else{
                      alert('編集に失敗ました。');
                      edit_submit.disabled = false;
                    }
                })

              },false)

          })
        }, false);

        //削除アクション
        delBtn.addEventListener('click', function(){

            const id_del = delBtn.getAttribute('data-bs-whatever')
            res = confirm("本当に削除してよいですか？削除すると元に戻せません。")
            if(!res){return false;}

            let deleteCode = Modal.getAttribute('code')
            let body = new FormData()
            body.append('id', id_del)

            const url = `${CONFIG.baseUrl}/photos/delete`;
            fetch(url, {
                method: "POST", // *GET, POST, PUT, DELETE, etc.
                mode: "cors", 
                cache: "no-cache",
                credentials: "same-origin",
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                redirect: "follow",
                referrerPolicy: "no-referrer",
                body
            })
            .then((response) => {
              return response.json();
            })
            .then((json) => {
              if( json.result == 'success' ){
                alert('削除に成功しました。');
                location.href = thisUrl;
              }else{
                alert('削除に失敗しました。');
              }
            })

        }, false)

        @endif

    </script>
    @endslot
</x-kaikon::app-layout>