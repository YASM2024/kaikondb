// specimen/index.js
import { DOM } from './dom.js';
import { bindModal } from './modal.js';
import { initSearch } from './search.js';

document.addEventListener('DOMContentLoaded', () => {
  // 1) モーダルのイベントをバインド（動的生成ボタンでもOK）
  bindModal(DOM.modal);

  // 2) 検索（フォームsubmit横取り→POST検索→描画）
  initSearch({
    baseUrl: CONFIG.baseUrl,
    endpoint: '/specimens/search', // ←あなたのLaravelルートに合わせて変更
    syncUrl: true,                // URL(クエリ)と同期する
    autoRun: true,                // 初回ロードで検索実行
  });
});
