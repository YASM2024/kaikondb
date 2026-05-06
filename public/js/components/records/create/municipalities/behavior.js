// js/components/records/create/municipalities/behavior.js
import { uncheckOtherMunicipalities } from './helpers.js';

export function setupMunicipalityCheckboxBehavior({
  unknownSelector = 'input[data-unknown="true"]',
  checkboxName = 'municipality_ids_array[]',
  recordRadioId = 'recordRadio',
  otherRadioId = 'otherRadio',
  additionalInputId = 'additionalInput'
} = {}) {

  const unknownCheckbox = document.querySelector(unknownSelector);
  const allCheckboxes = document.querySelectorAll(
    `input[name="${checkboxName}"]`
  );
  const recordRadio = document.getElementById(recordRadioId);
  const otherRadio = document.getElementById(otherRadioId);
  const additionalInput = document.getElementById(additionalInputId);

  if (!unknownCheckbox || !recordRadio || !otherRadio || !additionalInput) {
    console.warn('[setupMunicipalityCheckboxBehavior] 必要な要素が見つかりません');
    return;
  }

  /* ---------- 状態制御 ---------- */

  function enableRecordMode() {
    recordRadio.disabled = false;
    otherRadio.disabled = false;

    additionalInput.disabled = true;
    additionalInput.value = '';
  }

  function enableUnknownMode() {
    recordRadio.disabled = true;

    otherRadio.disabled = false;
    otherRadio.checked = true;

    additionalInput.disabled = false;
  }

  function enableOtherMode() {
    recordRadio.disabled = false;
    otherRadio.disabled = false;
    otherRadio.checked = true;

    additionalInput.disabled = false;
  }

  function syncAdditionalInput() {
    if (otherRadio.checked) {
      additionalInput.disabled = false;
      return;
    }
    additionalInput.disabled = true;
    additionalInput.value = '';
  }

  /* ---------- 初期状態 ---------- */
  // 初期状態は「採集記録」をデフォルトにするが、以降はユーザ選択を勝手に上書きしない
  if (!recordRadio.checked && !otherRadio.checked) {
    recordRadio.checked = true;
  }
  syncAdditionalInput();

  /* ---------- 地点不明 ---------- */
  unknownCheckbox.addEventListener('change', () => {
    if (unknownCheckbox.checked) {
      uncheckOtherMunicipalities(unknownCheckbox, allCheckboxes);
      enableUnknownMode();
      syncAdditionalInput();
    }
  });

  /* ---------- 他の市町村 ---------- */
  allCheckboxes.forEach(checkbox => {
    if (checkbox === unknownCheckbox) return;

    checkbox.addEventListener('change', () => {
      if (checkbox.checked) {
        unknownCheckbox.checked = false;
        // 市町村を選択しても is_collected は自動で採集記録に戻さない（ユーザ選択を尊重）
        recordRadio.disabled = false;
        otherRadio.disabled = false;
        syncAdditionalInput();
      }
    });
  });

  /* ---------- ラジオボタン ---------- */

  recordRadio.addEventListener('change', () => {
    if (recordRadio.checked) {
      syncAdditionalInput();
    }
  });

  otherRadio.addEventListener('change', () => {
    if (otherRadio.checked) {
      syncAdditionalInput();
    }
  });
}
