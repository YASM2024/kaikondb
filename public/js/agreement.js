export async function sendAgreement() {
const agree_url = window.agreeUrl;
try {
    const response = await fetch(agree_url, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': window.xCsrfToken
    },
    body: JSON.stringify({ agreed: true })
    });

    if (!response.ok) {
    console.error('同意の保存に失敗しました');
    return false;
    }
    console.log('同意が保存されました');
    return true;

} catch (error) {
    console.error('通信エラー:', error);
    return false;
}
}
export async function handleAgreementClick() {
    DOM_auth.agreeBtn.classList.add('d-none');
    DOM_auth.postBtn.classList.remove('d-none');
    const success = await sendAgreement();
    if (!success) {
    /* 同意の保存に失敗した場合の処理 */
    }
}
