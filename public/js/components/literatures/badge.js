function normalizedString(value) {
    return (value ?? '').trim();
}

export function shouldShowLanguageOnlyBadge(languageId, titleJa, titleEn) {
    const lang = Number(languageId);
    const ja = normalizedString(titleJa);
    const en = normalizedString(titleEn);
    if (!ja || !en) return false;
    return ja === en && (lang === 1 || lang === 2);
}

export function japaneseOnlyBadgeHtml() {
    return '<span class="badge bg-warning text-dark ms-1">Japanese Only</span>';
}

export function englishOnlyBadgeHtml() {
    return '<span class="badge bg-info text-dark ms-1">English Only</span>';
}

export function languageOnlyBadgeHtml(languageId, titleJa, titleEn) {
    if (!shouldShowLanguageOnlyBadge(languageId, titleJa, titleEn)) return '';
    const lang = Number(languageId);
    if (lang === 1) return japaneseOnlyBadgeHtml();
    if (lang === 2) return englishOnlyBadgeHtml();
    return '';
}

export function appendLanguageOnlyBadge(titleHtml, languageId, titleJa, titleEn) {
    const badge = languageOnlyBadgeHtml(languageId, titleJa, titleEn);
    if (!badge) return titleHtml;
    return `${titleHtml}${badge}`;
}
