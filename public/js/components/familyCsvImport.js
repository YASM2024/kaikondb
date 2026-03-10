// public/js/components/familyCsvImport.js
import { apiPaths, importLabels } from '../constants.js';
import { createCsvImportApp } from './createCsvImportApp.js';

export function mountFamilyCsvImport(selector) {
  const app = createCsvImportApp({
    fileFieldName: 'family_file',
    importPath: apiPaths.masterFamilyImport,
    defaultLabel: importLabels.family,
  });

  app.mount(selector);
}