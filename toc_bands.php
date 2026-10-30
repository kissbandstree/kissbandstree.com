<?php
require_once __DIR__ . '/page-routes.php';
$bandViews = [
  'bands_grid.php' => ['title' => 'Grid', 'icon' => 'view_grid'],
  'bands_table.php' => ['title' => 'Table', 'icon' => 'view_table'],
  'bands_list.php' => ['title' => 'List', 'icon' => 'view_list'],
];

$bandViewOrder = array_keys($bandViews);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
$currentViewIndex = array_search($currentPage, $bandViewOrder, true);

if ($currentViewIndex === false) {
  $currentView = 'bands_grid.php';
  $nextView = 'bands_grid.php';
} else {
  $currentView = $bandViewOrder[$currentViewIndex];
  $nextView = $bandViewOrder[($currentViewIndex + 1) % count($bandViewOrder)];
}

$currentViewIcon = $bandViews[$currentView]['icon'];
$viewQuery = $currentViewIndex === false ? '' : (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY) ?? '');
$nextViewUrl = resolve_page_request('/' . $nextView, $viewQuery)['url'];
?>

<div class="top-line">

  <svg class="icons" aria-hidden="true">
    <title>Bands</title>
    <use href="icons.svg#band"></use>
  </svg>

  <h2>BANDS</h2>

  <a
    href="<?= htmlspecialchars($nextViewUrl, ENT_QUOTES, 'UTF-8') ?>"
    title="View"
    data-view-group="bands"
    data-view-grid="<?= htmlspecialchars(get_page_url('bands', 'grid', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-table="<?= htmlspecialchars(get_page_url('bands', 'table', $viewQuery), ENT_QUOTES, 'UTF-8') ?>"
    data-view-list="<?= htmlspecialchars(get_page_url('bands', 'list', $viewQuery), ENT_QUOTES, 'UTF-8') ?>">
    <svg class="icons">
      <use href="icons.svg#<?= htmlspecialchars($currentViewIcon, ENT_QUOTES, 'UTF-8') ?>"></use>
    </svg>
  </a>

</div>