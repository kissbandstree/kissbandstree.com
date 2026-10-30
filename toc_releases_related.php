<?php
require_once __DIR__ . '/page-routes.php';
$relatedReleaseViews = [
  'releases_related_grid.php' => ['title' => 'Grid', 'icon' => 'view_grid'],
  'releases_related_table.php' => ['title' => 'Table', 'icon' => 'view_table'],
  'releases_related_list.php' => ['title' => 'List', 'icon' => 'view_list'],
];

$relatedReleaseViewOrder = array_keys($relatedReleaseViews);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentViewIndex = array_search($currentPage, $relatedReleaseViewOrder, true);

if ($currentViewIndex === false) {
  $currentView = 'releases_related_grid.php';
  $nextView = 'releases_related_grid.php';
} else {
  $currentView = $relatedReleaseViewOrder[$currentViewIndex];
  $nextView = $relatedReleaseViewOrder[($currentViewIndex + 1) % count($relatedReleaseViewOrder)];
}

$currentViewIcon = $relatedReleaseViews[$currentView]['icon'];
$viewQuery = $currentViewIndex === false ? '' : (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '');
$nextViewUrl = resolve_page_request('/' . $nextView, $viewQuery)['url'];
?>

<div class="top-line">

  <svg class="icons" aria-hidden="true">
    <title>Releases</title>
    <use href="icons.svg#lp"></use>
  </svg>

  <h2>RELATED</h2>

  <a
    href="<?= htmlspecialchars($nextViewUrl, ENT_QUOTES, 'UTF-8') ?>"
    title="View"
    data-view-group="related"
    data-view-grid="<?= htmlspecialchars(get_page_url('related', 'grid', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-table="<?= htmlspecialchars(get_page_url('related', 'table', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-list="<?= htmlspecialchars(get_page_url('related', 'list', $viewQuery), ENT_QUOTES, 'UTF-8') ?>">
    <svg class="icons">
      <use href="icons.svg#<?= htmlspecialchars($currentViewIcon, ENT_QUOTES, 'UTF-8') ?>"></use>
    </svg>
  </a>

</div>
