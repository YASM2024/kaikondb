import {

  escapeXml,

  fetchOptionalJson,

  latLonToSvgProjected,

} from './mapGeo.js';



/** JIS X 0410: 1次 40分×1度、2次 8×8、3次 10×10 */

const LAT_UNIT_1 = 2 / 3;

const LON_UNIT_1 = 1;

const LAT_UNIT_2 = LAT_UNIT_1 / 8;

const LON_UNIT_2 = LON_UNIT_1 / 8;

const LAT_UNIT_3 = LAT_UNIT_2 / 10;

const LON_UNIT_3 = LON_UNIT_2 / 10;



export const MESH3_STYLE_RULES = [

  '.mesh-3-cell { fill: transparent; stroke: #7a8a99; stroke-width: 0.125; vector-effect: non-scaling-stroke; pointer-events: none; }',

  '.mesh-3-highlight { fill: rgba(77, 181, 106, 0.28); stroke: #4db56a; stroke-width: 0.2; }',

].join('\n');



export function mesh3Url(mapsBase, mapStem) {

  return `${mapsBase}/${mapStem}_mesh3.json`;

}



export async function fetchMesh3Data(mapsBase, mapStem) {

  const data = await fetchOptionalJson(mesh3Url(mapsBase, mapStem));

  if (!data || data.order !== 3 || !Array.isArray(data.cells) || data.cells.length === 0) {

    return null;

  }



  return data;

}



export function latLonToMesh3Code(lat, lon) {

  if (!Number.isFinite(lat) || !Number.isFinite(lon)) {

    return null;

  }



  let tLat = lat * 1.5;

  let tLon = lon - 100;



  const m1Lat = Math.floor(tLat);

  const m1Lon = Math.floor(tLon);



  tLat = (tLat - m1Lat) * 8;

  tLon = (tLon - m1Lon) * 8;



  const m2Lat = Math.floor(tLat);

  const m2Lon = Math.floor(tLon);



  if (m2Lat < 0 || m2Lat > 7 || m2Lon < 0 || m2Lon > 7) {

    return null;

  }



  tLat = (tLat - m2Lat) * 10;

  tLon = (tLon - m2Lon) * 10;



  const m3Lat = Math.floor(tLat);

  const m3Lon = Math.floor(tLon);



  if (m3Lat < 0 || m3Lat > 9 || m3Lon < 0 || m3Lon > 9) {

    return null;

  }



  return `${String(m1Lat).padStart(2, '0')}${String(m1Lon).padStart(2, '0')}${m2Lat}${m2Lon}${m3Lat}${m3Lon}`;

}



export function resolveMesh3CodeRange(georef) {

  const mesh3 = georef?.mesh?.['3'] ?? georef?.mesh?.[3];

  const range = (mesh3 && typeof mesh3 === 'object') ? mesh3.code_range : null;

  if (!range) {

    return null;

  }



  const south = String(range.south ?? '').trim();

  const north = String(range.north ?? '').trim();

  if (!/^\d{8}$/.test(south) || !/^\d{8}$/.test(north) || south > north) {

    return null;

  }



  return { south, north };

}



function mesh3Bounds(code) {

  const p = Number(code.slice(0, 2));

  const q = Number(code.slice(2, 4));

  const r = Number(code[4]);

  const s = Number(code[5]);

  const t = Number(code[6]);

  const u = Number(code[7]);



  const south = (p * LAT_UNIT_1) + (r * LAT_UNIT_2) + (t * LAT_UNIT_3);

  const west = q + 100 + (s * LON_UNIT_2) + (u * LON_UNIT_3);



  return {

    north: south + LAT_UNIT_3,

    south,

    east: west + LON_UNIT_3,

    west,

  };

}



function boundsIntersect(a, b) {

  return a.south < b.north

    && a.north > b.south

    && a.west < b.east

    && a.east > b.west;

}



/** コード範囲内かつ都道府県 bounds と交差する 3 次メッシュを列挙する */

export function enumerateMesh3InCodeRange(southCode, northCode, prefectureBounds) {

  const southP = Number(southCode.slice(0, 2));

  const southQ = Number(southCode.slice(2, 4));

  const northP = Number(northCode.slice(0, 2));

  const northQ = Number(northCode.slice(2, 4));

  const pMin = Math.min(southP, northP) - 1;

  const pMax = Math.max(southP, northP) + 1;

  const qMin = Math.min(southQ, northQ) - 1;

  const qMax = Math.max(southQ, northQ) + 1;



  const cells = [];

  for (let p = pMin; p <= pMax; p++) {

    for (let q = qMin; q <= qMax; q++) {

      for (let r = 0; r <= 7; r++) {

        for (let s = 0; s <= 7; s++) {

          for (let t = 0; t <= 9; t++) {

            for (let u = 0; u <= 9; u++) {

              const code = `${String(p).padStart(2, '0')}${String(q).padStart(2, '0')}${r}${s}${t}${u}`;

              if (code < southCode || code > northCode) {

                continue;

              }

              const cellBounds = mesh3Bounds(code);

              if (boundsIntersect(prefectureBounds, cellBounds)) {

                cells.push({ code, bounds: cellBounds });

              }

            }

          }

        }

      }

    }

  }



  cells.sort((a, b) => a.code.localeCompare(b.code));

  return cells;

}



