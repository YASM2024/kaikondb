// js/components/records/create/selectors/order.js
import { DOM, waitForElement } from '../dom.js';
import { api } from '../api.js';
import { clearFamily, clearSpecies } from './common.js';
import { initFamilyByOrder } from './family.js';

export function initOrder() {
  DOM.selectOrder.innerText = 'すべての目';
  DOM.selectOrder.dataset.orderId = '';
  DOM.inputOrder.value = '';
  
  DOM.selectFamily.innerText = '';
  DOM.selectFamily.dataset.familyId = '';
  DOM.inputFamily.value = '';
  
  DOM.selectSpecies.innerText = '';
  DOM.selectSpecies.dataset.speciesId = '';
  DOM.inputSpecies.value = '';

  DOM.selectFamily.hidden = true;
  DOM.selectSpecies.hidden = true;

  DOM.familyList.innerHTML = '';
  DOM.speciesList.innerHTML = '';
  renderOrders();
}

async function renderOrders() {
  const container = await waitForElement('orderList');
  const list = await api.getOrders();
  container.innerHTML = list.map(o => `
      <div class="col-6 col-md-4">
        <div class="p-1 rounded-2 text-bg-secondary orderBtn"
             data-id="${o.id}"
             data-ja="${o.order_ja}"
             data-en="${o.order}">
          ${o.order_ja}<br>${o.order}
        </div>
      </div>
    `).join('');
}

export function selectOrder(orderId, ja, en) {
  DOM.selectOrder.innerText = `${ja}　${en}`;
  DOM.selectOrder.dataset.orderId = orderId;
  DOM.selectOrder.dataset.orderJa = ja;
  DOM.selectOrder.dataset.orderEn = en;
  DOM.inputOrder.value = orderId;

  DOM.selectFamily.hidden = true;
  DOM.selectSpecies.hidden = true;

  const container = DOM.orderList;
  container.innerHTML = '';
  
  initFamilyByOrder(orderId);
}
