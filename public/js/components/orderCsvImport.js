// public/js/components/orderCsvImport.js
import { apiPaths, importLabels } from '../constants.js';
import { createCsvImportApp } from './createCsvImportApp.js';

export function mountOrderCsvImport(selector) {
  const app = createCsvImportApp({
    fileFieldName: 'order_file',
    importPath: apiPaths.masterOrderImport,
    defaultLabel: importLabels.order,
  });

  app.mount(selector);
}