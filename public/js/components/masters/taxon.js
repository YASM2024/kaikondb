import * as C from "./constants.js";
import { DOM } from "./dom.js";
import { 
    getCsrfToken, escapeHtml, normalizeStatus, 
    getStatusLabel, escapeCsvValue
} from './utils.js';

const state = {
    orders: [],
    keyword: "",
    statusFilter: "all",
    selectedOrderIds: new Set(),
    editingOrderId: null
};


async function postForm(url, payload) {
    const body = new FormData();

    Object.entries(payload).forEach(([key, value]) => {
        body.append(key, value ?? "");
    });

    const response = await fetch(url, {
        method: "POST",
        mode: "cors",
        cache: "no-cache",
        credentials: "same-origin",
        headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        "Accept": "application/json"
        },
        redirect: "follow",
        referrerPolicy: "no-referrer",
        body
    });

    if (!response.ok) {
        let message = `HTTP ${response.status}`;

        try {
        const errorData = await response.json();
        message = errorData.message ?? message;
        } catch (_) {}

        throw new Error(message);
    }

    const contentType = response.headers.get("content-type") ?? "";
    if (contentType.includes("application/json")) {
        return await response.json();
    }

    return null;
}


const orderCreateAndEditModal = typeof bootstrap !== "undefined"
    ? new bootstrap.Modal(DOM.orderCreateAndEditModalElement)
    : null;

function mapOrder(item) {
    return {
        id: String(item.id ?? ""),
        orderJa: item.order_ja ?? "",
        order: item.order ?? "",
        code: item.code ?? "",
        status: normalizeStatus(item.status)
    };
}

function getFilteredOrders() {
    const keyword = state.keyword.trim().toLowerCase();

    return state.orders.filter((item) => {
        const isStatusMatched =
        state.statusFilter === "all" ||
        String(item.status) === state.statusFilter;

        const isKeywordMatched =
        keyword === "" ||
        String(item.code).toLowerCase().includes(keyword) ||
        String(item.orderJa).toLowerCase().includes(keyword) ||
        String(item.order).toLowerCase().includes(keyword);

        return isStatusMatched && isKeywordMatched;
    });
}

function getVisibleOrderIds() {
    return getFilteredOrders().map((item) => item.id);
}

function updateCsvDownloadButton() {
    const selectedCount = state.orders.filter((item) => state.selectedOrderIds.has(item.id)).length;
    DOM.csvDownloadButton.disabled = selectedCount === 0;
    DOM.csvDownloadButton.textContent =
        selectedCount === 0
        ? "CSVダウンロード"
        : `CSVダウンロード（${selectedCount}件）`;
}

function syncSelectAllCheckbox() {
    const visibleIds = getVisibleOrderIds();

    if (visibleIds.length === 0) {
        DOM.selectAllOrders.checked = false;
        DOM.selectAllOrders.indeterminate = false;
        DOM.selectAllOrders.disabled = true;
        return;
    }

    DOM.selectAllOrders.disabled = false;

    const selectedVisibleCount = visibleIds.filter((id) => state.selectedOrderIds.has(id)).length;

    DOM.selectAllOrders.checked = selectedVisibleCount === visibleIds.length;
    DOM.selectAllOrders.indeterminate =
        selectedVisibleCount > 0 && selectedVisibleCount < visibleIds.length;
}

