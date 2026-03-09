// js/components/records/create/municipalities/init.js
import { createMunicipalityBottomControls } from './helpers.js';

/**
 * municipalities_input の中にチェックボックス群を生成
 */
export function initMunicipalities() {
  const container = document.getElementById('municipalities_input');
  if (!container) return;

  const municipalities = JSON.parse(container.dataset.municipalities || '[]');
  const recordedMunicipalities = JSON.parse(container.dataset.recorded || '[]');
  const recordedSet = new Set(recordedMunicipalities.map(String));
  const recordedIsCollected = JSON.parse(container.dataset.isCollected || 'null');

  const fragment = document.createDocumentFragment();

  municipalities.forEach(municipality => {
    const isUnknown = municipality.municipality_ja === '地名不明';
    const inputId = `btn-check-${municipality.municipality_code}`;

    const span = document.createElement('span');
    span.className = 'd-inline-block m-1';

    const input = document.createElement('input');
    input.type = 'checkbox';
    input.className = 'btn-check';
    input.id = inputId;
    input.name = 'municipality_ids_array[]';
    input.value = municipality.municipality_code;
    input.autocomplete = 'off';
    input.setAttribute('form', 'registerRecord');

    if (isUnknown) input.dataset.unknown = 'true';
    // if (recordedMunicipalities.includes(municipality.municipality_code)) input.checked = true;
    if (recordedSet.has(String(municipality.municipality_code))) {
      input.checked = true;
    }
    const label = document.createElement('label');
    label.className = 'btn btn-outline-primary btn-sm';
    label.htmlFor = inputId;
    label.style.letterSpacing = '0';
    label.style.width = '112px';
    label.textContent = municipality.municipality_ja;

    span.appendChild(input);
    span.appendChild(label);
    fragment.appendChild(span);
  });

  // bottom controls を追加
  fragment.appendChild(createMunicipalityBottomControls(recordedIsCollected));

  container.appendChild(fragment);
}
