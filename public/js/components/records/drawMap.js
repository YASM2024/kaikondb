// js/components/records/drawMap.js
import {
  fetchOptionalJson,
  isValidGeoref,
  parseSvgViewBox,
  escapeXml,
  latLonToSvg,
} from '../map/mapGeo.js';
import {
  MESH3_STYLE_RULES,
  buildMesh3Layer,
  buildMesh3Styles,
  fetchMesh3Data,
} from '../map/meshGrid.js';

const MAP_CONFIG_ERROR_MESSAGE = 'マップIDが指定されていないか、不正です。';
const LANDMARK_PATTERNS = new Set(['mountain', 'urban']);
const LANDMARK_MARKER_SYMBOLS = {
  mountain: '▲',
  urban: '◎',
};

function mapConfigErrorHtml() {
  return `<p class="text-danger mb-0">${MAP_CONFIG_ERROR_MESSAGE}</p>`;
}

function isValidMapConfig(config) {
  return Boolean(
    config
    && typeof config === 'object'
    && typeof config.map_file_stem === 'string'
    && config.map_file_stem.length > 0
    && typeof config.map_shapes_id === 'string'
    && config.map_shapes_id.length > 0
  );
}

function resolvePrefectureMapConfig(prefecture) {
  const candidate = (prefecture && typeof prefecture === 'object')
    ? prefecture
    : (typeof window !== 'undefined' ? window.kaikonPrefectureMap : null);

  return isValidMapConfig(candidate) ? candidate : null;
}

function isValidLandmarks(data) {
  if (!data || !Array.isArray(data.points) || data.points.length === 0) {
    return false;
  }

  return data.points.every((point) => Boolean(
    point
    && typeof point.id === 'string'
    && point.id.length > 0
    && typeof point.label === 'string'
    && typeof point.lat === 'number'
    && Number.isFinite(point.lat)
    && typeof point.lon === 'number'
    && Number.isFinite(point.lon)
    && typeof point.pattern === 'string'
    && LANDMARK_PATTERNS.has(point.pattern)
  ));
}

function resolveLandmarkPattern(point) {
  return LANDMARK_PATTERNS.has(point.pattern) ? point.pattern : 'mountain';
}

function buildLandmarkMarker(point, id, x, y) {
  const pattern = resolveLandmarkPattern(point);
  const symbol = LANDMARK_MARKER_SYMBOLS[pattern];

  return `<text class="landmark-marker landmark-marker-${pattern}" data-landmark-id="${id}" data-landmark-pattern="${pattern}" x="${x}" y="${y}" text-anchor="middle" dominant-baseline="central">${symbol}</text>`;
}

function buildLandmarksLayer(landmarks, georef) {
  const labelOffsetY = -32;
  const parts = ['<g id="landmarks-layer">'];

  landmarks.points.forEach((point) => {
    const position = latLonToSvg(point.lat, point.lon, georef);
    if (!position) {
      return;
    }

    const id = escapeXml(point.id);
    const label = escapeXml(point.label);
    const pattern = resolveLandmarkPattern(point);
    const x = position.x.toFixed(2);
    const y = position.y.toFixed(2);

    parts.push(`<g class="landmark" data-landmark-id="${id}" data-landmark-pattern="${pattern}">`);
    parts.push(buildLandmarkMarker(point, id, x, y));
    parts.push(
      `<text class="landmark-label" data-landmark-id="${id}" x="${x}" y="${(position.y + labelOffsetY).toFixed(2)}" text-anchor="middle">${label}</text>`,
    );
    parts.push('</g>');
  });

  parts.push('</g>');
  return parts.join('');
}

