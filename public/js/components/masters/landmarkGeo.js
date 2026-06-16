const LANDMARK_PATTERNS = new Set(['mountain', 'urban']);

export const LANDMARK_ICONS = {
  mountain: '▲',
  urban: '◎',
};

export function resolveLandmarkPattern(pattern) {
  return LANDMARK_PATTERNS.has(pattern) ? pattern : 'mountain';
}

export function getLandmarkIcon(pattern) {
  return LANDMARK_ICONS[resolveLandmarkPattern(pattern)];
}

export function isValidGeoref(data) {
  if (!data || typeof data !== 'object') {
    return false;
  }

  const { bounds, svg } = data;
  if (!bounds || !svg) {
    return false;
  }

  const { north, south, east, west } = bounds;
  if (![north, south, east, west].every((value) => typeof value === 'number' && Number.isFinite(value))) {
    return false;
  }
  if (north <= south || east <= west) {
    return false;
  }
  if (typeof svg.width !== 'number' || typeof svg.height !== 'number') {
    return false;
  }
  if (svg.width <= 0 || svg.height <= 0) {
    return false;
  }

  const { mapRect } = data;
  if (mapRect !== undefined) {
    const { x, y, width, height } = mapRect;
    if (![x, y, width, height].every((value) => typeof value === 'number' && Number.isFinite(value))) {
      return false;
    }
    if (width <= 0 || height <= 0) {
      return false;
    }
  }

  return true;
}

function resolveProjectionRect(georef) {
  const { mapRect, svg } = georef;
  if (
    mapRect
    && typeof mapRect.x === 'number'
    && typeof mapRect.y === 'number'
    && typeof mapRect.width === 'number'
    && typeof mapRect.height === 'number'
    && mapRect.width > 0
    && mapRect.height > 0
  ) {
    return mapRect;
  }

  return { x: 0, y: 0, width: svg.width, height: svg.height };
}

export function latLonToSvg(lat, lon, georef) {
  const { bounds } = georef;
  if (lat < bounds.south || lat > bounds.north || lon < bounds.west || lon > bounds.east) {
    return null;
  }

  const rect = resolveProjectionRect(georef);
  const x = rect.x + ((lon - bounds.west) / (bounds.east - bounds.west)) * rect.width;
  const y = rect.y + ((bounds.north - lat) / (bounds.north - bounds.south)) * rect.height;

  return { x, y };
}

function parseSvgViewBox(svgText) {
  const match = svgText.match(/viewBox="([^"]+)"/);
  if (!match) {
    return null;
  }

  const parts = match[1].trim().split(/\s+/).map(Number);
  if (parts.length !== 4 || parts.some((value) => !Number.isFinite(value))) {
    return null;
  }

  return { width: parts[2], height: parts[3] };
}

export async function loadMapShapeMarkup(mapConfig) {
  if (!mapConfig?.map_file_stem || !mapConfig?.map_shapes_id) {
    return null;
  }

  const mapsBase = (typeof mapConfig.maps_url === 'string' && mapConfig.maps_url.length > 0)
    ? mapConfig.maps_url
    : './maps';

  const response = await fetch(`${mapsBase}/${mapConfig.map_file_stem}.svg`);
  if (!response.ok) {
    return null;
  }

  const text = await response.text();
  const parser = new DOMParser();
  const svgDoc = parser.parseFromString(text, 'image/svg+xml');

  let shapeGroup = svgDoc.getElementById(mapConfig.map_shapes_id);
  if (!shapeGroup) {
    shapeGroup = svgDoc.querySelector('g[id$="-map-shapes"]');
  }
  if (!shapeGroup) {
    return null;
  }

  const parsedViewBox = parseSvgViewBox(text);

  return {
    shapeMarkup: new XMLSerializer().serializeToString(shapeGroup),
    viewWidth: parsedViewBox?.width ?? null,
    viewHeight: parsedViewBox?.height ?? null,
  };
}

export function buildLandmarkPreviewSvg({
  shapeMarkup,
  georef,
  viewWidth,
  viewHeight,
  lat,
  lon,
  pattern,
  label,
}) {
  if (!shapeMarkup || !isValidGeoref(georef)) {
    return null;
  }

  const width = viewWidth ?? georef.svg.width;
  const height = viewHeight ?? georef.svg.height;
  const resolvedPattern = resolveLandmarkPattern(pattern);
  const icon = LANDMARK_ICONS[resolvedPattern];
  const position = latLonToSvg(lat, lon, georef);

  let markerMarkup = '';
  if (position) {
    const x = position.x.toFixed(2);
    const y = position.y.toFixed(2);
    const labelText = String(label ?? '').trim();
    const labelOffsetY = -32;
    markerMarkup = `
      <text class="landmark-preview-marker landmark-preview-marker-${resolvedPattern}" x="${x}" y="${y}" text-anchor="middle" dominant-baseline="central">${icon}</text>
    `;
    if (labelText) {
      markerMarkup += `
        <text class="landmark-preview-label" x="${x}" y="${(position.y + labelOffsetY).toFixed(2)}" text-anchor="middle">${escapeXml(labelText)}</text>
      `;
    }
  }

  return `
    <style>
      .landmark-preview-map { stroke: #333; stroke-width: 2; fill: #eeeeee; }
      .landmark-preview-marker { font-family: sans-serif; fill: #c0392b; stroke: #fff; stroke-width: 3; paint-order: stroke fill; }
      .landmark-preview-marker-mountain { font-size: 28px; }
      .landmark-preview-marker-urban { font-size: 34px; }
      .landmark-preview-label { font-family: sans-serif; font-size: 16px; fill: #333; }
    </style>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
      <g class="landmark-preview-map">${shapeMarkup}</g>
      ${markerMarkup}
    </svg>
  `;
}

function escapeXml(text) {
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
