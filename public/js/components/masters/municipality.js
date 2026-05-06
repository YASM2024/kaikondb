import * as C from "./constants.js";
import { DOM } from "./dom.js";
import {
  getCsrfToken,
  escapeHtml,
  normalizeStatus,
  getStatusLabel,
  escapeCsvValue
} from "./utils.js";

const state = {
  municipalities: [],
  keyword: "",
  statusFilter: "all",
  selectedIds: new Set(),
  editingId: null
};

const municipalityCreateAndEditModal = typeof bootstrap !== "undefined"
  ? new bootstrap.Modal(DOM.municipalityCreateAndEditModalElement)
  : null;

async function postForm(url, payload) {
  const body = new FormData();
  Object.entries(payload).forEach(([key, value]) => {
    body.append(key, value ?? "");
  });

  const response = await fetch(url, {
    method: "POST",
    credentials: "same-origin",
    headers: {
      "X-CSRF-TOKEN": getCsrfToken(),
      Accept: "application/json"
    },
    body
  });

  if (!response.ok) {
    const contentType = response.headers.get("content-type") ?? "";
    if (contentType.includes("application/json")) {
      const data = await response.json().catch(() => null);
      const err = new Error(`HTTP ${response.status}`);
      err.data = data;
      throw err;
    }

    const text = await response.text().catch(() => "");
    throw new Error(`HTTP ${response.status}${text ? `: ${text}` : ""}`);
  }

  return response.json().catch(() => ({}));
}

function showModalError(message) {
  DOM.municipalityEditErrorBox.textContent = message;
  DOM.municipalityEditErrorBox.classList.remove("d-none");
}

function clearModalError() {
  DOM.municipalityEditErrorBox.textContent = "";
  DOM.municipalityEditErrorBox.classList.add("d-none");
}

function formatValidationErrors(errors) {
  if (!errors || typeof errors !== "object") return "";
  const lines = [];
  Object.entries(errors).forEach(([field, msgs]) => {
    const arr = Array.isArray(msgs) ? msgs : [String(msgs)];
    arr.forEach(m => lines.push(`${field}: ${m}`));
  });
  return lines.join("\n");
}

function mapMunicipality(item) {
  return {
    id: String(item.id ?? ""),
    code: String(item.municipality_code ?? ""),
    ja: String(item.municipality_ja ?? ""),
    en: String(item.municipality_en ?? ""),
    status: normalizeStatus(item.status)
  };
}

function getFilteredMunicipalities() {
  const keyword = state.keyword.trim().toLowerCase();

  return state.municipalities.filter(item => {
    const statusMatch =
      state.statusFilter === "all" ||
      String(item.status) === state.statusFilter;

    const keywordMatch =
      keyword === "" ||
      item.code.toLowerCase().includes(keyword) ||
      item.ja.toLowerCase().includes(keyword) ||
      item.en.toLowerCase().includes(keyword);

    return (
      statusMatch && keywordMatch
    );
  });
}

function getVisibleIds() {
  return getFilteredMunicipalities().map(m => m.id);
}

function syncSelectAllCheckbox() {
  const visibleIds = getVisibleIds();

  if (visibleIds.length === 0) {
    DOM.selectAllMunicipalities.checked = false;
    DOM.selectAllMunicipalities.indeterminate = false;
    DOM.selectAllMunicipalities.disabled = true;
    return;
  }

  DOM.selectAllMunicipalities.disabled = false;

  const selectedVisibleCount = visibleIds.filter(id => state.selectedIds.has(id)).length;
  DOM.selectAllMunicipalities.checked = selectedVisibleCount === visibleIds.length;
  DOM.selectAllMunicipalities.indeterminate =
    selectedVisibleCount > 0 && selectedVisibleCount < visibleIds.length;
}

function updateCsvDownloadButton() {
  const selectedCount = state.selectedIds.size;
  DOM.csvDownloadButton.disabled = selectedCount === 0;
  DOM.csvDownloadButton.textContent =
    selectedCount === 0 ? "CSVダウンロード" : `CSVダウンロード（${selectedCount}件）`;
}

