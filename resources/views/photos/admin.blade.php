<x-kaikon::app-layout>
  @slot('header')
    投稿写真管理（承認・却下）
  @endslot

  <div class="container py-2">
      <h4 class="my-3 px-3 px-md-0">昆虫写真承認・差戻し</h4>
      <noscript><div class="container pt-3 py-2"><p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p></div></noscript>
      ステータス：
      <select class="form-control mb-2">
        <option value="1" selected>未承認</option>
        <option value="2">承認済み</option>
      </select>
      <div>詳細検索：<div>
      <div>
        <select class="form-control my-2">
          <option value="" selected>投稿者を選択</option>
          <option value="1">MY</option>
          <option value="2">AKI</option>
          <option value="3">竹石</option>
        </select>
        <input type="text" class="form-control my-2" name="keyword" placeholder="キーワード"></input>
      </div>
      <hr>
      <div class="row border-bottom">
          <div class="col">写真</div>
          <div class="col">タイトル</div>
          <div class="col">詳細</div>
          <div class="col">投稿者</div>
      </div>
      @foreach($photos as $photo)
      <div class="row border-bottom" data-bs-toggle="modal" data-bs-target="#Modal" data-bs-whatever="{{$photo->id}}">
          <div class="col ratio ratio-4x3 overflow-hidden" style="background-image: url('../storage/photos/{{$photo->thumbnail_url}}'); background-size:cover;"></div>
          <div class="col">{{$photo->photo_title}}</div>
          <div class="col">{{$photo->memo}}</div>
          <div class="col">{{$photo->photographer}}</div>
      </div>
      @endforeach        
  </div>

  <!-- モーダルの設定 -->
  <div class="modal fade" id="Modal" tabindex="-1" aria-labelledby="ModalLabel">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="ModalLabel"></h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>
        <div class="modal-body">
          <div class="w-100">
            <img src="{{ url('/storage/img/wait.png') }}" id="photo_url" class="w-100">
          </div>
          <table class="border w-100 mt-2">
            <tr class="border"><td class="border p-2">撮影地</td><td class="border p-2"><span id="place"></span></td></tr>
            <tr class="border"><td class="border p-2">撮影日</td><td class="border p-2"><span id="date"></span></td></tr>
            <tr class="border"><td class="border p-2">撮影者</td><td class="border p-2"><span id="photographer"></span></td></tr>
            <tr class="border"><td class="border p-2">コメント</td><td class="border p-2"><span id="memo"></span></td></tr>
          </table>
        </div>
        <div class="modal-footer">
          <button id="approveBtn" type="button" class="btn btn-danger" data-bs-dismiss="modal">承認</button>
        </div><!-- /.modal-footer -->
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->

  @slot('scripts')
    <script>
      const Modal = document.getElementById('Modal')
      const approveBtn = document.getElementById('approveBtn')

      Modal.addEventListener('show.bs.modal', () => {
        const button = event.relatedTarget
        const id = button.getAttribute('data-bs-whatever')
        let url=`{{ url("/photos") }}/${id}/show`;
        fetch(url)
        .then((response) => {
          return response.json();
        })
        .then((json) => {
          ModalLabel.innerText = json.photo_title;
          memo.innerText = json.memo;
          photographer.innerText = json.photographer;
          place.innerText = json.place;
          photo_url.setAttribute('src', "{{ url('/storage/photos') }}/" + json.url);
          date.innerText = json.date;
          approveBtn.setAttribute('code', json.id);
          approveBtn.classList.remove('d-none');
        })
      })

      Modal.addEventListener('hidden.bs.modal', () => {
        ModalLabel.innerText = 'title';
        memo.innerText = 'memo';
        photographer.innerText = 'photographer';
        place.innerText = 'place';
        photo_url.setAttribute('src', "{{ url('/storage/img/wait.png') }}");
        date.innerText = 'date';
        approveBtn.removeAttribute('code');
      })

      const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
      const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

      function cancel(id){
        let url=`{{ url("/photos") }}/${id}/cancel`;
        fetch(url)
        .then((response) => {
          return response.json();
        })
      }

      //承認アクション
      approveBtn.addEventListener('click', function(){
          let elemCode = approveBtn.getAttribute('code')
          let url = `{{ url("/photos") }}/${elemCode}/approve`;
          fetch(url)
          .then((response) => {
            return response.json();
          })
          .then((json) => {
            if( json.result == 'success' ){
              window.location.reload();
            }else{
              alert('承認に失敗しました。');
            }
          })
      }, false)
    </script>
  @endslot

</x-kaikon::app-layout>

