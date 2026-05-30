(function () {
    const config = window.photoAdminConfig ?? {};

    const tbody = document.querySelector('#photoAdminTable tbody');
    const loaderRoot = document.getElementById('next_page_loader');
    const msgRoot = document.getElementById('number_of_show');

    let isLoading = false;
    let pagination = { ...(config.pagination ?? {}) };

    function buildUrl(template, id) {
        return String(template).replace('__ID__', String(id));
    }

    function escapeHtml(value) {
        if (value === null || value === undefined || value === '') {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
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

    function renderRow(photo) {
        const status = photo.status ?? config.currentStatus;
        const approvedCell = status === 'published'
            ? `<td>${escapeHtml(photo.approved_at ?? '')}</td>`
            : '';
        const actionBtn = status === 'pending'
            ? '<button type="button" class="btn btn-danger btn-sm w-100 btn-approve">承認</button>'
            : '<button type="button" class="btn btn-secondary btn-sm w-100 btn-unapprove">取消</button>';

        return `
            <tr data-photo-id="${escapeHtml(photo.id)}" data-status="${escapeHtml(status)}">
                <td>
                    <img src="${escapeHtml(photo.thumbnail_url)}"
                         alt="" class="img-fluid rounded" style="max-height:3.5rem;">
                </td>
                <td class="text-break">${escapeHtml(photo.photo_title)}</td>
                <td>${escapeHtml(photo.show_name)}</td>
                <td>${escapeHtml(photo.place)}</td>
                <td>${escapeHtml(photo.date)}</td>
                <td>${escapeHtml(photo.created_at)}</td>
                <td>${escapeHtml(photo.agreed_at ?? '—')}</td>
                ${approvedCell}
                <td>${actionBtn}</td>
            </tr>
        `;
    }

    function appendRows(photos) {
        if (!tbody || !Array.isArray(photos) || photos.length === 0) {
            return;
        }
        tbody.insertAdjacentHTML('beforeend', photos.map(renderRow).join(''));
    }

    function updateCountMessage() {
        if (!msgRoot || typeof PaginationMessage === 'undefined') {
            return;
        }
        const displayed = PaginationMessage.displayedCountForInfiniteScroll(
            pagination.total,
            pagination.current_page,
            pagination.per_page
        );
        msgRoot.innerText = PaginationMessage.formatPaginationMessage(pagination.total, displayed);
    }

    function setButtonLoading(loading) {
        const btn = document.getElementById('next_page_btn');
        if (!btn) {
            return;
        }
        if (loading) {
            btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
            btn.textContent = '読み込み中...';
            btn.classList.add('disabled');
            btn.style.pointerEvents = 'none';
            btn.setAttribute('aria-disabled', 'true');
            return;
        }
        btn.textContent = btn.dataset.originalText || '続きを表示する';
        btn.classList.remove('disabled');
        btn.style.pointerEvents = '';
        btn.removeAttribute('aria-disabled');
    }

    function renderPaginationControls() {
        if (!loaderRoot) {
            return;
        }

        if (typeof NextPageLoader === 'undefined') {
            loaderRoot.innerHTML = '';
            updateCountMessage();
            return;
        }

        const loader = new NextPageLoader({
            current_page: pagination.current_page,
            last_page: pagination.last_page,
            per_page: pagination.per_page,
            total: pagination.total,
        });

        const created = loader.printBtn();
        if (created) {
            const nextPageBtn = document.getElementById('next_page_btn');
            if (nextPageBtn) {
                nextPageBtn.addEventListener('click', () => {
                    if (isLoading) {
                        return;
                    }
                    const currentPage = parseInt(nextPageBtn.getAttribute('data-current-page'), 10) || 1;
                    loadPage(currentPage + 1);
                });
            }
        }

        loader.printMsg();
    }

    function buildEntriesUrl(page) {
        const url = new URL(config.entriesUrl, window.location.origin);
        url.searchParams.set('status', config.currentStatus);
        url.searchParams.set('sort', config.sort ?? 'created_at');
        url.searchParams.set('dir', config.dir ?? 'desc');
        url.searchParams.set('page', String(page));

        Object.entries(config.filters ?? {}).forEach(([key, value]) => {
            if (value) {
                url.searchParams.set(key, value);
            }
        });

        return url;
    }

    async function loadPage(page) {
        if (isLoading || !config.entriesUrl) {
            return;
        }

        isLoading = true;
        setButtonLoading(true);

        try {
            const response = await fetch(buildEntriesUrl(page).toString(), {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const json = await response.json();
            appendRows(json.data);
            pagination = {
                current_page: json.current_page,
                last_page: json.last_page,
                per_page: json.per_page,
                total: json.total,
            };
            renderPaginationControls();
        } catch (error) {
            console.error('写真管理の追加読み込みに失敗しました:', error);
            showToast('追加の読み込みに失敗しました', true);
        } finally {
            isLoading = false;
            setButtonLoading(false);
        }
    }

    async function postApproval(id, approve) {
        const template = approve ? config.approveUrlTemplate : config.unapproveUrlTemplate;
        const url = buildUrl(template, id);

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
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

        if (pagination.total > 0) {
            pagination.total -= 1;
        }

        if (tbody && tbody.children.length === 0) {
            pagination.current_page = 1;
            pagination.last_page = 1;
            if (msgRoot) {
                msgRoot.innerText = '0 件';
            }
            if (loaderRoot) {
                loaderRoot.innerHTML = '';
            }
            return;
        }

        updateCountMessage();
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

        if (approveBtn) approveBtn.disabled = true;
        if (unapproveBtn) unapproveBtn.disabled = true;

        try {
            await postApproval(id, approve);
            removeRow(row);
            showToast(approve ? '承認しました' : '承認を取り消しました');
        } catch (error) {
            showToast(error.message ?? 'エラーが発生しました', true);
            if (approveBtn) approveBtn.disabled = false;
            if (unapproveBtn) unapproveBtn.disabled = false;
        }
    });

    if (tbody && loaderRoot && msgRoot) {
        renderPaginationControls();
    }
})();
