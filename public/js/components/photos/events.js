import { DOM, DOM_auth } from './dom.js';
import { generateQuery, refreshPage, searchPage } from './search.js';
import { handleCreateSubmit } from './create.js';
import { initializeEditModal } from './update.js';
import { handleDeleteClick } from './delete.js';
import { handleImageFileChange } from './utils.js';

export function addEventListeners(isAuthenticated = false) {

  const form = document.forms['search'];

  // --- 検索UI ---
  DOM.userIdSearchEle?.addEventListener('change', () => {
    generateQuery();
  });

  // 入力変更でクエリを更新（Enter/検索ボタンで実行）
  ['keyword', 'place', 'date'].forEach((name) => {
    const el = form?.elements?.[name];
    if (!el) return;
    el.addEventListener('input', () => generateQuery(), { passive: true });
  });

  const runSearch = async () => {
    generateQuery();
    refreshPage();
    await searchPage(1, true);
  };

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    await runSearch();
  });

  DOM.btnSearch?.addEventListener('click', async (e) => {
    e.preventDefault();
    await runSearch();
  });

  DOM.app.addEventListener('load', (e) => {
    const img = e.target;
    if (!(img instanceof HTMLImageElement)) return;
    if (!img.classList.contains('thumb-img')) return;
    img.classList.add('is-loaded');
  }, true);

  DOM.btnClear?.addEventListener('click', async () => {
    if (form) form.reset();
    generateQuery();
    refreshPage();
    await searchPage(1, true);
  });

  if (!isAuthenticated) return;

  DOM_auth.new_image_file_Ele.addEventListener('change', handleImageFileChange);
  DOM_auth.createSubmitBtn.addEventListener('click', handleCreateSubmit, false);
  DOM_auth.photoEditModal.addEventListener('show.bs.modal', initializeEditModal, false);
  DOM_auth.delBtn.addEventListener('click', () => {
    const code = DOM_auth.delBtn.getAttribute('data-bs-whatever');
    handleDeleteClick(code);
  });

  const postTab = document.getElementById('post-tab');
  const searchTab = document.getElementById('search-tab');
  postTab?.addEventListener('click', () => {
    searchTab?.classList.add('active');
    postTab.classList.remove('active');
  });

}
