// js/components/records/create/edit.js
import { DOM } from './dom.js';
import { selectFamily } from './selectors/family.js';
import { selectSpecies, openSpeciesByKeyword } from './selectors/species.js';
import { initMunicipalities } from './municipalities/init.js';
import { setupMunicipalityCheckboxBehavior } from './municipalities/behavior.js';
import { setupRecordForm } from './form.js';

document.addEventListener('DOMContentLoaded', () => {
  initMunicipalities({ defaultOnlySelected: true });
  setupMunicipalityCheckboxBehavior();
  DOM.keyword?.addEventListener('keydown', e => {
    if (e.key === 'Enter') {
      openSpeciesByKeyword(DOM.keyword.value);
    }
  });
  DOM.KeywordSearchBtn?.addEventListener('click', () => {
    openSpeciesByKeyword(DOM.keyword.value);
  });
  // フォーム設定
  setupRecordForm();
});

/* ---- イベント委譲 ---- */
document.addEventListener('click', e => {
  const orderBtn = e.target.closest('.orderBtn');
  if (orderBtn) {
    return;
  }
  
  const familyBtn = e.target.closest('.familyBtn');
  if (familyBtn) {
    selectFamily(
      familyBtn.dataset.id,
      familyBtn.dataset.ja,
      familyBtn.dataset.en
    );
    return;
  }

  const showALLFamilyBtn = e.target.closest('#select_family_id');
  if (showALLFamilyBtn) {
    return;
  }

  const speciesBtn = e.target.closest('.speciesBtn');
  if (speciesBtn) {
    selectSpecies(
      speciesBtn.dataset.id,
      speciesBtn.dataset.ja,
      speciesBtn.dataset.en,
      speciesBtn.dataset.exists === 'true'
    );
  }

  const allSpeciesBtn = e.target.closest('#select_species_id');
  if (allSpeciesBtn) {
    selectFamily(
      DOM.selectFamily.dataset.familyId,
      DOM.selectFamily.dataset.familyJa,
      DOM.selectFamily.dataset.familyEn
    );
    return;
  }

});
