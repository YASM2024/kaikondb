import {
  buildLandmarkPreviewSvg,
  isValidGeoref,
  latLonToSvg,
  loadMapShapeMarkup,
} from "./landmarkGeo.js";

const previewState = {
  mapAssets: null,
  mapAssetsPromise: null,
};

function getMapConfig() {
  return globalThis.landmarkMapConfig ?? null;
}

function getGeoref() {
  return globalThis.landmarkGeoref ?? null;
}

async function ensureMapAssets() {
  if (previewState.mapAssets) {
    return previewState.mapAssets;
  }

  if (!previewState.mapAssetsPromise) {
    const mapConfig = getMapConfig();
    previewState.mapAssetsPromise = loadMapShapeMarkup(mapConfig).then((assets) => {
      previewState.mapAssets = assets;
      return assets;
    });
  }

  return previewState.mapAssetsPromise;
}

export async function renderLandmarkMapPreview(container, { lat, lon, pattern, label }) {
  if (!container) {
    return;
  }

  const georef = getGeoref();
  if (!isValidGeoref(georef)) {
    container.innerHTML = '<div class="text-body-secondary small p-3">地図データを読み込めません。</div>';
    return;
  }

  const assets = await ensureMapAssets();
  if (!assets?.shapeMarkup) {
    container.innerHTML = '<div class="text-body-secondary small p-3">地図SVGを読み込めません。</div>';
    return;
  }

  if (!Number.isFinite(lat) || !Number.isFinite(lon)) {
    container.innerHTML = `
      <div class="landmark-map-preview-canvas text-body-secondary small p-3">緯度・経度を入力するとプレビューが表示されます。</div>
    `;
    return;
  }

  const svg = buildLandmarkPreviewSvg({
    shapeMarkup: assets.shapeMarkup,
    georef,
    viewWidth: assets.viewWidth,
    viewHeight: assets.viewHeight,
    lat,
    lon,
    pattern,
    label,
  });

  if (!svg) {
    container.innerHTML = '<div class="text-body-secondary small p-3">プレビューを生成できません。</div>';
    return;
  }

  const outOfBounds = latLonToSvg(lat, lon, georef) === null;
  const warning = outOfBounds
    ? '<div class="text-danger small px-2 py-1 border-top">入力座標が地図範囲外のため、アイコンは表示されません。</div>'
    : '';

  container.innerHTML = `
    <div class="landmark-map-preview-canvas">${svg}</div>
    ${warning}
  `;
}

export function resetLandmarkMapPreviewCache() {
  previewState.mapAssets = null;
  previewState.mapAssetsPromise = null;
}
