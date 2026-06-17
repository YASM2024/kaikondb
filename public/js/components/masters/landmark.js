import * as C from "./constants.js";
import { DOM } from "./dom.js";
import { getLandmarkIcon } from "./landmarkGeo.js";
import { renderLandmarkMapPreview } from "./landmarkMapPreview.js";
import {
  getCsrfToken,
  escapeHtml,
  escapeCsvValue
} from "./utils.js";

const georefBounds = globalThis.landmarkGeorefBounds ?? null;
let mapPreviewTimer = null;

const state = {
  landmarks: [],
  keyword: "",
  selectedIds: new Set(),
  editingId: null
};

const landmarkCreateAndEditModal = typeof bootstrap !== "undefined"
  ? new bootstrap.Modal(DOM.landmarkCreateAndEditModalElement)
  : null;

function getDeleteLandmarkButton() {
  return document.getElementById("deleteLandmarkButton");
}

function syncDeleteButtonVisibility() {
  const btn = getDeleteLandmarkButton();
  if (!btn) return;
  btn.hidden = !state.editingId;
}

function syncCodeFieldState() {
  const isEdit = Boolean(state.editingId);
  DOM.editLandmarkCode.disabled = isEdit;

  const hint = document.getElementById("landmarkCodeHint");
  if (hint) {
    hint.textContent = isEdit ? "" : "半角16文字まで";
    hint.hidden = isEdit;
  }

  const codeFieldWrap = DOM.editLandmarkCode.closest(".landmark-field-code");
  if (codeFieldWrap) {
    if (isEdit) {
      codeFieldWrap.setAttribute("title", "コードは登録後に変更できません。");
    } else {
      codeFieldWrap.removeAttribute("title");
    }
  }
}

function getMapPreviewContainer() {
  return document.getElementById("landmarkMapPreview");
}

function readMapPreviewInputs() {
  const lat = Number(DOM.editLandmarkLat.value);
  const lon = Number(DOM.editLandmarkLon.value);

  return {
    lat: Number.isFinite(lat) ? lat : NaN,
    lon: Number.isFinite(lon) ? lon : NaN,
    pattern: DOM.editLandmarkPattern.value,
    label: DOM.editLandmarkLabel.value.trim()
  };
}

function scheduleMapPreviewUpdate() {
  if (mapPreviewTimer) {
    clearTimeout(mapPreviewTimer);
  }

  mapPreviewTimer = setTimeout(() => {
    mapPreviewTimer = null;
    updateMapPreview();
  }, 150);
}

async function updateMapPreview() {
  const container = getMapPreviewContainer();
  if (!container) return;

  await renderLandmarkMapPreview(container, readMapPreviewInputs());
}

function bindMapPreviewInputs() {
  const fields = [
    DOM.editLandmarkLat,
    DOM.editLandmarkLon,
    DOM.editLandmarkLabel,
    DOM.editLandmarkPattern
  ];

  fields.forEach(field => {
    field?.addEventListener("input", scheduleMapPreviewUpdate);
    field?.addEventListener("change", scheduleMapPreviewUpdate);
  });
}

function applyGeorefInputHints() {
  const hint = DOM.landmarkBoundsHint;
  if (!georefBounds) {
    hint.textContent = "地図表示範囲が未設定のため、緯度・経度は手入力で確認してください。";
    return;
  }

  const { north, south, east, west } = georefBounds;
  DOM.editLandmarkLat.min = String(south);
  DOM.editLandmarkLat.max = String(north);
  DOM.editLandmarkLon.min = String(west);
  DOM.editLandmarkLon.max = String(east);

  hint.textContent =
    `参考範囲 — 緯度 ${south} 〜 ${north} / 経度 ${west} 〜 ${east}（地図プレビューで位置を確認できます）`;
}

function isOutsideGeorefBounds(lat, lon) {
  if (!georefBounds) {
    return false;
  }

  return (
    lat < georefBounds.south ||
    lat > georefBounds.north ||
    lon < georefBounds.west ||
    lon > georefBounds.east
  );
}

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
  DOM.landmarkEditErrorBox.textContent = message;
  DOM.landmarkEditErrorBox.classList.remove("d-none");
}