function renderMunicipalities() {
  const filtered = getFilteredMunicipalities();
  const inactiveCount = filtered.filter(i => i.status === 0).length;
  DOM.countPill.textContent = `${filtered.length} records / 無効 ${inactiveCount}件`;

  if (filtered.length === 0) {
    DOM.municipalityTableBody.innerHTML = "";
    DOM.tableWrap.classList.add("d-none");
    DOM.emptyBox.classList.remove("d-none");
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
    return;
  }

  DOM.emptyBox.classList.add("d-none");
  DOM.tableWrap.classList.remove("d-none");

  DOM.municipalityTableBody.innerHTML = filtered.map(item => {
    const isChecked = state.selectedIds.has(item.id);
    const isActive = item.status === 1;
    const toggleTitle = isActive ? "無効化" : "再有効化";
    const toggleIcon = isActive ? "bi-slash-circle" : "bi-arrow-clockwise";
    const toggleColor = isActive ? "text-danger" : "text-success";
    return `
      <tr class="${isActive ? "" : "table-secondary"}">
        <td class="px-2 py-2 text-center">
          <input
            type="checkbox"
            class="form-check-input mt-0 rowCheckbox js-selectMunicipality"
            data-municipality-id="${escapeHtml(item.id)}"
            aria-label="市町村を選択"
            ${isChecked ? "checked" : ""}>
        </td>

        <td class="px-3 py-2 text-nowrap">
          <code>${escapeHtml(item.code)}</code>
        </td>

        <td class="px-3 py-2">
          <div class="d-flex flex-column">
            <span class="fw-semibold">${escapeHtml(item.ja)}</span>
            <span class="text-muted small fst-italic">${escapeHtml(item.en)}</span>
          </div>
        </td>

        <td class="px-3 py-2 text-nowrap">
          <span class="badge ${isActive ? "text-bg-success" : "text-bg-secondary"}">
            ${escapeHtml(getStatusLabel(item.status))}
          </span>
        </td>

        <td class="px-3 py-2 text-end">
          <div class="actionIcons">
            <button
              type="button"
              class="actionButton text-secondary js-editMunicipality"
              data-municipality-id="${escapeHtml(item.id)}"
              title="編集"
            >
              <i class="bi bi-pencil"></i>
              <span class="actionLabel">編集</span>
            </button>

            <button
              type="button"
              class="actionButton ${toggleColor} js-toggleMunicipality"
              data-municipality-id="${escapeHtml(item.id)}"
              data-next-status="${isActive ? 0 : 1}"
              title="${toggleTitle}"
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

function bindRowActions() {
  document.querySelectorAll(".js-selectMunicipality").forEach(cb => {
    cb.addEventListener("change", e => {
      const id = e.currentTarget.dataset.municipalityId;
      if (e.currentTarget.checked) state.selectedIds.add(id);
      else state.selectedIds.delete(id);
      syncSelectAllCheckbox();
      updateCsvDownloadButton();
    });
  });

  document.querySelectorAll(".js-editMunicipality").forEach(btn => {
    btn.addEventListener("click", e => {
      const id = e.currentTarget.dataset.municipalityId;
      openEditModal(id);
    });
  });

  document.querySelectorAll(".js-toggleMunicipality").forEach(btn => {
    btn.addEventListener("click", async e => {
      const id = e.currentTarget.dataset.municipalityId;
      const next = e.currentTarget.dataset.nextStatus;
      await updateMunicipalityStatus(id, next);
    });
  });
}

async function fetchMunicipalities() {
  DOM.loadingBox.classList.remove("d-none");
  DOM.errorBox.classList.add("d-none");
  DOM.errorBox.textContent = "";

  try {
    const res = await fetch(C.MUNICIPALITIES_SHOW_URL, {
      headers: { Accept: "application/json" }
    });
    if (!res.ok) {
      const text = await res.text().catch(() => "");
      throw new Error(`HTTP ${res.status}${text ? `: ${text}` : ""}`);
    }

    const data = await res.json();
    state.municipalities = Array.isArray(data) ? data.map(mapMunicipality) : [];
  } catch (e) {
    state.municipalities = [];
    DOM.errorBox.textContent =
      `市町村一覧の取得に失敗しました（${escapeHtml(String(e?.message ?? e))}）`;
    DOM.errorBox.classList.remove("d-none");
  }

  const validIds = new Set(state.municipalities.map(m => m.id));
  state.selectedIds = new Set([...state.selectedIds].filter(id => validIds.has(id)));

  renderMunicipalities();
  DOM.loadingBox.classList.add("d-none");
}

function openEditModal(id) {
  const item = state.municipalities.find(m => m.id === id);
  if (!item) {
    alert("対象の市町村が見つかりません。");
    return;
  }

  if (!municipalityCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  state.editingId = id;
  clearModalError();

  DOM.municipalityCreateAndEditModalLabel.textContent = "市町村の編集";
  DOM.editMunicipalityId.value = item.id;
  DOM.editMunicipalityCode.value = item.code;
  DOM.editMunicipalityJa.value = item.ja;
  DOM.editMunicipalityEn.value = item.en;
  DOM.editMunicipalityStatus.value = String(item.status);

  municipalityCreateAndEditModal.show();
}

async function saveMunicipality() {
  const payload = {
    municipality_code: DOM.editMunicipalityCode.value,
    municipality_ja: DOM.editMunicipalityJa.value,
    municipality_en: DOM.editMunicipalityEn.value,
    status: Number(DOM.editMunicipalityStatus.value)
  };

  const url = state.editingId
    ? `${C.MUNICIPALITY_EDIT_URL}/${encodeURIComponent(state.editingId)}`
    : C.MUNICIPALITY_CREATE_URL;

  clearModalError();

  try {
    await postForm(url, payload);
    await fetchMunicipalities();
    municipalityCreateAndEditModal?.hide();
  } catch (e) {
    const validation = e?.data?.errors ? formatValidationErrors(e.data.errors) : "";
    const fallback = String(e?.message ?? e);
    showModalError(validation || `保存に失敗しました（${fallback}）`);
  }
}

async function updateMunicipalityStatus(id, nextStatus) {
  try {
    await postForm(C.MUNICIPALITY_STATUS_EDIT_URL, {
      id,
      status: Number(nextStatus)
    });
    await fetchMunicipalities();
  } catch (e) {
    const msg = e?.data?.errors ? formatValidationErrors(e.data.errors) : String(e?.message ?? e);
    alert(`ステータス更新に失敗しました。\n${msg}`);
  }
}

function createMunicipalityCsvFileName() {
  const now = new Date();
  const pad = v => String(v).padStart(2, "0");
  return `municipalities_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(
    now.getDate()
  )}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
}

function downloadSelectedMunicipalitiesCsv() {
  const selected = state.municipalities.filter(m => state.selectedIds.has(m.id));
  if (selected.length === 0) return;

  const header = ["municipality_code", "municipality_ja", "municipality_en", "status", "delete_flg"];
  const rows = selected.map(m => [m.code, m.ja, m.en, getStatusLabel(m.status), 0]);
  const csvContent = [header, ...rows].map(r => r.map(escapeCsvValue).join(",")).join("\r\n");

  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = createMunicipalityCsvFileName();
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

/* events */

DOM.searchButton.addEventListener("click", () => {
  state.keyword = DOM.keywordInput.value;
  state.statusFilter = DOM.statusFilter.value;
  renderMunicipalities();
});

DOM.reloadButton.addEventListener("click", fetchMunicipalities);

DOM.addMunicipalityButton.addEventListener("click", () => {
  if (!municipalityCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  state.editingId = null;
  clearModalError();

  DOM.municipalityCreateAndEditModalLabel.textContent = "市町村を追加";
  DOM.editMunicipalityId.value = "";
  DOM.editMunicipalityCode.value = "";
  DOM.editMunicipalityJa.value = "";
  DOM.editMunicipalityEn.value = "";
  DOM.editMunicipalityStatus.value = "1";

  municipalityCreateAndEditModal.show();
});

DOM.saveMunicipalityButton.addEventListener("click", saveMunicipality);

DOM.selectAllMunicipalities.addEventListener("change", e => {
  const visibleIds = getVisibleIds();
  if (e.currentTarget.checked) visibleIds.forEach(id => state.selectedIds.add(id));
  else visibleIds.forEach(id => state.selectedIds.delete(id));
  renderMunicipalities();
});

DOM.csvDownloadButton.addEventListener("click", downloadSelectedMunicipalitiesCsv);

async function importMunicipalityCsvFile(file) {
  if (!file) return;
  const body = new FormData();
  body.append("csv_file", file);

  DOM.errorBox.classList.add("d-none");
  DOM.errorBox.textContent = "";

  try {
    const response = await fetch(C.MUNICIPALITY_IMPORT_URL, {
      method: "POST",
      credentials: "same-origin",
      headers: {
        "X-CSRF-TOKEN": getCsrfToken(),
        Accept: "application/json"
      },
      body
    });

    if (!response.ok) {
      const contentType = response.headers.get("content-type") ?? "";
      if (contentType.includes("application/json")) {
        const data = await response.json().catch(() => null);
        const err = new Error(`HTTP ${response.status}`);
        err.data = data;
        throw err;
      }
      const text = await response.text().catch(() => "");
      throw new Error(`HTTP ${response.status}${text ? `: ${text}` : ""}`);
    }

    const data = await response.json().catch(() => ({}));
    alert(
      `CSV取込が完了しました。\n作成:${data.createdCount ?? 0} 更新:${data.updatedCount ?? 0} 無効化:${data.disabledCount ?? 0}`
    );
    await fetchMunicipalities();
  } catch (e) {
    const msg = e?.data?.errors
      ? formatValidationErrors(e.data.errors)
      : String(e?.message ?? e);
    DOM.errorBox.textContent = `CSV取込に失敗しました。\n${msg}`;
    DOM.errorBox.classList.remove("d-none");
  } finally {
    DOM.csvImportInput.value = "";
  }
}

DOM.csvImportInput.addEventListener("change", async e => {
  const file = e.currentTarget.files?.[0];
  await importMunicipalityCsvFile(file);
});

updateCsvDownloadButton();
syncSelectAllCheckbox();
fetchMunicipalities();

