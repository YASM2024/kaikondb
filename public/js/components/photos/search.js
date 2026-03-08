import { DOM } from './dom.js';
import { addModalEventListeners } from './utils.js';

let isLoadingNextPage = false;
function setNextButtonLoading(isLoading, text = '読み込み中...') {
  const btn = document.getElementById('next_page_btn');
  if (!btn) return;
  if (isLoading) {
    btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
    btn.textContent = text;
    btn.classList.add('disabled');
    btn.style.pointerEvents = 'none';
    btn.setAttribute('aria-disabled', 'true');
  } else {
    btn.textContent = btn.dataset.originalText || '続きを表示する';
    btn.classList.remove('disabled');
    btn.style.pointerEvents = '';
    btn.removeAttribute('aria-disabled');
  }
}

export function generateQuery(){
  let optionNames = ['keyword', 'user_id', 'place', 'date'];
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
export async function searchPage(page = '', reset = false) {
  if (isLoadingNextPage) return false;
  isLoadingNextPage = true;
  setNextButtonLoading(true);

  try {
    number_of_show.innerText = "";
    const urlHttpQuery = DOM.httpquery.value;

    // クエリがなくても実行する。
    const url = `${window.homeUrl}/photos/search?&${urlHttpQuery}&page=${page}`;

    const response = await fetch(url);
    const json = await response.json();

    const search_option = Object.entries(json.search_option || {})
      .filter(([_, value]) => value != null && value !== '')
      .map(([_, value]) => value);

    renderSearchHeader(json, search_option, page);
    // 
    await renderSearchResultsLazyImgChunked(json.data, reset);
    renderPagination(json);

    addModalEventListeners();
    return true;

  } catch (error) {
    console.error("検索失敗:", error);
    return false;
  
  } finally {
    isLoadingNextPage = false;
    setNextButtonLoading(false);
  }

}
export function renderSearchHeader(json, search_option, page) {
  if (page !== '' && page !== 1) return;
  if (json.total === 0) {
    DOM.app.insertAdjacentHTML('beforeend', '該当はありませんでした。<br>');
  } else {
    DOM.app.insertAdjacentHTML('beforeend', `${json.total}件がヒットしました。<br>`);
  }

  DOM.app.insertAdjacentHTML('beforeend', `検索条件：${search_option.join(' ')}<br><hr>`);
}

export function renderPagination(json) {
  const nextPageLoaderInstance = new NextPageLoader({
    current_page: json.current_page,
    last_page: json.last_page,
    per_page: json.per_page,
    total: json.total
  });

  const created = nextPageLoaderInstance.printBtn();
  if (created) {
      const nextPageBtn = document.getElementById('next_page_btn');
      if (nextPageBtn) {
          nextPageBtn.addEventListener('click', async () => {
            if (isLoadingNextPage) return;
            const currentPage = parseInt(nextPageBtn.getAttribute('data-current-page'), 10);
            await searchPage(currentPage + 1);
          }, { passive: true });
      }
  }
  nextPageLoaderInstance.printMsg();
}
function escapeHtml(str) {
  return String(str ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}

// 12枚ずつ追加してフリーズ回避（chunked）
export async function renderSearchResultsLazyImgChunked(data, reset = false, chunkSize = 12) {
  if (reset) DOM.app.innerHTML = '';
  for (let i = 0; i < data.length; i += chunkSize) {
    const slice = data.slice(i, i + chunkSize);

    const html = slice.map((item) => {
      // ★ここで必ず badgeHtml を定義する
      let badgeHtml = '';
      if (window.authenticated && window.userId === item.user_id) {
        badgeHtml = (item.approved_at == null)
          ? `<div class="m-2 badge bg-secondary">承認待ち</div>`
          : `<div class="m-2 badge bg-danger">公開中</div>`;
      }

      const thumbUrl = `./storage/photos/${encodeURIComponent(item.thumbnail_url)}`;
      const title = escapeHtml(item.photo_title);

      return `
        <div class="px-1 col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 mb-3 cursor-pointer">
          <div class="d-block" data-bs-toggle="modal" data-bs-target="#photoModal" data-bs-whatever="${item.id}">
            <div class="ratio ratio-4x3 overflow-hidden position-relative">
              <img
                src="${thumbUrl}"
                alt="${title}"
                class="w-100 h-100 object-fit-cover thumb-img"
                loading="lazy"
                decoding="async"
                fetchpriority="low"
              />
              <div class="position-absolute top-0 end-0">${badgeHtml}</div>
            </div>
            <div class="d-flex align-items-center justify-content-center text-decoration-none">
              ${title}
            </div>
          </div>
        </div>
      `;
    }).join('');

    DOM.app.insertAdjacentHTML('beforeend', html);
    await new Promise(requestAnimationFrame);
  }
}
