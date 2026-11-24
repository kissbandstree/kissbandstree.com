<?php
$runs = [];
$unavailable = false;
try {
    $feedPath = __DIR__ . '/data/updates.json';
    if (!is_readable($feedPath)) {
        throw new RuntimeException('Git history feed is not readable.');
    }
    $contents = file_get_contents($feedPath);
    if ($contents === false) {
        throw new RuntimeException('Git history feed could not be read.');
    }
    $feed = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
    if (!is_array($feed) || !isset($feed['workflow_runs']) || !is_array($feed['workflow_runs'])) {
        throw new RuntimeException('Invalid Git history feed.');
    }
    foreach ($feed['workflow_runs'] as $run) {
        if (!is_array($run) || !isset($run['head_sha'], $run['created_at'], $run['display_title'])
            || !is_string($run['head_sha']) || !preg_match('/\A[0-9a-f]{40}\z/', $run['head_sha'])
            || !is_string($run['created_at']) || strtotime($run['created_at']) === false
            || !is_string($run['display_title'])) {
            throw new RuntimeException('Invalid Git history entry.');
        }
    }
    $runs = $feed['workflow_runs'];
} catch (RuntimeException | JsonException $error) {
    error_log('Updates: ' . $error->getMessage());
    http_response_code(503);
    $unavailable = true;
}
$pageValue = filter_var($_GET['page'] ?? 1, FILTER_VALIDATE_INT);
if ($pageValue === false) {
    http_response_code(400);
    $pageValue = 1;
    $unavailable = true;
}
$perPage = 25;
$total = count($runs);
$pages = max(1, (int) ceil($total / $perPage));
$page = min($pages, max(1, $pageValue));
$pageRuns = array_slice($runs, ($page - 1) * $perPage, $perPage);
$escape = static fn($value) => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <?php require __DIR__ . '/head.php'; ?>
  <link rel="stylesheet" href="/updates.css?v=<?php echo filemtime(__DIR__ . '/updates.css'); ?>">
</head>
<body>
  <?php require __DIR__ . '/toc.php'; ?>
  <div class="top-line updates-heading">
    <svg class="icons" aria-hidden="true"><use href="/icons.svg#github"></use></svg>
    <h1>Updates</h1>
    <svg class="icons" aria-hidden="true"><use href="/icons.svg#github"></use></svg>
  </div>
  <div class="standard colored updates">
    <nav class="updates-pagination" aria-label="Updates pagination">
      <a href="/updates?page=<?php echo $page === 1 ? $pages : $page - 1; ?>" aria-label="Previous updates" title="Previous updates"><svg class="icons" aria-hidden="true"><use href="/icons.svg#arrow_left"></use></svg></a>
      <span>Page <?php echo $page; ?> of <?php echo $pages; ?></span>
      <a href="/updates?page=<?php echo $page === $pages ? 1 : $page + 1; ?>" aria-label="Next updates" title="Next updates"><svg class="icons" aria-hidden="true"><use href="/icons.svg#arrow_right"></use></svg></a>
    </nav>
    <div class="updates-scroll">
      <table>
        <thead><tr><th scope="col">Title</th><th scope="col">Date</th><th scope="col">Number</th><th scope="col">Commit</th></tr></thead>
        <tbody>
        <?php if ($unavailable): ?>
          <tr><td colspan="4">Updates unavailable</td></tr>
        <?php elseif (!$pageRuns): ?>
          <tr><td colspan="4">No updates available</td></tr>
        <?php else: ?>
          <?php foreach ($pageRuns as $index => $run): ?>
            <tr>
              <td><a href="<?php echo $escape($repository . '/commit/' . $run['head_sha']); ?>" target="_blank" rel="noopener noreferrer"><?php echo $escape($run['display_title']); ?></a></td>
              <td><time datetime="<?php echo $escape($run['created_at']); ?>"><?php echo gmdate('d-m-Y H:i', strtotime($run['created_at'])); ?> UTC</time></td>
              <td><?php echo $total - ($page - 1) * $perPage - $index; ?></td>
              <td><?php echo $escape(substr($run['head_sha'], 0, 7)); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php require __DIR__ . '/footer.php'; ?>
</body>
</html>
