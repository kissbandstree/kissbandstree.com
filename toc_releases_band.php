<?php
require_once __DIR__ . '/page-routes.php';
$releaseViews = [
  'releases_band_grid.php' => ['title' => 'Grid', 'icon' => 'view_grid'],
  'releases_band_table.php' => ['title' => 'Table', 'icon' => 'view_table'],
  'releases_band_list.php' => ['title' => 'List', 'icon' => 'view_list'],
];

$releaseViewOrder = array_keys($releaseViews);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentViewIndex = array_search($currentPage, $releaseViewOrder, true);

if ($currentViewIndex === false) {
  $currentView = 'releases_band_grid.php';
  $nextView = 'releases_band_grid.php';
} else {
  $currentView = $releaseViewOrder[$currentViewIndex];
  $nextView = $releaseViewOrder[($currentViewIndex + 1) % count($releaseViewOrder)];
}

$currentViewIcon = $releaseViews[$currentView]['icon'];
$viewQuery = $currentViewIndex === false ? '' : (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '');
$nextViewUrl = resolve_page_request('/' . $nextView, $viewQuery)['url'];
?>

<div class="top-line">

  <svg class="icons" aria-hidden="true">
    <title>Releases</title>
    <use href="icons.svg#lp"></use>
  </svg>

  <h2>RELEASES</h2>

  <a
    href="<?= htmlspecialchars($nextViewUrl, ENT_QUOTES, 'UTF-8') ?>"
    title="View"
    data-view-group="releases"
    data-view-grid="<?= htmlspecialchars(get_page_url('releases', 'grid', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-table="<?= htmlspecialchars(get_page_url('releases', 'table', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-list="<?= htmlspecialchars(get_page_url('releases', 'list', $viewQuery), ENT_QUOTES, 'UTF-8') ?>">
    <svg class="icons">
      <use href="icons.svg#<?= htmlspecialchars($currentViewIcon, ENT_QUOTES, 'UTF-8') ?>"></use>
    </svg>
  </a>

</div>
