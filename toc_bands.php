<?php
$bandViews = [
  'bands_grid.php' => ['title' => 'Grid', 'icon' => 'view_grid'],
  'bands_table.php' => ['title' => 'Table', 'icon' => 'view_table'],
  'bands_list.php' => ['title' => 'List', 'icon' => 'view_list'],
];

$bandViewOrder = array_keys($bandViews);
$currentPage = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$currentViewIndex = array_search($currentPage, $bandViewOrder, true);

if ($currentViewIndex === false) {
  $currentView = 'bands_grid.php';
  $nextView = 'bands_grid.php';
} else {
  $currentView = $bandViewOrder[$currentViewIndex];
  $nextView = $bandViewOrder[($currentViewIndex + 1) % count($bandViewOrder)];
}

$currentViewIcon = $bandViews[$currentView]['icon'];
?>

<div class="top-line">

  <svg class="icons" aria-hidden="true">
    <title>Bands</title>
    <use href="icons.svg#band"></use>
  </svg>

  <h2>BANDS</h2>

  <a
    href="<?= htmlspecialchars($nextView, ENT_QUOTES, 'UTF-8') ?>"
    title="View"
    data-view-group="bands"
    data-view-grid="bands_grid.php"
    data-view-table="bands_table.php"
    data-view-list="bands_list.php">
    <svg class="icons">
      <use href="icons.svg#<?= htmlspecialchars($currentViewIcon, ENT_QUOTES, 'UTF-8') ?>"></use>
    </svg>
  </a>

</div>