// project base url
export const PROJECT_BASE_URL        = globalThis.CONFIG?.baseUrl;

// master base url
export const MASTER_BASE_URL         = `${PROJECT_BASE_URL}/master`;

// taxon base url
export const TAXON_BASE_URL          = `${MASTER_BASE_URL}/taxon`;
export const UPPER_TAXA_URL          = `${PROJECT_BASE_URL}/upper-taxa`;

// orders
export const ORDER_CREATE_URL        = `${MASTER_BASE_URL}/order/create`;
export const ORDER_EDIT_URL          = `${MASTER_BASE_URL}/order/edit`;
export const ORDER_IMPORT_URL        = `${MASTER_BASE_URL}/order/import`;
export const ORDER_STATUS_EDIT_URL   = `${MASTER_BASE_URL}/order/edit-status`;

// families
export const FAMILY_CREATE_URL       = `${MASTER_BASE_URL}/family/create`;
export const FAMILY_EDIT_URL         = `${MASTER_BASE_URL}/family/edit`;
export const FAMILY_IMPORT_URL       = `${MASTER_BASE_URL}/family/import`;
export const FAMILY_STATUS_EDIT_URL  = `${MASTER_BASE_URL}/family/edit-status`;
export const FAMILY_SHOW_URL         = `${MASTER_BASE_URL}/family/show`;
export const ORDER_SHOW_URL          = `${MASTER_BASE_URL}/order/show`;

// species
export const SPECIES_CREATE_URL      = `${MASTER_BASE_URL}/species/create`;
export const SPECIES_EDIT_URL        = `${MASTER_BASE_URL}/species/edit`;
export const SPECIES_IMPORT_URL      = `${MASTER_BASE_URL}/species/import`;
export const SPECIES_STATUS_EDIT_URL = `${MASTER_BASE_URL}/species/edit-status`;
export const SPECIES_SHOW_URL        = `${MASTER_BASE_URL}/species/show`;
// const UPPER_TAXA_URL
// const ORDER_SHOW_URL
// const FAMILY_SHOW_URL

// journals
export const JOURNALS_SHOW_URL         = `${MASTER_BASE_URL}/journals`;
export const JOURNAL_CREATE_URL        = `${MASTER_BASE_URL}/journal/create`;
export const JOURNAL_EDIT_URL          = `${MASTER_BASE_URL}/journal/edit`;
export const JOURNAL_STATUS_EDIT_URL   = `${MASTER_BASE_URL}/journal/edit-status`;

// municipalities
export const MUNICIPALITIES_SHOW_URL      = `${MASTER_BASE_URL}/municipalities`;
export const MUNICIPALITY_CREATE_URL      = `${MASTER_BASE_URL}/municipality/create`;
export const MUNICIPALITY_EDIT_URL        = `${MASTER_BASE_URL}/municipality/edit`;
export const MUNICIPALITY_SHOW_URL        = `${MASTER_BASE_URL}/municipality/show`;
export const MUNICIPALITY_DELETE_URL      = `${MASTER_BASE_URL}/municipality/delete`;
export const MUNICIPALITY_DELETE_SCREENING_URL = `${MASTER_BASE_URL}/municipality/delete-screening`;
export const MUNICIPALITY_STATUS_EDIT_URL = `${MASTER_BASE_URL}/municipality/edit-status`;
export const MUNICIPALITY_IMPORT_URL      = `${MASTER_BASE_URL}/municipality/import`;