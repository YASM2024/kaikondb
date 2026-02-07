// js/components/records/municipalities/behavior.js
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
    recordRadio.checked = true;

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

  /* ---------- 初期状態 ---------- */
  enableRecordMode();

  /* ---------- 地点不明 ---------- */
  unknownCheckbox.addEventListener('change', () => {
    if (unknownCheckbox.checked) {
      uncheckOtherMunicipalities(unknownCheckbox, allCheckboxes);
      enableUnknownMode();
    }
  });

  /* ---------- 他の市町村 ---------- */
  allCheckboxes.forEach(checkbox => {
    if (checkbox === unknownCheckbox) return;

    checkbox.addEventListener('change', () => {
      if (checkbox.checked) {
        unknownCheckbox.checked = false;
        enableRecordMode();
      }
    });
  });

  /* ---------- ラジオボタン ---------- */

  recordRadio.addEventListener('change', () => {
    if (recordRadio.checked) {
      additionalInput.disabled = true;
      additionalInput.value = '';
    }
  });

  otherRadio.addEventListener('change', () => {
    if (otherRadio.checked) {
      additionalInput.disabled = false;
    }
  });
}
