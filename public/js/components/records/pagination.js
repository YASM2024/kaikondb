// js/components/records/pagination.js
class Pagination {
    constructor({ last_page, current_page, per_page, total }) {
        this.last_page = last_page;
        this.current_page = current_page;
        this.per_page = per_page;
        this.total = total;
        this.eleLink = document.getElementById('pagination');
        this.eleMsg = document.getElementById('number_of_show');
    }

    static pages(c, n) {
        if (n < 6) return [...Array(n)].map((_, i) => i + 1);
        if (c < 5) return [1, 2, 3, 4, 5, 0, n];
        if (c > n - 4) return [1, 0, n - 4, n - 3, n - 2, n - 1, n];
        return [1, 0, c - 1, c, c + 1, 0, n];
    }

    renderLinks() {
        if (this.last_page === 1) {
            this.eleLink.innerHTML = '';
            return;
        }

        const html = `
            <ul class="pagination mt-3">
                ${Pagination.pages(this.current_page, this.last_page).map(num => {
                    if (num === this.current_page) {
                        return `<li class="page-item mx-1"><span class="page-link bg-secondary text-light">${num}</span></li>`;
                    }
                    if (num) {
                        return `<li class="page-item mx-1"><button class="page-link" data-page="${num}">${num}</button></li>`;
                    }
                    return `<li class="page-item mx-1"><span class="page-link">...</span></li>`;
                }).join('')}
            </ul>
        `;
        this.eleLink.innerHTML = html;

        this.eleLink.querySelectorAll('[data-page]').forEach(el => {
            el.addEventListener('click', e => {
                const page = parseInt(e.target.dataset.page, 10);
                this.goToPage(page);
            });
        });
    }

    renderMessage() {
        if (!this.eleMsg) {
            return;
        }
        if (this.total === undefined || this.total === null) {
            this.eleMsg.innerText = '';
            return;
        }
        const pm = typeof PaginationMessage !== 'undefined' ? PaginationMessage : null;
        if (!pm) {
            this.eleMsg.innerText = '';
            return;
        }
        const displayed = pm.displayedCountForPage(
            this.total,
            this.current_page,
            this.per_page
        );
        this.eleMsg.innerText = pm.formatPaginationMessage(this.total, displayed);
    }

    goToPage(page) {
        // 外部の Search オブジェクトに依存しないようにコールバックを渡す設計にすると良い
        if (typeof this.onPageChange === 'function') {
            this.onPageChange(page);
        }
    }
}