export async function drawMapFromJson(data, prefecture) {
  const mapConfig = resolvePrefectureMapConfig(prefecture);
  if (!mapConfig) {
    return mapConfigErrorHtml();
  }

  try {
    const mapStem = mapConfig.map_file_stem;
    const mapsBase = (typeof mapConfig.maps_url === 'string' && mapConfig.maps_url.length > 0)
      ? mapConfig.maps_url
      : './maps';

    const landmarksUrl = (typeof mapConfig.landmarks_url === 'string' && mapConfig.landmarks_url.length > 0)
      ? mapConfig.landmarks_url
      : null;

    const [response, georef, landmarks, mesh3] = await Promise.all([
      fetch(`${mapsBase}/${mapStem}.svg`),
      fetchOptionalJson(`${mapsBase}/${mapStem}_georef.json`),
      landmarksUrl ? fetchOptionalJson(landmarksUrl) : Promise.resolve(null),
      fetchMesh3Data(mapsBase, mapStem),
    ]);

    if (!response.ok) {
      return mapConfigErrorHtml();
    }

    const text = await response.text();
    const parser = new DOMParser();
    const svgDoc = parser.parseFromString(text, 'image/svg+xml');

    let shapeGroup = svgDoc.getElementById(mapConfig.map_shapes_id);
    if (!shapeGroup) {
      shapeGroup = svgDoc.querySelector('g[id$="-map-shapes"]');
    }
    if (!shapeGroup) {
      return mapConfigErrorHtml();
    }

    const shapeMarkup = new XMLSerializer().serializeToString(shapeGroup);
    const parsedViewBox = parseSvgViewBox(text);
    const viewWidth = parsedViewBox?.width ?? georef?.svg?.width ?? 1200;
    const viewHeight = parsedViewBox?.height ?? georef?.svg?.height ?? 1200;
    let style = '<style>.map {stroke:#333;stroke-width:2;stroke-miterlimit:22.9256;fill:#eeeeee;width:100%;height:100%;}\n';
    style += '.landmark-marker {font-family:sans-serif;fill:#c0392b;stroke:#fff;stroke-width:4;paint-order:stroke fill;pointer-events:none;}\n';
    style += '.landmark-marker-mountain {font-size:36px;}\n';
    style += '.landmark-marker-urban {font-size:44px;}\n';
    style += '.landmark-label {font-family:sans-serif;font-size:22px;fill:#333;pointer-events:none;}\n';
    style += `${MESH3_STYLE_RULES}\n`;

    const collectionSet = new Set(data.collections || []);
    (data.collections || []).forEach((dist_code_collection) => {
      const codes = dist_code_collection.split(';');
      codes.forEach((code) => {
        const trimmed = code.trim();
        if (trimmed) {
          style += `#c${trimmed} { fill:#4db56a; }\n`;
          collectionSet.add(trimmed);
        }
      });
    });
    (data.observations || []).forEach((dist_code_observation) => {
      const codes = dist_code_observation.split(';');
      codes.forEach((code) => {
        const trimmed = code.trim();
        if (trimmed && !collectionSet.has(trimmed)) {
          style += `#c${trimmed} { fill: url(#bg-stripe); }\n`;
        }
      });
    });
    style += buildMesh3Styles(data);
    style += '</style>';

    let svg_source = '';
    svg_source += style;
    svg_source += `<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 ${viewWidth} ${viewHeight}">`;
    svg_source += `
    <defs>
      <pattern id="bg-stripe" patternUnits="userSpaceOnUse" width="40" height="40">
        <rect width="40" height="40" fill="#aaeeaa" />
        <line x1="0" y1="40" x2="40" y2="0" stroke="#eeeeee" stroke-width="12" />
        <polygon points="0,0 0,8 8,0" fill="#eeeeee" />
        <polygon points="40,40 40,32 32,40" fill="#eeeeee" />
      </pattern>
    </defs>
  `;
    svg_source += shapeMarkup;

    if (mesh3 && isValidGeoref(georef)) {
      svg_source += buildMesh3Layer(georef);
    }

    if (isValidGeoref(georef) && isValidLandmarks(landmarks)) {
      svg_source += buildLandmarksLayer(landmarks, georef);
    }

    const allCodes = [...(data.observations || []), ...(data.collections || [])];
    if (allCodes.includes('199900')) {
      svg_source += '<circle class="map" id="c199900" cx="11000" cy="18080" r="600" />';
      svg_source += '<text class="map" id="c199900" x="12000" y="18500" font-family="gothic" font-weight="bold" font-size="1200">不明</text>';
    }

    svg_source += '</svg>';
    return svg_source;
  } catch {
    return mapConfigErrorHtml();
  }
}
