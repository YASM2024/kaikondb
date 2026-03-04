// specimen/modal.js
import { DOM } from './dom.js';
import { fetchSpecimenDetail } from './api.js';

let currentAbort = null;
const cache = new Map(); // 任意：同じIDを何度も開くなら効く（不要なら消してOK）

/**
 * Bootstrap modal の show イベントをバインド
 * - 動的生成したボタンでも OK（event.relatedTarget で拾える）
 * - data-id だけ受けて、詳細は fetch で取得する
 */
export function bindModal(modalEl = DOM.modal, { baseUrl = CONFIG.baseUrl } = {}) {
  if (!modalEl) return;

  modalEl.addEventListener('show.bs.modal', async (event) => {
    const btn = event.relatedTarget;
    if (!btn) return;

    const id = btn.dataset?.id || btn.getAttribute('data-id');
    if (!id) return;

    await populateModalFromId(id, { baseUrl });
  });
}

/**
 * id から詳細取得してモーダルへ反映
 */
export async function populateModalFromId(id, { baseUrl = CONFIG.baseUrl } = {}) {
  // 前回の取得をキャンセル（連打/素早い切替対策）
  if (currentAbort) currentAbort.abort();
  currentAbort = new AbortController();

  // 直前の内容が残らないよう一旦クリア
  clearModal();

  // タイトルだけ "Loading..." などにしておくとUX良い
  setText(DOM.mTitle, 'Loading...');

  try {
    const key = String(id);

    let item = cache.get(key);
    if (!item) {
      item = await fetchSpecimenDetail({
        baseUrl,
        id: key,
        signal: currentAbort.signal,
      });
      cache.set(key, item);
    }

    populateModalFromItem(item);
  } catch (err) {
    if (err?.name === 'AbortError') return;
    console.error(err);

    // 失敗時の表示（必要ならDOM.mError等に出す）
    setText(DOM.mTitle, 'Error');
    // 既存UIに合わせてメッセージ表示先が無いなら、最低限 '-' のままでもOK
  }
}

/**
 * 取得した item(JSON) からモーダルへ反映
 * - サーバが返すキー名に合わせてマッピング
 */
export function populateModalFromItem(item = {}) {
  const speciesJa = item.species_ja ?? '';
  const species   = item.species ?? '';

  // --- テキスト系 ---
  setText(DOM.mTitle, speciesJa || species || 'Specimen');

  setText(DOM.mSpeciesJa, speciesJa || '-');
  setText(DOM.mSpecies, species || '-');
  setText(DOM.mSex, item.sex || '-');

  setText(DOM.mLocality, item.locality || '-');

  // 日付：あなたのモデルは collection_date_text
  setText(DOM.mDate, item.collection_date_text || item.collection_date || '-');

  setText(DOM.mCollectedBy, item.collected_by || '-');
  setText(DOM.mIdentifiedBy, item.identified_by || '-');
  setText(DOM.mOwner, item.owner || '-');
  setText(DOM.mTypeStatus, item.type_status || '-');

  // --- 座標 & Google Maps リンク ---
  const lat = item.decimal_latitude ?? item.lat ?? '';
  const lng = item.decimal_longitude ?? item.lng ?? '';
  const coordText = (lat !== '' && lng !== '') ? `${lat}, ${lng}` : '-';
  setText(DOM.mCoord, coordText);

  if (DOM.mMap) {
    DOM.mMap.innerHTML = '';
    if (lat !== '' && lng !== '') {
      const a = document.createElement('a');
      a.href = `https://www.google.com/maps?q=${encodeURIComponent(String(lat) + ',' + String(lng))}`;
      a.target = '_blank';
      a.rel = 'noopener';
      a.className = 'ms-2 small';
      a.textContent = 'Google Maps';
      DOM.mMap.appendChild(a);
    }
  }

  // --- その他 ---
  setText(DOM.mPreservation, item.preservation_method || item.preservation || '-');
  setText(DOM.mRepo, item.repository_institution || item.repo || '-');
  setText(DOM.mCatalog, item.repository_catalog_number || item.catalog || '-');

  // ライセンス
  setText(DOM.mLicense, item.license || '-');

  setText(DOM.mRemarks, item.remarks || '-');

  // --- 画像（空なら非表示） ---
  // APIキーが image_1 形式でも image1 形式でも受けられるように
  setImg(DOM.mImg1, item.image_1 || item.image1 || '');
  setImg(DOM.mImg2, item.image_2 || item.image2 || '');
  setImg(DOM.mImg3, item.image_3 || item.image3 || '');
}

/* ===== helpers ===== */

function clearModal() {
  // 既存要素があるものだけ '-' で初期化
  setText(DOM.mTitle, '');
  setText(DOM.mSpeciesJa, '-');
  setText(DOM.mSpecies, '-');
  setText(DOM.mSex, '-');
  setText(DOM.mLocality, '-');
  setText(DOM.mDate, '-');
  setText(DOM.mCollectedBy, '-');
  setText(DOM.mIdentifiedBy, '-');
  setText(DOM.mOwner, '-');
  setText(DOM.mTypeStatus, '-');
  setText(DOM.mCoord, '-');
  if (DOM.mMap) DOM.mMap.innerHTML = '';
  setText(DOM.mPreservation, '-');
  setText(DOM.mRepo, '-');
  setText(DOM.mCatalog, '-');
  setText(DOM.mLicense, '-');
  setText(DOM.mRemarks, '-');
  setImg(DOM.mImg1, '');
  setImg(DOM.mImg2, '');
  setImg(DOM.mImg3, '');
}

function setText(el, value) {
  if (!el) return;
  el.textContent = value ?? '';
}

function setImg(imgEl, url) {
  if (!imgEl) return;

  if (url) {
    imgEl.src = url;
    imgEl.classList.remove('d-none');
  } else {
    imgEl.removeAttribute('src');
    imgEl.classList.add('d-none');
  }
}
