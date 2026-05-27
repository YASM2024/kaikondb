// js/components/records/create/municipalities/helpers.js

/** 地点不明・詳細不明など（DB 表記揺れに対応） */
export const UNKNOWN_MUNICIPALITY_CODE = '199900';
export const UNKNOWN_MUNICIPALITY_LABELS = new Set([
  '詳細不明',
  '地名不明',
  '地点不明',
]);

export function isUnknownMunicipality(municipality) {
  const code = String(municipality?.municipality_code ?? '');
  const label = municipality?.municipality_ja ?? '';
  return code === UNKNOWN_MUNICIPALITY_CODE || UNKNOWN_MUNICIPALITY_LABELS.has(label);
}

/**
 * 複数チェックボックスから「指定以外をすべてオフ」にする
 * @param {HTMLInputElement} unknownCheckbox 地名不明チェックボックス
 * @param {NodeListOf<HTMLInputElement>} allCheckboxes 全チェックボックス
 */
export function uncheckOtherMunicipalities(unknownCheckbox, allCheckboxes) {
  allCheckboxes.forEach(checkbox => {
    if (checkbox !== unknownCheckbox) {
      checkbox.checked = false;
    }
  });
}

/**
 * bottom controls の DOM を生成する
 * @param {number|null} recordedIsCollected 既存データ
 * @returns {HTMLElement} DOM
 */
export function createMunicipalityBottomControls(recordedIsCollected) {
  const wrapper = document.createElement('div');
  wrapper.innerHTML = `
    <hr>
    <div class="d-flex flex-wrap align-items-center gap-3">
      <div class="form-check">
        <input class="form-check-input"
              type="radio"
              name="is_collected"
              id="recordRadio"
              value="1"
              form="registerRecord"
              ${recordedIsCollected === 1 ? 'checked' : ''}>
        <label class="form-check-label" for="recordRadio">採集記録</label>
      </div>
      <div class="form-check">
        <input class="form-check-input"
              type="radio"
              name="is_collected"
              id="otherRadio"
              value="0"
              form="registerRecord"
              ${recordedIsCollected === 0 ? 'checked' : ''}>
        <label class="form-check-label" for="otherRadio">その他</label>
      </div>
      <div class="flex-grow-1">
        <input class="form-control"
              type="text"
              id="additionalInput"
              placeholder="その他の詳細">
      </div>
    </div>
  `;
  return wrapper;
}
