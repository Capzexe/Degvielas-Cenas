// Scraped text is untrusted even when the upstream site belongs to a fuel chain.
export const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[character]));

export const safeSourceUrl = (value) => {
    try {
        const url = new URL(value);
        return url.protocol === 'https:' && !url.username && !url.password ? url.href : null;
    } catch {
        return null;
    }
};

export const popupContent = ({ rank, name, address, price, source }) => `
    <div class="fuel-map-popup">
        <p class="fuel-map-popup__rank">${escapeHtml(rank)}</p>
        <strong>${escapeHtml(name)}</strong>
        <span>${escapeHtml(address)}</span>
        <b>${escapeHtml(price)}</b>
        <small>${escapeHtml(source)}</small>
    </div>
`;
