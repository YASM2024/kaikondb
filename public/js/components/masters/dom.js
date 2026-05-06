export const DOM = {
    // 共通
    get loadingBox()                     { return document.getElementById("loadingBox"); },
    get errorBox()                       { return document.getElementById("errorBox"); },
    get emptyBox()                       { return document.getElementById("emptyBox"); },
    get tableWrap()                      { return document.getElementById("tableWrap"); },
    get searchButton()                   { return document.getElementById("searchButton"); },
    get reloadButton()                   { return document.getElementById("reloadButton"); },
    get csvDownloadButton()              { return document.getElementById("csvDownloadButton"); },
    get countPill()                      { return document.getElementById("countPill"); },
    get keywordInput()                   { return document.getElementById("keywordInput"); },
    get statusFilter()                   { return document.getElementById("statusFilter"); },
    get csvImportButton()                { return document.getElementById("csvImportButton"); },
    get csvImportInput()                 { return document.getElementById("csvImportInput"); },
    
    // orderマスタ
    get ordersTableBody()                { return document.getElementById("ordersTableBody"); },

    get selectAllOrders()                { return document.getElementById("selectAllOrders"); },
    get addOrderButton()                 { return document.getElementById("addOrderButton"); },
    get orderCreateAndEditModalLabel()   { return document.getElementById("orderCreateAndEditModalLabel"); },
    get orderCreateAndEditModalElement() { return document.getElementById("orderCreateAndEditModal"); },
    get orderCreateAndEditForm()         { return document.getElementById("orderCreateAndEditForm"); },
    get orderEditErrorBox()              { return document.getElementById("orderEditErrorBox"); },

    get editOrderId()                    { return document.getElementById("editOrderId"); },
    get editOrderCode()                  { return document.getElementById("editOrderCode"); },
    get editOrderLatin()                 { return document.getElementById("editOrderLatin"); },
    get editOrderJa()                    { return document.getElementById("editOrderJa"); },
    get editOrderStatus()                { return document.getElementById("editOrderStatus"); },
    get saveOrderButton()                { return document.getElementById("saveOrderButton"); },


    // familyマスタ
    get familiesTableBody()                  { return document.getElementById("familiesTableBody") },
    get selectAllFamilies()                  { return document.getElementById("selectAllFamilies") },
    get addFamilyButton()                    { return document.getElementById("addFamilyButton") },
    get familyCreateAndEditModalLabel()      { return document.getElementById("familyCreateAndEditModalLabel") },
    get familyCreateAndEditModalElement()    { return document.getElementById("familyCreateAndEditModal") },
    get familyCreateAndEditForm()            { return document.getElementById("familyCreateAndEditForm") },
    get familyEditErrorBox()                 { return document.getElementById("familyEditErrorBox") },
    get editFamilyId()                       { return document.getElementById("editFamilyId") },
    get editFamilyCode()                     { return document.getElementById("editFamilyCode") },
    get editFamilyLatin()                    { return document.getElementById("editFamilyLatin") },
    get editFamilyJa()                       { return document.getElementById("editFamilyJa") },
    get editOrderId()                        { return document.getElementById("editOrderId") },
    get editFamilyStatus()                   { return document.getElementById("editFamilyStatus") },
    get saveFamilyButton()                   { return document.getElementById("saveFamilyButton") },
    get currentOrderLabel()                  { return document.getElementById("currentOrderLabel") },


    // speciesマスタ
    get speciesTableBody()                   { return document.getElementById("speciesTableBody") },
    get selectAllSpecies()                   { return document.getElementById("selectAllSpecies") },
    get addSpeciesButton()                   { return document.getElementById("addSpeciesButton") },
    get speciesCreateAndEditModalLabel()     { return document.getElementById("speciesCreateAndEditModalLabel") },
    get speciesCreateAndEditModalElement()   { return document.getElementById("speciesCreateAndEditModal") },
    get speciesCreateAndEditForm()           { return document.getElementById("speciesCreateAndEditForm") },
    get speciesEditErrorBox()                { return document.getElementById("speciesEditErrorBox") },
    get editSpeciesId()                      { return document.getElementById("editSpeciesId") },
    get editSpeciesCode()                    { return document.getElementById("editSpeciesCode") },
    get editSpeciesLatin()                   { return document.getElementById("editSpeciesLatin") },
    get editSpeciesJa()                      { return document.getElementById("editSpeciesJa") },
    get editOrderId()                        { return document.getElementById("editOrderId") },
    get editFamilyId()                       { return document.getElementById("editFamilyId") },
    get editSpeciesStatus()                  { return document.getElementById("editSpeciesStatus") },
    get saveSpeciesButton()                  { return document.getElementById("saveSpeciesButton") },
    get currentTaxonomyLabel()               { return document.getElementById("currentTaxonomyLabel") },


    // journalマスタ
    get journalTableBody()                  { return document.getElementById("journalTableBody") },
    get selectAllJournals()                  { return document.getElementById("selectAllJournals") },
    get addJournalButton()                   { return document.getElementById("addJournalButton") },

    get journalCreateAndEditModalLabel()     { return document.getElementById("journalCreateAndEditModalLabel") },
    get journalCreateAndEditModalElement()   { return document.getElementById("journalCreateAndEditModal") },
    get journalCreateAndEditForm()           { return document.getElementById("journalCreateAndEditForm") },
    get journalEditErrorBox()                { return document.getElementById("journalEditErrorBox") },

    get editJournalId()                      { return document.getElementById("editJournalId") },
    get editJournalCode()                    { return document.getElementById("editJournalCode") },
    get editJournalEn()                      { return document.getElementById("editJournalEn") },
    get editJournalJa()                      { return document.getElementById("editJournalJa") },
    get editJournalCategory()                { return document.getElementById("editJournalCategory") },
    get editJournalPublisher()               { return document.getElementById("editJournalPublisher") },
    get editJournalUrl()                     { return document.getElementById("editJournalUrl") },
    get editJournalProvidedBy()              { return document.getElementById("editJournalProvidedBy") },

    get editJournalStatus()                  { return document.getElementById("editJournalStatus") },
    get saveJournalButton()                  { return document.getElementById("saveJournalButton") },

    // municipalityマスタ
    get municipalityTableBody()              { return document.getElementById("municipalityTableBody") },
    get selectAllMunicipalities()            { return document.getElementById("selectAllMunicipalities") },
    get addMunicipalityButton()              { return document.getElementById("addMunicipalityButton") },

    get municipalityCreateAndEditModalLabel()     { return document.getElementById("municipalityCreateAndEditModalLabel") },
    get municipalityCreateAndEditModalElement()   { return document.getElementById("municipalityCreateAndEditModal") },
    get municipalityCreateAndEditForm()           { return document.getElementById("municipalityCreateAndEditForm") },
    get municipalityEditErrorBox()                { return document.getElementById("municipalityEditErrorBox") },

    get editMunicipalityId()                { return document.getElementById("editMunicipalityId") },
    get editMunicipalityCode()              { return document.getElementById("editMunicipalityCode") },
    get editMunicipalityJa()                { return document.getElementById("editMunicipalityJa") },
    get editMunicipalityEn()                { return document.getElementById("editMunicipalityEn") },
    get editMunicipalityStatus()            { return document.getElementById("editMunicipalityStatus") },

    get saveMunicipalityButton()            { return document.getElementById("saveMunicipalityButton") },
    
};
