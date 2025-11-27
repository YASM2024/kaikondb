export async function fetchPhotoData(id) {
  const show_url = `${window.photoBaseUrl}/${id}/show`;
  const response = await fetch(show_url);
  return await response.json();
}
export function populateEditForm(data) {
    show_name_editForm.innerText = '投稿者：' + data.show_name;
    id_editForm.value = data.id;
    photo_title_editForm.value = data.photo_title;
    place_editForm.value = data.place;
    date_editForm.value = data.date;
    memo_editForm.value = data.memo;

    const previewElement = document.getElementById('photo_editForm');
    previewElement.src = `${window.homeUrl}/storage/photos/${data.url}`;
}
export function handleEditSubmit() {
    const edit_submit = document.getElementById('edit_submit');

    edit_submit.replaceWith(edit_submit.cloneNode(true));
    const new_edit_submit = document.getElementById('edit_submit');

    new_edit_submit.addEventListener('click', async () => {
      new_edit_submit.disabled = true;

      const edit_url = `${window.photoBaseUrl}/edit`;
      const body = new FormData();
      body.append('id', id_editForm.value);
      body.append('photo_title', photo_title_editForm.value);
      body.append('place', place_editForm.value);
      body.append('date', date_editForm.value);
      body.append('memo', memo_editForm.value);

      try {
        const response = await fetch(edit_url, {
          method: 'POST',
          mode: 'cors',
          cache: 'no-cache',
          credentials: 'same-origin',
          headers: { 'X-CSRF-TOKEN': window.xCsrfToken },
          redirect: 'follow',
          referrerPolicy: 'no-referrer',
          body
        });

        const result = await response.json();

        if (result.result === 'success') {
          alert('編集を完了しました。');
          location.href = window.photoBaseUrl;
        } else {
          alert('編集に失敗しました。');
          new_edit_submit.disabled = false;
        }

      } catch (error) {
        console.error('編集送信エラー:', error);
        alert('編集中にエラーが発生しました。');
        new_edit_submit.disabled = false;
      }
    });
}
export async function initializeEditModal(event) {
    try {
      const button = event.relatedTarget;
      const id_edit = button.getAttribute('data-bs-whatever');
      const photoData = await fetchPhotoData(id_edit);
      
      populateEditForm(photoData);
      handleEditSubmit();

    } catch (error) {
      console.error('モーダル初期化エラー:', error);
      alert('データの取得に失敗しました。');
    }
}