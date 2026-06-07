import {
  escapeHtml,
  photosBySlot,
  renderAdminPhotoGrid,
  speciesPhotoStorageUrl,
} from './species-photo-render.js';

const adminState = {
  randomKey: null,
  speciesJa: '',
  slots: [null, null, null],
  activeSlot: null,
  searchTimer: null,
};

function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function slotsToPhotoIds() {
  return adminState.slots
    .map((slot) => slot?.id)
    .filter((id) => id != null);
}

function renderAdminPhotoSlots() {
  renderAdminPhotoGrid(adminState.slots, adminState.speciesJa, adminState.activeSlot);
  bindSlotButtons();
}

function bindSlotButtons() {
  document.querySelectorAll('.species-photo-pick-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const index = Number(btn.getAttribute('data-slot-index'));
      openPicker(index);
    });
  });

  document.querySelectorAll('.species-photo-clear-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      const index = Number(btn.getAttribute('data-slot-index'));
      adminState.slots[index] = null;
      renderAdminPhotoSlots();
    });
  });
}

function defaultPickerKeyword() {
  return (adminState.speciesJa ?? '').trim();
}

function openPicker(slotIndex) {
  adminState.activeSlot = slotIndex;
  const picker = document.getElementById('species_photo_picker');
  const label = document.getElementById('species_photo_picker_slot_label');
  if (label) {
    label.textContent = `スロット ${slotIndex + 1} に設定する写真を選んでください`;
  }
  if (picker) {
    picker.classList.remove('d-none');
  }
  const input = document.getElementById('species_photo_picker_keyword');
  const keyword = defaultPickerKeyword();
  if (input) {
    input.value = keyword;
  }
  searchCandidates(keyword, { titleOnly: keyword !== '' });
  renderAdminPhotoSlots();
  input?.focus();
}

function closePicker() {
  adminState.activeSlot = null;
  const picker = document.getElementById('species_photo_picker');
  if (picker) {
    picker.classList.add('d-none');
  }
  const results = document.getElementById('species_photo_picker_results');
  if (results) {
    results.innerHTML = '';
  }
  renderAdminPhotoSlots();
}

async function searchCandidates(keyword, options = {}) {
  const results = document.getElementById('species_photo_picker_results');
  if (!results) {
    return;
  }

  results.innerHTML = '<div class="small text-muted p-2">検索中…</div>';

  try {
    const params = new URLSearchParams({ keyword });
    if (options.titleOnly) {
      params.set('title_only', '1');
    }
    const url = `./species/photos/candidates?${params}`;
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }
    const json = await response.json();
    const items = Array.isArray(json.data) ? json.data : [];

    if (items.length === 0) {
      results.innerHTML = '<div class="small text-muted p-2">該当する承認済み写真がありません。</div>';
      return;
    }

    const selectedIds = new Set(slotsToPhotoIds());

    results.innerHTML = items.map((item) => {
      const thumb = speciesPhotoStorageUrl(item.thumbnail_url || item.url);
      const disabled = selectedIds.has(item.id) ? 'disabled' : '';
      const title = escapeHtml(item.photo_title);
      const place = escapeHtml(item.place);
      return `
        <button type="button" class="list-group-item list-group-item-action species-photo-candidate ${disabled}" data-photo-id="${item.id}" ${disabled}>
          <div class="d-flex align-items-center gap-2">
            <img src="${escapeHtml(thumb)}" alt="" width="64" height="48" class="rounded object-fit-cover flex-shrink-0" style="aspect-ratio: 4/3;">
            <div class="text-start">
              <div class="fw-semibold small">${title}</div>
              <div class="small text-muted">${place}</div>
            </div>
          </div>
        </button>`;
    }).join('');

    results.querySelectorAll('.species-photo-candidate:not([disabled])').forEach((btn) => {
      btn.addEventListener('click', () => {
        const photoId = Number(btn.getAttribute('data-photo-id'));
        const photo = items.find((p) => p.id === photoId);
        if (photo == null || adminState.activeSlot == null) {
          return;
        }

        const duplicateIndex = adminState.slots.findIndex((s) => s?.id === photo.id);
        if (duplicateIndex !== -1 && duplicateIndex !== adminState.activeSlot) {
          adminState.slots[duplicateIndex] = null;
        }

        adminState.slots[adminState.activeSlot] = {
          id: photo.id,
          url: photo.url,
          place: photo.place,
          show_name: null,
        };

        closePicker();
        renderAdminPhotoSlots();
      });
    });
  } catch {
    results.innerHTML = '<div class="small text-danger p-2">写真の取得に失敗しました。</div>';
  }
}

