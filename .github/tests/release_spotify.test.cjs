const assert = require('node:assert/strict');
const { spawnSync } = require('node:child_process');
const path = require('node:path');
const test = require('node:test');

const root = path.resolve(__dirname, '../..');

test('Spotify links appear above tracklists on both kinds of release', async () => {
  for (const [route, url] of [
    ['/release_band?a=destroyer', 'https://open.spotify.com/album/2oPeCzPDtjkm2Ux5r7edAb'],
    ['/release_related?a=blackjack', 'https://open.spotify.com/album/6rgoiOCPma9vp1ZWA4jGEi'],
  ]) {
    const response = await fetch(`http://localhost:8003${route}`);
    assert.equal(response.status, 200, route);
    const html = await response.text();
    const link = `<a href="${url}">Spotify <svg class="external-link-icon"`;
    assert.ok(html.includes(link), route);
    assert.ok(html.indexOf(link) < html.indexOf('<h2>Tracklist</h2>'), route);
    assert.ok(html.includes('/icons.svg#spotify'), route);
  }
});

test('Missing Spotify links stay absent and invalid URLs fail explicitly', () => {
  const run = value => spawnSync('php', ['-r',
    `require 'functions.php'; echo render_release_spotify_link(${JSON.stringify(value)});`,
  ], { cwd: root, encoding: 'utf8' });
  assert.equal(run('').stdout, '');
  const invalid = run('https://example.com/album/123');
  assert.notEqual(invalid.status, 0);
  assert.match(invalid.stderr, /Invalid Spotify album URL/);
});

test('A release without a verified Spotify URL has no album link', async () => {
  const response = await fetch('http://localhost:8003/release_related?a=bk3');
  assert.equal(response.status, 200);
  assert.doesNotMatch(await response.text(), /href="https:\/\/open\.spotify\.com\/album\//);
});
