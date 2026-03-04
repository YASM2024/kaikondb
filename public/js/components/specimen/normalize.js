// specimen/normalize.js

/**
 * APIレスポンスを「{ total, items }」に正規化する
 * - json.items / json.data などの揺れを吸収
 * - total が無ければ items.length
 * - items は normalizeSpecimen 済み（camelCase）
 */
export function normalizeList(json) {
  const rawItems = Array.isArray(json?.items) ? json.items
                : Array.isArray(json?.data)  ? json.data
                : Array.isArray(json)        ? json
                : [];

  const total = (typeof json?.total === 'number') ? json.total
              : (typeof json?.count === 'number') ? json.count
              : rawItems.length;

  const items = rawItems.map(normalizeSpecimen);

  return { total, items };
}

/**
 * 1件の標本データを render.js が期待する形に正規化
 * - snake_case / camelCase /別名キーを吸収
 * - null/undefined を '' に寄せる（表示は render.js 側で '-' にする）
 */
export function normalizeSpecimen(x = {}) {
  // 文字列化ユーティリティ
  const s = (v) => (v === undefined || v === null) ? '' : String(v);

  return {
    id: s(x.id ?? x.specimen_id ?? x.specimenId),

    species: s(
      x.species ??
      x.scientific_name ?? x.scientificName ??
      x.scientific ??
      x.taxon_scientific_name
    ),

    speciesJa: s(
      x.species_ja ?? x.speciesJa ??
      x.japanese_name ?? x.japaneseName ??
      x.common_name_ja ?? x.commonNameJa ??
      x.name_ja ?? x.nameJa
    ),

    locality: s(
      x.locality ??
      x.place ?? x.location ??
      x.collection_locality ?? x.collectionLocality
    ),

    collectedBy: s(
      x.collected_by ?? x.collectedBy ??
      x.collector ?? x.collector_name ?? x.collectorName
    ),

    identifiedBy: s(
      x.identified_by ?? x.identifiedBy ??
      x.identifier ?? x.identifier_name ?? x.identifierName
    ),

    image1: s(
      x.image1 ?? x.image_1 ?? x.img1 ?? ''
    ),

  };
}

export function normalizeSpecimenDetail(x = {}) {
  // 文字列化ユーティリティ
  const s = (v) => (v === undefined || v === null) ? '' : String(v);

  return {
    id: s(x.id ?? x.specimen_id ?? x.specimenId),

    species: s(
      x.species ??
      x.scientific_name ?? x.scientificName ??
      x.scientific ??
      x.taxon_scientific_name
    ),

    speciesJa: s(
      x.species_ja ?? x.speciesJa ??
      x.japanese_name ?? x.japaneseName ??
      x.common_name_ja ?? x.commonNameJa ??
      x.name_ja ?? x.nameJa
    ),

    sex: s(x.sex),

    locality: s(
      x.locality ??
      x.place ?? x.location ??
      x.collection_locality ?? x.collectionLocality
    ),

    date: s(
      x.date ??
      x.collection_date ?? x.collectionDate ??
      x.collected_date ?? x.collectedDate
    ),

    collectedBy: s(
      x.collected_by ?? x.collectedBy ??
      x.collector ?? x.collector_name ?? x.collectorName
    ),

    identifiedBy: s(
      x.identified_by ?? x.identifiedBy ??
      x.identifier ?? x.identifier_name ?? x.identifierName
    ),

    owner: s(x.owner ?? x.collection_owner ?? x.collectionOwner),

    typeStatus: s(
      x.type_status ?? x.typeStatus ??
      x.type ?? x.typestatus ?? x.typeStatusText
    ),

    lat: s(x.lat ?? x.latitude),
    lng: s(x.lng ?? x.lon ?? x.longitude),

    preservation: s(x.preservation ?? x.preserve_method ?? x.preserveMethod),

    repo: s(x.repo ?? x.repository ?? x.institution),

    catalog: s(
      x.catalog ?? x.catalog_number ?? x.catalogNumber ??
      x.accession ?? x.accession_number ?? x.accessionNumber
    ),

    remarks: s(x.remarks ?? x.note ?? x.notes ?? x.comment),

    license: s(x.license ?? x.licence),

    // 画像は配列で来る場合も吸収（images[0].url など）
    image1: pickImage(x, 0),
    image2: pickImage(x, 1),
    image3: pickImage(x, 2),
  };
}

/* ===== helpers ===== */

function pickImage(x, idx) {
  const s = (v) => (v === undefined || v === null) ? '' : String(v);

  // 直接キー（image1 / image_1 など）
  if (idx === 0) return s(x.image1 ?? x.image_1 ?? x.img1 ?? '');
  if (idx === 1) return s(x.image2 ?? x.image_2 ?? x.img2 ?? '');
  if (idx === 2) return s(x.image3 ?? x.image_3 ?? x.img3 ?? '');

  // 画像配列（images: [...]）
  const arr = Array.isArray(x.images) ? x.images : null;
  if (!arr || !arr[idx]) return '';

  const item = arr[idx];
  // item が文字列URLの場合
  if (typeof item === 'string') return s(item);
  // { url } / { src } など
  return s(item.url ?? item.src ?? item.path ?? '');
}
