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
    inputLockBtn.disabled = !inputLockBtn.disabled;
    unLockBtn.disabled = !unLockBtn.disabled;

    const edit_url = `./records/complete`;
    let body = new FormData();
    body.append('literature_id', inputLockBtn.getAttribute('literature-id'));
    body.append('on', on);
    let postData = generatePostData(body);
    fetch(edit_url, postData)
    .then(response => response.json())
    .then(data => {
        if (!data.result){ throw new Error('切替え処理に失敗しました。'); }
    })
    .catch(error => {
        console.error('エラー:', error);
        alert(`エラーが発生しました：${error}`);
    });
}
export function handleUnlockClick(event){
    lockBtnClick(false);
    enableInputLockBtn();
    event.currentTarget.removeEventListener('click', handleUnlockClick);
}
export function handleInputLockClick(event){
    lockBtnClick(true);
    enableUnLockBtn();
    event.currentTarget.removeEventListener('click', handleInputLockClick);
}
export function enableUnLockBtn () {
    toggleClasses(DOM_auth.unLockBtn, ['d-inline'], ['d-none']);
    toggleClasses(DOM_auth.inputLockBtn, ['d-none'], ['d-inline']);
    DOM_auth.unLockBtn.addEventListener('click', handleUnlockClick, false);
}
export function enableInputLockBtn () {
    toggleClasses(DOM_auth.unLockBtn, ['d-none'], ['d-inline']);
    toggleClasses(DOM_auth.inputLockBtn, ['d-inline'], ['d-none']);
    DOM_auth.inputLockBtn.addEventListener('click', handleInputLockClick, false);
}