export function enumerateMesh3ForPrefecture(georef) {

  if (!georef?.bounds) {

    return [];

  }



  const codeRange = resolveMesh3CodeRange(georef);

  if (codeRange) {

    return enumerateMesh3InCodeRange(codeRange.south, codeRange.north, georef.bounds);

  }



  return enumerateMesh3InBounds(georef.bounds);

}



/** georef bounds と交差する 3 次メッシュを JIS 規則どおり列挙する */

export function enumerateMesh3InBounds(bounds) {

  const cells = [];

  const pMin = Math.floor(bounds.south / LAT_UNIT_1);

  const pMax = Math.floor(bounds.north / LAT_UNIT_1);

  const qMin = Math.floor(bounds.west - 100);

  const qMax = Math.floor(bounds.east - 100);



  for (let p = pMin; p <= pMax; p++) {

    for (let q = qMin; q <= qMax; q++) {

      for (let r = 0; r <= 7; r++) {

        for (let s = 0; s <= 7; s++) {

          for (let t = 0; t <= 9; t++) {

            for (let u = 0; u <= 9; u++) {

              const code = `${String(p).padStart(2, '0')}${String(q).padStart(2, '0')}${r}${s}${t}${u}`;

              const cellBounds = mesh3Bounds(code);

              if (boundsIntersect(bounds, cellBounds)) {

                cells.push({ code, bounds: cellBounds });

              }

            }

          }

        }

      }

    }

  }



  cells.sort((a, b) => a.code.localeCompare(b.code));

  return cells;

}



function boundsToSvgPath(bounds, georef) {

  const corners = [

    latLonToSvgProjected(bounds.north, bounds.west, georef),

    latLonToSvgProjected(bounds.north, bounds.east, georef),

    latLonToSvgProjected(bounds.south, bounds.east, georef),

    latLonToSvgProjected(bounds.south, bounds.west, georef),

  ];



  const [nw, ne, se, sw] = corners;

  return [

    `M ${nw.x.toFixed(2)} ${nw.y.toFixed(2)}`,

    `L ${ne.x.toFixed(2)} ${ne.y.toFixed(2)}`,

    `L ${se.x.toFixed(2)} ${se.y.toFixed(2)}`,

    `L ${sw.x.toFixed(2)} ${sw.y.toFixed(2)}`,

    'Z',

  ].join(' ');

}



export function buildMesh3Layer(georef, options = {}) {

  if (!georef?.bounds) {

    return '';

  }



  const cells = enumerateMesh3ForPrefecture(georef);

  if (cells.length === 0) {

    return '';

  }



  const { highlightCode = null } = options;

  const parts = ['<g id="mesh-layer-3" class="mesh-layer mesh-layer-3">'];



  cells.forEach((cell) => {

    const path = boundsToSvgPath(cell.bounds, georef);

    const code = escapeXml(cell.code);

    const classes = ['mesh-3-cell'];

    if (highlightCode && cell.code === highlightCode) {

      classes.push('mesh-3-highlight');

    }



    parts.push(

      `<path id="m3-${code}" class="${classes.join(' ')}" data-mesh-code="${code}" d="${path}" />`,

    );

  });



  parts.push('</g>');

  return parts.join('');

}



export function buildMesh3Styles(data) {

  if (!data || typeof data !== 'object') {

    return '';

  }



  let style = '';

  const collectionSet = new Set();



  (data.mesh3?.collections || data.mesh3_collections || []).forEach((entry) => {

    String(entry).split(';').forEach((code) => {

      const trimmed = code.trim();

      if (trimmed) {

        style += `#m3-${trimmed} { fill:#4db56a; }\n`;

        collectionSet.add(trimmed);

      }

    });

  });



  (data.mesh3?.observations || data.mesh3_observations || []).forEach((entry) => {

    String(entry).split(';').forEach((code) => {

      const trimmed = code.trim();

      if (trimmed && !collectionSet.has(trimmed)) {

        style += `#m3-${trimmed} { fill: url(#bg-stripe); }\n`;

      }

    });

  });



  return style;

}


