// js/components/records/dom.js
export const DOM = {
  get orderList() {
    return document.getElementById('orderList');
  },
  get familyList() {
    return document.getElementById('familyList');
  },
  get speciesList() {
    return document.getElementById('speciesList');
  },

  get KeywordSearchBtn() {
    return document.getElementById('keyword_search_btn');
  },
  
  get selectOrder() {
    return document.getElementById('select_order_id');
  },
  get selectFamily() {
    return document.getElementById('select_family_id');
  },
  get selectSpecies() {
    return document.getElementById('select_species_id');
  },

  get inputOrder() {
    return document.getElementById('input_order_id');
  },
  get inputFamily() {
    return document.getElementById('input_family_id');
  },
  get inputSpecies() {
    return document.getElementById('input_species_id');
  },

  // セパレーター
  get sepOrderFamily() {
    return document.getElementById('sep_order_family');
  },
  get sepFamilySpecies() {
    return document.getElementById('sep_family_species');
  },

  // キーワード検索ボックス
  get keyword() {
    return document.getElementById('keyword');
  }
};

export function waitForElement(id) {
  return new Promise(resolve => {
    const el = document.getElementById(id);
    if (el) return resolve(el);

    const observer = new MutationObserver(() => {
      const el = document.getElementById(id);
      if (el) {
        observer.disconnect();
        resolve(el);
      }
    });

    observer.observe(document.body, { childList: true, subtree: true });
  });
}
