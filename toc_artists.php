<?php
require_once __DIR__ . '/page-routes.php';
$artistViews = [
  'artists_grid.php' => ['title' => 'Grid', 'icon' => 'view_grid'],
  'artists_table.php' => ['title' => 'Table', 'icon' => 'view_table'],
  'artists_list.php' => ['title' => 'List', 'icon' => 'view_list'],
];

$artistViewOrder = array_keys($artistViews);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentViewIndex = array_search($currentPage, $artistViewOrder, true);

if ($currentViewIndex === false) {
  $currentView = 'artists_grid.php';
  $nextView = 'artists_grid.php';
} else {
  $currentView = $artistViewOrder[$currentViewIndex];
  $nextView = $artistViewOrder[($currentViewIndex + 1) % count($artistViewOrder)];
}

$currentViewIcon = $artistViews[$currentView]['icon'];
$viewQuery = $currentViewIndex === false ? '' : (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '');
$nextViewUrl = resolve_page_request('/' . $nextView, $viewQuery)['url'];
?>

<div class="top-line">

  <svg class="icons" aria-hidden="true">
    <title>Artists</title>
    <use href="icons.svg#person"></use>
  </svg>

  <h2>ARTISTS</h2>

  <a
    href="<?= htmlspecialchars($nextViewUrl, ENT_QUOTES, 'UTF-8') ?>"
    title="View"
    data-view-group="artists"
    data-view-grid="<?= htmlspecialchars(get_page_url('artists', 'grid', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-table="<?= htmlspecialchars(get_page_url('artists', 'table', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-list="<?= htmlspecialchars(get_page_url('artists', 'list', $viewQuery), ENT_QUOTES, 'UTF-8') ?>">
    <svg class="icons">
      <use href="icons.svg#<?= htmlspecialchars($currentViewIcon, ENT_QUOTES, 'UTF-8') ?>"></use>
    </svg>
  </a>

</div>