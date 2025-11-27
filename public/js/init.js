import { addEventListeners } from './events.js';
import { generateQuery, refreshPage, searchPage, refreshProfileModal } from './search.js';

export function init() {
  generateQuery();
  refreshPage();
  searchPage();
  refreshProfileModal();
  addEventListeners(window.authenticated);
}

init();
