export function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";
};

export function escapeHtml(value) {
    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

export function normalizeStatus(value) {
    return Number(value) === 1 ? 1 : 0;
};

export function getStatusLabel(status) {
    return Number(status) === 1 ? "有効" : "無効";
};

export function escapeCsvValue(value) {
    return `"${String(value ?? "").replace(/"/g, '""')}"`;
};