// public/js/constants.js
export const appConfig = Object.freeze({
  baseUrl: 'http://localhost/',
});

export const apiPaths = Object.freeze({
  masterOrderShow: '/master/order/show',
  masterFamilyShow: '/master/family/show',
  masterFamilyEdit: '/master/family/edit',
  masterSpeciesShow: '/master/species/show',
  masterSpeciesEdit: '/master/species/edit',
  masterFamilyImport: '/master/family/import',
});

export const sentinelValues = Object.freeze({
  none: 'none',
  newRowId: 'new',
});

export const resultStatus = Object.freeze({
  success: 'success',
  error: 'error',
});

export const domSelectors = Object.freeze({
  csrfMeta: 'meta[name="csrf-token"]',
});

export const messages = Object.freeze({
  commonError: 'エラーが発生しました。',
  uploadNow: 'アップロード中...',
  uploadSuccess: 'アップロード成功！',
  uploadFailure: 'アップロード失敗…',
  submitConfirm: '更新してもよろしいですか？元に戻すことはできません。',
  submitSuccess: '修正を完了しました。画面を再読み込みします。',
  submitDetectedError: '！エラーを感知しました。画面を再読み込みします。！',
  submitFailure: '修正に失敗しました。',
});