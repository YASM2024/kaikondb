const config = window.__EXPANDED_FORM__;

function getSelectedValue(name) {
  const selected = document.querySelector(`input[name="${name}"]:checked`);
  return selected ? selected.value : null;
}

function collectPayload() {
  const idEl = document.getElementById('id');
  const titleEn = document.getElementById('title_en').value;
  const bodyEn = document.getElementById('body_en').value;
  const title = document.getElementById('title').value;
  const body = document.getElementById('body').value;

  return {
    id: idEl ? idEl.value : 'new',
    route_name: document.getElementById('route_name').value,
    title,
    title_en: titleEn !== '' ? titleEn : title,
    body,
    body_en: bodyEn !== '' ? bodyEn : body,
    open: getSelectedValue('open'),
    seq: document.querySelector('select[name="seq"]').value,
  };
}

function validateRequired() {
  const inputs = document.querySelectorAll('input[type="text"][required], textarea[required]');
  const invalid = [];

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      invalid.push(input);
      input.classList.add('error');
    } else {
      input.classList.remove('error');
    }
  });

  if (invalid.length > 0) {
    alert(`全ての必須項目を入力してください: ${invalid.map((input) => input.name).join(', ')}`);
    return false;
  }

  return true;
}

async function postJson(url, body) {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Content-Type': 'application/json',
    },
    body: JSON.stringify(body),
  });

  if (!response.ok) {
    throw new Error('Network response was not ok');
  }

  return response.json();
}

async function savePage() {
  if (!validateRequired()) {
    return;
  }

  try {
    const action = config.isEdit ? config.updateUrl : config.createUrl;
    const result = await postJson(action, collectPayload());

    if (result.res === 0) {
      alert('送信に成功しました！');
      window.location.href = config.indexUrl;
    } else {
      alert(result.message || 'エラーが発生しました。再度試してください。');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('送信に失敗しました。');
  }
}

async function deletePage() {
  if (!confirm('本当に削除しますか？')) {
    return;
  }

  try {
    const result = await postJson(config.deleteUrl, {
      id: document.querySelector('input[name="id"]').value,
    });

    if (result.res === 0) {
      alert('削除しました');
      window.location.href = config.indexUrl;
    } else {
      alert(result.message || 'エラーが発生しました。再度試してください。');
    }
  } catch (error) {
    console.error('Error:', error);
    alert('送信に失敗しました。');
  }
}

document.getElementById('main')?.addEventListener('click', savePage);

if (config.isEdit) {
  document.getElementById('deleteBtn')?.addEventListener('click', deletePage);
}
