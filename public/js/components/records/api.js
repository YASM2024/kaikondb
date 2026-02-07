// js/components/records/api.js
export const api = {
  getOrders() {
    return fetch('../master/order/show_enabled').then(r => r.json());
  },
  getFamilies(orderId) {
    return fetch(`../master/family/show?order_id=${orderId}`).then(r => r.json());
  },
  getSpeciesByFamily(familyId) {
    return fetch(`../master/species/show?family_id=${familyId}`).then(r => r.json());
  },
  getSpeciesByKeyword(keyword) {
    return fetch(`../master/species/show?keyword=${encodeURIComponent(keyword)}`)
      .then(r => r.json());
  }
};
