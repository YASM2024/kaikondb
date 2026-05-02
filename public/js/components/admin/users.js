/* ============================================
   定数
============================================ */
const BASEURL      = '/database/admin/users';
const USERICONWAIT = '/database/storage/img/wait.png';
const USERICONURL  = '/database/storage/profile';

const userModalEle = document.getElementById('userModal');
const userIcon     = document.getElementById('userIcon');
const submitBtn    = document.getElementById('submit');

/* ============================================
   hiddenFileInput を一度だけ作成
============================================ */
const hiddenFileInput = document.createElement('input');
hiddenFileInput.type = 'file';
hiddenFileInput.style.display = 'none';
hiddenFileInput.id = 'hiddenFileInput';
document.body.appendChild(hiddenFileInput);

hiddenFileInput.addEventListener('change', handleFileSelect);

/* ============================================
   1. ユーザー行クリック → モーダル表示
============================================ */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-user-id]').forEach(button => {
        button.addEventListener('click', () => loadUser(button.dataset.userId));
    });
});

function loadUser(userId) {
    fetch(`${BASEURL}/${userId}`)
        .then(res => res.json())
        .then(updateModal)
        .catch(err => {
            console.error(err);
            alert('更新に失敗しました。');
        });
}

/* ============================================
   2. モーダル内のイベントは「委譲」で一度だけ登録
============================================ */
document.addEventListener('click', (e) => {
    if (e.target.id === 'editIcon') openFileDialog();
    if (e.target.id === 'delete')   handleDelete();
});

/* ============================================
   3. モーダルが閉じた時の処理
============================================ */
userModalEle.addEventListener('hidden.bs.modal', () => {
    resetInputForm();
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
});

/* ============================================
   4. fetch タイムアウト
============================================ */
function fetchWithTimeout(url, options, timeout = 5000) {
    return new Promise((resolve, reject) => {
        const timer = setTimeout(() => reject(new Error("通信がタイムアウトしました。")), timeout);

        fetch(url, options)
            .then(response => {
                clearTimeout(timer);
                resolve(response);
            })
            .catch(reject);
    });
}

/* ============================================
   5. モーダル更新処理
============================================ */
function updateModal(data) {
    userIcon.src = `${USERICONURL}/${data.icon || 'anonymousIcon.svg'}`;

    rolesRow?.classList.toggle('d-none', !data.email_verified);

    const fieldActions = {
        id:         el => el.textContent = data.id ?? 'N/A',
        name:       el => el.textContent = data.name ?? 'N/A',
        show_name:  el => el.value       = data.show_name,
        email:      el => el.placeholder = data.email,
        last_login: el => el.textContent = data.last_login ?? 'N/A',

        is_active: () => updateActiveSwitch(data),
        roles:     el => updateRoles(el, data.roles)
    };

    document.querySelectorAll('[data-field]').forEach(el => {
        const field = el.dataset.field;
        (fieldActions[field] || (() => console.error('Unmapped field:', field)))(el);
    });

    applyZebraStripes();
    new bootstrap.Modal(userModalEle).show();
}

function updateActiveSwitch(data) {
    const target = document.querySelector('#is_active');
    const label  = document.querySelector('label[for="is_active"]');

    if (!target || !label) return;

    if (!data.email_verified) {
        target.style.display = 'none';
        label.textContent = '未認証';
        return;
    }

    target.style.display = 'inline-block';
    target.checked = data.is_active;
    label.textContent = data.is_active ? '有効' : '無効';

    target.onchange = () => {
        label.textContent = target.checked ? '有効' : '無効';
    };
}

function updateRoles(el, rolesStr) {
    const arrRoles = rolesStr.split(',');
    el.querySelectorAll('input').forEach(role => {
        if (arrRoles.includes('999')) {
            role.disabled = true;
            role.checked = true;
        } else {
            role.disabled = false;
            role.checked = arrRoles.includes(role.value);
        }
    });
}

/* ============================================
   6. ファイル選択（最適化済み）
============================================ */
function openFileDialog() {
    hiddenFileInput.click();
}

function handleFileSelect() {
    if (!this.files.length) return;

    const file = this.files[0];
    userIcon.src = USERICONWAIT;

    const reader = new FileReader();
    reader.onload = () => userIcon.src = reader.result;
    reader.readAsDataURL(file);
}

/* ============================================
   7. モーダル閉じたら file input リセット
============================================ */
function resetInputForm() {
    hiddenFileInput.value = null;
}

/* ============================================
   8. 投稿処理
============================================ */
submitBtn.addEventListener('click', submitForm);

function submitForm() {
    const id = document.querySelector('div[data-field="id"]').textContent;
    const url = `${BASEURL}/${id}`;

    const body = new FormData();
    body.append('show_name', document.querySelector('input[data-field="show_name"]')?.value ?? null);
    body.append('email',     document.querySelector('input[data-field="email"]')?.value ?? null);
    body.append('is_active', document.querySelector('#is_active')?.checked ?? null);

    const roles = [...document.querySelectorAll('input[name="roles[]"]:checked')].map(i => i.value);
    body.append('roles', JSON.stringify(roles));

    const file = hiddenFileInput.files[0];
    if (file) body.append('icon', file);

    fetch(url, {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body
    })
    .then(res => res.json())
    .then(handleSubmitResponse)
    .catch(() => alert("更新に失敗しました。"));
}

function handleSubmitResponse(data) {
    if (data.res === 0) {
        alert("更新しました。");
        location.reload();
        return;
    }

    if (data.res === 1) {
        const errors = Object.values(data.errors).flat().join('\n');
        alert("更新に失敗しました:\n" + errors);
        return;
    }

    alert("不明なレスポンス");
}

/* ============================================
   9. スイッチの状態変更
============================================ */
document.querySelectorAll(".form-check-custom").forEach((ele, index) => {
    let previousState = {};

    ele.addEventListener("mousedown", () => {
        previousState[index] = ele.checked;
    });

    ele.addEventListener("change", () => updateActiveUser(ele, previousState[index]));
});

function updateActiveUser(ele, prevState) {
    const userId = ele.id.split("-").pop();
    const url = `${BASEURL}/${userId}`;

    const body = new FormData();
    body.append('is_active', ele.checked);

    fetchWithTimeout(url, {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body
    })
    .then(res => res.json())
    .then(data => {
        if (data.res === 0) {
            let label = ele.closest(".form-check").querySelector("label");
            label.textContent = ele.checked ? "有効" : "無効";
        } else {
            throw new Error();
        }
    })
    .catch(() => {
        alert("更新に失敗しました。");
        ele.checked = prevState;
    });
}

/* ============================================
   10. ゼブラスタイル
============================================ */
function applyZebraStripes() {
    const rows = document.querySelectorAll('.zebra-container > .zebra');
    let visibleIndex = 0;

    rows.forEach(row => {
        if (row.classList.contains('d-none')) return;
        row.style.backgroundColor = (visibleIndex++ % 2 === 0) ? 'white' : '#e0e0e0';
    });
}

/* ============================================
   11. 削除ボタン（必要なら実装）
============================================ */
function handleDelete() {
    console.log("アカウント削除がクリックされました");
}
