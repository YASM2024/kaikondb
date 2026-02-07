// js/components/records/selectors/species.js
import { DOM } from '../dom.js';
import { api } from '../api.js';
import { clearOrder } from './common.js';

export function initSpeciesByFamily(familyId) {

  DOM.selectSpecies.innerText = '';
  DOM.selectSpecies.dataset.speciesId = '';
  DOM.inputSpecies.value = '';

  DOM.speciesList.innerHTML = '';

  DOM.selectSpecies.hidden = true;

  api.getSpeciesByFamily(familyId).then(renderSpecies);
}

export function openSpeciesByKeyword(keyword) {
  if (!keyword) {
    DOM.speciesList.innerHTML = '';
    alert('キーワードを入力してください。');
    return;
  };
  clearOrderAndFamily();
  api.getSpeciesByKeyword(keyword).then(renderSpecies);
}

export function selectSpecies(id, ja, en, exists) {
  
  const container = DOM.speciesList;
  DOM.selectSpecies.dataset.speciesId = id;
  container.innerHTML = '';
  
  DOM.selectSpecies.innerText = `${ja}　${en}`;
  DOM.inputSpecies.value = id;

  DOM.selectSpecies.hidden = false;
  
  DOM.selectSpecies.classList.toggle('text-danger', !exists);
}

function renderSpecies(list) {
  DOM.speciesList.innerHTML = list.map(s => `
    <div class="col-6 col-md-4">
      <div class="p-1 rounded-2 text-bg-secondary speciesBtn"
           data-id="${s.id}"
           data-ja="${s.species_ja}"
           data-en="${s.species}"
           data-exists="${s.exists_in_records}">
        ${s.species_ja}<br>${s.species}
      </div>
    </div>
  `).join('');
}

function clearOrderAndFamily() {
  DOM.orderList.innerHTML = '';
  DOM.familyList.innerHTML = '';
}