function renderOrders() {
    const filteredOrders = getFilteredOrders();
    const inactiveCount = filteredOrders.filter((item) => Number(item.status) === 0).length;

    DOM.countPill.textContent = `${filteredOrders.length} records / 無効 ${inactiveCount}件`;

    if (filteredOrders.length === 0) {
        DOM.ordersTableBody.innerHTML = "";
        DOM.tableWrap.classList.add("d-none");
        DOM.emptyBox.classList.remove("d-none");
        syncSelectAllCheckbox();
        updateCsvDownloadButton();
        return;
    }

    DOM.emptyBox.classList.add("d-none");
    DOM.tableWrap.classList.remove("d-none");

    DOM.ordersTableBody.innerHTML = filteredOrders.map((item) => {
        const isActive = Number(item.status) === 1;
        const isChecked = state.selectedOrderIds.has(item.id);
        const toggleTitle = isActive ? "無効化" : "再有効化";
        const toggleIcon = isActive ? "bi-slash-circle" : "bi-arrow-clockwise";
        const toggleColorClass = isActive ? "text-danger" : "text-success";

        return `
        <tr class="${isActive ? "" : "table-secondary"}">
            <td class="px-2 py-2 text-center">
            <input
                type="checkbox"
                class="form-check-input mt-0 rowCheckbox js-selectOrder"
                data-order-id="${escapeHtml(item.id)}"
                aria-label="Orderを選択"
                ${isChecked ? "checked" : ""}
            >
            </td>

            <td class="px-3 py-2 text-nowrap">
            <code>${escapeHtml(item.code)}</code>
            </td>

            <td class="px-3 py-2">
            <div class="orderNames d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-1 gap-lg-2">
                <div class="orderPrimary">
                <code class="inlineCodeMobile">${escapeHtml(item.code)}</code>
                <span class="fw-semibold text-dark lh-sm">
                    ${escapeHtml(item.orderJa)}
                </span>
                </div>

                <span class="d-none d-lg-inline text-body-tertiary">·</span>

                <span class="text-body-secondary fst-italic small lh-sm orderLatin">
                ${escapeHtml(item.order)}
                </span>
            </div>
            </td>

            <td class="px-3 py-2 text-nowrap">
            <span class="badge ${isActive ? "text-bg-success" : "text-bg-secondary"}">
                ${escapeHtml(getStatusLabel(item.status))}
            </span>
            </td>

            <td class="px-3 py-2 text-end">
            <div class="actionIcons">
                <a
                href="/database/master/taxon/family?order_id=${escapeHtml(item.id)}"
                type="button"
                class="actionButton text-primary js-lowerCategory"
                data-order-id="${escapeHtml(item.id)}"
                title="下位分類"
                aria-label="下位分類"
                >
                <i class="bi bi-diagram-3"></i>
                <span class="actionLabel">下位分類</span>
                </a>

                <button
                type="button"
                class="actionButton text-secondary js-editOrder"
                data-order-id="${escapeHtml(item.id)}"
                title="編集"
                aria-label="編集"
                >
                <i class="bi bi-pencil"></i>
                <span class="actionLabel">編集</span>
                </button>

                <button
                type="button"
                class="actionButton ${toggleColorClass} js-toggleOrder"
                data-order-id="${escapeHtml(item.id)}"
                data-next-status="${isActive ? 0 : 1}"
                title="${toggleTitle}"
                aria-label="${toggleTitle}"
                >
                <i class="bi ${toggleIcon}"></i>
                <span class="actionLabel">${toggleTitle}</span>
                </button>
            </div>
            </td>
        </tr>
        `;
    }).join("");

    bindRowActions();
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
}

function getOrderById(orderId) {
    return state.orders.find((item) => item.id === String(orderId)) ?? null;
}

function resetOrderEditError() {
    DOM.orderEditErrorBox.classList.add("d-none");
    DOM.orderEditErrorBox.textContent = "";
}


function openOrderCreateModal() {
    if (!orderCreateAndEditModal) {
        alert("Bootstrap の modal が利用できません。");
        return;
    }

    state.editingOrderId = null;
    resetOrderEditError();

    DOM.orderCreateAndEditModalLabel.textContent = "Orderを追加";

    DOM.editOrderId.value = "";
    DOM.editOrderCode.value = "";
    DOM.editOrderLatin.value = "";
    DOM.editOrderJa.value = "";
    DOM.editOrderStatus.value = "1";

    orderCreateAndEditModal.show();
}

function openOrderEditModal(orderId) {
    const targetOrder = getOrderById(orderId);

    if (!targetOrder) {
        alert("対象の Order が見つかりません。");
        return;
    }

    if (!orderCreateAndEditModal) {
        alert("Bootstrap の modal が利用できません。");
        return;
    }

    state.editingOrderId = targetOrder.id;
    resetOrderEditError();

    DOM.orderCreateAndEditModalLabel.textContent = "Orderを編集";

    DOM.editOrderId.value = targetOrder.id;
    DOM.editOrderCode.value = targetOrder.code ?? "";
    DOM.editOrderLatin.value = targetOrder.order ?? "";
    DOM.editOrderJa.value = targetOrder.orderJa ?? "";
    DOM.editOrderStatus.value = String(targetOrder.status ?? 1);

    orderCreateAndEditModal.show();
}

