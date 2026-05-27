export async function sendDeleteRequest(deleteCode) {
    const body = new FormData();
    body.append('id', deleteCode);
    const url = `${window.photoBaseUrl}/delete`;
    try {
    const response = await fetch(url, {
        method: "POST",
        mode: "cors",
        cache: "no-cache",
        credentials: "same-origin",
        headers: { 'X-CSRF-TOKEN': window.xCsrfToken },
        redirect: "follow",
        referrerPolicy: "no-referrer",
        body: body
    });
    const json = await response.json();
    return json;
    } catch (error) {
    console.error('通信エラー:', error);
    throw new Error('通信エラーが発生しました');
    }
}
export async function handleDeleteClick(deleteCode) {
    const id = deleteCode || document.getElementById('photoModal')?.getAttribute('code');
    if (!id || !/^[0-9]+$/.test(String(id))) {
        alert('削除する写真を特定できませんでした。');
        return;
    }

    const confirmed = confirm("本当に削除してよいですか？削除すると元に戻せません。");
    if (!confirmed) return;

    try {
        const result = await sendDeleteRequest(id);
        if (result.result === 'success') {
            alert('削除に成功しました。');
            location.href = window.photoBaseUrl;
        } else {
            alert('削除に失敗しました。');
        }
    } catch (err) {
        alert(err.message);
    }
}
