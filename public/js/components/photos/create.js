export function gatherCreateFormData() {
    const formData = new FormData();
    formData.append('photographer', 'newPost');
    formData.append('name', DOM_auth.new_photo_title_Ele.value);
    formData.append('place', DOM_auth.new_place_Ele.value);
    formData.append('date', DOM_auth.new_date_Ele.value);
    formData.append('memo', DOM_auth.new_memo_Ele.value);
    formData.append('image_file', DOM_auth.new_image_file_Ele.files[0]);
    formData.append('verified', '1');

    return formData;
}
export async function submitNewPhoto(formData) {
    const url = `${window.photoBaseUrl}/create`;

    const response = await fetch(url, {
      method: "POST",
      mode: "cors",
      cache: "no-cache",
      credentials: "same-origin",
      headers: { 'X-CSRF-TOKEN': window.xCsrfToken },
      redirect: "follow",
      referrerPolicy: "no-referrer",
      body: formData
    });

    if (!response.ok) {
      const errorText = await response.text(); // APIから返されたメッセージ
      throw new Error(`HTTP ${response.status} エラー`);
    }

    return response;
}
export async function handleCreateSubmit(event) {

    try {
      const formData = gatherCreateFormData();
      const res = await submitNewPhoto(formData);
      alert("投稿されました。\n承認をお待ちください。");
      location.href = window.photoBaseUrl;

    } catch (error) {
      console.error('投稿エラー:', error.message);
      alert("投稿に失敗しました。\n" + error.message);
    }
}