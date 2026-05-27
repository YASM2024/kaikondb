// paginationMessage.js — 一覧件数表示の共通フォーマット
(function (global) {
    const numberFormatter = new Intl.NumberFormat('ja-JP');

    function formatCount(n) {
        return numberFormatter.format(Number(n) || 0);
    }

    /**
     * @param {number} total 検索条件適用後の総件数
     * @param {number} displayed 表示中（または読み込み済み）件数
     * @returns {string}
     */
    function formatPaginationMessage(total, displayed) {
        const t = Number(total) || 0;
        const d = Number(displayed) || 0;
        if (t === 0) {
            return '0 件';
        }
        return `全 ${formatCount(t)} 件中 ${formatCount(d)} 件を表示`;
    }

    /** ページ番号ベースの一覧（/species など） */
    function displayedCountForPage(total, currentPage, perPage) {
        const t = Number(total) || 0;
        if (t === 0) {
            return 0;
        }
        const page = Number(currentPage) || 1;
        const size = Number(perPage) || 1;
        const start = (size * (page - 1)) + 1;
        const end = Math.min(t, size * page);
        return Math.max(0, end - start + 1);
    }

    /** 無限スクロール（続きを表示）の読み込み済み件数 */
    function displayedCountForInfiniteScroll(total, currentPage, perPage) {
        const t = Number(total) || 0;
        if (t === 0) {
            return 0;
        }
        const page = Number(currentPage) || 1;
        const size = Number(perPage) || 1;
        return Math.min(t, size * page);
    }

    global.PaginationMessage = {
        formatPaginationMessage,
        displayedCountForPage,
        displayedCountForInfiniteScroll,
    };
})(typeof window !== 'undefined' ? window : globalThis);
