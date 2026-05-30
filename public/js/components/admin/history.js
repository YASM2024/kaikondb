(function () {
    const config = window.adminHistory;
    if (!config) {
        return;
    }

    const tbody = document.getElementById('history_entries_body');
    const loaderRoot = document.getElementById('next_page_loader');
    const msgRoot = document.getElementById('number_of_show');
    if (!tbody || !loaderRoot || !msgRoot) {
        return;
    }

    let isLoading = false;
    let pagination = { ...config.pagination };

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

    function renderContent(entry) {
        const content = entry.content || {};

        if (config.type === 'records') {
            let html = escapeHtml(content.species_ja ?? '—');
            if (content.species) {
                html += `<span class="text-muted fst-italic ms-1">${escapeHtml(content.species)}</span>`;
            }
            return html;
        }

        return escapeHtml(content.text ?? '—');
    }

    function renderRow(entry) {
        const userName = entry.user_name ? escapeHtml(entry.user_name) : '—';
        const recordedAt = entry.recorded_at ? escapeHtml(entry.recorded_at) : '—';
        const action = entry.action ? escapeHtml(entry.action) : '';

        return `
            <tr>
                <td class="text-nowrap">${recordedAt}</td>
                <td>${userName}</td>
                <td>${action}</td>
                <td class="text-break">${renderContent(entry)}</td>
            </tr>
        `;
    }

    function appendRows(entries) {
        if (!Array.isArray(entries) || entries.length === 0) {
            return;
        }
        tbody.insertAdjacentHTML('beforeend', entries.map(renderRow).join(''));
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
        if (typeof NextPageLoader === 'undefined') {
            loaderRoot.innerHTML = '';
            msgRoot.innerText = '';
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

    async function loadPage(page) {
        if (isLoading) {
            return;
        }

        isLoading = true;
        setButtonLoading(true);

        const url = new URL(config.entriesUrl, window.location.origin);
        url.searchParams.set('days', String(config.days));
        url.searchParams.set('page', String(page));

        try {
            const response = await fetch(url.toString(), {
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
            console.error('履歴の追加読み込みに失敗しました:', error);
        } finally {
            isLoading = false;
            setButtonLoading(false);
        }
    }

    renderPaginationControls();
})();