async function savePhotoLinks() {
  const status = document.getElementById('species_photo_admin_status');
  const saveBtn = document.getElementById('species_photo_admin_save');

  if (!adminState.randomKey) {
    return;
  }

  if (saveBtn) {
    saveBtn.disabled = true;
  }
  if (status) {
    status.textContent = '保存中…';
    status.className = 'small text-muted';
  }

  const body = new FormData();
  adminState.slots.forEach((slot, index) => {
    if (slot?.id != null) {
      body.append(`slot_${index + 1}`, String(slot.id));
    }
  });

  try {
    const response = await fetch(`./species/${encodeURIComponent(adminState.randomKey)}/photos`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': getCsrfToken(),
        Accept: 'application/json',
      },
      credentials: 'same-origin',
      body,
    });

    if (!response.ok) {
      let message = '保存に失敗しました。';
      try {
        const err = await response.json();
        if (err.message) {
          message = err.message;
        } else if (err.errors?.slot_1?.[0]) {
          message = err.errors.slot_1[0];
        }
      } catch {
        // ignore
      }
      throw new Error(message);
    }

    const json = await response.json();
    fillSlotsFromPhotos(json.photos ?? []);
    renderAdminPhotoSlots();

    if (status) {
      status.textContent = '保存しました。';
      status.className = 'small text-success';
    }
  } catch (error) {
    if (status) {
      status.textContent = error.message ?? '保存に失敗しました。';
      status.className = 'small text-danger';
    }
  } finally {
    if (saveBtn) {
      saveBtn.disabled = false;
    }
  }
}

function fillSlotsFromPhotos(photos) {
  adminState.slots = photosBySlot(photos);
}

function showAdminPanel() {
  const panel = document.getElementById('species_photo_admin_panel');
  if (panel) {
    panel.classList.remove('d-none');
  }
}

export function resetSpeciesPhotoAdmin() {
  adminState.randomKey = null;
  adminState.speciesJa = '';
  adminState.slots = [null, null, null];
  adminState.activeSlot = null;
  closePicker();

  const panel = document.getElementById('species_photo_admin_panel');
  if (panel) {
    panel.classList.add('d-none');
  }

  const status = document.getElementById('species_photo_admin_status');
  if (status) {
    status.textContent = '';
  }
}

export function handleSpeciesPhotoAdmin(data) {
  if (!window.isAdministrator || !window.photosEnabled || !data?.can_manage_photos) {
    resetSpeciesPhotoAdmin();
    return false;
  }

  adminState.randomKey = data.species.random_key;
  adminState.speciesJa = data.species.species_ja;
  fillSlotsFromPhotos(data.photos);
  renderAdminPhotoSlots();
  showAdminPanel();

  return true;
}

function bindAdminPanelOnce() {
  if (window.__speciesPhotoAdminBound) {
    return;
  }
  window.__speciesPhotoAdminBound = true;

  document.getElementById('species_photo_admin_save')?.addEventListener('click', savePhotoLinks);
  document.getElementById('species_photo_picker_close')?.addEventListener('click', closePicker);

  const keywordInput = document.getElementById('species_photo_picker_keyword');
  keywordInput?.addEventListener('input', () => {
    clearTimeout(adminState.searchTimer);
    adminState.searchTimer = setTimeout(() => {
      searchCandidates(keywordInput.value.trim(), { titleOnly: false });
    }, 300);
  });
}

document.addEventListener('DOMContentLoaded', bindAdminPanelOnce);
