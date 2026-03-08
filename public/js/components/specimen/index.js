import { DOM } from './dom.js';
import { bindModal } from './modal.js';
import { initSearch } from './search.js';

document.addEventListener('DOMContentLoaded', () => {
  bindModal(DOM.modal);

  const sp = new URLSearchParams(window.location.search);
  const hasFilters = ['q','locality','date','collected_by','identified_by','owner']
    .some((k) => (sp.get(k) ?? '').trim() !== '');

  initSearch({
    baseUrl: CONFIG.baseUrl,
    endpoint: '/specimens/search',
    syncUrl: true,
    autoRun: hasFilters,
    perPage: 12, //　12件ずつ表示
  });
});