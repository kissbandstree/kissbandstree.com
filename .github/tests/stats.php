<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
chdir($root);
require_once $root . '/functions.php';

function check_stats($condition, $message)
{
  if (!$condition) {
    throw new RuntimeException($message);
  }
}

foreach ([
  ['2026 - present', true],
  ['Nov 2018 - PRESENT', true],
  ["  present\r\n", true],
  ['1974', false],
  ['1970 - 1971', false],
  ['representation', false],
  ['presently', false],
  ['', false],
] as [$dates, $expected]) {
  check_stats(is_band_active($dates) === $expected, 'Active-date classification failed: ' . $dates);
}

set_error_handler(function ($severity, $message, $file, $line) {
  throw new ErrorException($message, 0, $severity, $file, $line);
});
$_SERVER['DOCUMENT_ROOT'] = $root;
$_SERVER['SCRIPT_FILENAME'] = $root . '/stats.php';
$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'] = '/stats.php';
$_SERVER['HTTP_HOST'] = 'localhost:8003';

ob_start();
try {
  require $root . '/stats.php';
  $html = ob_get_contents();
} finally {
  ob_end_clean();
}
$total_bands = count(content_records('bands'));
check_stats(array_sum($active_band_counts) === $total_bands, 'Active counts do not cover every lineup.');
check_stats(substr_count($html, 'class="cake-pie"') === 7, 'Expected seven Stats charts.');
foreach ($active_band_counts as $key => $count) {
  check_stats(str_contains($html, "data-group='active-bands' data-member='$key'>" . strtoupper($key) . "</span>: $count"),
    'Active legend count missing: ' . $key);
  check_stats(substr_count($html, 'data-group="active-bands" data-member="' . $key . '"') === ($count > 0 ? 1 : 0),
    'Active chart slice count differs: ' . $key);
}

ob_start();
try {
  require $root . '/active_band.php';
  $active_widget = ob_get_contents();
} finally {
  ob_end_clean();
}
check_stats(substr_count($active_widget, "<div class='band-item'>") === $active_band_counts['yes'],
  'Stats active count differs from the homepage.');
restore_error_handler();
echo "PASS: active-date cases, seven charts, YES/NO legends, all $total_bands lineups and homepage agreement.\n";
