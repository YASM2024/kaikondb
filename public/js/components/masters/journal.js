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
  journals: [],
  keyword: "",
  statusFilter: "all",
  selectedJournalIds: new Set(),
  editingJournalId: null
};

const journalCreateAndEditModal = typeof bootstrap !== "undefined"
  ? new bootstrap.Modal(DOM.journalCreateAndEditModalElement)
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
    throw new Error(`HTTP ${response.status}`);
  }

  return response.json();
}

function mapJournal(item) {
  return {
    id: String(item.id ?? ""),
    code: String(item.journal_code ?? ""),
    nameJa: item.journal_name_ja ?? "",
    nameEn: item.journal_name_en ?? "",
    category: Number(item.category ?? 0),
    publisher: String(item.publisher ?? ""),
    url: String(item.url ?? ""),
    providedBy: String(item.provided_by ?? ""),
    status: normalizeStatus(item.status)
  };
}

function escapeAttr(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/"/g, "&quot;")
    .replace(/</g, "&lt;");
}

function formatJournalUrlCell(url) {
  const u = String(url ?? "").trim();
  if (!u) {
    return `<span class="text-body-tertiary">—</span>`;
  }

  const safe = escapeHtml(u);

  if (/^https?:\/\//i.test(u)) {
    return `<a href="${escapeAttr(u)}" target="_blank" rel="noopener noreferrer" class="text-truncate d-inline-block w-100" title="${escapeAttr(u)}">${safe}</a>`;
  }

  return `<span class="text-truncate d-inline-block w-100" title="${escapeAttr(u)}">${safe}</span>`;
}

function formatJournalUrlProvidedColumn(item) {
  const urlLine = formatJournalUrlCell(item.url);
  const provLine = item.providedBy
    ? `<span class="d-inline-block text-truncate w-100" title="${escapeAttr(item.providedBy)}">${escapeHtml(item.providedBy)}</span>`
    : `<span class="text-body-tertiary">—</span>`;

  return `
    <div class="d-flex flex-column gap-1 small journalUrlProvidedCol" style="max-width: 12rem">
      <div style="min-width:0">${urlLine}</div>
      <div class="text-body-secondary" style="min-width:0">${provLine}</div>
    </div>
  `;
}

function getFilteredJournals() {
  const keyword = state.keyword.trim().toLowerCase();

  return state.journals.filter(item => {
    const statusMatch =
      state.statusFilter === "all" ||
      String(item.status) === state.statusFilter;

    const keywordMatch =
      keyword === "" ||
      item.code.toLowerCase().includes(keyword) ||
      item.nameJa.toLowerCase().includes(keyword) ||
      item.nameEn.toLowerCase().includes(keyword) ||
      String(item.category).includes(keyword) ||
      item.publisher.toLowerCase().includes(keyword) ||
      item.url.toLowerCase().includes(keyword) ||
      item.providedBy.toLowerCase().includes(keyword);

    return statusMatch && keywordMatch;
  });
}

function getVisibleJournalIds() {
  return getFilteredJournals().map(item => item.id);
}

function updateCsvDownloadButton() {
  const selectedCount = state.journals.filter(item =>
    state.selectedJournalIds.has(item.id)
  ).length;

  DOM.csvDownloadButton.disabled = selectedCount === 0;
  DOM.csvDownloadButton.textContent =
    selectedCount === 0
      ? "CSVダウンロード"
      : `CSVダウンロード（${selectedCount}件）`;
}

function syncSelectAllCheckbox() {
  const visibleIds = getVisibleJournalIds();

  if (visibleIds.length === 0) {
    DOM.selectAllJournals.checked = false;
    DOM.selectAllJournals.indeterminate = false;
    DOM.selectAllJournals.disabled = true;
    return;
  }

  DOM.selectAllJournals.disabled = false;

  const selectedVisibleCount = visibleIds.filter(id =>
    state.selectedJournalIds.has(id)
  ).length;

  DOM.selectAllJournals.checked =
    selectedVisibleCount === visibleIds.length;
  DOM.selectAllJournals.indeterminate =
    selectedVisibleCount > 0 &&
    selectedVisibleCount < visibleIds.length;
}

