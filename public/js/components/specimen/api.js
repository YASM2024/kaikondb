/**
 * 標本一覧APIのURLを組み立てる
 * - params: URLSearchParams / plain object / null
 * - 省略時は現在の location.search をそのまま使う
 */
export function buildSpecimenListUrl({ baseUrl, endpoint = '/api/specimens', params } = {}) {
  if (!baseUrl) throw new Error('buildSpecimenListUrl: baseUrl is required');

  const u = new URL(baseUrl.replace(/\/+$/, '') + endpoint);

  if (params instanceof URLSearchParams) {
    u.search = params.toString();
  } else if (params && typeof params === 'object') {
    const sp = new URLSearchParams();
    for (const [k, v] of Object.entries(params)) {
      if (v === undefined || v === null || v === '') continue;
      sp.set(k, String(v));
    }
    u.search = sp.toString();
  } else {
    // デフォルト：現在のクエリを引き継ぐ（?q=...&locality=...）
    u.search = window.location.search;
  }

  return u.toString();
}

/**
 * 標本一覧を取得してJSONを返す
 * - 返り値は「生のJSON」(normalizeは別モジュール)
 */
export async function fetchSpecimens({ baseUrl, endpoint, params, signal } = {}) {
  const url = buildSpecimenListUrl({ baseUrl, endpoint, params });

  const res = await fetch(url, {
    method: 'GET',
    headers: { Accept: 'application/json' },
    credentials: 'same-origin',
    signal,
  });

  if (!res.ok) {
    // エラー本文が取れるなら取って、デバッグしやすく
    let body = '';
    try { body = await res.text(); } catch {}
    const err = new Error(`Specimen API failed: ${res.status} ${res.statusText}`);
    err.status = res.status;
    err.url = url;
    err.body = body;
    throw err;
  }

  return res.json();
}

// specimen/api.js
export async function fetchSpecimenDetail({ baseUrl, id, signal } = {}) {
  const url = `${baseUrl}/specimens/${encodeURIComponent(id)}`;

  const res = await fetch(url, {
    method: 'GET',
    headers: { 'Accept': 'application/json' },
    signal,
  });

  if (!res.ok) throw new Error(`fetchSpecimenDetail failed: ${res.status}`);
  return await res.json();
}

/**
 * （任意）キャンセル可能な fetch を作りたい場合のヘルパ
 */
export function createAbortController() {
  return new AbortController();
}
