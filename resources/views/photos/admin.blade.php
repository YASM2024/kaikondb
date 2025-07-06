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
    <!-- ページアイコン -->

    
    <div id="component-serch-photos" >
        <div class="container mt-4 py-2">
            <h4 class="my-3 px-3 px-md-0">昆虫写真承認・差戻し</h4>
            <noscript><div class="container pt-3 py-2"><p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p></div></noscript>
            <div class="py-2 mx-1 row">
                @foreach($photos as $photo)
                <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer click-target position-relative" data-url="{{ route('photo.show', ['id' => $photo->id]) }}" style="cursor: pointer;" data-id="{{ $photo->id }}">
                  <!-- 背景画像など -->
                  <img src="../storage/photos/{{ $photo->thumbnail_url }}" class="img-fluid w-100" />
                  <!-- ボタンが上に乗っている -->
                  <div class="position-absolute top-0 mt-2" style="left: 0.8em;" onclick="handleButtonClick(event)">
                    @if( $photo->approved_at == null )
                    <button class="btn btn-danger top-0 me-1 z-1" style="left: 0.8em;" onclick=" accept(event, true); handleButtonClick(event)">承認</button>
                    <button class="btn btn-secondary top-0 me-1 z-1" style="left: 0.8em;" onclick="accept(event, false); handleButtonClick(event)">却下</button>
                    @else
                    <button class="btn btn-secondary top-0 me-1 z-1" style="left: 0.8em;" onclick="handleButtonClick(event)">承認取消</button>
                    @endif
                  </div>
                </div>
                @endforeach
            </div>
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
        <div class="w-100">
          <img src="../storage/img/wait.png" id="photo_url" class="w-100">
        </div>
        <div class="m-2">
          <div id="ModalLabel" class="h4 m-2" style="clear: both;"></div>
          <span name="photographer" class="view_data ms-2"></span>
          <span class="ms-2">
              <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="../svg/icons.svg#map"></use></svg>
              <span name="place" class="view_data" value=""></span>
          </span>
          <span class="ms-2">
              <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="../svg/icons.svg#calendar"></use></svg>
              <span name="date" class="view_data" value=""></span>
          </span>
          <div class="m-2">
              <svg class="bi ms-1" width="1.2em" height="1.2em"><use xlink:href="../svg/icons.svg#memo"></use></svg>
              <span name="memo" class="view_data" value=""></span>
          </div>
        </div>
      </div>
      <div class="modal-footer p-1 conatiner">
        <div class="row w-100">
          <div id="acceptBtn" class="col mx-1 btn btn-primary">承認</div>
          <div id="rejectBtn" class="col mx-1 btn btn-danger">却下</div>
          <div id="cancelBtn" class="col mx-1 btn btn-secondary">承認取消</div>
        </div>
      </div><!-- /.modal-footer -->
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

    @slot('scripts')
    <script>

  document.querySelectorAll('[data-url]').forEach(element => {
    element.addEventListener('click', function(event) {
      // クリックされた要素がボタンやその子孫なら return（モーダル開かない）
      if (event.target.closest('button')) {
        return;
      }

      const modalElement = document.getElementById('photoModal');
      const modal = new bootstrap.Modal(modalElement);
      const url = event.currentTarget.dataset.url;

      const viewDataPlace = document.querySelector('.view_data[name="place"]')
      const viewDataDate = document.querySelector('.view_data[name="date"]')
      const viewDataPhotographer = document.querySelector('.view_data[name="photographer"]')
      const viewDataMemo = document.querySelector('.view_data[name="memo"]')
      const photo_url = document.getElementById('photo_url')
      const ModalLabel = document.getElementById('ModalLabel');

      // console.log('クリックされた要素のURL:', url);

      fetch(url)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then((json) => {
          ModalLabel.innerText = json.photo_title;
          viewDataPlace.innerText = json.place;
          viewDataDate.innerText = json.date;
          viewDataPhotographer.innerHTML = `
          <div class="d-inline">
              <img src="../storage/profile/${json.icon}" class="round img-fluid me-1" style="width:1.4em; height:1.4em; border-radius:50%;">
              ${json.show_name}
          </div>`;
          viewDataMemo.innerText = json.memo;

          viewDataPlace.setAttribute('value', json.place);
          viewDataDate.setAttribute('value', json.date);
          viewDataMemo.setAttribute('value', json.memo);

          photo_url.setAttribute('src', `../storage/photos/${json.url}`);
          modalElement.setAttribute('code', json.id);

          if(json.approved_at == null) {
            document.getElementById('acceptBtn').style.display = 'none';
            document.getElementById('rejectBtn').style.display = 'none';
            document.getElementById('cancelBtn').style.display = 'block';
          } else {
            document.getElementById('acceptBtn').style.display = 'block';
            document.getElementById('rejectBtn').style.display = 'block';
            document.getElementById('cancelBtn').style.display = 'none';
          }
        })
        .then(() => {
          modal.show();
        })
        .catch(error => {
          console.error('There was a problem with the fetch operation:', error);
        });
    });
  });


  function handleButtonClick(event) {
    event.stopPropagation();
  }
  
  function accept(event, acceptOrReject) {
    fetch(`{{ route('photos.accept') }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({
        id: event.currentTarget.closest('.click-target').dataset.id,
        acceptOrReject: acceptOrReject ? 'accept' : 'reject'
      })
    })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
    })
    .then(data => {
      alert(acceptOrReject ? '承認しました。' : '却下しました。');
      // location.reload();
    })
  }
    </script>
    @endslot

</x-kaikon::app-layout>