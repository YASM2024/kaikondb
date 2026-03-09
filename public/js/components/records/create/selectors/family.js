// js/components/records/create/selectors/family.js
import { DOM } from '../dom.js';
import { api } from '../api.js';
import { clearSpecies } from './common.js';
import { initSpeciesByFamily } from './species.js';

export function initFamilyByOrder(orderId) {
    
  DOM.selectFamily.innerText = '';
  DOM.selectFamily.dataset.familyId = '';
  DOM.inputFamily.value = '';
  
  DOM.selectSpecies.innerText = '';
  DOM.selectSpecies.dataset.speciesId = '';
  DOM.inputSpecies.value = '';

  DOM.familyList.innerHTML = '';
  DOM.speciesList.innerHTML = '';
  
  DOM.selectFamily.hidden = true;
  DOM.selectSpecies.hidden = true;

  api.getFamilies(orderId).then(list => {
    DOM.familyList.innerHTML = list.map(f => `
      <div class="col-6 col-md-4">
        <div class="p-1 rounded-2 text-bg-secondary familyBtn"
             data-id="${f.id}"
             data-ja="${f.family_ja}"
             data-en="${f.family}">
          ${f.family_ja}<br>${f.family}
        </div>
      </div>
    `).join('');
  });
}

export function selectFamily(id, ja, en) {
  DOM.selectFamily.innerText = `${ja}　${en}`;
  DOM.selectFamily.dataset.familyId = id;
  DOM.selectFamily.dataset.familyJa = ja;
  DOM.selectFamily.dataset.familyEn = en;
  DOM.inputFamily.value = id;
  
  DOM.selectFamily.hidden = false;
  DOM.selectSpecies.hidden = true;

  const container = DOM.familyList;
  container.innerHTML = '';

  initSpeciesByFamily(id);
}
