// public/js/pages/masterImportPage.js
import { mountOrderCsvImport } from '../components/orderCsvImport.js';
import { mountFamilyCsvImport } from '../components/familyCsvImport.js';
import { mountSpeciesCsvImport } from '../components/speciesCsvImport.js';

if (document.querySelector('#orderImportApp')) {
  mountOrderCsvImport('#orderImportApp');
}

if (document.querySelector('#familyImportApp')) {
  mountFamilyCsvImport('#familyImportApp');
}

if (document.querySelector('#speciesImportApp')) {
  mountSpeciesCsvImport('#speciesImportApp');
}