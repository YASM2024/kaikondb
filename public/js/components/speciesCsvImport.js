// public/js/components/speciesCsvImport.js
import { apiPaths, importLabels } from '../constants.js';
import { createCsvImportApp } from './createCsvImportApp.js';

export function mountSpeciesCsvImport(selector) {
  const app = createCsvImportApp({
    fileFieldName: 'species_file',
    importPath: apiPaths.masterSpeciesImport,
    defaultLabel: importLabels.species,
  });

  app.mount(selector);
}