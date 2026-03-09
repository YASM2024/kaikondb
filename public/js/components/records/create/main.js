// js/components/records/create/main.js
import { DOM } from './dom.js';
import { initOrder, selectOrder } from './selectors/order.js';
import { initFamilyByOrder, selectFamily } from './selectors/family.js';
import { initSpeciesByFamily, selectSpecies, openSpeciesByKeyword } from './selectors/species.js';
import { clearFamily , clearSpecies, resetFamilyList } from './selectors/common.js';
import { initMunicipalities } from './municipalities/init.js';
import { setupMunicipalityCheckboxBehavior } from './municipalities/behavior.js';
import { setupRecordForm } from './form.js';

function updateSelectorSeparators() {
  DOM.sepOrderFamily.hidden = DOM.selectFamily.hidden;
  DOM.sepFamilySpecies.hidden = DOM.selectSpecies.hidden;
}

document.addEventListener('DOMContentLoaded', () => {
  // 初期表示
  initOrder();
  updateSelectorSeparators();
  // DOM生成
  initMunicipalities();
  // 振舞いを付与
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
    selectOrder(
      orderBtn.dataset.id,
      orderBtn.dataset.ja,
      orderBtn.dataset.en
    );
    updateSelectorSeparators();
    return;
  }
  
  const showALLOrderBtn = e.target.closest('#select_order_id');
  if (showALLOrderBtn) {
    initOrder();
    updateSelectorSeparators();
    return;
  }

  const familyBtn = e.target.closest('.familyBtn');
  if (familyBtn) {
    selectFamily(
      familyBtn.dataset.id,
      familyBtn.dataset.ja,
      familyBtn.dataset.en
    );
    updateSelectorSeparators();
    return;
  }

  const showALLFamilyBtn = e.target.closest('#select_family_id');
  if (showALLFamilyBtn) {
      // family解除
      DOM.selectFamily.innerText = '';
      DOM.selectFamily.dataset.familyId = '';
      DOM.selectFamily.dataset.familyJa = '';
      DOM.selectFamily.dataset.familyEn = '';
      DOM.inputFamily.value = '';
      
      // speciesもクリア
      DOM.selectSpecies.innerText = '';
      DOM.selectSpecies.dataset.speciesId = '';
      DOM.inputSpecies.value = '';
      DOM.speciesList.innerHTML = '';

      // species解除 & 隠す
      clearSpecies(true);

      // family/speciesボタン不可視
      DOM.selectFamily.hidden = true;
      DOM.selectSpecies.hidden = true;

      // family一覧を再表示（orderは維持）
      const orderId = DOM.selectOrder.dataset.orderId;
      if (orderId) {
        initFamilyByOrder(orderId);
      }
      updateSelectorSeparators();

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
    updateSelectorSeparators();
    return;
  }

  const allSpeciesBtn = e.target.closest('#select_species_id');
  if (allSpeciesBtn) {
      // species解除
      DOM.selectSpecies.innerText = '';
      DOM.selectSpecies.dataset.speciesId = '';
      DOM.inputSpecies.value = '';
      DOM.selectSpecies.hidden = true;

      // species一覧を再表示（familyは維持）
      const familyId = DOM.selectFamily.dataset.familyId;
      if (familyId) {
        initSpeciesByFamily(familyId);
      }
      updateSelectorSeparators();
      return;
  }


});
