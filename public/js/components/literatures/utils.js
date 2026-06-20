import { DOM_auth } from './dom.js';

export function toggleClasses(element, addClasses, removeClasses) {
        removeClasses.forEach(cls => {
            if (element.classList.contains(cls)) {
                element.classList.remove(cls);
            }
        });
        addClasses.forEach(cls => {
            if (!element.classList.contains(cls)) {
                element.classList.add(cls);
            }
        });
}

export function generatePostData(body){
    const tokenEle = document.querySelector('meta[name="csrf-token"]');
    return {
        method: "POST",
        mode: "cors", 
        cache: "no-cache",
        credentials: "same-origin",
        headers: {
            'X-CSRF-TOKEN': tokenEle.getAttribute('content'),
        },
        redirect: "follow",
        referrerPolicy: "no-referrer",
        body
    };
}
export function lockBtnClick(on){
    if (typeof on !== 'boolean') { throw new TypeError('不正な値が送信されました。');}

    const edit_url = `${window.home_url}/records/complete`;
    const body = new FormData();
    body.append('literature_id', DOM_auth.inputLockBtn.getAttribute('literature-id'));
    body.append('on', on);
    const postData = generatePostData(body);
    return fetch(edit_url, postData)
    .then(response => {
        if (!response.ok) {
            throw new Error(`サーバーエラー (${response.status})`);
        }
        return response.json();
    })
    .then(data => {
        if (!data.result) {
            throw new Error(data.message || '切替え処理に失敗しました。');
        }
    });
}
function showLockError(error) {
    console.error('エラー:', error);
    alert(`エラーが発生しました：${error.message}`);
}
export function handleUnlockClick(){
    lockBtnClick(false)
    .then(() => enableInputLockBtn())
    .catch(showLockError);
}
export function handleInputLockClick(){
    lockBtnClick(true)
    .then(() => enableUnLockBtn())
    .catch(showLockError);
}
export function enableUnLockBtn () {
    toggleClasses(DOM_auth.unLockBtn, ['d-inline'], ['d-none']);
    toggleClasses(DOM_auth.inputLockBtn, ['d-none'], ['d-inline']);
    DOM_auth.unLockBtn.onclick = handleUnlockClick;
}
export function enableInputLockBtn () {
    toggleClasses(DOM_auth.unLockBtn, ['d-none'], ['d-inline']);
    toggleClasses(DOM_auth.inputLockBtn, ['d-inline'], ['d-none']);
    DOM_auth.inputLockBtn.onclick = handleInputLockClick;
}
