const SPECIES_PHOTO_SLOT_COUNT = 3;
const SPECIES_PHOTO_ASPECT_WIDTH = 4;
const SPECIES_PHOTO_ASPECT_HEIGHT = 3;

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

function escapeHtml(text) {
  const el = document.createElement('div');
  el.textContent = text ?? '';
  return el.innerHTML;
}

function speciesPhotoStorageUrl(filename) {
  if (!filename) {
    return typeof window.waitImg === 'string' ? window.waitImg : '';
  }
  const base = (typeof window.homeUrl === 'string' ? window.homeUrl : '').replace(/\/$/, '');
  return `${base}/storage/photos/${encodeURIComponent(filename)}`;
}

function speciesPhotoCaption(photo) {
  const place = (photo?.place ?? '').trim();
  const showName = (photo?.show_name ?? '').trim();
  if (!place && !showName) {
    return '';
  }
  const atPlace = place ? `＠${place}` : '';
  const byLine = showName ? `Photoed By ${showName}` : '';
  return [atPlace, byLine].filter(Boolean).join('　');
}

function slotsToPhotoIds() {
  return adminState.slots
    .map((slot) => slot?.id)
    .filter((id) => id != null);
}

function renderAdminPhotoSlots() {
  const container = document.getElementById('species_photos');
  if (!container) {
    return;
  }

  const html = Array.from({ length: SPECIES_PHOTO_SLOT_COUNT }, (_, index) => {
    const photo = adminState.slots[index];
    const aspectStyle = `aspect-ratio: ${SPECIES_PHOTO_ASPECT_WIDTH} / ${SPECIES_PHOTO_ASPECT_HEIGHT};`;
    const activeClass = adminState.activeSlot === index ? ' species-photo-slot-active' : '';

    if (photo?.url) {
      const src = speciesPhotoStorageUrl(photo.url);
      const caption = speciesPhotoCaption(photo);
      const captionHtml = caption
        ? `<span class="species-photo-caption">${escapeHtml(caption)}</span>`
        : '';

      return `
        <div class="col-12 col-md-4${activeClass}" data-slot-index="${index}">
          <div class="position-relative species-photo-wrap w-100">
            <div class="species-photo-frame" style="${aspectStyle}">
              <img src="${escapeHtml(src)}" alt="${escapeHtml(adminState.speciesJa)}" class="species-photo-img" loading="lazy">
              <div class="species-photo-admin-overlay" aria-hidden="false">
                <button type="button" class="species-photo-admin-btn species-photo-pick-btn" data-slot-index="${index}" title="変更" aria-label="変更">
                  <i class="bi bi-pencil-square" aria-hidden="true"></i>
                </button>
                <button type="button" class="species-photo-admin-btn species-photo-admin-btn-danger species-photo-clear-btn" data-slot-index="${index}" title="リンク解除" aria-label="リンク解除">
                  <i class="bi bi-trash" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            ${captionHtml}
          </div>
        </div>`;
    }

    return `
      <div class="col-12 col-md-4${activeClass}" data-slot-index="${index}">
        <div class="position-relative species-photo-wrap w-100">
          <div class="species-photo-frame species-photo-placeholder" style="${aspectStyle}" role="img" aria-label="写真を選ぶ">
            <div class="species-photo-admin-overlay species-photo-admin-overlay-empty">
              <button type="button" class="species-photo-admin-btn species-photo-pick-btn" data-slot-index="${index}" title="写真を選ぶ" aria-label="写真を選ぶ">
                <i class="bi bi-link-45deg" aria-hidden="true"></i>
              </button>
            </div>
          </div>
        </div>
      </div>`;
  }).join('');

  container.innerHTML = html;
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
  slotsToPhotoIds().forEach((id) => body.append('photo_ids[]', String(id)));

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
        } else if (err.errors?.photo_ids?.[0]) {
          message = err.errors.photo_ids[0];
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
  adminState.slots = [null, null, null];
  (photos ?? []).slice(0, SPECIES_PHOTO_SLOT_COUNT).forEach((photo, index) => {
    adminState.slots[index] = photo;
  });
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
