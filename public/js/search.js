import { DOM } from './dom.js';
import { addModalEventListeners } from './utils.js';

export function generateQuery(){
  let optionNames = ['keyword', 'user_id'];
  let formData = {};
  let searchFlg = false;
  optionNames.forEach(optionName => {
      const element = document.forms['search'].elements[optionName];
      const value = element ? element.value : '';
      formData[optionName] = value ?? '';
      if (value) searchFlg = true;
  });
  DOM.httpquery.value = searchFlg ? new URLSearchParams(formData).toString() : '';
}
export function refreshPage(){
  DOM.app.innerHTML = '';
  number_of_show.innerText = '';
  next_page_loader.innerHTML = '';
}
export function refreshProfileModal(){
  DOM.profileModal.addEventListener('show.bs.modal', (event) => {
    const trigger = event.relatedTarget;
    if (!trigger) return;

    const profileId = trigger.getAttribute('data-bs-whatever');
    const url = `${window.homeUrl}/users/${profileId}`;

    fetch(url)
      .then(response => response.json())
      .then(data => {
        document.getElementById('profile_show_name').innerText = data.show_name;
        document.getElementById('profile_icon').src = `${window.profileUrl}/${data.icon}`;
        document.getElementById('profile_description').innerText = data.description;
        // 戻るボタンの設定は、photoModalが開かれたときに行う
      })
      .catch(err => console.error(err));
  });
}
export async function searchPage(page = '') {
  setTimeout(async () => {
    number_of_show.innerText = "";

    const urlHttpQuery = DOM.httpquery.value;

    // クエリがなくても実行する。
    const url = `${window.homeUrl}/photos/search?&${urlHttpQuery}&page=${page}`;

    try {
      const response = await fetch(url);
      const jsonx = await response.json();

      const search_option = Object.entries(jsonx.search_option || {})
        .filter(([_, value]) => value != null && value !== '')
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
export function renderSearchHeader(jsonx, search_option, page) {
  if (page !== '' && page !== 1) return;
  if (jsonx.total === 0) {
    DOM.app.insertAdjacentHTML('beforeend', '該当はありませんでした。<br>');
  } else {
    DOM.app.insertAdjacentHTML('beforeend', `${jsonx.total}件がヒットしました。<br>`);
  }

  DOM.app.insertAdjacentHTML('beforeend', `検索条件：${search_option.join(' ')}<br><hr>`);
}
export function renderSearchResults(data) {
  data.forEach((item, index) => {
    let html = `
      <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer">
          <div class="d-block" data-bs-toggle="modal" data-bs-target="#photoModal" data-bs-whatever="${item.id}">
              <div class="ratio ratio-4x3 overflow-hidden" style="background-image: url('./storage/photos/${item.thumbnail_url}'); background-size:cover;">
                  <div style="float: right;">`;
                  if(window.authenticated){
                      if (window.userId === item.user_id) {
                        if (item.approved_at == null){
                          html += `                          <div class="m-3 badge bg-secondary">承認待ち</div>`;
                        } else{
                          html += `                          <div class="m-3 badge bg-danger">公開中</div>`;
                        }
                      }
                  }
                  html += `                        </div>
              </div>
              <div class="d-flex align-items-center justify-content-center text-decoration-none">${item.photo_title}</div>
          </div>
      </div>
    `;
    DOM.app.insertAdjacentHTML('beforeend', html);
  });
}
export function renderPagination(jsonx) {
  const nextPageLoaderInstance = new NextPageLoader({
    current_page: jsonx.current_page,
    last_page: jsonx.last_page,
    per_page: jsonx.per_page,
    total: jsonx.total
  });

  const created = nextPageLoaderInstance.printBtn();
  if (created) {
      const nextPageBtn = document.getElementById('next_page_btn');
      if (nextPageBtn) {
          nextPageBtn.addEventListener('click', () => {
              const currentPage = parseInt(nextPageBtn.getAttribute('data-current-page'));
              searchPage(currentPage + 1);
          });
      }
  }
  nextPageLoaderInstance.printMsg();
}