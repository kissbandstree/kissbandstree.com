const assert = require('node:assert/strict');
const test = require('node:test');

test('External text links have indicators, without marking internal or image-only links', async () => {
  for (const route of ['/', '/tree', '/band?a=shogun_mojo_2', '/artist?a=dan_reed']) {
    const response = await fetch(`http://localhost:8003${route}`);
    assert.equal(response.status, 200, route);
    const html = await response.text();
    const links = [...html.matchAll(/<a\b[^>]*href=["']([^"']*)["'][^>]*>([\s\S]*?)<\/a>/gi)];
    let externalTextLinks = 0;
    for (const [, href, content] of links) {
      const text = content.replace(/<svg\b[\s\S]*?<\/svg>/gi, '').replace(/<[^>]+>/g, '').trim();
      const external = /^https?:\/\//i.test(href)
        && !/^(?:www\.)?kissbandstree\.com$/i.test(new URL(href).hostname);
      const icons = [...content.matchAll(/<use href="\/icons\.svg#external"><\/use>/g)];
      const expected = external && text !== '';
      assert.equal(icons.length, expected ? 1 : 0, `${route}: ${href}`);
      if (expected) externalTextLinks++;
    }
    assert.ok(externalTextLinks >= 2, route);
  }
});
