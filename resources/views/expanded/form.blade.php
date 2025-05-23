<x-kaikon::app-layout>
    @slot('header')
    運営情報管理
    @endslot

    <style>
    /* プロジェクト名の高さをナビゲーションと同じにする */
    header h3 {  line-height: 3rem;}
    /* コンテナのカスタマイズ */
    @media (min-width: 768px) {  .container {    max-width: 736px;  }}
    /* アイコン（オンマウスで色づく） */
    .icon-articles, .icon-species { box-sizing: border-box; border: 1px solid #ffffff; fill:#222222;}
    .icon-articles:hover, .icon-species:hover { box-sizing: border-box; border: 1px solid #333399; color:#333399; fill:#333399;}
    .convey-icon-color{ fill: inherit;}
    .st0{ fill:inherit; } .st0:hover{ fill:#333399; }
    .custom-border{
        margin-top: -1px;
        margin-left: -1px;
        border: 1px solid black;
    }

    /** textarea 高さ可変対応 */
    .FlexTextarea {
      height: 10em; /* フォーカスアウト時の高さ */
      transition: height 0.2s ease;
    }

    .FlexTextarea:focus {
      field-sizing: content;
      height: auto;
    }
    </style>

    <!-- アイコンの設定 -->
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">

      <symbol id="x" viewBox="0 0 512 512"><style>.x{fill:#4B4B4B;}</style>
        <g>
          <polygon class="x" points="512,89.75 422.256,0.005 256.004,166.256 89.754,0.005 0,89.75 166.255,256 0,422.25 89.754,511.995 
            256.004,345.745 422.26,511.995 512,422.25 345.744,256"></polygon>
        </g>
      </symbol>

      <symbol id="upload" viewBox="0 0 512 512"><style>.uploadicon{fill:#4B4B4B;}</style>
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

    <div class="container mt-4 py-2">
      <h4 class="my-3 px-0 mx-2">
      @if( $action_type ==='create' )
      運営情報追加
      @elseif( $action_type ==='edit' )
      運営情報編集
      @endif
      </h4>
      <div>
        @csrf
        @if( $action_type ==='edit' )
        <input id="id" class="d-none" type="text" name="id" value="{{ @$page->id }}">
        @endif
        <input id="entered" class="d-none" type="text" name="entered" value="1">
        <div class="row mb-0">

          <label for="route_name" class="col-sm-3 custom-border col-form-label text-danger">
              <span>識別子</span>
          </label>
          <div class="col-sm-9 custom-border col-form-label"><input id="route_name" type="text" name="route_name" class="form-control"
          required="true" 
          @if( $action_type ==='edit' )
          disabled readonly
          @endif
          value="{{ old('route_name', @$page->route_name) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="title" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-center">
              <span>表題</span>
              <a data-bs-toggle="collapse" href="#title_en_area" role="button" aria-expanded="false" aria-controls="title_en" class="btn btn-sm btn-secondary collapsed">
              +English
              </a>
          </label>
          <div class="col-sm-9 custom-border col-form-label"><input id="title" type="text" name="title" class="form-control" required="true" value="{{ old('title', @$page->title) }}"></div>
        </div>

        <div id="title_en_area" class="row mb-0 collapse">
          <label for="title_en" class="col-sm-3 custom-border col-form-label">
              <span>(TITLE)</span>
          </label>
          <div class="col-sm-9 custom-border col-form-label"><input id="title_en" type="text" name="title_en" class="form-control" value="{{ old('title_en', @$page->title_en) }}"></div>
        </div>

        <div class="row mb-0">
          <label for="body" class="col-sm-3 custom-border col-form-label text-danger d-flex justify-content-between align-items-start">
              <span>本文</span>
              <a data-bs-toggle="collapse" href="#body_en_area" role="button" aria-expanded="false" aria-controls="body_en" class="btn btn-sm btn-secondary collapsed">
              +English
              </a>
          </label>
          <div class="col-sm-9 custom-border col-form-label">
              <textarea id="body" name="body" class="form-control FlexTextarea" required="true">{{ old('body', @$page->body) }}</textarea>
          </div>
        </div>

        <div id="body_en_area" class="row mb-0 collapse">
          <label for="body_en" class="col-sm-3 custom-border col-form-label">
              <span>(BODY)</span>
          </label>
          <div class="col-sm-9 custom-border col-form-label">
              <textarea id="body_en" name="body_en" class="form-control FlexTextarea">{{ old('body_en', @$page->body_en) }}</textarea>
          </div>
        </div>

        <div class="row mb-0">
          <label for="open" class="col-sm-3 custom-border col-form-label">
            公開設定
          </label>
          @if( $action_type ==='create' )
          <div class="col-sm-9 custom-border col-form-label">
              <span>下書き（非公開）</span>
              <input class="d-none" type="radio" name="open" id="radioOn" value="0" checked>
          </div>
          @elseif( $action_type ==='edit' )
          <div class="col-sm-9 custom-border col-form-label">
              <div class="form-check form-check-inline">
                  <input class="form-check-input cursor-pointer" type="radio" name="open" id="radioOn" value="1"
                      {{ old('open', $page->open ?? '') == 1 ? 'checked' : '' }}>
                  <label class="form-check-label cursor-pointer" for="radioOn">ON</label>
              </div>
              <div class="form-check form-check-inline">
                  <input class="form-check-input cursor-pointer" type="radio" name="open" id="radioOff" value="0"
                      {{ old('open', $page->open ?? '') == 0 ? 'checked' : '' }}>
                  <label class="form-check-label cursor-pointer" for="radioOff">OFF</label>
              </div>
          </div>
          @endif
        </div>

        <div class="row mb-0">
          <label for="seq" class="col-sm-3 custom-border col-form-label">
            公開順
          </label>
          <div class="col-sm-9 custom-border col-form-label">
              <select name="seq" class="form-select" aria-label="Default select example">
                  @foreach($seqs as $p)
                      <option value="{{ $p }}" 
                          {{ old('seq', $page->seq ?? -1) == $p ? 'selected' : '' }}>
                          {{ $p }}
                      </option>
                  @endforeach
                  @if( $action_type ==='create' )
                  <option value="{{ max($seqs) + 1 }}" 
                      {{ old('seq', $page->seq ?? -1) == -1 ? 'selected' : '' }}>
                      末尾
                  </option>
                  @endif
              </select>
          </div>
        </div>

      </div>
    <br>

      <div class="center-button">
        <button class="btn btn-primary" id="main" type="button">確認</button>
        @if( $action_type ==='edit' )
        <button class="btn btn-danger" id="deleteBtn" type="button">削除</button>
        @endif
      </div>
    </div>
  

    @slot('scripts')
    <script>
      function getSelectedValue(name) {
        const selectedRadio = document.querySelector(`input[name="${name}"]:checked`);
        return selectedRadio ? selectedRadio.value : null;
      }

      document.getElementById('main').addEventListener('click', async function() {
        let hasError = [];
        const inputs = document.querySelectorAll('input[type="text"][required="true"], textarea[required="true"]');

        inputs.forEach(input => {
          if (!input.value.trim()) { 
            hasError.push(input);
            input.classList.add('error');
          } else {
            input.classList.remove('error');
          }
        });
        if (hasError.length > 0) {
          alert(`全ての必須項目を入力してください: ${hasError.map(input => input.name).join(', ')}`);
          return;
        }
        
        try {
          const action = '{{ $action_type ==="edit" ? route("expanded_page.update") : route("expanded_page.create") }}';
          const response = await fetch(action, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, // CSRFトークンを送信
              'Content-Type': 'application/json', // JSON形式を指定
            },
            body: JSON.stringify({
                id: document.getElementById('id') !== null ? document.getElementById('id').value : 'new',
                route_name: document.getElementById('route_name').value,
                title: document.getElementById('title').value,
                title_en: document.getElementById('title_en').value !=='' ? document.getElementById('title_en').value : document.getElementById('title').value,
                body: document.getElementById('body').value,
                body_en: document.getElementById('body_en').value !=='' ? document.getElementById('body_en').value : document.getElementById('body').value,
                open: getSelectedValue('open'),
                seq: document.querySelector('select[name="seq"]').value,
              }),
          });

          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          
          const result = await response.json();
          if (result.res === 0) {
              console.log('Success:', result); alert('送信に成功しました！');
              window.location.href = "{{ route('expanded_page.index') }}";
          } else {
              console.error('Error:', result); alert('エラーが発生しました。再度試してください。');
          }
        } catch (error) {
          console.error('Error:', error);
          alert('送信に失敗しました。');
        }
      });

      @if( $action_type ==='edit' )
      document.getElementById('deleteBtn').addEventListener('click', async function() {
        if (confirm('本当に削除しますか？')) {
          const deleteUrl = "{{ route('expanded_page.delete') }}"; // 削除用のURL
          try {
            const response = await fetch(deleteUrl, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, // CSRFトークンを送信
                'Content-Type': 'application/json', // JSON形式を指定
              },
              body: JSON.stringify({
                id: document.querySelector('input[name="id"]').value, // 削除するIDを送信
              }),
            });

            if (!response.ok) throw new Error('Network response was not ok');
            const result = await response.json();
            if (result.res === 0) {
                console.log('Success:', result); alert('削除しました');
                window.location.href = "{{ route('expanded_page.index') }}";
            } else {
                console.error('Error:', result); alert('エラーが発生しました。再度試してください。');
            }
          } catch (error) {
            console.error('Error:', error);
            alert('送信に失敗しました。');
          }
        }
      });
      @endif
    </script>
    @endslot

</x-kaikon::app-layout>