function bindRowActions() {
    document.querySelectorAll(".js-selectOrder").forEach((checkbox) => {
        checkbox.addEventListener("change", (event) => {
        const { orderId } = event.currentTarget.dataset;

        if (event.currentTarget.checked) {
            state.selectedOrderIds.add(orderId);
        } else {
            state.selectedOrderIds.delete(orderId);
        }

        syncSelectAllCheckbox();
        updateCsvDownloadButton();
        });
    });

    document.querySelectorAll(".js-lowerCategory").forEach((button) => {
        button.addEventListener("click", (event) => {
        const { orderId } = event.currentTarget.dataset;
        });
    });

    document.querySelectorAll(".js-editOrder").forEach((button) => {
        button.addEventListener("click", (event) => {
        const { orderId } = event.currentTarget.dataset;
        openOrderEditModal(orderId);
        });
    });

    document.querySelectorAll(".js-toggleOrder").forEach((button) => {
        button.addEventListener("click", async (event) => {
        const { orderId, nextStatus } = event.currentTarget.dataset;
        await updateOrderStatus(orderId, nextStatus);
        });
    });

}

async function updateOrderStatus(orderId, nextStatus) {
    const targetOrder = getOrderById(orderId);

    if (!targetOrder) {
        alert("対象の Order が見つかりません。");
        return;
    }

    try {
        await postForm(C.ORDER_STATUS_EDIT_URL, {
            id: orderId,
            status: Number(nextStatus)
        });

        updateOrderInState({
            ...targetOrder,
            status: Number(nextStatus)
        });

        renderOrders();
    } catch (error) {
        alert(`状態変更に失敗しました: ${error.message}`);
    }
}

async function importOrderCsv(file) {
    if (!file) {
        return;
    }

    const body = new FormData();
    body.append("csv_file", file);

    if (DOM.csvImportButton) {
        DOM.csvImportButton.classList.add("disabled");
        DOM.csvImportButton.setAttribute("aria-disabled", "true");
    }

    try {
        const response = await fetch(C.ORDER_IMPORT_URL, {
        method: "POST",
        credentials: "same-origin",
        headers: {
            "X-CSRF-TOKEN": getCsrfToken(),
            "Accept": "application/json"
        },
        body
        });

        if (!response.ok) {
        let message = `HTTP ${response.status}`;

        try {
            const errorData = await response.json();
            message = errorData.message ?? message;
        } catch (_) {}

        throw new Error(message);
        }

        const result = await response.json();

        await fetchOrders();

        alert(
        [
            "CSV取込が完了しました。",
            `追加: ${result.createdCount ?? 0}件`,
            `更新: ${result.updatedCount ?? 0}件`,
            `削除: ${result.deletedCount ?? 0}件`,
            `スキップ: ${result.skippedCount ?? 0}件`
        ].join("\n")
        );
    } catch (error) {
        alert(`CSV取込に失敗しました: ${error.message}`);
    } finally {
        if (DOM.csvImportButton) {
            DOM.csvImportButton.classList.remove("disabled");
            DOM.csvImportButton.removeAttribute("aria-disabled");
        }

        if (DOM.csvImportInput) {
            DOM.csvImportInput.value = "";
        }
    }
}