function clearModalError() {
  DOM.landmarkEditErrorBox.textContent = "";
  DOM.landmarkEditErrorBox.classList.add("d-none");
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

function mapLandmark(item) {
  return {
    id: String(item.id ?? ""),
    code: String(item.code ?? ""),
    label: String(item.label ?? ""),
    lat: Number(item.lat ?? 0),
    lon: Number(item.lon ?? 0),
    pattern: String(item.pattern ?? "mountain"),
    sortOrder: Number(item.sort_order ?? 0)
  };
}

function getFilteredLandmarks() {
  const keyword = state.keyword.trim().toLowerCase();

  return state.landmarks.filter(item => {
    if (keyword === "") {
      return true;
    }

    return (
      item.code.toLowerCase().includes(keyword) ||
      item.label.toLowerCase().includes(keyword)
    );
  });
}

function getFilteredIds() {
  return getFilteredLandmarks().map(item => item.id);
}

function syncSelectAllCheckbox() {
  const filteredIds = getFilteredIds();

  if (filteredIds.length === 0) {
    DOM.selectAllLandmarks.checked = false;
    DOM.selectAllLandmarks.indeterminate = false;
    DOM.selectAllLandmarks.disabled = true;
    return;
  }

  DOM.selectAllLandmarks.disabled = false;

  const selectedCount = filteredIds.filter(id => state.selectedIds.has(id)).length;
  DOM.selectAllLandmarks.checked = selectedCount === filteredIds.length;
  DOM.selectAllLandmarks.indeterminate =
    selectedCount > 0 && selectedCount < filteredIds.length;
}

function updateCsvDownloadButton() {
  const selectedCount = state.selectedIds.size;
  DOM.csvDownloadButton.disabled = selectedCount === 0;
  DOM.csvDownloadButton.textContent =
    selectedCount === 0 ? "CSVダウンロード" : `CSVダウンロード（${selectedCount}件）`;
}

function formatCoordinates(item) {
  const lat = Number.isFinite(item.lat) ? item.lat.toFixed(6) : "—";
  const lon = Number.isFinite(item.lon) ? item.lon.toFixed(6) : "—";
  const outOfBounds = isOutsideGeorefBounds(item.lat, item.lon);
  const warning = outOfBounds
    ? `<div class="text-danger small">範囲外の可能性</div>`
    : "";

  return `
    <div class="small font-monospace">${escapeHtml(lat)}<br>${escapeHtml(lon)}</div>
    ${warning}
  `;
}

function renderLandmarks() {
  const filtered = getFilteredLandmarks();
  DOM.countPill.textContent = `${filtered.length} records`;

  if (filtered.length === 0) {
    DOM.landmarkTableBody.innerHTML = "";
    DOM.tableWrap.classList.add("d-none");
    DOM.emptyBox.classList.remove("d-none");
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
    return;
  }

  DOM.emptyBox.classList.add("d-none");
  DOM.tableWrap.classList.remove("d-none");

  DOM.landmarkTableBody.innerHTML = filtered.map(item => {
    const isChecked = state.selectedIds.has(item.id);
    const patternIcon = getLandmarkIcon(item.pattern);

    return `
      <tr>
        <td class="px-2 py-2 text-center">
          <input
            type="checkbox"
            class="form-check-input mt-0 rowCheckbox js-selectLandmark"
            data-landmark-id="${escapeHtml(item.id)}"
            aria-label="地点を選択"
            ${isChecked ? "checked" : ""}>
        </td>

        <td class="px-3 py-2 text-nowrap">
          <code>${escapeHtml(item.code)}</code>
        </td>

        <td class="px-3 py-2">
          <span class="fw-semibold">${escapeHtml(item.label)}</span>
        </td>

        <td class="px-3 py-2 text-nowrap">
          ${formatCoordinates(item)}
        </td>

        <td class="px-3 py-2 text-nowrap text-center fs-5">
          ${escapeHtml(patternIcon)}
        </td>

        <td class="px-3 py-2 text-nowrap">
          ${escapeHtml(String(item.sortOrder))}
        </td>

        <td class="px-3 py-2 text-end">
          <div class="actionIcons">
            <button
              type="button"
              class="actionButton text-secondary js-editLandmark"
              data-landmark-id="${escapeHtml(item.id)}"
              title="編集"
            >
              <i class="bi bi-pencil"></i>
              <span class="actionLabel">編集</span>
            </button>
            <button
              type="button"
              class="actionButton text-danger js-deleteLandmark"
              data-landmark-id="${escapeHtml(item.id)}"
              title="削除"
            >
              <i class="bi bi-trash"></i>
              <span class="actionLabel">削除</span>
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
  document.querySelectorAll(".js-selectLandmark").forEach(cb => {
    cb.addEventListener("change", e => {
      const id = e.currentTarget.dataset.landmarkId;
      if (e.currentTarget.checked) state.selectedIds.add(id);
      else state.selectedIds.delete(id);
      syncSelectAllCheckbox();
      updateCsvDownloadButton();
    });
  });

  document.querySelectorAll(".js-editLandmark").forEach(btn => {
    btn.addEventListener("click", e => {
      openEditModal(e.currentTarget.dataset.landmarkId);
    });
  });

  document.querySelectorAll(".js-deleteLandmark").forEach(btn => {
    btn.addEventListener("click", e => {
      const id = e.currentTarget.dataset.landmarkId;
      if (!id) return;
      deleteLandmark(id);
    });
  });
}

async function deleteLandmark(id) {
  const item = state.landmarks.find(row => row.id === id);
  const label = item ? `（${item.label}）` : "";
  const ok = window.confirm(`この地点を削除しますか？${label}`);
  if (!ok) return;

  try {
    await postForm(`${C.LANDMARK_DELETE_URL}/${encodeURIComponent(id)}`, {});
    // 削除した行が選択されていれば選択状態もクリア
    state.selectedIds.delete(String(id));
    await fetchLandmarks();
    if (state.editingId === id) {
      state.editingId = null;
      landmarkCreateAndEditModal?.hide();
    }
  } catch (e) {
    alert(`削除に失敗しました（${String(e?.message ?? e)}）`);
  }
}

async function fetchLandmarks() {
  DOM.loadingBox.classList.remove("d-none");
  DOM.errorBox.classList.add("d-none");
  DOM.errorBox.textContent = "";

  try {
    const res = await fetch(C.LANDMARKS_ADMIN_URL, {
      headers: { Accept: "application/json" }
    });
    if (!res.ok) {
      const text = await res.text().catch(() => "");
      throw new Error(`HTTP ${res.status}${text ? `: ${text}` : ""}`);
    }

    const data = await res.json();
    state.landmarks = Array.isArray(data) ? data.map(mapLandmark) : [];
  } catch (e) {
    state.landmarks = [];
    DOM.errorBox.textContent =
      `地点一覧の取得に失敗しました（${escapeHtml(String(e?.message ?? e))}）`;
    DOM.errorBox.classList.remove("d-none");
  }

  const validIds = new Set(state.landmarks.map(item => item.id));
  state.selectedIds = new Set([...state.selectedIds].filter(id => validIds.has(id)));

  renderLandmarks();
  DOM.loadingBox.classList.add("d-none");
}

function openEditModal(id) {
  const item = state.landmarks.find(row => row.id === id);
  if (!item) {
    alert("対象の地点が見つかりません。");
    return;
  }

  if (!landmarkCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  state.editingId = id;
  clearModalError();

  DOM.landmarkCreateAndEditModalLabel.textContent = "地点の編集";
  DOM.editLandmarkId.value = item.id;
  DOM.editLandmarkCode.value = item.code;
  DOM.editLandmarkLabel.value = item.label;
  DOM.editLandmarkLat.value = String(item.lat);
  DOM.editLandmarkLon.value = String(item.lon);
  DOM.editLandmarkPattern.value = item.pattern;
  DOM.editLandmarkSortOrder.value = String(item.sortOrder);

  syncDeleteButtonVisibility();
  syncCodeFieldState();
  landmarkCreateAndEditModal.show();
}

async function saveLandmark() {
  const lat = Number(DOM.editLandmarkLat.value);
  const lon = Number(DOM.editLandmarkLon.value);

  if (isOutsideGeorefBounds(lat, lon)) {
    const ok = window.confirm(
      "入力した緯度・経度が地図の参考表示範囲外です。\nこのまま保存しますか？"
    );
    if (!ok) {
      return;
    }
  }

  const payload = {
    label: DOM.editLandmarkLabel.value.trim(),
    lat: DOM.editLandmarkLat.value,
    lon: DOM.editLandmarkLon.value,
    pattern: DOM.editLandmarkPattern.value,
    sort_order: DOM.editLandmarkSortOrder.value
  };

  if (!state.editingId) {
    payload.code = DOM.editLandmarkCode.value.trim();
  }

  const url = state.editingId
    ? `${C.LANDMARK_EDIT_URL}/${encodeURIComponent(state.editingId)}`
    : C.LANDMARK_CREATE_URL;

  clearModalError();

  try {
    await postForm(url, payload);
    await fetchLandmarks();
    landmarkCreateAndEditModal?.hide();
  } catch (e) {
    const validation = e?.data?.errors ? formatValidationErrors(e.data.errors) : "";
    const fallback = String(e?.message ?? e);
    showModalError(validation || `保存に失敗しました（${fallback}）`);
  }
}

function createLandmarkCsvFileName() {
  const now = new Date();
  const pad = v => String(v).padStart(2, "0");
  return `landmarks_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(
    now.getDate()
  )}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
}

function downloadSelectedLandmarksCsv() {
  const selected = state.landmarks.filter(item => state.selectedIds.has(item.id));
  if (selected.length === 0) return;

  const header = ["code", "label", "lat", "lon", "pattern", "sort_order"];
  const rows = selected.map(item => [
    item.code,
    item.label,
    item.lat,
    item.lon,
    item.pattern,
    item.sortOrder
  ]);
  const csvContent = [header, ...rows].map(r => r.map(escapeCsvValue).join(",")).join("\r\n");

  const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = createLandmarkCsvFileName();
  document.body.appendChild(link);
  link.click();
  link.remove();
  URL.revokeObjectURL(url);
}

DOM.searchButton.addEventListener("click", () => {
  state.keyword = DOM.keywordInput.value;
  renderLandmarks();
});

DOM.reloadButton.addEventListener("click", fetchLandmarks);

DOM.addLandmarkButton.addEventListener("click", () => {
  if (!landmarkCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  state.editingId = null;
  clearModalError();

  DOM.landmarkCreateAndEditModalLabel.textContent = "地点を追加";
  DOM.editLandmarkId.value = "";
  DOM.editLandmarkCode.value = "";
  DOM.editLandmarkLabel.value = "";
  DOM.editLandmarkLat.value = "";
  DOM.editLandmarkLon.value = "";
  DOM.editLandmarkPattern.value = "mountain";
  DOM.editLandmarkSortOrder.value = "";

  syncDeleteButtonVisibility();
  syncCodeFieldState();
  landmarkCreateAndEditModal.show();
});

DOM.saveLandmarkButton.addEventListener("click", saveLandmark);

getDeleteLandmarkButton()?.addEventListener("click", () => {
  if (!state.editingId) return;
  deleteLandmark(state.editingId);
});

DOM.landmarkCreateAndEditModalElement?.addEventListener("shown.bs.modal", () => {
  syncDeleteButtonVisibility();
  syncCodeFieldState();
  updateMapPreview();
});

DOM.selectAllLandmarks.addEventListener("change", e => {
  const filteredIds = getFilteredIds();
  if (e.currentTarget.checked) filteredIds.forEach(id => state.selectedIds.add(id));
  else filteredIds.forEach(id => state.selectedIds.delete(id));
  renderLandmarks();
});

DOM.csvDownloadButton.addEventListener("click", downloadSelectedLandmarksCsv);

DOM.csvImportButton?.addEventListener("click", e => {
  e.preventDefault();
  alert("CSV取込は未対応です。");
});

DOM.csvImportInput?.addEventListener("click", e => {
  e.preventDefault();
});

applyGeorefInputHints();
bindMapPreviewInputs();
updateCsvDownloadButton();
syncSelectAllCheckbox();
fetchLandmarks();
