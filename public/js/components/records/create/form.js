// js/components/records/create/form.js
import { DOM } from './dom.js';

export function setupRecordForm() {
  const form = document.getElementById('registerRecord');
  const submitBtn = document.getElementById('submitBtn');
  const deleteBtn = document.getElementById('deleteBtn');

  if (!form || !submitBtn || !deleteBtn) {
    console.warn('[setupRecordForm] フォーム要素が見つかりません');
    return;
  }

  /* ---------- 確認（submit） ---------- */
  submitBtn.addEventListener('click', e => {
    e.preventDefault();

    if (!DOM.inputSpecies.value) {
      return;
    }

    // municipality 未選択チェック（必要なら）
    if (
      typeof window.municipality_ids_array !== 'undefined' &&
      window.municipality_ids_array.length >= 1
    ) {
      return;
    }

    form.action = '';
    form.submit();
  });

  /* ---------- 削除 ---------- */
  deleteBtn.addEventListener('click', e => {
    e.preventDefault();

    if (!confirm('本当に削除しますか？')) return;
    if (!DOM.inputSpecies.value) return;

    form.action = 'delete';
    alert('削除しました。');
    form.submit();
  });
}
