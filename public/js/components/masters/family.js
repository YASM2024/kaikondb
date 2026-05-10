import * as C from "./constants.js";
import { DOM } from "./dom.js";
import { 
    getCsrfToken, escapeHtml, normalizeStatus, 
    getStatusLabel, escapeCsvValue
} from './utils.js';

const searchParams = new URLSearchParams(window.location.search);
const currentOrderId = searchParams.get("order_id");

const state = {
  families: [],
  keyword: "",
  statusFilter: "all",
  selectedFamilyIds: new Set(),
  editingFamilyId: null,
  upperTaxa: {
    orderId: "",
    orderLabel: ""
  },
  orderOptions: []
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

const familyCreateAndEditModal = typeof bootstrap !== "undefined"
  ? new bootstrap.Modal(DOM.familyCreateAndEditModalElement)
  : null;

function buildTaxonomyOptionLabel(jaName, enName) {
  return [jaName, enName].filter(Boolean).join(" / ");
}

function renderOrderOptions(selectedValue = "") {
  DOM.editOrderId.innerHTML = [
    '<option value="">選択してください</option>',
    ...state.orderOptions.map((item) => `
      <option value="${escapeHtml(item.id)}" ${String(item.id) === String(selectedValue) ? "selected" : ""}>
        ${escapeHtml(buildTaxonomyOptionLabel(item.order_ja, item.order))}
      </option>
    `)
  ].join("");
}

function mapFamily(item) {
  return {
    id: String(item.id ?? ""),
    familyJa: item.family_ja ?? "",
    family: item.family ?? "",
    code: item.code ?? "",
    status: normalizeStatus(item.status),
    orderId: String(item.order_id ?? "")
  };
}

function getFilteredFamilies() {
  const keyword = state.keyword.trim().toLowerCase();

  return state.families.filter((item) => {
    const isStatusMatched =
      state.statusFilter === "all" ||
      String(item.status) === state.statusFilter;

    const isKeywordMatched =
      keyword === "" ||
      String(item.code).toLowerCase().includes(keyword) ||
      String(item.familyJa).toLowerCase().includes(keyword) ||
      String(item.family).toLowerCase().includes(keyword);

    return isStatusMatched && isKeywordMatched;
  });
}

function getVisibleFamilyIds() {
  return getFilteredFamilies().map((item) => item.id);
}

function updateCsvDownloadButton() {
  const selectedCount = state.families.filter((item) => state.selectedFamilyIds.has(item.id)).length;

  DOM.csvDownloadButton.disabled = selectedCount === 0;
  DOM.csvDownloadButton.textContent =
    selectedCount === 0
      ? "CSVダウンロード"
      : `CSVダウンロード（${selectedCount}件）`;
}

function syncSelectAllCheckbox() {
  const visibleIds = getVisibleFamilyIds();

  if (visibleIds.length === 0) {
    DOM.selectAllFamilies.checked = false;
    DOM.selectAllFamilies.indeterminate = false;
    DOM.selectAllFamilies.disabled = true;
    return;
  }

  DOM.selectAllFamilies.disabled = false;

  const selectedVisibleCount = visibleIds.filter((id) => state.selectedFamilyIds.has(id)).length;

  DOM.selectAllFamilies.checked = selectedVisibleCount === visibleIds.length;
  DOM.selectAllFamilies.indeterminate =
    selectedVisibleCount > 0 && selectedVisibleCount < visibleIds.length;
}

function renderFamilies() {
  const filteredFamilies = getFilteredFamilies();
  const inactiveCount = filteredFamilies.filter((item) => Number(item.status) === 0).length;

  DOM.countPill.textContent = `${filteredFamilies.length} records / 無効 ${inactiveCount}件`;

  if (filteredFamilies.length === 0) {
    DOM.familiesTableBody.innerHTML = "";
    DOM.tableWrap.classList.add("d-none");
    DOM.emptyBox.classList.remove("d-none");
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
    return;
  }

  DOM.emptyBox.classList.add("d-none");
  DOM.tableWrap.classList.remove("d-none");

  DOM.familiesTableBody.innerHTML = filteredFamilies.map((item) => {
    const isActive = Number(item.status) === 1;
    const isChecked = state.selectedFamilyIds.has(item.id);
    const toggleTitle = isActive ? "無効化" : "再有効化";
    const toggleIcon = isActive ? "bi-slash-circle" : "bi-arrow-clockwise";
    const toggleColorClass = isActive ? "text-danger" : "text-success";

    return `
      <tr class="${isActive ? "" : "table-secondary"}">
        <td class="px-2 py-2 text-center">
          <input
            type="checkbox"
            class="form-check-input mt-0 rowCheckbox js-selectFamily"
            data-family-id="${escapeHtml(item.id)}"
            aria-label="Familyを選択"
            ${isChecked ? "checked" : ""}
          >
        </td>

        <td class="px-3 py-2 text-nowrap">
          <code>${escapeHtml(item.code)}</code>
        </td>

        <td class="px-3 py-2">
          <div class="familyNames d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-1 gap-lg-2">
            <div class="familyPrimary">
              <code class="inlineCodeMobile">${escapeHtml(item.code)}</code>
              <span class="fw-semibold text-dark lh-sm">
                ${escapeHtml(item.familyJa)}
              </span>
            </div>

            <span class="d-none d-lg-inline text-body-tertiary">·</span>

            <span class="text-body-secondary fst-italic small lh-sm familyLatin">
              ${escapeHtml(item.family)}
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
              href="${C.TAXON_BASE_URL}/species?family_id=${escapeHtml(item.id)}"
              class="actionButton text-primary js-lowerCategory"
              data-family-id="${escapeHtml(item.id)}"
              title="下位分類"
              aria-label="下位分類"
            >
              <i class="bi bi-diagram-3"></i>
              <span class="actionLabel">下位分類</span>
            </a>

            <button
              type="button"
              class="actionButton text-secondary js-editFamily"
              data-family-id="${escapeHtml(item.id)}"
              title="編集"
              aria-label="編集"
            >
              <i class="bi bi-pencil"></i>
              <span class="actionLabel">編集</span>
            </button>

            <button
              type="button"
              class="actionButton ${toggleColorClass} js-toggleFamily"
              data-family-id="${escapeHtml(item.id)}"
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

function getFamilyById(familyId) {
  return state.families.find((item) => item.id === String(familyId)) ?? null;
}

function resetFamilyEditError() {
  DOM.familyEditErrorBox.classList.add("d-none");
  DOM.familyEditErrorBox.textContent = "";
}

async function fetchOrders() {
  const response = await fetch(C.ORDER_SHOW_URL, {
    method: "GET",
    headers: {
      Accept: "application/json"
    }
  });

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}`);
  }

  const data = await response.json();
  state.orderOptions = Array.isArray(data?.data)
    ? data.data
    : Array.isArray(data)
      ? data
      : [];
}

async function openFamilyCreateModal() {
  if (!currentOrderId) {
    alert("order_id が指定されていません。");
    return;
  }

  if (!familyCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  try {
    if (!state.upperTaxa.orderId) {
      await fetchUpperTaxa();
    }

    state.editingFamilyId = null;
    resetFamilyEditError();

    DOM.familyCreateAndEditModalLabel.textContent = "Familyを追加";

    DOM.editFamilyId.value = "";
    DOM.editFamilyCode.value = "";
    DOM.editFamilyLatin.value = "";
    DOM.editFamilyJa.value = "";
    DOM.editFamilyStatus.value = "1";

    await fetchOrders();
    renderOrderOptions(state.upperTaxa.orderId || currentOrderId || "");

    familyCreateAndEditModal.show();
  } catch (error) {
    alert(`初期データの取得に失敗しました: ${error.message}`);
  }
}

async function openFamilyEditModal(familyId) {
  const targetFamily = getFamilyById(familyId);

  if (!targetFamily) {
    alert("対象の Family が見つかりません。");
    return;
  }

  if (!familyCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  try {
    if (!state.upperTaxa.orderId) {
      await fetchUpperTaxa();
    }

    state.editingFamilyId = targetFamily.id;
    resetFamilyEditError();

    DOM.familyCreateAndEditModalLabel.textContent = "Familyを編集";

    DOM.editFamilyId.value = targetFamily.id;
    DOM.editFamilyCode.value = targetFamily.code ?? "";
    DOM.editFamilyLatin.value = targetFamily.family ?? "";
    DOM.editFamilyJa.value = targetFamily.familyJa ?? "";
    DOM.editFamilyStatus.value = String(targetFamily.status ?? 1);

    await fetchOrders();
    renderOrderOptions(targetFamily.orderId || state.upperTaxa.orderId || currentOrderId || "");

    familyCreateAndEditModal.show();
  } catch (error) {
    alert(`初期データの取得に失敗しました: ${error.message}`);
  }
}

function bindRowActions() {
  document.querySelectorAll(".js-selectFamily").forEach((checkbox) => {
    checkbox.addEventListener("change", (event) => {
      const { familyId } = event.currentTarget.dataset;

      if (event.currentTarget.checked) {
        state.selectedFamilyIds.add(familyId);
      } else {
        state.selectedFamilyIds.delete(familyId);
      }

      syncSelectAllCheckbox();
      updateCsvDownloadButton();
    });
  });

  document.querySelectorAll(".js-editFamily").forEach((button) => {
    button.addEventListener("click", (event) => {
      const { familyId } = event.currentTarget.dataset;
      openFamilyEditModal(familyId);
    });
  });

  document.querySelectorAll(".js-toggleFamily").forEach((button) => {
    button.addEventListener("click", async (event) => {
      const { familyId, nextStatus } = event.currentTarget.dataset;
      await updateFamilyStatus(familyId, nextStatus);
    });
  });
}

async function updateFamilyStatus(familyId, nextStatus) {
  const targetFamily = getFamilyById(familyId);

  if (!targetFamily) {
    alert("対象の Family が見つかりません。");
    return;
  }

  try {
    await postForm(C.FAMILY_STATUS_EDIT_URL, {
      id: familyId,
      status: Number(nextStatus)
    });

    updateFamilyInState({
      ...targetFamily,
      status: Number(nextStatus)
    });

    renderFamilies();
  } catch (error) {
    alert(`状態変更に失敗しました: ${error.message}`);
  }
}

async function importFamilyCsv(file) {
  if (!file) {
    return;
  }

  if (!currentOrderId) {
    alert("order_id が指定されていません。");
    return;
  }

  const body = new FormData();
  body.append("csv_file", file);
  body.append("order_id", currentOrderId);

  DOM.csvImportButton.disabled = true;

  try {
    const response = await fetch(C.FAMILY_IMPORT_URL, {
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

    await fetchFamilies();

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
    DOM.csvImportButton.disabled = false;
    DOM.csvImportInput.value = "";
  }
}

function createCsvFileName() {
  const now = new Date();
  const pad = (value) => String(value).padStart(2, "0");

  return `families_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
}

function downloadSelectedFamiliesCsv() {
  const selectedFamilies = state.families.filter((item) => state.selectedFamilyIds.has(item.id));

  if (selectedFamilies.length === 0) {
    return;
  }

  const header = ["code", "family_ja", "family", "status"];
  const rows = selectedFamilies.map((item) => [
    item.code,
    item.familyJa,
    item.family,
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

async function fetchFamilies() {
  DOM.loadingBox.classList.remove("d-none");
  DOM.errorBox.classList.add("d-none");
  DOM.errorBox.textContent = "";
  DOM.emptyBox.classList.add("d-none");
  DOM.tableWrap.classList.add("d-none");

  if (!currentOrderId) {
    DOM.errorBox.textContent = "order_id が指定されていません。URL に ?order_id=5 のように付与してください。";
    DOM.errorBox.classList.remove("d-none");
    DOM.loadingBox.classList.add("d-none");
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
    return;
  }

  try {
    const response = await fetch(
      `${C.FAMILY_SHOW_URL}?order_id=${encodeURIComponent(currentOrderId)}`,
      {
        method: "GET",
        headers: {
          Accept: "application/json"
        }
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    state.families = Array.isArray(data) ? data.map(mapFamily) : [];

    const validIds = new Set(state.families.map((item) => item.id));
    state.selectedFamilyIds = new Set(
      [...state.selectedFamilyIds].filter((id) => validIds.has(id))
    );

    renderFamilies();
  } catch (error) {
    DOM.errorBox.textContent = `Families の取得に失敗しました: ${error.message}`;
    DOM.errorBox.classList.remove("d-none");
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
  } finally {
    DOM.loadingBox.classList.add("d-none");
  }
}

async function fetchUpperTaxa() {
  if (!currentOrderId) {
    DOM.currentOrderLabel.textContent = "order_id が指定されていません >> 登録済みの Families 一覧";
    return;
  }

  try {
    const response = await fetch(
      `${C.UPPER_TAXA_URL}?order_id=${encodeURIComponent(currentOrderId)}`,
      {
        method: "GET",
        headers: {
          Accept: "application/json"
        }
      }
    );

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    const orderData = data?.order ?? {};

    const orderJa = orderData.order_ja ?? "";
    const orderEn = orderData.order ?? "";

    state.upperTaxa.orderId = String(orderData.id ?? orderData.order_id ?? currentOrderId ?? "");
    state.upperTaxa.orderLabel = [orderJa, orderEn].filter(Boolean).join("/");

    DOM.currentOrderLabel.textContent =
      `${state.upperTaxa.orderLabel || "不明"} >> 登録済みの Families 一覧`;
  } catch (error) {
    DOM.currentOrderLabel.textContent = "取得失敗 >> 登録済みの Families 一覧";
    console.error("upper taxa の取得に失敗しました:", error);
  }
}

function applyFilters() {
  state.keyword = DOM.keywordInput.value;
  state.statusFilter = DOM.statusFilter.value;
  renderFamilies();
}

function validateFamilyEditForm() {
  if (DOM.editFamilyCode.value.trim() === "") {
    return "code は必須です。";
  }

  if (DOM.editFamilyLatin.value.trim() === "") {
    return "family は必須です。";
  }

  if (DOM.editFamilyJa.value.trim() === "") {
    return "family_ja は必須です。";
  }

  if (DOM.editOrderId.value.trim() === "") {
    return "order_id は必須です。";
  }

  if (!["0", "1"].includes(DOM.editFamilyStatus.value)) {
    return "status が不正です。";
  }

  return "";
}

function addFamilyToState(newFamily) {
  state.families = [newFamily, ...state.families];
}

function updateFamilyInState(updatedFamily) {
  state.families = state.families.map((item) => {
    if (item.id !== updatedFamily.id) {
      return item;
    }

    return {
      ...item,
      code: updatedFamily.code,
      family: updatedFamily.family,
      familyJa: updatedFamily.familyJa,
      status: normalizeStatus(updatedFamily.status),
      orderId: String(updatedFamily.orderId ?? updatedFamily.order_id ?? item.orderId ?? "")
    };
  });
}

async function saveFamily() {
  const validationMessage = validateFamilyEditForm();

  if (validationMessage) {
    DOM.familyEditErrorBox.textContent = validationMessage;
    DOM.familyEditErrorBox.classList.remove("d-none");
    return;
  }

  if (!currentOrderId) {
    DOM.familyEditErrorBox.textContent = "order_id が指定されていません。";
    DOM.familyEditErrorBox.classList.remove("d-none");
    return;
  }

  resetFamilyEditError();
  DOM.saveFamilyButton.disabled = true;

  const isCreateMode = state.editingFamilyId === null;

  const payload = {
    code: DOM.editFamilyCode.value.trim(),
    family: DOM.editFamilyLatin.value.trim(),
    family_ja: DOM.editFamilyJa.value.trim(),
    order_id: DOM.editOrderId.value.trim(),
    status: Number(DOM.editFamilyStatus.value)
  };

  if (!isCreateMode) {
    payload.id = state.editingFamilyId;
  }

  try {
    const result = await postForm(
      isCreateMode ? C.FAMILY_CREATE_URL : C.FAMILY_EDIT_URL,
      payload
    );

    const serverFamily = result?.data
      ? mapFamily(result.data)
      : {
          id: isCreateMode ? String(result?.id ?? "") : String(payload.id),
          code: payload.code,
          family: payload.family,
          familyJa: payload.family_ja,
          status: payload.status,
          orderId: String(payload.order_id ?? "")
        };

    if (isCreateMode) {
      if (serverFamily.id) {
        addFamilyToState(serverFamily);
        renderFamilies();
      } else {
        await fetchFamilies();
      }
    } else {
      updateFamilyInState(serverFamily);
      renderFamilies();
    }

    await fetchFamilies();
    familyCreateAndEditModal?.hide();
  } catch (error) {
    DOM.familyEditErrorBox.textContent = `保存に失敗しました: ${error.message}`;
    DOM.familyEditErrorBox.classList.remove("d-none");
  } finally {
    DOM.saveFamilyButton.disabled = false;
  }
}

DOM.selectAllFamilies.addEventListener("change", (event) => {
  const visibleIds = getVisibleFamilyIds();

  if (event.currentTarget.checked) {
    visibleIds.forEach((id) => state.selectedFamilyIds.add(id));
  } else {
    visibleIds.forEach((id) => state.selectedFamilyIds.delete(id));
  }

  renderFamilies();
});

DOM.searchButton.addEventListener("click", applyFilters);
DOM.reloadButton.addEventListener("click", fetchFamilies);
DOM.csvDownloadButton.addEventListener("click", downloadSelectedFamiliesCsv);

DOM.csvImportButton.addEventListener("click", () => {
  DOM.csvImportInput.click();
});

DOM.csvImportInput.addEventListener("change", async (event) => {
  const file = event.target.files?.[0] ?? null;
  await importFamilyCsv(file);
});

DOM.keywordInput.addEventListener("keydown", (event) => {
  if (event.key === "Enter") {
    applyFilters();
  }
});

DOM.statusFilter.addEventListener("change", applyFilters);

DOM.addFamilyButton.addEventListener("click", openFamilyCreateModal);
DOM.saveFamilyButton.addEventListener("click", saveFamily);

DOM.familyCreateAndEditForm.addEventListener("submit", (event) => {
  event.preventDefault();
  saveFamily();
});

updateCsvDownloadButton();
syncSelectAllCheckbox();
fetchUpperTaxa();
fetchFamilies();