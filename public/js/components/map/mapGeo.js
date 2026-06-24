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

export function resolveProjectionRect(georef) {
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

  return latLonToSvgProjected(lat, lon, georef);
}

/** georef bounds 外も含め線形投影する（メッシュ枠などの描画用） */
export function latLonToSvgProjected(lat, lon, georef) {
  const { bounds } = georef;
  const rect = resolveProjectionRect(georef);
  const x = rect.x + ((lon - bounds.west) / (bounds.east - bounds.west)) * rect.width;
  const y = rect.y + ((bounds.north - lat) / (bounds.north - bounds.south)) * rect.height;

  return { x, y };
}

export function parseSvgViewBox(svgText) {
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

export function escapeXml(text) {
  return String(text)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

export function stripSvgIds(markup) {
  if (!markup) {
    return '';
  }

  try {
    const parser = new DOMParser();
    const doc = parser.parseFromString(markup, 'image/svg+xml');
    doc.querySelectorAll('[id]').forEach((element) => {
      element.removeAttribute('id');
    });

    const root = doc.documentElement.firstElementChild;
    return root ? new XMLSerializer().serializeToString(root) : markup.replace(/\s id="[^"]*"/g, '');
  } catch {
    return markup.replace(/\s id="[^"]*"/g, '');
  }
}

export async function fetchOptionalJson(url) {
  try {
    const response = await fetch(url);
    if (!response.ok) {
      return null;
    }
    return await response.json();
  } catch {
    return null;
  }
}
