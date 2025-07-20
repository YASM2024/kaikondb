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
    <div class="container">
        <div class="px-2">{{ $photos->firstItem() }}～{{ $photos->lastItem() }}件／全{{ $photos->total() }}件</div>

        <div class="py-2 mx-1 row">
            @foreach($photos as $photo)
            <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer">
                <div class="d-block" data-bs-toggle="modal" data-bs-target="#photoModal" data-bs-whatever="{{$photo->id}}">
                    @if ( \Illuminate\Support\Facades\Auth::check() )
                      @if ( $photo->approved_at == null )
                      <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('./storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;">
                        <div style="float: right;">
                          <div class="m-3 badge bg-secondary">承認待ち</div>
                        </div>
                      </div>
                      @else
                      <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('./storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;">
                        <div style="float: right;">
                          <div class="m-3 badge bg-danger">公開中</div>
                        </div>
                      </div>
                      @endif
                    @elseif ($photo->approved_at != null)
                      <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('./storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;"></div>
                    @endif
                    <div class="d-flex align-items-center justify-content-center text-decoration-none">{{$photo->photo_title}}</div>
                </div>
            </div>
            @endforeach
        </div>

        <div>
            <div id="pagination" class="container pt-3 py-2">{{$photos->links('kaikon::vendor.pagination.original')}}</div>
        </div>

    </div>
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
                  @endif
                </div>
                <div class="m-2">
                      @if (\Illuminate\Support\Facades\Auth::check())
                          <div id="editAndDelete" class="d-none" style="float: right; padding-right: 1em;">
                              <span id="editBtn" data-bs-toggle="modal" data-bs-target="#photoEditModal">
                                  <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#edit"></use></svg>
                              </span>
                              <span id="delBtn">
                                  <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="./svg/icons.svg#delete"></use></svg>
                              </span>
                          </div>
                      @endif
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
    <script>
      const thisUrl = location.href
      const xCsrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      const userIdSearchEle = document.getElementById('user_id_selectbox')
      userIdSearchEle.addEventListener('change', function() {
        const searchPhotos = document.getElementById('searchPhotos')
        searchPhotos.submit();
      });

      const photoModal = document.getElementById('photoModal')
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
      photoModal.addEventListener('show.bs.modal', () => {
        const button = event.relatedTarget
        const id = button.getAttribute('data-bs-whatever')
        
        let url=`{{route('photos')}}/${id}/show`;
        fetch(url)
        .then((response) => {
          return response.json();
        })
        .then((json) => {
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

          photo_url.setAttribute('src', `./storage/photos/${json.url}`);
          photoModal.setAttribute('code', json.id);

          @if (\Illuminate\Support\Facades\Auth::check())
          if( json.user_id === {{\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->id}} ) editDeleteIcons.classList.remove('d-none');
          editBtn.setAttribute( 'data-bs-whatever', json.id)
          delBtn.setAttribute( 'data-bs-whatever', json.id)
          if( json.approved_at == null ){
            document.getElementById('closed').style.display = 'block';
            document.getElementById('opened').style.display = 'none'; 
          }else{
            document.getElementById('closed').style.display = 'none';
            document.getElementById('opened').style.display = 'block';
          }
          @endif

          profileModal.addEventListener('show.bs.modal', () => {
            const openProfileBtn = document.getElementById('openProfileBtn');
            const profileId = openProfileBtn.getAttribute('data-bs-whatever');
            let url=`{{ route('home') }}/users/${profileId}`;
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
      photoModal.addEventListener('hidden.bs.modal', () => {
        ModalLabel.innerText = 'title';
        viewDataMemo.innerText = 'memo';
        viewDataPhotographer.innerText = 'photographer';
        viewDataPlace.innerText = 'place';
        viewDataDate.innerText = 'date';
        photo_url.setAttribute('src', `../storage/img/wait.png`);
        document.querySelectorAll('.view_data').forEach(function(ele){ele.removeAttribute('value')})
        photoModal.setAttribute('code','')
        })


        @if (\Illuminate\Support\Facades\Auth::check())

        //新規登録モーダル
        const photoRegisterModal = document.getElementById('photoRegisterModal')
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

        // 投稿アクション
        const createSubmitBtn = document.getElementById('create_submit');

        // 入力されたフォームデータをまとめる関数
        function gatherCreateFormData() {
          const formData = new FormData();
          formData.append('photographer', 'newPost');
          formData.append('name', new_photo_title_Ele.value);
          formData.append('place', new_place_Ele.value);
          formData.append('date', new_date_Ele.value);
          formData.append('memo', new_memo_Ele.value);
          formData.append('image_file', new_image_file_Ele.files[0]);
          formData.append('verified', '1');

          return formData;
        }

        // 投稿リクエストを送信する関数
        async function submitNewPhoto(formData) {
          const url = "{{ url('/photos/create') }}";

          const response = await fetch(url, {
            method: "POST",
            mode: "cors",
            cache: "no-cache",
            credentials: "same-origin",
            headers: { 'X-CSRF-TOKEN': xCsrfToken },
            redirect: "follow",
            referrerPolicy: "no-referrer",
            body: formData
          });

          if (!response.ok) {
            const errorText = await response.text(); // APIから返されたメッセージ
            throw new Error(`HTTP ${response.status} エラー`);
          }

          return response;
        }

        // 投稿ボタンのクリックイベント処理
        async function handleCreateSubmit(event) {

          try {
            const formData = gatherCreateFormData();
            const res = await submitNewPhoto(formData);
            alert("投稿されました。\n承認をお待ちください。");
            location.href = thisUrl;

          } catch (error) {
            console.error('投稿エラー:', error.message);
            alert("投稿に失敗しました。\n" + error.message);
          }
        }

        // イベントリスナー登録
        createSubmitBtn.addEventListener('click', handleCreateSubmit, false);



        //編集モーダル表示
        const photoEditModal = document.getElementById('photoEditModal');

        //写真データ取得
        async function fetchPhotoData(id) {
          const show_url = `{{ route('photos') }}/${id}/show`;
          const response = await fetch(show_url);
          return await response.json();
        }

        //フォームに値を設定
        function populateEditForm(data) {
          show_name_editForm.innerText = '投稿者：' + data.show_name;
          id_editForm.value = data.id;
          photo_title_editForm.value = data.photo_title;
          place_editForm.value = data.place;
          date_editForm.value = data.date;
          memo_editForm.value = data.memo;

          const previewElement = document.getElementById('photo_editForm');
          previewElement.src = `${CONFIG.baseUrl}/storage/photos/${data.url}`;
        }

        //編集送信処理
        function handleEditSubmit() {
          const edit_submit = document.getElementById('edit_submit');

          edit_submit.replaceWith(edit_submit.cloneNode(true));
          const new_edit_submit = document.getElementById('edit_submit');

          new_edit_submit.addEventListener('click', async () => {
            new_edit_submit.disabled = true;

            const edit_url = `{{ route('photos') }}/edit`;
            const body = new FormData();
            body.append('id', id_editForm.value);
            body.append('photo_title', photo_title_editForm.value);
            body.append('place', place_editForm.value);
            body.append('date', date_editForm.value);
            body.append('memo', memo_editForm.value);

            try {
              const response = await fetch(edit_url, {
                method: 'POST',
                mode: 'cors',
                cache: 'no-cache',
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': xCsrfToken },
                redirect: 'follow',
                referrerPolicy: 'no-referrer',
                body
              });

              const result = await response.json();

              if (result.result === 'success') {
                alert('編集を完了しました。');
                location.href = thisUrl;
              } else {
                alert('編集に失敗しました。');
                new_edit_submit.disabled = false;
              }

            } catch (error) {
              console.error('編集送信エラー:', error);
              alert('編集中にエラーが発生しました。');
              new_edit_submit.disabled = false;
            }
          });
        }

        //モーダル表示時の初期化
        async function initializeEditModal(event) {
          try {
            const button = event.relatedTarget;
            const id_edit = button.getAttribute('data-bs-whatever');
            const photoData = await fetchPhotoData(id_edit);
            
            populateEditForm(photoData);
            handleEditSubmit();

          } catch (error) {
            console.error('モーダル初期化エラー:', error);
            alert('データの取得に失敗しました。');
          }
        }

        // モーダル表示イベントにフック
        photoEditModal.addEventListener('show.bs.modal', initializeEditModal, false);

        //削除アクション
        async function sendDeleteRequest(id) {
          const body = new FormData();
          body.append('id', id);
          const url = `{{ route('photos') }}/delete`;
          try {
            const response = await fetch(url, {
              method: "POST",
              mode: "cors",
              cache: "no-cache",
              credentials: "same-origin",
              headers: { 'X-CSRF-TOKEN': xCsrfToken },
              redirect: "follow",
              referrerPolicy: "no-referrer",
              body: body
            });
            const json = await response.json();
            return json;
          } catch (error) {
            console.error('通信エラー:', error);
            throw new Error('通信エラーが発生しました');
          }
        }

        // 削除ボタンのクリックハンドラ（イベントロジック）
        async function handleDeleteClick() {
          const deleteCode = delBtn.getAttribute('data-bs-whatever');
          const confirmed = confirm("本当に削除してよいですか？削除すると元に戻せません。");

          if (!confirmed) return;

          try {
            const result = await sendDeleteRequest(deleteCode);
            if (result.result === 'success') {
              alert('削除に成功しました。');
              location.href = thisUrl;
            } else {
              alert('削除に失敗しました。');
            }
          } catch (err) {
            alert(err.message);
          }
        }

        // イベント登録
        delBtn.addEventListener('click', handleDeleteClick, false);


        const agreeBtn = document.getElementById('agreementButton');
        const postBtn = document.getElementById('postButton');

        // 同意ボタンのクリックイベント
        // [同意] ボタンをクリックすると、[投稿] ボタンが表示される
        async function sendAgreement() {
          const agree_url = `{{ route('agree') }}`;
          try {
            const response = await fetch(agree_url, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': xCsrfToken
              },
              body: JSON.stringify({ agreed: true })
            });

            if (!response.ok) {
              console.error('同意の保存に失敗しました');
              return false;
            }
            console.log('同意が保存されました');
            return true;

          } catch (error) {
            console.error('通信エラー:', error);
            return false;
          }
        }

        async function handleAgreementClick() {
          agreeBtn.classList.add('d-none');
          postBtn.classList.remove('d-none');
          const success = await sendAgreement();
          if (!success) {
            /* 同意の保存に失敗した場合の処理 */
          }
        }

        agreeBtn.addEventListener('click', handleAgreementClick);

        @endif
      
    </script>
    @endslot
</x-kaikon::app-layout>