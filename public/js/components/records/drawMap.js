// js/components/records/drawMap.js
const MAP_CONFIG_ERROR_MESSAGE = 'マップIDが指定されていないか、不正です。';

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

async function drawMapFromJson(data, prefecture) {
  const mapConfig = resolvePrefectureMapConfig(prefecture);
  if (!mapConfig) {
    return mapConfigErrorHtml();
  }

  try {
    const mapStem = mapConfig.map_file_stem;
    const mapsBase = (typeof mapConfig.maps_url === 'string' && mapConfig.maps_url.length > 0)
      ? mapConfig.maps_url
      : './maps';
    const response = await fetch(`${mapsBase}/${mapStem}.svg`);
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

    // 色分けスタイル
    let style = '<style>.map {stroke:black;stroke-width:4;stroke-miterlimit:22.9256;fill:#eeeeee;width:100%;height:100%;}\n';

    const collectionSet = new Set(data.collections || []);
    // 標本（緑）を処理
    (data.collections || []).forEach(dist_code_collection => {
      const codes = dist_code_collection.split(';');
      codes.forEach(code => {
        const trimmed = code.trim();
        if (trimmed) {
          style += `#c${trimmed} { fill:#4db56a; }\n`;
          collectionSet.add(trimmed);
        }
      });
    });
    // 観察（ストライプ）を処理
    (data.observations || []).forEach(dist_code_observation => {
      const codes = dist_code_observation.split(';');
      codes.forEach(code => {
        const trimmed = code.trim();
        if (trimmed && !collectionSet.has(trimmed)) {
          style += `#c${trimmed} { fill: url(#bg-stripe); }\n`;
        }
      });
    });

    style += '</style>';

    // SVG構築
    let svg_source = '';
    svg_source += style;
    svg_source += `<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 1200 1200">`;
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
    svg_source += new XMLSerializer().serializeToString(shapeGroup);

    // 特定コード「不明」表示
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
