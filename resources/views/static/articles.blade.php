<x-kaikon::app-layout>
    @slot('header')
    {{ __('messages.Literatures') }}
    @endslot
  <style>
    /* 文献検索結果のスタイル */
    #component-serch-articles .custom-border{ margin-top: -1px; margin-left: -1px; border: 1px solid gray; word-wrap: break-word;}
    #component-serch-articles .mb-2{margin-bottom:0 !important;}
    #component-serch-articles .article_title{ font-size: 1.2em;}
    #component-serch-articles .break-word{ overflow-wrap:break-word; word-break:break-all; }
    #component-serch-articles hr { margin: 0.8rem 0;}

    /*検索条件の横線スタイル*/
    #component-serch-articles td{
      border-width: 0 0 1px 0; /* 上下だけ引く */
      border-color: silver;
      border-style: solid;
      padding: 1em 0;        /* セル内側の余白 */
    }
    #component-serch-articles table{border-spacing: 0 0.8em;}
  </style>
  <div id="component-serch-articles" class="container mt-4 py-2">
    <form id="form" name="search" action="" method="get" target="result">
          <h4 class="ssj my-3 px-3 px-md-0">{{ __('messages.Literatures') }}</h4>
            <noscript>
              <div class="container pt-3 py-2">
                <p class="text-danger fw-bold">検索機能を利用するには、ブラウザーで JavaScript を有効にしてください。</p>
              </div>
            </noscript>
            <div class="container pt-3 py-2">学術出版物、商業誌、同好会誌および図鑑等の単行本の情報を検索できます。</div>
            <div class="container text-center py-2">
          <table style="width:100%; ">
            <tbody>
              <tr>
                <td style="width: 5.2em; text-align-last: justify; border-top: 1px solid silver; font-weight:440; letter-spacing: 0;">{{__('messages.Keywords')}}</td>
                <td style="border-top: 1px solid silver;">
                  <div class="ms-3 me-1">
                    <input name="keyword" type="text" class="form-control" placeholder="論文の表題、種名、地名など">
                  </div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.Taxon')}}</td>
                <td>
                  <div class="ms-3 me-1">
                    <select name="order_id" class="form-select" title="order_id">
                      <option value="">{{ session('locale') == 'en' ? 'select order...' : '目(order)を選択...' }}</option>
                      @foreach ( $orders as $order )
                      <option value="{{ $order->order_id }}">{{ session('locale') == 'en' ? '' : $order->order_ja }} {{ $order->order }}</option>
                      @endforeach
                    </select>
                  </div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.JournalName')}}</td>
                <td>
                  <div class="ms-3 me-1">
                    <select name="journal_code" class="form-select" title="journal_code">
                      <option value="">{{ session('locale') == 'en' ? 'select journal...' : '雑誌名を選択...' }}</option>
                      @foreach ( $journals as $journal )
                      <option value="{{ $journal->journal_code }}">{{ session('locale') == 'en' ? $journal->journal_name_en : $journal->journal_name_ja }}</option>
                      @endforeach
                    </select>
                  </div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.PublishedAt')}}</td>
                <td>
                  <div class="ms-3 me-1"><input name="year" type="text" class="form-control" placeholder="20XX"></div>
                </td>
              </tr>
              <tr>
                <td style="text-align-last: justify; font-weight:440;">{{__('messages.Author')}}</td>
                <td>
                  <div class="ms-3 me-1"><input name="author" type="text" class="form-control" placeholder="山梨太郎"></div>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="text-center mt-4 mb-4">
            <button id="searchBtn" class="btn btn-secondary mb-2">{{__('messages.ArticleSearch')}}</button>　　
            <button class="btn btn-secondary mb-2" type="reset">{{__('messages.ArticleReset')}}</button>
          </div>
        </div>
    </form>
    <form class="d-none">
      <label for="httpquery">httpquery</label><input name="httpquery" id="httpquery"></input>
    </form>
    <div id="app" style="text-align: start;"></div>
    <div id="number_of_show"></div>
    <div id="next_page_loader"></div>
  </div>





  @slot('modal')
  <div class="modal fade" id="ModalItemDetail" tabindex="-1" aria-labelledby="ModalArticle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg z-3">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">
            <span id="title">題名</span>
            @if (Auth::check())
            <div class="d-inline row" style="margin-right: 12px;">
              <a id="editArticleBtn" target="_blank" rel="noopener">
                <svg class="bi ms-1 me-2" width="1.2em" height="1.2em"><use xlink:href="./svg/article_symbols.svg#edit"></use></svg>
              </a>
            </div>
            @endif
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="閉じる"></button>
        </div>
          
        <div class="modal-body">
          <div class="mb-3">
            <table class="table">
              <tbody>
                <tr><th colspan="2">詳細情報</th></tr>
                <tr><td style="width:5em !important;">{{__('messages.Author')}}</td><td id="author" class="text-break"></td></tr>
                <tr><td>{{__('messages.PublishedAt')}}</td><td id="year"></td></tr>
                <tr><td>{{__('messages.JournalName')}}</td><td id="journal_name" class="text-break"></td></tr>
                <tr><td>{{__('messages.VolumeNumber')}}</td><td id="volno"></td></tr>
                <tr><td>{{__('messages.Page')}}</td><td id="page"></td></tr>
                <tr><td>カテゴリ</td><td id="category" class="text-break"></td></tr>
                <tr><td>{{__('messages.Link')}}</td><td id="link" class="text-break"></td></tr>
                <tr><td>{{__('messages.Comment')}}</td><td id="comment" class="text-break"></td></tr>
                @if (Auth::check())
                <tr><td>ファイル</td>
                  <td class="break-word"><span id="fileInfo"></span></td>
                </tr>
                <tr>
                  <td>種データ</td>
                  <td class="break-word">
                    <div class="text-left">
                      <div class="d-inline row" style="margin-left: 12px; margin-right: 12px;">
                        <a id="openSpeciesListBtn" class="col btn btn-secondary d-inline" target="_blank" rel="noopener">リスト</a>
                        <div id="inputLockBtn" class="col btn btn-danger d-inline">完了</div>
                        <div id="unLockBtn" class="col btn btn-danger d-inline">ロック解除</div>
                      </div>
                    </div>
                  </td>
                </tr>
                @endif

              </tbody>
            </table>
            <div id="registration_date" class="small text-end small mb-4">registration_date</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  @endslot





  
  @slot('scripts')
    <script src ="{{url('/')}}/js/nextPageLoader.js"></script>
    <script>
      const BASEURL = CONFIG.baseUrl;
      const httpquery = document.getElementById('httpquery')
      const searchBtn = document.getElementById('searchBtn');
      const openSpeciesListBtn = document.getElementById('openSpeciesListBtn');
      const inputLockBtn = document.getElementById('inputLockBtn');
      const unLockBtn = document.getElementById('unLockBtn');
      const Modal1 = document.getElementById('ModalItemDetail');

      document.getElementById("form").onsubmit = function(){ return false }

      function generateQuery(){
        let optionNames = ['author', 'journal_code', 'keyword', 'order_id', 'year'];
        let formData = Object;
        let searchFlg = false;
        optionNames.forEach(optionName => {
            let strOptionInput = document.forms['search'].elements[optionName].value;
            formData[optionName] = strOptionInput ?? '';
            if (strOptionInput) searchFlg = true;
        });
        httpquery.value = searchFlg ? new URLSearchParams(formData).toString() : '';
      }
      
      // 検索結果表示エリアを初期化
      function refreshPage(){
        app.innerHTML = '';
        number_of_show.innerText = '';
        next_page_loader.innerHTML = '';
      }

      // 検索結果表示エリアに検索結果を表示
      function renderSearchHeader(jsonx, search_option, page) {
        if (page !== '' && page !== 1) return;

        app.insertAdjacentHTML('beforeend', '<h4 class="my-3 px-3 px-md-0">{{__("messages.SearchResult")}}</h4>');

        if (jsonx.too_many) {
          app.insertAdjacentHTML('beforeend', 'ヒット件数が100件を超えました。検索条件をご確認ください。<br>');
        } else if (jsonx.total === 0) {
          app.insertAdjacentHTML('beforeend', '該当はありませんでした。<br>');
        } else {
          app.insertAdjacentHTML('beforeend', `${jsonx.total}件がヒットしました。<br>`);
        }

        app.insertAdjacentHTML('beforeend', `検索条件：${search_option.join(' ')}<br><hr>`);
      }

      function renderSearchResults(data) {
        data.forEach((item, index) => {
          let html = `
            <a href="" class="article_title text-decoration-none" 
              data-bs-toggle="modal" 
              data-bs-target="#ModalItemDetail" 
              data-bs-whatever="${item.random_id}">
              ${item.title}
            </a>
          `;

          if (item.document === 1) {
            html += '<span class="badge rounded-pill bg-danger mx-1">PDF</span>';
          }
          if (item.inventory === 1) {
            html += '<span class="badge rounded-pill bg-secondary mx-1">Inventory</span>';
          }

          html += `
            <a class="badge rounded-pill bg-white text-dark border text-decoration-none ms-3" 
              data-bs-toggle="popover" 
              data-bs-placement="bottom" 
              data-bs-content-id="popover-content-${index + 1}" 
              tabindex="0" role="button">＋</a>
            <div id="popover-content-${index + 1}" class="d-none">
              <a class="btn btn-outline-secondary" href="./articles/${item.random_id}/edit">編集</a>
              <a class="btn btn-outline-danger" href="./articles/${item.random_id}/delete">削除</a>
            </div>
            <div>${item.summary}</div><hr>
          `;

          app.insertAdjacentHTML('beforeend', html);
        });
      }

      function renderPagination(jsonx) {
        const nextPageLoaderInstance = new NextPageLoader({
          current_page: jsonx.current_page,
          last_page: jsonx.last_page,
          per_page: jsonx.per_page,
          total: jsonx.total
        });

        nextPageLoaderInstance.printBtn();
        nextPageLoaderInstance.printMsg();
      }

      async function searchPage(page = '') {
        setTimeout(async () => {
          number_of_show.innerText = "";

          const urlHttpQuery = httpquery.value;
          if (!urlHttpQuery) return false;

          const url = `{{ route('home') }}/articles/search?&${urlHttpQuery}&page=${page}`;

          try {
            const response = await fetch(url);
            const jsonx = await response.json();

            const search_option = Object.entries(jsonx.search_option)
              .filter(([_, value]) => value !== null && value !== '')
              .map(([_, value]) => value);

            renderSearchHeader(jsonx, search_option, page);
            renderSearchResults(jsonx.data);
            renderPagination(jsonx);

            addModalEventListeners();
            return true;
          } catch (error) {
            console.error("検索失敗:", error);
            return false;
          }
        }, 50);
      }


      function toggleClasses(element, addClasses, removeClasses) {
          removeClasses.forEach(cls => {
              if (element.classList.contains(cls)) {
                  element.classList.remove(cls);
              }
          });
          addClasses.forEach(cls => {
              if (!element.classList.contains(cls)) {
                  element.classList.add(cls);
              }
          });
      }
      function generatePostData(body){
          const tokenEle = document.querySelector('meta[name="csrf-token"]');
          return {
              method: "POST",
              mode: "cors", 
              cache: "no-cache",
              credentials: "same-origin",
              headers: {
                  'X-CSRF-TOKEN': tokenEle.getAttribute('content'),
              },
              redirect: "follow",
              referrerPolicy: "no-referrer",
              body
          };
      }
      function lockBtnClick(on){
          if (typeof on !== 'boolean') { throw new TypeError('不正な値が送信されました。');}
          inputLockBtn.disabled = !inputLockBtn.disabled;
          unLockBtn.disabled = !unLockBtn.disabled;

          const edit_url = `./records/complete`;
          let body = new FormData();
          body.append('article_id', inputLockBtn.getAttribute('article-id'));
          body.append('on', on);
          let postData = generatePostData(body);
          fetch(edit_url, postData)
          .then(response => response.json())
          .then(data => {
              if (!data.result){ throw new Error('切替え処理に失敗しました。'); }
          })
          .catch(error => {
              console.error('エラー:', error);
              alert(`エラーが発生しました：${error}`);
          });
      }
      function handleUnlockClick(event){
           lockBtnClick(false);
           enableInputLockBtn();
           event.currentTarget.removeEventListener('click', handleUnlockClick);
      }
      function handleInputLockClick(event){
           lockBtnClick(true);
           enableUnLockBtn();
           event.currentTarget.removeEventListener('click', handleInputLockClick);
      }
      function enableUnLockBtn () {
          toggleClasses(unLockBtn, ['d-inline'], ['d-none']);
          toggleClasses(inputLockBtn, ['d-none'], ['d-inline']);
          unLockBtn.addEventListener('click', handleUnlockClick, false);
      }
      function enableInputLockBtn () {
          toggleClasses(unLockBtn, ['d-none'], ['d-inline']);
          toggleClasses(inputLockBtn, ['d-inline'], ['d-none']);
          inputLockBtn.addEventListener('click', handleInputLockClick, false);
      }
      // 検索
      searchBtn.addEventListener('click', () => {
        generateQuery();
        refreshPage();
        searchPage();
      }, false);

      function addModalEventListeners(){
        setTimeout(() => {
          Modal1.addEventListener('show.bs.modal', event => {
            // モーダルを起動するボタン
            const button1 = event.relatedTarget;
            let article_code = button1.getAttribute('data-bs-whatever')
              let url2 = `{{ route('home') }}/articles/${article_code}/show`
              fetch(url2)
              .then(function (response) {
              return response.json();
              })
              .then(function (json) {

                  //文献詳細情報を表示
                  title.innerHTML = json.title ?? '';
                  year.textContent = (json.year ?? '') + '年';
                  author.textContent = json.author ?? '';
                  journal_name.textContent = json.journal_name ?? '';
                  page.textContent = json.page ?? '';
                  category.textContent = json.order_names ?? '';
                  volno.textContent = json.vol_no ?? '';
                  comment.textContent = json.comment ?? '';
                  registration_date.textContent = json.registration_date ?? '';
                  link.innerHTML = json.link.length >= 2 
                  ? `<a href="${json.link ?? ''}" target="_blank" rel="noopener">${json.link ?? ''}</a>${json.provided_by ?? ''}`
                  : ' ';

                  
                  //認証済ユーザオプションを表示
                  openSpeciesListBtn.href=`{{ route('home') }}/articles/${article_code}/species`;
                  inputLockBtn.setAttribute('article-id', json.id);
                  editArticleBtn.href= `{{ route('home') }}/articles/${article_code}/edit`;
                  const fileInfo = document.getElementById('fileInfo');
                  fileInfo.innerHTML = '';

                  console.log(json.documents);
                  if(json.documents){
                    json.documents.forEach((element) => {
                      fileInfo.innerHTML += `
                      <span class="badge bg-danger me-2">PDF</span>
                      <a class="me-2" href="./articles/documents/${element.file_name}" target="_blank" rel="noopener">
                        ${element.display_title}
                      </a>
                      `;
                    })
                  }
                  json.is_recorded ? enableUnLockBtn() : enableInputLockBtn();
            })
          })
        }, 100);
      }
    </script>
  @endslot
  
</x-kaikon::app-layout>