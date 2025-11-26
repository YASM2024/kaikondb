export const DOM = {
    httpquery : document.getElementById('httpquery'),
    searchBtn : document.getElementById('searchBtn'),
    openSpeciesListBtn : document.getElementById('openSpeciesListBtn'),
    modal : document.getElementById('ModalItemDetail'),
    form : document.getElementById("form"),
};

export let DOM_auth;
export function initDOM(authenticated) {
  if (authenticated) {
    DOM_auth = {
        username : document.getElementById('username'),
        editArticleBtn : document.getElementById('editArticleBtn'),
        inputLockBtn : document.getElementById('inputLockBtn'),
        unLockBtn : document.getElementById('unLockBtn'),
    };
  }
}
