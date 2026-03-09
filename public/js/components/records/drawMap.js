// js/components/records/drawMap.js
async function drawMapFromJson(data) {
  const response = await fetch('./maps/19_yamanashi.svg');
  const text = await response.text();
  const parser = new DOMParser();
  const svgDoc = parser.parseFromString(text, "image/svg+xml");

  const shapeGroup = svgDoc.getElementById('yamanashi-map-shapes');

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
}
