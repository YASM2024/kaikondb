// specimen/render.js
import { DOM } from './dom.js';

/**
 * 一覧コンテナを初期化（rowクラス付与・クリア）
 */
export function initGrid(container = DOM.app) {
  if (!container) return;

  container.className = 'row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3';
  container.textContent = '';
}

/**
 * 「読み込み中」表示（任意）
 */
export function renderLoading(container = DOM.app) {
  if (!container) return;
  container.className = '';
  container.innerHTML = `<div class="alert alert-secondary mb-0">読み込み中…</div>`;
}

/**
 * 空結果表示
 */
export function renderEmpty(container = DOM.app, message = '該当する標本がありません。') {
  if (!container) return;
  container.className = '';
  container.innerHTML = `<div class="alert alert-secondary mb-0">${escapeHtml(message)}</div>`;
}

/**
 * エラー表示
 */
export function renderError(container = DOM.app, message = '標本データの取得に失敗しました。') {
  if (!container) return;
  container.className = '';
  container.innerHTML = `<div class="alert alert-danger mb-0">${escapeHtml(message)}</div>`;
}

/**
 * 件数表示（任意：#resultCountがある場合）
 */
export function renderCount(count, countEl = DOM.resultCount) {
  if (!countEl) return;
  countEl.textContent = typeof count === 'number' ? `${count} 件` : '';
}

/**
 * 一覧を描画（items は normalize 済みを想定）
 * items: [{ id, species, speciesJa, ... }]
 */
export function renderGrid(items, container = DOM.app) {
  if (!container) return;

  initGrid(container);

  if (!Array.isArray(items) || items.length === 0) {
    renderEmpty(container);
    return;
  }

  const frag = document.createDocumentFragment();
  for (const s of items) frag.appendChild(renderSpecimenCard(s));
  container.appendChild(frag);
}

/**
 * 1枚のカードを作る
 * s は normalize 済みを想定（キーは camelCase）
 */
export function renderSpecimenCard(s) {
  const col = el('div', 'col');
  const card = el('div', 'card h-100 shadow-sm');
  col.appendChild(card);

  // thumb
  if (s.image1) {
    const img = document.createElement('img');
    img.src = s.image1;
    img.alt = 'specimen image';
    img.loading = 'lazy';
    img.className = 'specimen-thumb';
    card.appendChild(img);
  } else {
    const noImg = el('div', 'specimen-thumb d-flex align-items-center justify-content-center text-muted');
    noImg.textContent = 'No Image';
    card.appendChild(noImg);
  }

  // body
  const body = el('div', 'card-body');
  card.appendChild(body);

  const title = el('div', 'mb-1 fw-bold break-word');
  title.textContent = s.speciesJa || '-';
  body.appendChild(title);

  const sci = el('div', 'text-muted fst-italic break-word');
  sci.textContent = s.species || '';
  body.appendChild(sci);

  const meta = el('div', 'mt-2 small');
  meta.appendChild(kv('採集地:', s.locality));
  meta.appendChild(kv('採集日:', s.collection_date_text));
  meta.appendChild(kv('採集者:', s.collectedBy));
  meta.appendChild(kv('同定者:', s.identifiedBy));
  body.appendChild(meta);

  // footer
  const footer = el('div', 'card-footer bg-white border-top-0 d-flex justify-content-between align-items-center');
  card.appendChild(footer);

  const badge = el('span', 'badge text-bg-light');
  badge.textContent = s.license || '';
  footer.appendChild(badge);

  const btn = el('button', 'btn btn-sm btn-outline-secondary', {
    type: 'button',
    'data-bs-toggle': 'modal',
    'data-bs-target': '#ModalSpecimenDetail',
  });
  btn.textContent = '詳細';

  // モーダルが読む data-* を付与（dash-case）
  setData(btn, 'id', s.id);
  setData(btn, 'species', s.species);
  setData(btn, 'species-ja', s.speciesJa);
  setData(btn, 'sex', s.sex);
  setData(btn, 'locality', s.locality);
  setData(btn, 'collection-date', s.collection_date_text);
  setData(btn, 'collected-by', s.collectedBy);
  setData(btn, 'identified-by', s.identifiedBy);
  // setData(btn, 'owner', s.owner);
  // setData(btn, 'type-status', s.typeStatus);
  // setData(btn, 'lat', s.lat);
  // setData(btn, 'lng', s.lng);
  // setData(btn, 'preservation', s.preservation);
  // setData(btn, 'repo', s.repo);
  // setData(btn, 'catalog', s.catalog);
  // setData(btn, 'remarks', s.remarks);
  setData(btn, 'license', s.license);
  setData(btn, 'image1', s.image1);

  footer.appendChild(btn);

  return col;
}

/* ===== helpers ===== */

function kv(label, value) {
  const row = el('div', 'break-word');
  const k = el('span', 'text-muted');
  k.textContent = label + ' ';
  row.appendChild(k);
  row.appendChild(document.createTextNode(value || '-'));
  return row;
}

function setData(node, key, value) {
  node.setAttribute('data-' + key, value ?? '');
}

function el(tag, className = '', attrs = {}) {
  const node = document.createElement(tag);
  if (className) node.className = className;
  for (const [k, v] of Object.entries(attrs)) {
    if (v === undefined || v === null) continue;
    node.setAttribute(k, String(v));
  }
  return node;
}

/**
 * 最低限のエスケープ（innerHTML を使う箇所向け）
 */
function escapeHtml(s) {
  return String(s ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#39;');
}
