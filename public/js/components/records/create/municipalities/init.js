// js/components/records/create/municipalities/init.js
import { createMunicipalityBottomControls, isUnknownMunicipality } from './helpers.js';

/**
 * municipalities_input の中にチェックボックス群を生成
 * @param {{ defaultOnlySelected?: boolean }} [options]
 */
export function initMunicipalities(options = {}) {
  const { defaultOnlySelected = false } = options;
  const container = document.getElementById('municipalities_input');
  if (!container) return;

  const municipalities = JSON.parse(container.dataset.municipalities || '[]');
  const recordedMunicipalities = JSON.parse(container.dataset.recorded || '[]');
  const recordedSet = new Set(recordedMunicipalities.map(String));
  const recordedIsCollected = JSON.parse(container.dataset.isCollected || 'null');

  // UI を組み直す（検索/選択中のみ/全解除 + グリッド表示）
  container.innerHTML = '';
  container.classList.add('municipalitiesRoot');

  const toolbar = document.createElement('div');
  toolbar.className = 'municipalitiesToolbar d-flex flex-wrap align-items-center gap-2 mb-2';
  toolbar.innerHTML = `
    <input
      type="search"
      class="form-control form-control-sm"
      id="municipalitySearch"
      placeholder="市町村を検索"
      style="max-width: 260px;"
    >
    <div class="form-check ms-1">
      <input class="form-check-input" type="checkbox" id="municipalityOnlySelected">
      <label class="form-check-label small" for="municipalityOnlySelected">選択中のみ</label>
    </div>
    <button type="button" class="btn btn-outline-secondary btn-sm ms-auto" id="municipalityClearAll">
      全解除
    </button>
  `;

  const list = document.createElement('div');
  list.id = 'municipalityList';
  list.className = 'municipalitiesList';

  const fragment = document.createDocumentFragment();

  municipalities.forEach(municipality => {
    const isUnknown = isUnknownMunicipality(municipality);
    const inputId = `btn-check-${municipality.municipality_code}`;

    const span = document.createElement('span');
    span.className = 'municipalityItem';

    const input = document.createElement('input');
    input.type = 'checkbox';
    input.className = 'btn-check';
    input.id = inputId;
    input.name = 'municipality_ids_array[]';
    input.value = municipality.municipality_code;
    input.autocomplete = 'off';
    input.setAttribute('form', 'registerRecord');

    if (isUnknown) input.dataset.unknown = 'true';
    if (recordedSet.has(String(municipality.municipality_code))) {
      input.checked = true;
    }
    const label = document.createElement('label');
    label.className = 'btn btn-outline-primary btn-sm municipalityLabel';
    label.htmlFor = inputId;
    label.style.letterSpacing = '0';
    label.textContent = municipality.municipality_ja;

    // 検索対象（日本語/英語/コード）
    span.dataset.label = municipality.municipality_ja;
    span.dataset.labelEn = municipality.municipality_en ?? '';
    span.dataset.code = String(municipality.municipality_code ?? '');
    span.appendChild(input);
    span.appendChild(label);
    fragment.appendChild(span);
  });

  // bottom controls を追加
  const bottomControls = createMunicipalityBottomControls(recordedIsCollected);

  list.appendChild(fragment);
  container.appendChild(toolbar);
  container.appendChild(list);
  container.appendChild(bottomControls);

  // 最低限のスタイル（CSSファイルを増やさずに改善）
  if (!document.getElementById('municipalitiesStyle')) {
    const style = document.createElement('style');
    style.id = 'municipalitiesStyle';
    style.textContent = `
      .municipalitiesList{
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: .4rem .4rem;
        padding: .25rem;
        border-radius: .25rem;
      }
      /* 検索中だけスクロール枠にする */
      .municipalitiesRoot.isSearching .municipalitiesList{
        max-height: 320px;
        overflow:auto;
      }
      .municipalityItem{ display:block; }
      .municipalityLabel{
        width: 100%;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
      }
    `;
    document.head.appendChild(style);
  }

  // フィルタ（検索 + 選択中のみ）
  const searchInput = document.getElementById('municipalitySearch');
  const onlySelected = document.getElementById('municipalityOnlySelected');

  function applyFilter() {
    const q = (searchInput?.value ?? '').trim();
    const qLower = q.toLowerCase();
    const selectedOnly = !!onlySelected?.checked;
    container.classList.toggle('isSearching', qLower !== '');

    container.querySelectorAll('.municipalityItem').forEach((item) => {
      const labelText = (item.dataset.label ?? '').toLowerCase();
      const labelEn = (item.dataset.labelEn ?? '').toLowerCase();
      const code = (item.dataset.code ?? '').toLowerCase();
      const checkbox = item.querySelector('input[type="checkbox"]');
      const isSelected = !!checkbox?.checked;
      const matchQuery =
        qLower === '' ||
        labelText.startsWith(qLower) ||
        labelEn.startsWith(qLower) ||
        code.startsWith(qLower);
      const matchSelected = !selectedOnly || isSelected;
      item.style.display = matchQuery && matchSelected ? '' : 'none';
    });
  }

  searchInput?.addEventListener('input', applyFilter);
  onlySelected?.addEventListener('change', applyFilter);

  if (defaultOnlySelected && onlySelected) {
    onlySelected.checked = true;
  }

  // 全解除（地点不明含む）
  document.getElementById('municipalityClearAll')?.addEventListener('click', () => {
    container.querySelectorAll('input[name="municipality_ids_array[]"]').forEach((cb) => {
      cb.checked = false;
      cb.dispatchEvent(new Event('change', { bubbles: true }));
    });
    applyFilter();
  });

  applyFilter();
}
