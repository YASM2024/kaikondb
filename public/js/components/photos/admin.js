const config = window.photoAdminConfig ?? {};

function buildUrl(template, id) {
    return String(template).replace('__ID__', String(id));
}

function showToast(message, isError = false) {
    const toastEl = document.getElementById('photoAdminToast');
    const bodyEl = document.getElementById('photoAdminToastBody');
    if (!toastEl || !bodyEl) {
        return;
    }

    bodyEl.textContent = message;
    toastEl.classList.remove('text-bg-success', 'text-bg-danger');
    toastEl.classList.add(isError ? 'text-bg-danger' : 'text-bg-success');

    const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 3000 });
    toast.show();
}

async function postApproval(id, approve) {
    const template = approve ? config.approveUrlTemplate : config.unapproveUrlTemplate;
    const url = buildUrl(template, id);

    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': config.csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const json = await response.json().catch(() => ({}));

    if (!response.ok || !json.success) {
        throw new Error(json.message ?? '処理に失敗しました');
    }

    return json;
}

function removeRow(row) {
    row.remove();

    const tbody = document.querySelector('#photoAdminTable tbody');
    if (tbody && tbody.children.length === 0) {
        window.location.reload();
    }
}

document.addEventListener('click', async (event) => {
    const approveBtn = event.target.closest('.btn-approve');
    const unapproveBtn = event.target.closest('.btn-unapprove');
    if (!approveBtn && !unapproveBtn) {
        return;
    }

    const row = event.target.closest('tr[data-photo-id]');
    if (!row) {
        return;
    }

    const id = row.dataset.photoId;
    const approve = !!approveBtn;

    approveBtn && (approveBtn.disabled = true);
    unapproveBtn && (unapproveBtn.disabled = true);

    try {
        await postApproval(id, approve);
        removeRow(row);
        showToast(approve ? '承認しました' : '承認を取り消しました');
    } catch (error) {
        showToast(error.message ?? 'エラーが発生しました', true);
        approveBtn && (approveBtn.disabled = false);
        unapproveBtn && (unapproveBtn.disabled = false);
    }
});
