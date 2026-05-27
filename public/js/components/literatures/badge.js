export function isJapaneseOnly(titleJa, titleEn) {
    const ja = titleJa ?? '';
    const en = (titleEn ?? '').trim();
    if (!en) {
        return true;
    }
    return ja === titleEn;
}

export function japaneseOnlyBadgeHtml() {
    return '<span class="badge bg-warning text-dark ms-1">Japanese Only</span>';
}

export function appendJapaneseOnlyBadge(titleHtml, titleJa, titleEn) {
    if (!isJapaneseOnly(titleJa, titleEn)) {
        return titleHtml;
    }
    return `${titleHtml}${japaneseOnlyBadgeHtml()}`;
}