function renderJournals() {
  const filtered = getFilteredJournals();
  const inactiveCount = filtered.filter(i => i.status === 0).length;

  DOM.countPill.textContent =
    `${filtered.length} records / 無効 ${inactiveCount}件`;

  if (filtered.length === 0) {
    DOM.journalTableBody.innerHTML = "";
    DOM.tableWrap.classList.add("d-none");
    DOM.emptyBox.classList.remove("d-none");
    syncSelectAllCheckbox();
    updateCsvDownloadButton();
    return;
  }

  DOM.emptyBox.classList.add("d-none");
  DOM.tableWrap.classList.remove("d-none");

  DOM.journalTableBody.innerHTML = filtered.map(item => {
    const isActive = item.status === 1;
    const isChecked = state.selectedJournalIds.has(item.id);

    const toggleTitle = isActive ? "無効化" : "再有効化";
    const toggleIcon = isActive ? "bi-slash-circle" : "bi-arrow-clockwise";
    const toggleColor = isActive ? "text-danger" : "text-success";

    return `
      <tr class="${isActive ? "" : "table-secondary"}">
        <td class="px-2 py-2 text-center">
          <input type="checkbox"
            class="form-check-input mt-0 rowCheckbox js-selectJournal"
            data-journal-id="${escapeHtml(item.id)}"
            aria-label="雑誌を選択"
            ${isChecked ? "checked" : ""}>
        </td>

        <td class="px-3 py-2 text-nowrap">
          <code>${escapeHtml(item.code)}</code>
        </td>

        <td class="px-3 py-2">
          <div class="d-flex flex-column">
            <span class="fw-semibold">
              ${escapeHtml(item.nameJa)}${item.publisher ? `（${escapeHtml(item.publisher)}）` : ""}
            </span>
            <span class="text-muted small fst-italic">
              ${escapeHtml(item.nameEn)}
            </span>
          </div>
        </td>

        <td class="px-3 py-2 small journal-col-urlprov">
          ${formatJournalUrlProvidedColumn(item)}
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
              class="actionButton text-secondary js-editJournal"
              data-journal-id="${escapeHtml(item.id)}"
              title="編集"
            >
              <i class="bi bi-pencil"></i>
              <span class="actionLabel">編集</span>
            </button>

            <button
              type="button"
              class="actionButton ${toggleColor} js-toggleJournal"
              data-journal-id="${escapeHtml(item.id)}"
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

  document.querySelectorAll(".js-selectJournal").forEach(cb => {
    cb.addEventListener("change", e => {
      const id = e.currentTarget.dataset.journalId;
      if (e.currentTarget.checked) {
        state.selectedJournalIds.add(id);
      } else {
        state.selectedJournalIds.delete(id);
      }
      syncSelectAllCheckbox();
      updateCsvDownloadButton();
    });
  });

  document.querySelectorAll(".js-editJournal").forEach(btn => {
    btn.addEventListener("click", e => {
      const id = e.currentTarget.dataset.journalId;
      openEditModal(id);
    });
  });

  document.querySelectorAll(".js-toggleJournal").forEach(btn => {
    btn.addEventListener("click", async e => {
      const id = e.currentTarget.dataset.journalId;
      const next = e.currentTarget.dataset.nextStatus;
      await updateJournalStatus(id, next);
    });
  });
}

async function updateJournalStatus(id, nextStatus) {
  await postForm(C.JOURNAL_STATUS_EDIT_URL, {
    id,
    status: Number(nextStatus)
  });

  await fetchJournals();
}

function openEditModal(id) {
  const journal = state.journals.find(j => j.id === id);
  if (!journal) {
    alert("対象の Journal が見つかりません。");
    return;
  }

  if (!journalCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  state.editingJournalId = id;

  DOM.journalCreateAndEditModalLabel.textContent = "雑誌の編集";

  DOM.editJournalId.value = journal.id;
  DOM.editJournalCode.value = journal.code;
  DOM.editJournalEn.value = journal.nameEn;
  DOM.editJournalJa.value = journal.nameJa;
  DOM.editJournalCategory.value = String(journal.category);
  DOM.editJournalPublisher.value = journal.publisher;
  DOM.editJournalUrl.value = journal.url;
  DOM.editJournalProvidedBy.value = journal.providedBy;
  DOM.editJournalStatus.value = journal.status;

  journalCreateAndEditModal.show();
}

async function fetchJournals() {
  DOM.loadingBox.classList.remove("d-none");

  const res = await fetch(C.JOURNALS_SHOW_URL, {
    headers: { Accept: "application/json" }
  });

  const data = await res.json();
  state.journals = Array.isArray(data)
    ? data.map(mapJournal)
    : [];

  const validIds = new Set(state.journals.map(j => j.id));
  state.selectedJournalIds = new Set(
    [...state.selectedJournalIds].filter(id => validIds.has(id))
  );

  renderJournals();
  DOM.loadingBox.classList.add("d-none");
}

/* events */

DOM.searchButton.addEventListener("click", () => {
  state.keyword = DOM.keywordInput.value;
  state.statusFilter = DOM.statusFilter.value;
  renderJournals();
});

DOM.reloadButton.addEventListener("click", fetchJournals);

DOM.addJournalButton.addEventListener("click", () => {
  if (!journalCreateAndEditModal) {
    alert("Bootstrap の modal が利用できません。");
    return;
  }

  state.editingJournalId = null;

  DOM.journalCreateAndEditModalLabel.textContent = "雑誌を追加";

  DOM.editJournalId.value = "";
  DOM.editJournalCode.value = "";
  DOM.editJournalEn.value = "";
  DOM.editJournalJa.value = "";
  DOM.editJournalCategory.value = "0";
  DOM.editJournalPublisher.value = "";
  DOM.editJournalUrl.value = "";
  DOM.editJournalProvidedBy.value = "";
  DOM.editJournalStatus.value = "1";

  journalCreateAndEditModal.show();
});

DOM.saveJournalButton.addEventListener("click", async () => {
  const categoryNum = Number(DOM.editJournalCategory.value);

  const payload = {
    journal_code: DOM.editJournalCode.value,
    journal_name_en: DOM.editJournalEn.value,
    journal_name_ja: DOM.editJournalJa.value,
    category: Number.isFinite(categoryNum) ? categoryNum : 0,
    publisher: DOM.editJournalPublisher.value,
    url: DOM.editJournalUrl.value,
    provided_by: DOM.editJournalProvidedBy.value,
    status: Number(DOM.editJournalStatus.value)
  };

  const url = state.editingJournalId
    ? `${C.JOURNAL_EDIT_URL}/${encodeURIComponent(state.editingJournalId)}`
    : C.JOURNAL_CREATE_URL;

  await postForm(url, payload);

  await fetchJournals();

  journalCreateAndEditModal?.hide();
});

DOM.selectAllJournals.addEventListener("change", e => {
  const visibleIds = getVisibleJournalIds();

  if (e.currentTarget.checked) {
    visibleIds.forEach(id => state.selectedJournalIds.add(id));
  } else {
    visibleIds.forEach(id => state.selectedJournalIds.delete(id));
  }

  renderJournals();
});

function createJournalCsvFileName() {
  const now = new Date();
  const pad = value => String(value).padStart(2, "0");

  return `journals_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(
    now.getDate()
  )}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(
    now.getSeconds()
  )}.csv`;
}

function downloadSelectedJournalsCsv() {
  const selected = state.journals.filter(item =>
    state.selectedJournalIds.has(item.id)
  );

  if (selected.length === 0) {
    return;
  }

  const header = [
    "journal_code",
    "journal_name_ja",
    "journal_name_en",
    "category",
    "publisher",
    "url",
    "provided_by",
    "status"
  ];
  const rows = selected.map(item => [
    item.code,
    item.nameJa,
    item.nameEn,
    item.category,
    item.publisher,
    item.url,
    item.providedBy,
    getStatusLabel(item.status)
  ]);

  const csvContent = [header, ...rows]
    .map(row => row.map(escapeCsvValue).join(","))
    .join("\r\n");

  const blob = new Blob([csvContent], {
    type: "text/csv;charset=utf-8;"
  });

  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");

  link.href = url;
  link.download = createJournalCsvFileName();
  document.body.appendChild(link);
  link.click();
  link.remove();

  URL.revokeObjectURL(url);
}

DOM.csvDownloadButton.addEventListener("click", downloadSelectedJournalsCsv);

updateCsvDownloadButton();
syncSelectAllCheckbox();
fetchJournals();