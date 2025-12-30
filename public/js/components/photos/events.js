import { DOM, DOM_auth } from './dom.js';
import { generateQuery } from './search.js';
import { handleCreateSubmit } from './create.js';
import { initializeEditModal } from './update.js';
import { handleDeleteClick } from './delete.js';
import { handleAgreementClick } from './agreement.js';
import { handleImageFileChange } from './utils.js';

export function addEventListeners(isAuthenticated = false) {
  DOM.userIdSearchEle.addEventListener('change', generateQuery);

  if (!isAuthenticated) return;

  DOM_auth.new_image_file_Ele.addEventListener('change', handleImageFileChange);
  DOM_auth.createSubmitBtn.addEventListener('click', handleCreateSubmit, false);
  DOM_auth.photoEditModal.addEventListener('show.bs.modal', initializeEditModal, false);
  DOM_auth.delBtn.addEventListener('click', () => {
    const code = DOM_auth.delBtn.dataset.bswhatever;
    handleDeleteClick(code);
  });
  DOM_auth.agreeBtn.addEventListener('click', () => {
    handleAgreementClick({
      agreeBtn: DOM_auth.agreeBtn,
      postBtn: DOM_auth.postBtn
    });
  });
}
