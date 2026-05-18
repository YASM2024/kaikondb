<x-kaikon::app-layout>
    <x-slot:additionalStyles>
        <link rel="stylesheet" href="{{ url('/css/photos.css') }}">
    </x-slot:additionalStyles>

    <x-slot:header>昆虫写真承認・差戻し</x-slot:header>

    <div id="component-search-photos">
        <div class="container mt-4 py-2">
            @include('kaikon::photos.partials.header-section', [
                'title' => '昆虫写真承認・差戻し',
            ])

            <div class="py-2 mx-1 row">
                @foreach ($photos as $photo)
                    <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer click-target position-relative"
                         data-url="{{ route('photo.show', ['id' => $photo->id]) }}"
                         style="cursor: pointer;"
                         data-id="{{ $photo->id }}">
                        <img src="../storage/photos/{{ $photo->thumbnail_url }}" class="img-fluid w-100" alt="">
                        <div class="position-absolute top-0 mt-2" style="left: 0.8em;" onclick="handleButtonClick(event)">
                            @if ($photo->approved_at == null)
                                <button class="btn btn-danger top-0 me-1 z-1" style="left: 0.8em;" onclick="accept(event, true); handleButtonClick(event)">承認</button>
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

    @include('kaikon::photos.partials.modal-detail', [
        'variant' => 'admin',
        'iconsHref' => '../svg/icons.svg',
        'waitImage' => '../storage/img/wait.png',
    ])

    <x-slot:scripts>
        <script>
          document.querySelectorAll('[data-url]').forEach(element => {
            element.addEventListener('click', function(event) {
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

                  if (json.approved_at == null) {
                    document.getElementById('acceptBtn').style.display = 'block';
                    document.getElementById('rejectBtn').style.display = 'block';
                    document.getElementById('cancelBtn').style.display = 'none';
                  } else {
                    document.getElementById('acceptBtn').style.display = 'none';
                    document.getElementById('rejectBtn').style.display = 'none';
                    document.getElementById('cancelBtn').style.display = 'block';
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
            .then(() => {
              alert(acceptOrReject ? '承認しました。' : '却下しました。');
            });
          }
        </script>
    </x-slot:scripts>
</x-kaikon::app-layout>
