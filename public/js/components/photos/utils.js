import { DOM, DOM_auth } from './dom.js';

export function addModalEventListeners(){

    if (window.isEventListenerSet) return;
    window.isEventListenerSet = true;

    // 写真モーダル関連
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    // 写真モーダル表示
    DOM.photoModal.addEventListener('show.bs.modal', (event) => {
        const button = event.relatedTarget;
        if (!button) return;
        const id = button.getAttribute('data-bs-whatever')
        
        let url=`${window.photoBaseUrl}/${id}/show`;
        fetch(url)
        .then((response) => {
            return response.json();
        })
        .then((json) => {
            DOM.ModalLabel.innerText = json.photo_title;
            DOM.viewDataPlace.innerText = json.place;
            DOM.viewDataDate.innerText = json.date;
            DOM.viewDataPhotographer.innerHTML = `
            <div id="openProfileBtn" class="d-inline cursor-pointer" data-bs-target="#profileModal" data-bs-whatever="${json.user_id}" data-bs-toggle="modal">
                <img src="./storage/profile/${json.icon}" class="round img-fluid" style="width:1.4em; height:1.4em; border-radius:50%;">
                <u>${json.show_name}</u>
            </div>`;
            DOM.viewDataMemo.innerText = json.memo;

            DOM.viewDataPlace.setAttribute('value', json.place);
            DOM.viewDataDate.setAttribute('value', json.date);
            DOM.viewDataMemo.setAttribute('value', json.memo);
            DOM.photo_url.setAttribute('src', `./storage/photos/${json.url}`);
            DOM.photoModal.setAttribute('code', json.id);

            if(window.authenticated){
                const visible = ( json.user_id === window.userId );
                const editDeleteIcons = document.getElementById('editAndDelete');
                toggleVisibilityByUser(visible, editDeleteIcons);

                DOM_auth.editBtn.setAttribute( 'data-bs-whatever', json.id)
                DOM_auth.delBtn.setAttribute( 'data-bs-whatever', json.id)
                if( json.approved_at == null ){
                document.getElementById('closed').style.display = 'block';
                document.getElementById('opened').style.display = 'none'; 
                }else{
                document.getElementById('closed').style.display = 'none';
                document.getElementById('opened').style.display = 'block';
                }
            }

            // プロフィールの「戻る」ボタン設定
            document.getElementById('backBtn').setAttribute('data-bs-whatever', id);
        });
    });

    // 写真モーダル非表示
    DOM.photoModal.addEventListener('hidden.bs.modal', () => {
    DOM.ModalLabel.innerText = 'title';
    DOM.viewDataPlace.innerText = 'place';
    DOM.viewDataDate.innerText = 'date';
    DOM.viewDataPhotographer.innerHTML = '<div>photographer</div>';
    DOM.viewDataMemo.innerText = 'memo';
    DOM.photo_url.setAttribute('src', `../storage/img/wait.png`);
    document.querySelectorAll('.view_data').forEach(function(ele){ele.removeAttribute('value')})
    DOM.photoModal.setAttribute('code','')
    })

}
export function toggleVisibilityByUser(visible, targetElement) {
    if (visible) {
        if (targetElement.classList.contains('d-none')) {
            targetElement.classList.remove('d-none');
        }
    } else {
        if (!targetElement.classList.contains('d-none')) {
            targetElement.classList.add('d-none');
        }
    }
}
export function handleImageFileChange() {
    if (!this.files.length) {
        return;
    }
    const file = this.files[0];
    const fr = new FileReader();
    const previewElement = document.getElementById('preview');
    previewElement.src = window.waitImg;
    fr.onload = function() {
    previewElement.src = this.result;
    }
    fr.readAsDataURL(file);
}