function onReady(fn) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        fn();
    }
}

/** @param {unknown} raw @returns {string[]} */
function normalizeLabelList(raw) {
    if (Array.isArray(raw)) {
        return raw.filter((x) => typeof x === 'string' && x.length > 0);
    }
    if (raw && typeof raw === 'object') {
        return Object.values(raw).filter((x) => typeof x === 'string' && x.length > 0);
    }
    return [];
}

/** @param {Record<string, unknown>} data */
function formatDeleteErrorMessage(data) {
    const err = data.errors;
    if (typeof err === 'string') {
        return err;
    }
    if (Array.isArray(err)) {
        return err.map(String).join('\n');
    }
    if (err && typeof err === 'object') {
        return Object.values(err)
            .flat()
            .map(String)
            .join('\n');
    }
    if (typeof data.message === 'string' && data.message.length > 0) {
        return data.message;
    }
    try {
        return JSON.stringify(data);
    } catch {
        return '不明なエラー';
    }
}

/**
 * 管理画面：ユーザ一覧・モーダル編集
 * @param {HTMLElement} root
 */
export function initAdminUsersPage(root) {
    const TAGGABLE_ROLES = ['010', '900'];
    const ADMIN_ROLE = '999';

    const apiBase = root.dataset.apiBase || '';
    const userIconWait = root.dataset.waitImage || '';
    const userIconUrl = root.dataset.profileDir || '';

    const userModalEle = document.getElementById('userModal');
    const userIcon = document.getElementById('userIcon');
    const submitBtn = document.getElementById('submit');

    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content;

    const hiddenFileInput = document.createElement('input');
    hiddenFileInput.type = 'file';
    hiddenFileInput.style.display = 'none';
    hiddenFileInput.id = 'hiddenFileInput';
    document.body.appendChild(hiddenFileInput);

    hiddenFileInput.addEventListener('change', handleFileSelect);

    onReady(() => {
        document.querySelectorAll('[data-user-id]').forEach((button) => {
            button.addEventListener('click', () => loadUser(button.dataset.userId));
        });

        ['#role-010', '#role-900'].forEach((selector) => {
            document.querySelector(selector)?.addEventListener('change', toggleTagsRow);
        });
    });

    function loadUser(userId) {
        fetch(`${apiBase}/${userId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then((res) => res.json())
            .then(updateModal)
            .catch((err) => {
                console.error(err);
                alert('読み込みに失敗しました。');
            });
    }

    document.addEventListener('click', (e) => {
        if (e.target.id === 'editIcon' || e.target.closest?.('#editIcon')) openFileDialog();
        if (e.target.id === 'delete' || e.target.closest?.('#delete')) handleDelete();
    });

    userModalEle.addEventListener('hidden.bs.modal', () => {
        resetInputForm();
        document.querySelectorAll('.modal-backdrop').forEach((el) => el.remove());
    });

    function fetchWithTimeout(url, options, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const timer = setTimeout(() => reject(new Error('通信がタイムアウトしました。')), timeout);

            fetch(url, options)
                .then((response) => {
                    clearTimeout(timer);
                    resolve(response);
                })
                .catch(reject);
        });
    }

    function updateModal(data) {
        userIcon.src = `${userIconUrl}/${data.icon || 'anonymousIcon.svg'}`;

        const rolesRow = document.getElementById('rolesRow');
        rolesRow?.classList.toggle('d-none', !data.email_verified);

        const fieldActions = {
            id: (el) => {
                el.textContent = data.id ?? 'N/A';
            },
            name: (el) => {
                el.textContent = data.name ?? 'N/A';
            },
            show_name: (el) => {
                el.value = data.show_name;
            },
            email: (el) => {
                el.placeholder = data.email;
            },
            last_login: (el) => {
                el.textContent = data.last_login ?? 'N/A';
            },
            is_active: () => updateActiveSwitch(data),
            roles: (el) => updateRoles(el, data.roles),
        };

        document.querySelectorAll('[data-field]').forEach((el) => {
            const field = el.dataset.field;
            if (field === 'tags') {
                return;
            }
            (fieldActions[field] || (() => console.error('Unmapped field:', field)))(el);
        });

        toggleTagsRow();
        const tagsEl = document.querySelector('[data-field="tags"]');
        if (tagsEl && usesTagsUi()) {
            updateTags(tagsEl, data.tags);
        }

        applyZebraStripes();
        bootstrap.Modal.getOrCreateInstance(userModalEle).show();
    }

    function isAdministratorAccount() {
        const roleInputs = [...document.querySelectorAll('input[name="roles[]"]')];
        const checked = roleInputs.filter((role) => role.checked).map((role) => role.value);

        return roleInputs.some((role) => role.disabled) && checked.includes(ADMIN_ROLE);
    }

    function usesTagsUi() {
        if (isAdministratorAccount()) {
            return false;
        }

        const checkedRoles = [...document.querySelectorAll('input[name="roles[]"]:checked')].map(
            (role) => role.value
        );

        return checkedRoles.some((code) => TAGGABLE_ROLES.includes(code));
    }

    function toggleTagsRow() {
        const tagsRow = document.getElementById('tagsRow');
        const rolesRow = document.getElementById('rolesRow');
        const emailVerified = !rolesRow?.classList.contains('d-none');
        const showTags = emailVerified && usesTagsUi();

        tagsRow?.classList.toggle('d-none', !showTags);

        if (!showTags) {
            tagsRow?.querySelectorAll('input[name="tags[]"]').forEach((tag) => {
                tag.checked = false;
            });
        }

        applyZebraStripes();
    }

    function updateActiveSwitch(data) {
        const target = document.querySelector('#is_active');
        const label = document.querySelector('label[for="is_active"]');

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
        el.querySelectorAll('input').forEach((role) => {
            if (arrRoles.includes('999')) {
                role.disabled = true;
                role.checked = true;
            } else {
                role.disabled = false;
                role.checked = arrRoles.includes(role.value);
            }
        });
    }

    /** @param {unknown} tagsRaw */
    function normalizeTagIds(tagsRaw) {
        if (Array.isArray(tagsRaw)) {
            return tagsRaw
                .map((item) => {
                    if (item && typeof item === 'object' && 'id' in item) {
                        return String(item.id);
                    }
                    return String(item);
                })
                .filter(Boolean);
        }

        if (tagsRaw === null || tagsRaw === undefined || tagsRaw === '') {
            return [];
        }

        return String(tagsRaw)
            .split(',')
            .map((item) => item.trim())
            .filter(Boolean);
    }

    /** @param {HTMLElement} el @param {unknown} tagsRaw */
    function updateTags(el, tagsRaw) {
        const arrTags = normalizeTagIds(tagsRaw);

        el.querySelectorAll('input[name="tags[]"]').forEach((tag) => {
            tag.checked = arrTags.includes(String(tag.value));
        });
    }

    function openFileDialog() {
        hiddenFileInput.click();
    }

    function handleFileSelect() {
        if (!this.files.length) return;

        const file = this.files[0];
        userIcon.src = userIconWait;

        const reader = new FileReader();
        reader.onload = () => {
            userIcon.src = reader.result;
        };
        reader.readAsDataURL(file);
    }

    function resetInputForm() {
        hiddenFileInput.value = null;
    }

    if (submitBtn) submitBtn.addEventListener('click', submitForm);

    function submitForm() {
        const id = document.querySelector('div[data-field="id"]').textContent;
        const url = `${apiBase}/${id}`;

        const body = new FormData();
        body.append('show_name', document.querySelector('input[data-field="show_name"]')?.value ?? null);
        body.append('email', document.querySelector('input[data-field="email"]')?.value ?? null);
        body.append('is_active', document.querySelector('#is_active')?.checked ?? null);

        const roles = [...document.querySelectorAll('input[name="roles[]"]:checked')].map((i) => i.value);
        body.append('roles', JSON.stringify(roles));

        if (usesTagsUi()) {
            const tagIds = [...document.querySelectorAll('input[name="tags[]"]:checked')].map((i) => i.value);
            body.append('tags', JSON.stringify(tagIds));
        }

        const file = hiddenFileInput.files[0];
        if (file) body.append('icon', file);

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        })
            .then((res) => res.json())
            .then(handleSubmitResponse)
            .catch(() => alert('更新に失敗しました。'));
    }

    function handleSubmitResponse(data) {
        if (data.res === 0) {
            alert('更新しました。');
            location.reload();
            return;
        }

        if (data.res === 1) {
            const errors = Object.values(data.errors).flat().join('\n');
            alert(`更新に失敗しました:\n${errors}`);
            return;
        }

        alert('不明なレスポンス');
    }

    onReady(() => {
        document.querySelectorAll('.form-check-custom').forEach((ele, index) => {
            const previousState = {};

            ele.addEventListener('mousedown', () => {
                previousState[index] = ele.checked;
            });

            ele.addEventListener('change', () => updateActiveUser(ele, previousState[index]));
        });
    });

    function updateActiveUser(ele, prevState) {
        const userId = ele.id.split('-').pop();
        const url = `${apiBase}/${userId}`;

        const body = new FormData();
        body.append('is_active', ele.checked);

        fetchWithTimeout(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.res === 0) {
                    const label = ele.closest('.form-check').querySelector('label');
                    label.textContent = ele.checked ? '有効' : '無効';
                } else {
                    throw new Error();
                }
            })
            .catch(() => {
                alert('更新に失敗しました。');
                ele.checked = prevState;
            });
    }

    function applyZebraStripes() {
        const rows = document.querySelectorAll('.zebra-container > .zebra');
        let visibleIndex = 0;

        rows.forEach((row) => {
            if (row.classList.contains('d-none')) return;
            row.style.backgroundColor = visibleIndex++ % 2 === 0 ? 'white' : '#e0e0e0';
        });
    }

    function handleDelete() {
        const id = document.querySelector('div[data-field="id"]')?.textContent?.trim();
        if (!id || id === 'N/A') {
            alert('ユーザーが選択されていません。');
            return;
        }
        if (!confirm('このユーザを削除します。よろしいですか？')) return;

        const modal = bootstrap.Modal.getInstance(userModalEle);

        fetch(`${apiBase}/${id}/purge`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrf(),
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        })
            .then((res) => res.json())
            .then((data) => {
                const resCode = Number(data.res);
                if (resCode === 0) {
                    modal?.hide();
                    alert('削除しました。');
                    location.reload();
                    return;
                }

                const labels = normalizeLabelList(data.labels);
                const isInUse =
                    String(data.code || '') === 'in_use' ||
                    (labels.length > 0 && typeof data.message === 'string' && data.message.length > 0);

                if (isInUse && labels.length > 0) {
                    const head =
                        data.message || 'コンテンツで使用されているため削除できません。';
                    alert(`${head}\n\n・${labels.join('\n・')}`);
                    return;
                }

                const msg = formatDeleteErrorMessage(data);
                alert(`削除に失敗しました。\n${msg}`);
            })
            .catch(() => alert('削除に失敗しました。'));
    }
}
