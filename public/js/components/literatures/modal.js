import { DOM } from './dom.js';
import { enableUnLockBtn, enableInputLockBtn } from './utils.js';

const home_url = window.home_url;
const authenticated = window.authenticated;

export const ModalModule = {
    init() {
        setTimeout(() => {
            DOM.modal.addEventListener('show.bs.modal', this.handleShow);
        }, 100);
    },
    async handleShow(event) {
        const button = event.relatedTarget;
        const literatureCode = button.getAttribute('data-bs-whatever');
        const url = `${home_url}/literatures/${literatureCode}/show`;
        const json = await fetch(url).then(r => r.json());
        ModalModule.renderDetails(json, literatureCode);
    },
    renderDetails(json, literatureCode) {
        // jsonを使ってDOM更新
        title.innerHTML = json.title ?? '';
        year.textContent = (json.year ?? '') + '年';
        author.textContent = json.author ?? '';
        journal_name.textContent = json.journal_name ?? '';
        page.textContent = json.page ?? '';
        category.textContent = json.order_names ?? '';
        volno.textContent = json.vol_no ?? '';
        comment.textContent = json.comment ?? '';
        created_at.textContent = json.created_at ?? '';

        const date = new Date(json.created_at);
        created_at.textContent = date.getFullYear() + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + String(date.getDate()).padStart(2, '0');

        link.innerHTML = json.link.length >= 2 
        ? `<a href="${json.link ?? ''}" target="_blank" rel="noopener">${json.link ?? ''}</a>${json.provided_by ?? ''}`
        : ' ';

        if (authenticated) {
            //認証済ユーザオプションを表示
            username.textContent = json.user_name ?? '';
            openSpeciesListBtn.href=`${home_url}/literatures/${literatureCode}/species`;
            inputLockBtn.setAttribute('literature-id', json.id);
            editLiteratureBtn.href= `${home_url}/literatures/${literatureCode}/edit`;
            const fileInfo = document.getElementById('fileInfo');
            fileInfo.innerHTML = '';
            if(json.documents){
                json.documents.forEach((element) => {
                fileInfo.innerHTML += `
                <span class="badge bg-danger me-2">PDF</span>
                <a class="me-2" href="./literatures/documents/${element.file_name}" target="_blank" rel="noopener">
                    ${element.display_title}
                </a>
                `;
                })
            }
            json.is_recorded ? enableUnLockBtn() : enableInputLockBtn();
        }
    }
};
