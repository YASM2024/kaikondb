// specimen/search.js

import { DOM } from './dom.js';
import { fetchSpecimens } from './api.js';
import { normalizeList } from './normalize.js';
import {
  renderLoading,
  renderError,
  renderGrid,
  renderCount,
  renderSpecimenCard,
} from './render.js';

let currentAbort = null;
let isLoadingNextPage = false;

const STATE = {
  baseUrl: CONFIG.baseUrl,
  endpoint: '/specimens/search',
  syncUrl: true,
  perPage: 12,
  params: {},
};

function ensureNextPageArea() {
  // 標本ページでは #pagination がある前提（あなたのHTML貼り付けに存在）
  // その中に NextPageLoader が参照する #number_of_show / #next_page_loader を動的に用意する
  const root = document.getElementById('pagination');
  if (!root) return;

  let msg = document.getElementById('number_of_show');
  if (!msg) {
    msg = document.createElement('div');
    msg.id = 'number_of_show';
    msg.className = 'text-muted small';
    root.appendChild(msg);
  }

  let loader = document.getElementById('next_page_loader');
  if (!loader) {
    loader = document.createElement('div');
    loader.id = 'next_page_loader';
    loader.className = 'mt-2';
    root.appendChild(loader);
  }
}

function clearNextPageArea() {
  const msg = document.getElementById('number_of_show');
  if (msg) msg.innerText = '';
  const loader = document.getElementById('next_page_loader');
  if (loader) loader.innerHTML = '';
}

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

function hasAnyFilter(params) {
  return params && Object.keys(params).length > 0;
}

// 追加描画（append）
function appendGrid(items, container = DOM.app) {
  if (!container) return;
  if (!Array.isArray(items) || items.length === 0) return;

  // initGrid と同じ grid クラスだけ揃える（ただしクリアはしない）
  container.className = 'row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3';

  const frag = document.createDocumentFragment();
  for (const s of items) frag.appendChild(renderSpecimenCard(s));
  container.appendChild(frag);
}

export function refreshPage() {
  if (DOM.app) DOM.app.innerHTML = '';
  renderCount(null);
  clearNextPageArea();
}

/**
 * 検索の初期化（フォームbind + 初回ロード + 戻る/進む対応）
 */
export function initSearch({
  baseUrl = CONFIG.baseUrl,
  endpoint = '/specimens/search',
  syncUrl = true,
  autoRun = true, // ※ただし「URLに検索条件がある場合のみ」初回検索する
  perPage = 12,
} = {}) {
  if (!DOM.form) return;

  STATE.baseUrl = baseUrl;
  STATE.endpoint = endpoint;
  STATE.syncUrl = syncUrl;
  STATE.perPage = perPage;

  ensureNextPageArea();

  // submit：検索（1ページ目から）
  DOM.form.addEventListener('submit', (e) => {
    e.preventDefault();

    const params = buildBodyFromForm(DOM.form);
    STATE.params = params;

    if (syncUrl) applyBodyToUrl(params);

    // 1ページ目から（reset=true）
    searchPage(1, true);
  });

  // 取り消し：初期状態へ（＝検索は走らせず、何も表示しない）
  if (DOM.cancelBtn) {
    DOM.cancelBtn.addEventListener('click', (e) => {
      e.preventDefault();
      DOM.form.reset();

      STATE.params = {};
      if (syncUrl) clearUrlQuery();
      refreshPage();
    });
  }

  // 戻る/進む（URLからフォーム復元 → 条件ありなら検索 / なしなら初期状態）
  window.addEventListener('popstate', () => {
    if (!syncUrl) return;

    applyUrlToForm();
    const params = buildBodyFromForm(DOM.form);
    STATE.params = params;

    if (hasAnyFilter(params)) {
      searchPage(1, true);
    } else {
      refreshPage();
    }
  });

  // 初回：
  // - URLに検索条件があるなら検索
  // - なければ「何も表示しない」
  if (autoRun) {
    if (syncUrl) applyUrlToForm();

    const params = buildBodyFromForm(DOM.form);
    STATE.params = params;

    if (hasAnyFilter(params)) {
      searchPage(1, true);
    } else {
      refreshPage();
    }
  } else {
    refreshPage();
  }
}

/**
 * Photos と同じ流れ：ページ指定で検索して、結果に応じて「続きを表示」を生成
 */
