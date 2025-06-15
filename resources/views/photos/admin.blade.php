<x-kaikon::app-layout>
    @slot('header')
    昆虫写真承認・差戻し
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

    <div id="component-serch-photos" >
        <div class="container mt-4 py-2">
            <h4 class="my-3 px-3 px-md-0">昆虫写真承認・差戻し</h4>
            <noscript><div class="container pt-3 py-2"><p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p></div></noscript>
        </div>
    </div>
</div>
<div>
    <div>
        <div class="py-2 mx-1 row">
            @foreach($photos as $photo)
            <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer">
                <div class="d-block" data-bs-toggle="modal" data-bs-target="#Modal" data-bs-whatever="{{$photo->id}}">
                    @if ( \Illuminate\Support\Facades\Auth::check() && $photo->approved_at == null )
                    <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('../storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;">
                      <div style="float: right;">
                        <div class="m-3 badge bg-danger">承認待ち</div>
                      </div>
                    </div>
                    @else
                    <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('../storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;"></div>
                    @endif
                    <div class="d-flex align-items-center justify-content-center text-decoration-none">{{$photo->photo_title}}</div>
                </div>
            </div>
            @endforeach
        </div>
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
                <div id="editAndDelete" class="d-none" style="float: right; padding-right: 1em;">
                    <span id="editBtn" data-bs-toggle="modal" data-bs-target="#Modal-edit">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#edit"></use></svg>
                    </span>
                    <span id="delBtn">
                        <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="#delete"></use></svg>
                    </span>
                </div>
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
              <button id="deleteBtn" type="button" class="d-none btn btn-outline-danger">承認</button>
          </div><!-- /.modal-footer -->
        </div><!-- /.modal-content -->
      </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    @slot('scripts')
    <script>

      const Modal = document.getElementById('Modal');
      const viewDataPlace = Modal.querySelector('span[name="place"]');
      const viewDataDate = Modal.querySelector('span[name="date"]');
      const viewDataPhotographer = Modal.querySelector('span[name="photographer"]');
      const viewDataMemo = Modal.querySelector('span[name="memo"]');
      // const ModalLabel = Modal.querySelector('#ModalLabel');
      // const photo_url = Modal.querySelector('#photo_url');
      // const editDeleteIcons = Modal.querySelector('#editAndDelete');
      // const editBtn = Modal.querySelector('#editBtn');
      // const delBtn = Modal.querySelector('#delBtn');
      // const profileModal = document.getElementById('profileModal');

      // 写真モーダル表示
      Modal.addEventListener('show.bs.modal', () => {
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
          <div class="d-inline"><img src="../storage/profile/${json.icon}" class="round img-fluid" style="width:1.4em; height:1.4em; border-radius:50%;">
          ${json.show_name}</div>`;
          viewDataMemo.innerText = json.memo;

          viewDataPlace.setAttribute('value', json.place);
          viewDataDate.setAttribute('value', json.date);
          viewDataMemo.setAttribute('value', json.memo);

          photo_url.setAttribute('src', `{{url('/storage/photos')}}/${json.url}`);
          Modal.setAttribute('code', json.id);
          if( json.user_id === {{\Kaikon2\Kaikondb\Models\User::fromAppUser(\Illuminate\Support\Facades\Auth::user())->id}} ) editDeleteIcons.classList.remove('d-none');
          editBtn.setAttribute( 'data-bs-whatever', json.id)
          delBtn.setAttribute( 'data-bs-whatever', json.id)
        })
      })

      // 写真モーダル非表示
      Modal.addEventListener('hidden.bs.modal', () => {
          ModalLabel.innerText = 'title';
          viewDataMemo.innerText = 'memo';
          viewDataPhotographer.innerText = 'photographer';
          viewDataPlace.innerText = 'place';
          viewDataDate.innerText = 'date';
          photo_url.setAttribute('src', `{{url('/storage')}}/img/wait.png`);
          document.querySelectorAll('.view_data').forEach(function(ele){ele.removeAttribute('value')})
          Modal.setAttribute('code','')
      })

    </script>
    @endslot

</x-kaikon::app-layout>