import * as C from "./constants.js";
import { DOM } from "./dom.js";
import { 
    getCsrfToken, escapeHtml, normalizeStatus, 
    getStatusLabel, escapeCsvValue
} from './utils.js';

const searchParams = new URLSearchParams(window.location.search);
const currentFamilyId = searchParams.get("family_id");

  const state = {
    species: [],
    keyword: "",
    statusFilter: "all",
    selectedSpeciesIds: new Set(),
    editingSpeciesId: null,
    upperTaxa: {
      orderId: "",
      orderLabel: "",
      familyLabel: ""
    },
    orderOptions: [],
    familyOptions: []
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

  const speciesCreateAndEditModal = typeof bootstrap !== "undefined"
    ? new bootstrap.Modal(DOM.speciesCreateAndEditModalElement)
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

  function renderFamilyOptions(selectedValue = "") {
    DOM.editFamilyId.innerHTML = [
      '<option value="">選択してください</option>',
      ...state.familyOptions.map((item) => `
        <option value="${escapeHtml(item.id)}" ${String(item.id) === String(selectedValue) ? "selected" : ""}>
          ${escapeHtml(buildTaxonomyOptionLabel(item.family_ja, item.family))}
        </option>
      `)
    ].join("");
  }

  function mapSpecies(item) {
    return {
      id: String(item.id ?? ""),
      speciesJa: item.species_ja ?? "",
      species: item.species ?? "",
      code: item.code ?? "",
      status: normalizeStatus(item.status),
      orderId: String(item.order_id ?? ""),
      familyId: String(item.family_id ?? "")
    };
  }

  function getFilteredSpecies() {
    const keyword = state.keyword.trim().toLowerCase();

    return state.species.filter((item) => {
      const isStatusMatched =
        state.statusFilter === "all" ||
        String(item.status) === state.statusFilter;

      const isKeywordMatched =
        keyword === "" ||
        String(item.code).toLowerCase().includes(keyword) ||
        String(item.speciesJa).toLowerCase().includes(keyword) ||
        String(item.species).toLowerCase().includes(keyword);

      return isStatusMatched && isKeywordMatched;
    });
  }

  function getVisibleSpeciesIds() {
    return getFilteredSpecies().map((item) => item.id);
  }

  function updateCsvDownloadButton() {
    const selectedCount = state.species.filter((item) => state.selectedSpeciesIds.has(item.id)).length;

    DOM.csvDownloadButton.disabled = selectedCount === 0;
    DOM.csvDownloadButton.textContent =
      selectedCount === 0
        ? "CSVダウンロード"
        : `CSVダウンロード（${selectedCount}件）`;
  }

  function syncSelectAllCheckbox() {
    const visibleIds = getVisibleSpeciesIds();

    if (visibleIds.length === 0) {
      DOM.selectAllSpecies.checked = false;
      DOM.selectAllSpecies.indeterminate = false;
      DOM.selectAllSpecies.disabled = true;
      return;
    }

    DOM.selectAllSpecies.disabled = false;

    const selectedVisibleCount = visibleIds.filter((id) => state.selectedSpeciesIds.has(id)).length;

    DOM.selectAllSpecies.checked = selectedVisibleCount === visibleIds.length;
    DOM.selectAllSpecies.indeterminate =
      selectedVisibleCount > 0 && selectedVisibleCount < visibleIds.length;
  }

  function renderSpecies() {
    const filteredSpecies = getFilteredSpecies();
    const inactiveCount = filteredSpecies.filter((item) => Number(item.status) === 0).length;

    DOM.countPill.textContent = `${filteredSpecies.length} records / 無効 ${inactiveCount}件`;

    if (filteredSpecies.length === 0) {
      DOM.speciesTableBody.innerHTML = "";
      DOM.tableWrap.classList.add("d-none");
      DOM.emptyBox.classList.remove("d-none");
      syncSelectAllCheckbox();
      updateCsvDownloadButton();
      return;
    }

    DOM.emptyBox.classList.add("d-none");
    DOM.tableWrap.classList.remove("d-none");

    DOM.speciesTableBody.innerHTML = filteredSpecies.map((item) => {
      const isActive = Number(item.status) === 1;
      const isChecked = state.selectedSpeciesIds.has(item.id);
      const toggleTitle = isActive ? "無効化" : "再有効化";
      const toggleIcon = isActive ? "bi-slash-circle" : "bi-arrow-clockwise";
      const toggleColorClass = isActive ? "text-danger" : "text-success";

      return `
        <tr class="${isActive ? "" : "table-secondary"}">
          <td class="px-2 py-2 text-center">
            <input
              type="checkbox"
              class="form-check-input mt-0 rowCheckbox js-selectSpecies"
              data-species-id="${escapeHtml(item.id)}"
              aria-label="Speciesを選択"
              ${isChecked ? "checked" : ""}
            >
          </td>

          <td class="px-3 py-2 text-nowrap">
            <code>${escapeHtml(item.code)}</code>
          </td>

          <td class="px-3 py-2">
            <div class="speciesNames d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-1 gap-lg-2">
              <div class="speciesPrimary">
                <code class="inlineCodeMobile">${escapeHtml(item.code)}</code>
                <span class="fw-semibold text-dark lh-sm">
                  ${escapeHtml(item.speciesJa)}
                </span>
              </div>

              <span class="d-none d-lg-inline text-body-tertiary">·</span>

              <span class="text-body-secondary fst-italic small lh-sm speciesLatin">
                ${escapeHtml(item.species)}
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
              <button
                type="button"
                class="actionButton text-secondary js-editSpecies"
                data-species-id="${escapeHtml(item.id)}"
                title="編集"
                aria-label="編集"
              >
                <i class="bi bi-pencil"></i>
                <span class="actionLabel">編集</span>
              </button>

              <button
                type="button"
                class="actionButton ${toggleColorClass} js-toggleSpecies"
                data-species-id="${escapeHtml(item.id)}"
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

  function getSpeciesById(speciesId) {
    return state.species.find((item) => item.id === String(speciesId)) ?? null;
  }

  function resetSpeciesEditError() {
    DOM.speciesEditErrorBox.classList.add("d-none");
    DOM.speciesEditErrorBox.textContent = "";
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

  async function fetchFamilies(orderId = "") {
    const url = orderId
      ? `${C.FAMILY_SHOW_URL}?order_id=${encodeURIComponent(orderId)}`
      : C.FAMILY_SHOW_URL;

    const response = await fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json"
      }
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    state.familyOptions = Array.isArray(data?.data)
      ? data.data
      : Array.isArray(data)
        ? data
        : [];
  }

  async function openSpeciesCreateModal() {
    if (!currentFamilyId) {
      alert("family_id が指定されていません。");
      return;
    }

    if (!speciesCreateAndEditModal) {
      alert("Bootstrap の modal が利用できません。");
      return;
    }

    try {
      if (!state.upperTaxa.orderId) {
        await fetchUpperTaxa();
      }

      state.editingSpeciesId = null;
      resetSpeciesEditError();

      DOM.speciesCreateAndEditModalLabel.textContent = "Speciesを追加";

      DOM.editSpeciesId.value = "";
      DOM.editSpeciesCode.value = "";
      DOM.editSpeciesLatin.value = "";
      DOM.editSpeciesJa.value = "";
      DOM.editSpeciesStatus.value = "1";

      await fetchOrders();
      renderOrderOptions(state.upperTaxa.orderId);

      await fetchFamilies(state.upperTaxa.orderId);
      renderFamilyOptions(currentFamilyId);

      speciesCreateAndEditModal.show();
    } catch (error) {
      alert(`初期データの取得に失敗しました: ${error.message}`);
    }
  }

  async function openSpeciesEditModal(speciesId) {
    const targetSpecies = getSpeciesById(speciesId);

    if (!targetSpecies) {
      alert("対象の Species が見つかりません。");
      return;
    }

    if (!speciesCreateAndEditModal) {
      alert("Bootstrap の modal が利用できません。");
      return;
    }

    try {
      if (!state.upperTaxa.orderId) {
        await fetchUpperTaxa();
      }

      state.editingSpeciesId = targetSpecies.id;
      resetSpeciesEditError();

      DOM.speciesCreateAndEditModalLabel.textContent = "Speciesを編集";

      DOM.editSpeciesId.value = targetSpecies.id;
      DOM.editSpeciesCode.value = targetSpecies.code ?? "";
      DOM.editSpeciesLatin.value = targetSpecies.species ?? "";
      DOM.editSpeciesJa.value = targetSpecies.speciesJa ?? "";
      DOM.editSpeciesStatus.value = String(targetSpecies.status ?? 1);

      await fetchOrders();
      renderOrderOptions(targetSpecies.orderId || state.upperTaxa.orderId || "");

      await fetchFamilies(targetSpecies.orderId || state.upperTaxa.orderId || "");
      renderFamilyOptions(targetSpecies.familyId || currentFamilyId || "");

      speciesCreateAndEditModal.show();
    } catch (error) {
      alert(`初期データの取得に失敗しました: ${error.message}`);
    }
  }

  function bindRowActions() {
    document.querySelectorAll(".js-selectSpecies").forEach((checkbox) => {
      checkbox.addEventListener("change", (event) => {
        const { speciesId } = event.currentTarget.dataset;

        if (event.currentTarget.checked) {
          state.selectedSpeciesIds.add(speciesId);
        } else {
          state.selectedSpeciesIds.delete(speciesId);
        }

        syncSelectAllCheckbox();
        updateCsvDownloadButton();
      });
    });

    document.querySelectorAll(".js-editSpecies").forEach((button) => {
      button.addEventListener("click", (event) => {
        const { speciesId } = event.currentTarget.dataset;
        openSpeciesEditModal(speciesId);
      });
    });

    document.querySelectorAll(".js-toggleSpecies").forEach((button) => {
      button.addEventListener("click", async (event) => {
        const { speciesId, nextStatus } = event.currentTarget.dataset;
        await updateSpeciesStatus(speciesId, nextStatus);
      });
    });
  }

  async function updateSpeciesStatus(speciesId, nextStatus) {
    const targetSpecies = getSpeciesById(speciesId);

    if (!targetSpecies) {
      alert("対象の Species が見つかりません。");
      return;
    }

    try {
      await postForm(C.SPECIES_STATUS_EDIT_URL, {
        id: speciesId,
        status: Number(nextStatus)
      });

      updateSpeciesInState({
        ...targetSpecies,
        status: Number(nextStatus)
      });

      renderSpecies();
    } catch (error) {
      alert(`状態変更に失敗しました: ${error.message}`);
    }
  }

  async function importSpeciesCsv(file) {
    if (!file) {
      return;
    }

    if (!currentFamilyId) {
      alert("family_id が指定されていません。");
      return;
    }

    const body = new FormData();
    body.append("csv_file", file);
    body.append("family_id", currentFamilyId);

    DOM.csvImportButton.disabled = true;

    try {
      const response = await fetch(C.SPECIES_IMPORT_URL, {
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

      await fetchSpecies();

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

    return `species_${now.getFullYear()}${pad(now.getMonth() + 1)}${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.csv`;
  }

  function downloadSelectedSpeciesCsv() {
    const selectedSpecies = state.species.filter((item) => state.selectedSpeciesIds.has(item.id));

    if (selectedSpecies.length === 0) {
      return;
    }

    const header = ["code", "species_ja", "species", "status"];
    const rows = selectedSpecies.map((item) => [
      item.code,
      item.speciesJa,
      item.species,
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

  async function fetchSpecies() {
    DOM.loadingBox.classList.remove("d-none");
    DOM.errorBox.classList.add("d-none");
    DOM.errorBox.textContent = "";
    DOM.emptyBox.classList.add("d-none");
    DOM.tableWrap.classList.add("d-none");

    if (!currentFamilyId) {
      DOM.errorBox.textContent = "family_id が指定されていません。URL に ?family_id=5 のように付与してください。";
      DOM.errorBox.classList.remove("d-none");
      DOM.loadingBox.classList.add("d-none");
      syncSelectAllCheckbox();
      updateCsvDownloadButton();
      return;
    }

    try {
      const response = await fetch(
        `${C.SPECIES_SHOW_URL}?family_id=${encodeURIComponent(currentFamilyId)}`,
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
      state.species = Array.isArray(data) ? data.map(mapSpecies) : [];

      const validIds = new Set(state.species.map((item) => item.id));
      state.selectedSpeciesIds = new Set(
        [...state.selectedSpeciesIds].filter((id) => validIds.has(id))
      );

      renderSpecies();
    } catch (error) {
      DOM.errorBox.textContent = `Species の取得に失敗しました: ${error.message}`;
      DOM.errorBox.classList.remove("d-none");
      syncSelectAllCheckbox();
      updateCsvDownloadButton();
    } finally {
      DOM.loadingBox.classList.add("d-none");
    }
  }

  async function fetchUpperTaxa() {
    if (!currentFamilyId) {
      DOM.currentTaxonomyLabel.textContent =
        "family_id が指定されていません >> 登録済みの Species 一覧";
      return;
    }

    try {
      const response = await fetch(
        `${C.UPPER_TAXA_URL}?family_id=${encodeURIComponent(currentFamilyId)}`,
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
      const familyData = data?.family ?? {};

      const orderJa = orderData.order_ja ?? "";
      const orderEn = orderData.order ?? "";
      const familyJa = familyData.family_ja ?? "";
      const familyEn = familyData.family ?? "";

      state.upperTaxa.orderId = String(orderData.id ?? orderData.order_id ?? "");
      state.upperTaxa.orderLabel = [orderJa, orderEn].filter(Boolean).join("/");
      state.upperTaxa.familyLabel = [familyJa, familyEn].filter(Boolean).join("/");

      DOM.currentTaxonomyLabel.textContent =
        `${state.upperTaxa.orderLabel || "不明"} >> ${state.upperTaxa.familyLabel || "不明"} >> 登録済みの Species 一覧`;
    } catch (error) {
      DOM.currentTaxonomyLabel.textContent =
        "取得失敗 >> 取得失敗 >> 登録済みの Species 一覧";
      console.error("upper taxa の取得に失敗しました:", error);
    }
  }

  function applyFilters() {
    state.keyword = DOM.keywordInput.value;
    state.statusFilter = DOM.statusFilter.value;
    renderSpecies();
  }

  function validateSpeciesEditForm() {
    if (DOM.editSpeciesCode.value.trim() === "") {
      return "code は必須です。";
    }

    if (DOM.editSpeciesLatin.value.trim() === "") {
      return "species は必須です。";
    }

    if (DOM.editSpeciesJa.value.trim() === "") {
      return "species_ja は必須です。";
    }

    if (DOM.editOrderId.value.trim() === "") {
      return "order_id は必須です。";
    }

    if (DOM.editFamilyId.value.trim() === "") {
      return "family_id は必須です。";
    }

    if (!["0", "1"].includes(DOM.editSpeciesStatus.value)) {
      return "status が不正です。";
    }

    return "";
  }

  function addSpeciesToState(newSpecies) {
    state.species = [newSpecies, ...state.species];
  }

  function updateSpeciesInState(updatedSpecies) {
    state.species = state.species.map((item) => {
      if (item.id !== updatedSpecies.id) {
        return item;
      }

      return {
        ...item,
        code: updatedSpecies.code,
        species: updatedSpecies.species,
        speciesJa: updatedSpecies.speciesJa,
        status: normalizeStatus(updatedSpecies.status),
        orderId: String(updatedSpecies.orderId ?? updatedSpecies.order_id ?? item.orderId ?? ""),
        familyId: String(updatedSpecies.familyId ?? updatedSpecies.family_id ?? item.familyId ?? currentFamilyId ?? "")
      };
    });
  }

  async function saveSpecies() {
    const validationMessage = validateSpeciesEditForm();

    if (validationMessage) {
      DOM.speciesEditErrorBox.textContent = validationMessage;
      DOM.speciesEditErrorBox.classList.remove("d-none");
      return;
    }

    if (!currentFamilyId) {
      DOM.speciesEditErrorBox.textContent = "family_id が指定されていません。";
      DOM.speciesEditErrorBox.classList.remove("d-none");
      return;
    }

    resetSpeciesEditError();
    DOM.saveSpeciesButton.disabled = true;

    const isCreateMode = state.editingSpeciesId === null;

    const payload = {
      code: DOM.editSpeciesCode.value.trim(),
      species: DOM.editSpeciesLatin.value.trim(),
      species_ja: DOM.editSpeciesJa.value.trim(),
      order_id: DOM.editOrderId.value.trim(),
      family_id: DOM.editFamilyId.value.trim(),
      status: Number(DOM.editSpeciesStatus.value)
    };

    if (isCreateMode) {
      payload.family_id = DOM.editFamilyId.value.trim();
    } else {
      payload.id = state.editingSpeciesId;
    }

    try {
      const result = await postForm(
        isCreateMode ? C.SPECIES_CREATE_URL : C.SPECIES_EDIT_URL,
        payload
      );

      const serverSpecies = result?.data
        ? mapSpecies(result.data)
        : {
            id: isCreateMode ? String(result?.id ?? "") : String(payload.id),
            code: payload.code,
            species: payload.species,
            speciesJa: payload.species_ja,
            status: payload.status,
            orderId: String(payload.order_id ?? ""),
            familyId: String(payload.family_id ?? "")
          };

      if (isCreateMode) {
        if (serverSpecies.id) {
          addSpeciesToState(serverSpecies);
          renderSpecies();
        } else {
          await fetchSpecies();
        }
      } else {
        updateSpeciesInState(serverSpecies);
        renderSpecies();
      }

      await fetchSpecies();
      speciesCreateAndEditModal?.hide();
    } catch (error) {
      DOM.speciesEditErrorBox.textContent = `保存に失敗しました: ${error.message}`;
      DOM.speciesEditErrorBox.classList.remove("d-none");
    } finally {
      DOM.saveSpeciesButton.disabled = false;
    }
  }

  DOM.selectAllSpecies.addEventListener("change", (event) => {
    const visibleIds = getVisibleSpeciesIds();

    if (event.currentTarget.checked) {
      visibleIds.forEach((id) => state.selectedSpeciesIds.add(id));
    } else {
      visibleIds.forEach((id) => state.selectedSpeciesIds.delete(id));
    }

    renderSpecies();
  });

  DOM.searchButton.addEventListener("click", applyFilters);
  DOM.reloadButton.addEventListener("click", fetchSpecies);
  DOM.csvDownloadButton.addEventListener("click", downloadSelectedSpeciesCsv);

  DOM.keywordInput.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
      applyFilters();
    }
  });

  DOM.statusFilter.addEventListener("change", applyFilters);

  DOM.editOrderId.addEventListener("change", async () => {
    const orderId = DOM.editOrderId.value;

    try {
      await fetchFamilies(orderId);
      renderFamilyOptions("");
      DOM.editFamilyId.value = "";
    } catch (error) {
      DOM.speciesEditErrorBox.textContent = `family 一覧の取得に失敗しました: ${error.message}`;
      DOM.speciesEditErrorBox.classList.remove("d-none");
    }
  });

  DOM.addSpeciesButton.addEventListener("click", openSpeciesCreateModal);
  DOM.saveSpeciesButton.addEventListener("click", saveSpecies);

  DOM.speciesCreateAndEditForm.addEventListener("submit", (event) => {
    event.preventDefault();
    saveSpecies();
  });

  DOM.csvImportButton.addEventListener("click", () => {
    DOM.csvImportInput.click();
  });

  DOM.csvImportInput.addEventListener("change", async (event) => {
    const file = event.target.files?.[0] ?? null;
    await importSpeciesCsv(file);
  });

  updateCsvDownloadButton();
  syncSelectAllCheckbox();
  fetchSpecies();
  fetchUpperTaxa();