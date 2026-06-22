<?php
$bandFilesSorted = glob('bands/*.txt');
sort($bandFilesSorted, SORT_NATURAL | SORT_FLAG_CASE);
$bandsData = [];
foreach ($bandFilesSorted as $bandFile) {
  $slug = basename($bandFile, '.txt');
  $lines = file($bandFile, FILE_IGNORE_NEW_LINES);
  $name = trim($lines[0] ?? $slug);
  $years = trim($lines[1] ?? '');
  $bandsData[] = [
    'slug' => $slug,
    'name' => $name,
    'years' => $years,
  ];
}

$artistFilesSorted = glob('artists/*.txt');
sort($artistFilesSorted, SORT_NATURAL | SORT_FLAG_CASE);
$artistsData = [];
foreach ($artistFilesSorted as $artistFile) {
  $slug = basename($artistFile, '.txt');
  $lines = file($artistFile, FILE_IGNORE_NEW_LINES);
  $name = trim($lines[0] ?? $slug);
  $artistsData[] = [
    'slug' => $slug,
    'name' => $name,
  ];
}

$searchData = [
  'bands' => $bandsData,
  'artists' => $artistsData,
];
?>

<div class="top-line">
  <svg class="icons" aria-hidden="true" focusable="false">
    <title>Search</title>
    <use href="icons.svg#search"></use>
  </svg>
  <h2>SEARCH</h2>
  <svg class="icons show-pointer no-margin"
    onclick="toggleDiv('searchContainer')"
    tabindex="0" aria-label="Toggle search">
    <title>Collapse</title>
    <use href="icons.svg#triangle_down" />
  </svg>
</div>

<div class="filter-circles colored small-top-margin search-wrap" id="searchContainer">
  <div class="search-row">
    <input type="text" id="bandSearchInput" class="search-input" placeholder="BAND..." aria-label="Band" autocomplete="off">
  </div>

  <div id="bandSearchResults" class="search-results"></div>

  <div class="search-row">
    <input type="text" id="artistSearchInput" class="search-input" placeholder="ARTIST..." aria-label="Artist" autocomplete="off">
  </div>

  <div id="artistSearchResults" class="search-results"></div>
</div>

<script>
  window.kbtSearchData = <?php echo json_encode($searchData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>
<script src="/search.js"></script>
