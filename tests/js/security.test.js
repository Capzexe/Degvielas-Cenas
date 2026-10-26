import test from 'node:test';
import assert from 'node:assert/strict';
import { escapeHtml, popupContent, safeSourceUrl } from '../../resources/js/security.js';

test('map popup escapes every untrusted field', () => {
    const attack = '<img src=x onerror="alert(1)"><script>alert(2)</script>';
    const html = popupContent(Object.fromEntries(['rank', 'name', 'address', 'price', 'source'].map((key) => [key, attack])));
    assert.equal(html.includes('<img'), false);
    assert.equal(html.includes('<script'), false);
    assert.equal(html.split('&lt;img').length - 1, 5);
    assert.equal(escapeHtml('Rīga & "DUS"'), 'Rīga &amp; &quot;DUS&quot;');
});

test('source links reject executable URLs, credentials and malformed inputs', () => {
    for (const value of ['javascript:alert(1)', 'data:text/html,<script>', '//evil.example', 'http://example.com', 'https://u:p@example.com', null, 'invalid']) {
        assert.equal(safeSourceUrl(value), null);
    }
    assert.equal(safeSourceUrl('https://www.viada.lv/zemakas-degvielas-cenas/'), 'https://www.viada.lv/zemakas-degvielas-cenas/');
});
