import { DOM, initDOM } from './dom.js';
import { SearchModule } from './search.js';
import { ModalModule } from './modal.js';

// Bladeから渡された値を参照
const home_url = window.home_url;
const authenticated = window.authenticated;

// DOM初期化
initDOM(authenticated);

function init() {
  DOM.form.onsubmit = () => false;
  DOM.searchBtn.addEventListener('click', () => {
    SearchModule.generateQuery();
    SearchModule.refreshPage();
    SearchModule.searchPage();
  });
  ModalModule.init();
}

init();

// グローバルに公開したい場合
window.searchPage = SearchModule.searchPage.bind(SearchModule);
