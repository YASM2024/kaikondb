// specimen/search.js
import { DOM } from './dom.js';
import { fetchSpecimens } from './api.js';
import { normalizeList } from './normalize.js';
import { renderLoading, renderError, renderGrid, renderCount } from './render.js';

let currentAbort = null;

/**
 * 検索の初期化（フォームbind + 初回ロード + 戻る/進む対応）
 */
export function initSearch({
  baseUrl = CONFIG.baseUrl,
  endpoint = '/specimens/search',   // ←あなたのルートに合わせて
  syncUrl = true,
  autoRun = true,
} = {}) {
  if (!DOM.form) return;

  // submit を横取りして POST検索
  DOM.form.addEventListener('submit', (e) => {
    e.preventDefault();
    const body = buildBodyFromForm(DOM.form);
    if (syncUrl) applyBodyToUrl(body);
    runSearch({ baseUrl, endpoint, body });
  });

  // 取り消し（フォームをリセットして再検索）
  if (DOM.cancelBtn) {
    DOM.cancelBtn.addEventListener('click', (e) => {
      e.preventDefault();
      DOM.form.reset();
      const body = buildBodyFromForm(DOM.form); // 空になる
      if (syncUrl) clearUrlQuery();
      runSearch({ baseUrl, endpoint, body });
    });
  }

  // 戻る/進む（URLからフォーム復元して再検索）
  window.addEventListener('popstate', () => {
    if (!syncUrl) return;
    applyUrlToForm();
    const body = buildBodyFromForm(DOM.form);
    runSearch({ baseUrl, endpoint, body });
  });

  // 初回：URLのクエリをフォームへ反映して検索
  if (autoRun) {
    if (syncUrl) applyUrlToForm();
    const body = buildBodyFromForm(DOM.form);
    runSearch({ baseUrl, endpoint, body });
  }
}

/**
 * 実際に検索して描画
 */
export async function runSearch({ baseUrl, endpoint, body } = {}) {
  // 連打時は前回をキャンセル
  if (currentAbort) currentAbort.abort();
  currentAbort = new AbortController();

  renderLoading(DOM.app);

  try {
    const json = await fetchSpecimens({
      baseUrl,
      endpoint,
      body,
      signal: currentAbort.signal,
    });

    const { total, items } = normalizeList(json);
    renderCount(total);
    renderGrid(items, DOM.app);
  } catch (err) {
    // Abort は無視
    if (err?.name === 'AbortError') return;

    console.error(err);
    renderError(DOM.app, '標本データの取得に失敗しました。');
    renderCount(null);
  }
}

/**
 * フォームからPOST bodyを作る（空値は落とす）
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
 * POST body を URLクエリへ反映（ページ遷移しない）
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
