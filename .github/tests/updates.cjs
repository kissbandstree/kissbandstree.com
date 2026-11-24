const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const test = require('node:test');

const root = path.resolve(__dirname, '..', '..');

test('Updates paginates Git history and reports invalid input or feeds', t => {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), 'site-updates-'));
  t.after(() => fs.rmSync(dir, { recursive: true, force: true }));
  fs.mkdirSync(path.join(dir, 'data'));
  for (const file of ['updates_view.php', 'updates.css']) {
    fs.copyFileSync(path.join(root, file), path.join(dir, file));
  }
  for (const file of ['head.php', 'toc.php', 'footer.php']) {
    fs.writeFileSync(path.join(dir, file), '');
  }
  const runs = Array.from({ length: 28 }, (_, index) => ({
    head_sha: index.toString(16).padStart(40, '0'),
    created_at: '2026-01-01T00:00:00Z',
    display_title: `Update ${index} <test>`
  }));
  const feed = path.join(dir, 'data', 'updates.json');
  fs.writeFileSync(feed, JSON.stringify({ workflow_runs: runs }));
  const render = query => {
    const script = `$_GET = json_decode(getenv('TEST_QUERY'), true); $repository = 'https://github.com/example/site'; require ${JSON.stringify(path.join(dir, 'updates_view.php'))}; fwrite(STDERR, "\\nSTATUS:" . (http_response_code() ?: 200));`;
    const result = spawnSync('php', ['-r', script], {
      encoding: 'utf8',
      env: { ...process.env, TEST_QUERY: JSON.stringify(query) }
    });
    assert.equal(result.status, 0, result.stderr);
    return { body: result.stdout, status: Number(result.stderr.match(/STATUS:(\d+)/)?.[1]) };
  };
  const first = render({});
  assert.equal(first.status, 200);
  assert.match(first.body, /<svg class="icons" aria-hidden="true"><use href="\/icons\.svg#github"><\/use><\/svg>\s*<h1>Updates<\/h1>\s*<svg class="icons" aria-hidden="true"><use href="\/icons\.svg#github"><\/use><\/svg>/);
  assert.match(first.body, /Page 1 of 2/);
  assert.match(first.body, /Update 0 &lt;test&gt;/);
  assert.match(first.body, /<time datetime="2026-01-01T00:00:00Z">01-01-2026 00:00<\/time>/);
  assert.match(first.body, /<table>\s*<thead><tr><th scope="col">Title<\/th><th scope="col">Date<\/th><th scope="col">Number<\/th><th scope="col">Commit<\/th><\/tr><\/thead>\s*<tbody>/);
  assert.doesNotMatch(first.body, /data-label=/);
  const styles = fs.readFileSync(path.join(root, 'updates.css'), 'utf8');
  assert.doesNotMatch(styles, /container-type|@container|data-label|display:\s*grid/);
  assert.match(first.body, new RegExp(`https://github.com/example/site/commit/${runs[0].head_sha}`));
  assert.doesNotMatch(first.body, /Update 25 &lt;test&gt;/);
  const second = render({ page: '2' });
  assert.match(second.body, /Update 25 &lt;test&gt;/);
  assert.equal(render({ page: ['1'] }).status, 400);
  fs.writeFileSync(feed, '{"workflow_runs":[{}]}');
  const invalid = render({});
  assert.equal(invalid.status, 503);
  assert.match(invalid.body, /Updates unavailable/);
});