function createCsvFileName() {
    const now = new Date();
    const pad = (value) => String(value).padStart(2, "0");

    return `orders_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
}

function downloadSelectedOrdersCsv() {
    const selectedOrders = state.orders.filter((item) => state.selectedOrderIds.has(item.id));

    if (selectedOrders.length === 0) {
        return;
    }

    const header = ["code", "order_ja", "order", "status"];
    const rows = selectedOrders.map((item) => [
        item.code,
        item.orderJa,
        item.order,
        getStatusLabel(item.status)
    ]);

    const csvContent = [header, ...rows]
        .map((row) => row.map(escapeCsvValue).join(","))
        .join("\r\n");

    const blob = new Blob(
        [csvContent],
        { type: "text/csv;charset=utf-8;" }
    );

    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");

    link.href = url;
    link.download = createCsvFileName();
    document.body.appendChild(link);
    link.click();
    link.remove();

    URL.revokeObjectURL(url);
}

async function fetchOrders() {
    DOM.loadingBox.classList.remove("d-none");
    DOM.errorBox.classList.add("d-none");
    DOM.errorBox.textContent = "";
    DOM.emptyBox.classList.add("d-none");
    DOM.tableWrap.classList.add("d-none");

    try {
        const response = await fetch("/database/master/order/show", {
        method: "GET",
        headers: {
            Accept: "application/json"
        }
        });

        if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        state.orders = Array.isArray(data) ? data.map(mapOrder) : [];

        const validIds = new Set(state.orders.map((item) => item.id));
        state.selectedOrderIds = new Set(
        [...state.selectedOrderIds].filter((id) => validIds.has(id))
        );

        renderOrders();
    } catch (error) {
        DOM.errorBox.textContent = `Orders の取得に失敗しました: ${error.message}`;
        DOM.errorBox.classList.remove("d-none");
        syncSelectAllCheckbox();
        updateCsvDownloadButton();
    } finally {
        DOM.loadingBox.classList.add("d-none");
    }
}

function applyFilters() {
    state.keyword = DOM.keywordInput.value;
    state.statusFilter = DOM.statusFilter.value;
    renderOrders();
}


function validateOrderEditForm() {
    if (DOM.editOrderCode.value.trim() === "") {
        return "code は必須です。";
    }

    if (DOM.editOrderLatin.value.trim() === "") {
        return "order は必須です。";
    }

    if (DOM.editOrderJa.value.trim() === "") {
        return "order_ja は必須です。";
    }

    if (!["0", "1"].includes(DOM.editOrderStatus.value)) {
        return "status が不正です。";
    }

    return "";
}

function addOrderToState(newOrder) {
    state.orders = [newOrder, ...state.orders];
}

function updateOrderInState(updatedOrder) {
    state.orders = state.orders.map((item) => {
        if (item.id !== updatedOrder.id) {
            return item;
        }

        return {
            ...item,
            code: updatedOrder.code,
            order: updatedOrder.order,
            orderJa: updatedOrder.orderJa,
            status: normalizeStatus(updatedOrder.status)
        };
    });
}

async function saveOrder() {
    const validationMessage = validateOrderEditForm();

    if (validationMessage) {
        DOM.orderEditErrorBox.textContent = validationMessage;
        DOM.orderEditErrorBox.classList.remove("d-none");
        return;
    }

    resetOrderEditError();
    DOM.saveOrderButton.disabled = true;

    const isCreateMode = state.editingOrderId === null;

    const payload = {
        code: DOM.editOrderCode.value.trim(),
        order: DOM.editOrderLatin.value.trim(),
        order_ja: DOM.editOrderJa.value.trim(),
        status: Number(DOM.editOrderStatus.value)
    };

    if (!isCreateMode) {
        payload.id = state.editingOrderId;
    }

    try {
        const result = await postForm(
        isCreateMode ? C.ORDER_CREATE_URL : C.ORDER_EDIT_URL,
        payload
        );

        // サーバが data を返す場合を優先
        const serverOrder = result?.data
        ? mapOrder(result.data)
        : {
            id: isCreateMode ? String(result?.id ?? "") : String(payload.id),
            code: payload.code,
            order: payload.order,
            orderJa: payload.order_ja,
            status: payload.status
            };

        if (isCreateMode) {
        // idがレスポンスで返らないAPIなら fetchOrders() の方が安全
        if (serverOrder.id) {
            addOrderToState(serverOrder);
            renderOrders();
        } else {
            await fetchOrders();
        }
        } else {
        updateOrderInState(serverOrder);
        renderOrders();
        }

        await fetchOrders();
        orderCreateAndEditModal.hide();

    } catch (error) {
        DOM.orderEditErrorBox.textContent = `保存に失敗しました: ${error.message}`;
        DOM.orderEditErrorBox.classList.remove("d-none");
    } finally {
        DOM.saveOrderButton.disabled = false;
    }
}


DOM.selectAllOrders.addEventListener("change", (event) => {
    const visibleIds = getVisibleOrderIds();

    if (event.currentTarget.checked) {
        visibleIds.forEach((id) => state.selectedOrderIds.add(id));
    } else {
        visibleIds.forEach((id) => state.selectedOrderIds.delete(id));
    }

    renderOrders();
});

DOM.searchButton.addEventListener("click", applyFilters);
DOM.reloadButton.addEventListener("click", fetchOrders);
DOM.csvDownloadButton.addEventListener("click", downloadSelectedOrdersCsv);

DOM.csvImportInput.addEventListener("change", async (event) => {
    const file = event.target.files?.[0] ?? null;
    await importOrderCsv(file);
});

DOM.keywordInput.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
        applyFilters();
    }
});

DOM.statusFilter.addEventListener("change", applyFilters);

DOM.addOrderButton.addEventListener("click", openOrderCreateModal);
DOM.saveOrderButton.addEventListener("click", saveOrder);

DOM.orderCreateAndEditForm.addEventListener("submit", (event) => {
    event.preventDefault();
    saveOrder();
});

updateCsvDownloadButton();
syncSelectAllCheckbox();
fetchOrders();