export async function searchPage(page = 1, reset = false) {
  if (isLoadingNextPage) return false;
  isLoadingNextPage = true;
  setNextButtonLoading(true);

  try {
    // 連打時は前回をキャンセル
    if (currentAbort) currentAbort.abort();
    currentAbort = new AbortController();

    if (reset) {
      renderLoading(DOM.app);
      clearNextPageArea();
    }

    const params = {
      ...(STATE.params || {}),
      page,
      per_page: STATE.perPage,
    };

    const json = await fetchSpecimens({
      baseUrl: STATE.baseUrl,
      endpoint: STATE.endpoint,
      params,
      signal: currentAbort.signal,
    });

    const { total, items } = normalizeList(json);
    renderCount(total);

    if (reset || page === 1) {
      renderGrid(items, DOM.app);
    } else {
      appendGrid(items, DOM.app);
    }

    renderPagination(json);
    return true;
  } catch (err) {
    if (err?.name === 'AbortError') return false;
    console.error(err);
    renderError(DOM.app, '標本データの取得に失敗しました。');
    renderCount(null);
    clearNextPageArea();
    return false;
  } finally {
    isLoadingNextPage = false;
    setNextButtonLoading(false);
  }
}

/**
 * 後方互換：従来の runSearch({ baseUrl, endpoint, body }) を呼んでいた箇所があっても動くように
 */
export async function runSearch({ baseUrl, endpoint, body } = {}) {
  if (baseUrl) STATE.baseUrl = baseUrl;
  if (endpoint) STATE.endpoint = endpoint;
  STATE.params = body || {};
  return await searchPage(1, true);
}

export function renderPagination(json) {
  ensureNextPageArea();

  // NextPageLoader が読み込まれていない環境でも落とさない
  if (typeof NextPageLoader === 'undefined') {
    clearNextPageArea();
    return;
  }

  const nextPageLoaderInstance = new NextPageLoader({
    current_page: json.current_page ?? 1,
    last_page: json.last_page ?? 1,
    per_page: json.per_page ?? STATE.perPage,
    total: json.total ?? json.count ?? 0,
  });

  const created = nextPageLoaderInstance.printBtn();

  if (created) {
    const nextPageBtn = document.getElementById('next_page_btn');
    if (nextPageBtn) {
      // 文言を要件に寄せる（NextPageLoader本体は触らずに上書き）
      nextPageBtn.textContent = '次の12件を表示';
      nextPageBtn.dataset.originalText = '次の12件を表示';

      // クリック時：次ページを append
      nextPageBtn.addEventListener(
        'click',
        async () => {
          if (isLoadingNextPage) return;
          const currentPage = parseInt(nextPageBtn.getAttribute('data-current-page'), 12) || 1;
          await searchPage(currentPage + 1, false);
        },
        { passive: true },
      );
    }
  }

  nextPageLoaderInstance.printMsg();
}

/**
 * フォームからGET paramsを作る（空値は落とす）
 * - name属性をそのままキーにする（q/locality/date/collected_by...）
 */
export function buildBodyFromForm(form) {
  const fd = new FormData(form);
  const body = {};
  for (const [k, v] of fd.entries()) {
    const val = String(v ?? '').trim();
    if (!val) continue;
    body[k] = val;
  }
  return body;
}

/* ===== URL同期（任意） ===== */
/**
 * URLのクエリをフォームへ反映（?q=... など）
 * - form の name に一致するものだけセット
 */
export function applyUrlToForm(form = DOM.form) {
  if (!form) return;
  const params = new URLSearchParams(window.location.search);
  const fields = Array.from(form.elements).filter((el) => el?.name);

  // 一旦クリア（リセットはしない：placeholderなど壊さない）
  for (const el of fields) {
    // checkbox/radio等は必要なら拡張
    if (el.type === 'checkbox' || el.type === 'radio') continue;
    el.value = '';
  }

  for (const el of fields) {
    const name = el.name;
    if (!name) continue;
    if (!params.has(name)) continue;
    const value = params.get(name) ?? '';

    if (el.type === 'checkbox' || el.type === 'radio') {
      // 必要なら実装
      continue;
    }
    el.value = value;
  }
}

/**
 * params を URLクエリへ反映（ページ遷移しない）
 */
export function applyBodyToUrl(body) {
  const sp = new URLSearchParams();
  for (const [k, v] of Object.entries(body || {})) {
    if (v === undefined || v === null || v === '') continue;
    sp.set(k, String(v));
  }
  const newUrl = sp.toString()
    ? `${window.location.pathname}?${sp.toString()}`
    : window.location.pathname;
  history.pushState({}, '', newUrl);
}

/**
 * URLクエリを消す
 */
export function clearUrlQuery() {
  history.pushState({}, '', window.location.pathname);
}