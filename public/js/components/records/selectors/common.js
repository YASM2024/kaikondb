// js/components/records/selectors/common.js
import { DOM } from '../dom.js';

export function clearOrder() {
  DOM.orderList.innerHTML = '';
}

export function clearFamily(reset = true) {
  DOM.familyList.innerHTML = '';
  DOM.inputFamily.hidden = true;
  if (reset) {
    resetFamilyList();
    clearSpecies();
  }
}

export function resetFamilyList() {
  DOM.familyList.innerHTML = '';
  DOM.inputFamily.value = '';
  DOM.selectFamily.innerText = '';
}

export function clearSpecies(reset = true) {
  DOM.speciesList.innerHTML = '';
  DOM.inputSpecies.hidden = true;
  if (reset) {
    resetSpeciesList();
  }
}

export function resetSpeciesList() {
  DOM.speciesList.innerHTML = '';
  DOM.inputSpecies.value = '';
  DOM.selectSpecies.innerText = '';
}
