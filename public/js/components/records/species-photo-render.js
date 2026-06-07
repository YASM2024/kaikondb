export const SPECIES_PHOTO_SLOT_COUNT = 3;
/** 縦：横＝3:4（高さ3・幅4の横長 → aspect-ratio 幅/高さ＝4/3） */
export const SPECIES_PHOTO_ASPECT_WIDTH = 4;
export const SPECIES_PHOTO_ASPECT_HEIGHT = 3;
export const SPECIES_PHOTOS_CAROUSEL_ID = 'species_photos_carousel';

export function escapeHtml(text) {
  const el = document.createElement('div');
  el.textContent = text ?? '';
  return el.innerHTML;
}

/** /photos と同様に public/storage 配下の写真 URL を組み立てる */
export function speciesPhotoStorageUrl(filename) {
  if (!filename) {
    return typeof window.waitImg === 'string' ? window.waitImg : '';
  }
  const base = (typeof window.homeUrl === 'string' ? window.homeUrl : '').replace(/\/$/, '');
  return `${base}/storage/photos/${encodeURIComponent(filename)}`;
}

export function speciesPhotoCaption(photo) {
  const place = (photo?.place ?? '').trim();
  const showName = (photo?.show_name ?? '').trim();
  if (!place && !showName) {
    return '';
  }
  const atPlace = place ? `＠${place}` : '';
  const byLine = showName ? `Photoed By ${showName}` : '';
  return [atPlace, byLine].filter(Boolean).join('　');
}

export function speciesPhotoAspectStyle() {
  return `aspect-ratio: ${SPECIES_PHOTO_ASPECT_WIDTH} / ${SPECIES_PHOTO_ASPECT_HEIGHT};`;
}

export function photosBySlot(photos) {
  const slots = Array.from({ length: SPECIES_PHOTO_SLOT_COUNT }, () => null);
  (photos ?? []).forEach((photo) => {
    const slotIndex = Number(photo.sort_order ?? 0) - 1;
    if (slotIndex >= 0 && slotIndex < SPECIES_PHOTO_SLOT_COUNT) {
      slots[slotIndex] = photo;
      return;
    }
    const firstEmpty = slots.findIndex((slot) => slot == null);
    if (firstEmpty !== -1) {
      slots[firstEmpty] = photo;
    }
  });
  return slots;
}

function captionHtmlFor(photo) {
  const caption = speciesPhotoCaption(photo);
  return caption
    ? `<span class="species-photo-caption">${escapeHtml(caption)}</span>`
    : '';
}

export function buildViewerPhotoInnerHtml(photo, speciesJa) {
  const aspectStyle = speciesPhotoAspectStyle();

  if (photo?.url) {
    const src = speciesPhotoStorageUrl(photo.url);
    return `
      <div class="position-relative species-photo-wrap w-100">
        <div class="species-photo-frame" style="${aspectStyle}">
          <img
            src="${escapeHtml(src)}"
            alt="${escapeHtml(speciesJa)}"
            class="species-photo-img"
            loading="lazy"
          >
        </div>
        ${captionHtmlFor(photo)}
      </div>`;
  }

  return `
    <div class="species-photo-wrap w-100">
      <div
        class="species-photo-frame species-photo-placeholder"
        style="${aspectStyle}"
        role="img"
        aria-label="写真なし"
      >
        <span class="species-photo-placeholder-text">写真なし</span>
      </div>
    </div>`;
}

function buildGridSlotHtml(photo, speciesJa, slotIndex) {
  return `
    <div class="col-md-4" data-slot-index="${slotIndex}">
      ${buildViewerPhotoInnerHtml(photo, speciesJa)}
    </div>`;
}

function buildCarouselItemHtml(photo, speciesJa, slotIndex, isActive) {
  const activeClass = isActive ? ' active' : '';
  return `
    <div class="carousel-item${activeClass}" data-slot-index="${slotIndex}">
      ${buildViewerPhotoInnerHtml(photo, speciesJa)}
    </div>`;
}

function buildCarouselIndicatorsHtml() {
  return Array.from({ length: SPECIES_PHOTO_SLOT_COUNT }, (_, index) => {
    const activeAttrs = index === 0 ? ' class="active" aria-current="true"' : '';
    return `<button
      type="button"
      data-bs-target="#${SPECIES_PHOTOS_CAROUSEL_ID}"
      data-bs-slide-to="${index}"${activeAttrs}
      aria-label="スロット ${index + 1}"
    ></button>`;
  }).join('');
}

function buildCarouselHtml(slots, speciesJa) {
  const items = slots
    .map((photo, index) => buildCarouselItemHtml(photo, speciesJa, index, index === 0))
    .join('');

  return `
    <div
      id="${SPECIES_PHOTOS_CAROUSEL_ID}"
      class="carousel slide species-photos-carousel d-md-none"
      data-bs-interval="false"
      data-bs-touch="true"
      aria-label="種の写真（スワイプで切替）"
    >
      <div class="carousel-indicators">${buildCarouselIndicatorsHtml()}</div>
      <div class="carousel-inner">${items}</div>
      <button
        class="carousel-control-prev"
        type="button"
        data-bs-target="#${SPECIES_PHOTOS_CAROUSEL_ID}"
        data-bs-slide="prev"
      >
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">前の写真</span>
      </button>
      <button
        class="carousel-control-next"
        type="button"
        data-bs-target="#${SPECIES_PHOTOS_CAROUSEL_ID}"
        data-bs-slide="next"
      >
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">次の写真</span>
      </button>
    </div>`;
}

function buildGridHtml(slots, speciesJa) {
  const cols = slots
    .map((photo, index) => buildGridSlotHtml(photo, speciesJa, index))
    .join('');
  return `<div class="row g-2 species-photos-grid d-none d-md-flex">${cols}</div>`;
}

/** 一般閲覧: スマホはカルーセル（初期=スロット1）、md以上は3列グリッド */
export function renderSpeciesPhotoViewer(photos, speciesJa) {
  const container = document.getElementById('species_photos');
  if (!container) {
    return;
  }

  const slots = photosBySlot(Array.isArray(photos) ? photos : []);
  container.innerHTML = buildCarouselHtml(slots, speciesJa) + buildGridHtml(slots, speciesJa);
}

export function buildAdminSlotHtml(photo, speciesJa, slotIndex, activeSlot) {
  const aspectStyle = speciesPhotoAspectStyle();
  const activeClass = activeSlot === slotIndex ? ' species-photo-slot-active' : '';

  if (photo?.url) {
    const src = speciesPhotoStorageUrl(photo.url);
    return `
      <div class="col-12 col-md-4${activeClass}" data-slot-index="${slotIndex}">
        <div class="position-relative species-photo-wrap w-100">
          <div class="species-photo-frame" style="${aspectStyle}">
            <img src="${escapeHtml(src)}" alt="${escapeHtml(speciesJa)}" class="species-photo-img" loading="lazy">
            <div class="species-photo-admin-overlay" aria-hidden="false">
              <button type="button" class="species-photo-admin-btn species-photo-pick-btn" data-slot-index="${slotIndex}" title="変更" aria-label="変更">
                <i class="bi bi-pencil-square" aria-hidden="true"></i>
              </button>
              <button type="button" class="species-photo-admin-btn species-photo-admin-btn-danger species-photo-clear-btn" data-slot-index="${slotIndex}" title="リンク解除" aria-label="リンク解除">
                <i class="bi bi-trash" aria-hidden="true"></i>
              </button>
            </div>
          </div>
          ${captionHtmlFor(photo)}
        </div>
      </div>`;
  }

  return `
    <div class="col-12 col-md-4${activeClass}" data-slot-index="${slotIndex}">
      <div class="position-relative species-photo-wrap w-100">
        <div class="species-photo-frame species-photo-placeholder" style="${aspectStyle}" role="img" aria-label="写真を選ぶ">
          <div class="species-photo-admin-overlay species-photo-admin-overlay-empty">
            <button type="button" class="species-photo-admin-btn species-photo-pick-btn" data-slot-index="${slotIndex}" title="写真を選ぶ" aria-label="写真を選ぶ">
              <i class="bi bi-link-45deg" aria-hidden="true"></i>
            </button>
          </div>
        </div>
      </div>
    </div>`;
}

/** 管理者: 全画面幅で3列グリッド（スマホでも縦並びで編集可能） */
export function renderAdminPhotoGrid(slots, speciesJa, activeSlot) {
  const container = document.getElementById('species_photos');
  if (!container) {
    return;
  }

  const html = Array.from({ length: SPECIES_PHOTO_SLOT_COUNT }, (_, index) =>
    buildAdminSlotHtml(slots[index], speciesJa, index, activeSlot)
  ).join('');

  container.innerHTML = `<div class="row g-2">${html}</div>`;
}
