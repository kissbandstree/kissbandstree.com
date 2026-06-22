<!DOCTYPE html>
<html lang="en">
<head>
<?php require($_SERVER['DOCUMENT_ROOT'] . "/head.php"); ?>
<link rel="stylesheet" href="stats.css?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/stats.css') ?>">
</head>

<body>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/toc.php"); ?>

<div class="top-line">
  <svg class="icons" aria-hidden="true" focusable="false">
    <title>Stats</title>
    <use href="icons.svg#cake_diagram"></use>
  </svg>

  <h2>STATS</h2>

  <svg class="icons" aria-hidden="true" focusable="false">
    <title>Stats</title>
    <use href="icons.svg#cake_diagram"></use>
  </svg>
</div>

<?php
  $bands = glob('bands/*.txt');
  $artists = glob('artists/*.txt');
  $bands_photos = glob('photos_bands/*.png'); 
  $artists_photos = glob('photos_artists/*.png');

  function count_from_list_file($path) {
    $rows = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($rows === false) {
      return 0;
    }
    return count($rows);
  }

  function names_tooltip($names) {
    if (empty($names)) {
      return 'No entries';
    }
    $sorted_names = array_values(array_unique($names));
    natcasesort($sorted_names);
    return htmlspecialchars(implode("\n", $sorted_names), ENT_QUOTES, 'UTF-8');
  }

  function pie_slice_path($cx, $cy, $radius, $start_angle, $end_angle) {
    $start_rad = deg2rad($start_angle - 90);
    $end_rad = deg2rad($end_angle - 90);

    $x1 = $cx + $radius * cos($start_rad);
    $y1 = $cy + $radius * sin($start_rad);
    $x2 = $cx + $radius * cos($end_rad);
    $y2 = $cy + $radius * sin($end_rad);

    $large_arc = ($end_angle - $start_angle) > 180 ? 1 : 0;
    return sprintf(
      'M %.3F %.3F L %.3F %.3F A %.3F %.3F 0 %d 1 %.3F %.3F Z',
      $cx,
      $cy,
      $x1,
      $y1,
      $radius,
      $radius,
      $large_arc,
      $x2,
      $y2
    );
  }

  function svg_pie($data, $colors, $links = [], $titles = [], $group = '', $size = 190) {
    $total = array_sum($data);
    $cx = $size / 2;
    $cy = $size / 2;
    $radius = $size / 2;

    if ($total <= 0) {
      return '<svg class="cake-pie" viewBox="0 0 ' . $size . ' ' . $size . '" aria-hidden="true"><circle cx="' . $cx . '" cy="' . $cy . '" r="' . $radius . '" fill="#7f8ea8" /></svg>';
    }

    $svg = '<svg class="cake-pie" viewBox="0 0 ' . $size . ' ' . $size . '">';
    $start = 0.0;

    foreach ($data as $key => $value) {
      if ($value <= 0) {
        continue;
      }

      $end = $start + (($value / $total) * 360.0);
      $path = pie_slice_path($cx, $cy, $radius, $start, $end);
      $fill = htmlspecialchars($colors[$key] ?? '#7f8ea8', ENT_QUOTES, 'UTF-8');
      $slice_title = htmlspecialchars($titles[$key] ?? '', ENT_QUOTES, 'UTF-8');
      $slice_key = htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8');
      $slice_group = htmlspecialchars((string)$group, ENT_QUOTES, 'UTF-8');
      $slice = '<path d="' . $path . '" fill="' . $fill . '" data-group="' . $slice_group . '" data-member="' . $slice_key . '" tabindex="0" role="button">';
      if ($slice_title !== '') {
        $slice .= '<title>' . $slice_title . '</title>';
      }
      $slice .= '</path>';
      $svg .= $slice;

      $start = $end;
    }

    $svg .= '</svg>';
    return $svg;
  }

  $kiss_members = [
    'gene_simmons' => ['label' => 'GENE SIMMONS', 'count' => count_from_list_file('bands_list/gene_simmons.txt')],
    'paul_stanley' => ['label' => 'PAUL STANLEY', 'count' => count_from_list_file('bands_list/paul_stanley.txt')],
    'peter_criss' => ['label' => 'PETER CRISS', 'count' => count_from_list_file('bands_list/peter_criss.txt')],
    'ace_frehley' => ['label' => 'ACE FREHLEY', 'count' => count_from_list_file('bands_list/ace_frehley.txt')],
    'eric_carr' => ['label' => 'ERIC CARR', 'count' => count_from_list_file('bands_list/eric_carr.txt')],
    'vinnie_vincent' => ['label' => 'VINNIE VINCENT', 'count' => count_from_list_file('bands_list/vinnie_vincent.txt')],
    'mark_st_john' => ['label' => 'MARK ST. JOHN', 'count' => count_from_list_file('bands_list/mark_st_john.txt')],
    'bruce_kulick' => ['label' => 'BRUCE KULICK', 'count' => count_from_list_file('bands_list/bruce_kulick.txt')],
    'eric_singer' => ['label' => 'ERIC SINGER', 'count' => count_from_list_file('bands_list/eric_singer.txt')],
    'tommy_thayer' => ['label' => 'TOMMY THAYER', 'count' => count_from_list_file('bands_list/tommy_thayer.txt')],
  ];

  $member_colors = [
    'gene_simmons' => 'var(--grey1)',
    'paul_stanley' => 'var(--grey2)',
    'peter_criss' => 'var(--grey1)',
    'ace_frehley' => 'var(--grey2)',
    'eric_carr' => 'var(--grey1)',
    'vinnie_vincent' => 'var(--grey2)',
    'mark_st_john' => 'var(--grey1)',
    'bruce_kulick' => 'var(--grey2)',
    'eric_singer' => 'var(--grey1)',
    'tommy_thayer' => 'var(--grey2)',
  ];

  $male_count = 0;
  $female_count = 0;
  $artist_with_counts = [];
  $artist_with_names = [];
  $lineup_names_by_member = [];
  foreach ($kiss_members as $member_key => $member_data) {
    $artist_with_counts[$member_key] = 0;
    $artist_with_names[$member_key] = [];
    $lineup_rows = file('bands_list/' . $member_key . '.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $lineup_names_by_member[$member_key] = $lineup_rows === false ? [] : $lineup_rows;
  }

  foreach ($artists as $artist_file) {
    $artist_lines = file($artist_file, FILE_IGNORE_NEW_LINES);
    $artist_name = trim($artist_lines[0] ?? '');
    $gender = strtolower(trim($artist_lines[3] ?? ''));
    if ($gender === 'male') {
      $male_count++;
    } elseif ($gender === 'female') {
      $female_count++;
    }

    $with_members = trim($artist_lines[5] ?? '');
    if ($with_members !== '') {
      $member_list = array_map(function ($name) {
        return strtolower(trim($name));
      }, explode(',', $with_members));
      foreach ($kiss_members as $member_key => $member_data) {
        if (in_array(strtolower($member_data['label']), $member_list, true)) {
          $artist_with_counts[$member_key]++;
          if ($artist_name !== '') {
            $artist_with_names[$member_key][] = $artist_name;
          }
        }
      }
    }
  }

  $bands_member_counts = [];
  $artists_member_counts = [];
  foreach ($kiss_members as $member_key => $member_data) {
    $bands_member_counts[$member_key] = $member_data['count'];
    $artists_member_counts[$member_key] = $artist_with_counts[$member_key];
  }

  $gender_counts = [
    'male' => $male_count,
    'female' => $female_count,
  ];

  $band_photo_counts = [
    'yes' => count($bands_photos),
    'no' => max(0, count($bands) - count($bands_photos)),
  ];

  $artist_photo_counts = [
    'yes' => count($artists_photos),
    'no' => max(0, count($artists) - count($artists_photos)),
  ];

  $gender_colors = [
    'male' => 'var(--grey1)',
    'female' => 'var(--grey2)',
  ];

  $photo_colors = [
    'yes' => 'var(--grey1)',
    'no' => 'var(--grey2)',
  ];

  $instrument_counts = [];
  $instrument_artists = [];
  foreach ($artists as $artist_file) {
    $artist_lines = file($artist_file, FILE_IGNORE_NEW_LINES);
    $artist_name = trim($artist_lines[0] ?? '');
    $raw_instruments = trim($artist_lines[1] ?? '');
    if ($raw_instruments === '') {
      continue;
    }
    foreach (explode(',', $raw_instruments) as $instr) {
      $instr = trim($instr);
      if ($instr === '') {
        continue;
      }
      $instr_key = strtolower($instr);
      $instrument_counts[$instr_key] = ($instrument_counts[$instr_key] ?? 0) + 1;
      if ($artist_name !== '') {
        $instrument_artists[$instr_key][] = $artist_name;
      }
    }
  }
  ksort($instrument_counts);

  $pie_colors_pool = ['var(--grey1)', 'var(--grey2)'];
  $instrument_colors = [];
  $i = 0;
  foreach ($instrument_counts as $key => $count) {
    $instrument_colors[$key] = $pie_colors_pool[$i % 2];
    $i++;
  }

  $instrument_titles = [];
  foreach ($instrument_counts as $key => $count) {
    $instrument_titles[$key] = ucwords($key) . ': ' . $count;
  }

  $instrument_pie_svg = svg_pie($instrument_counts, $instrument_colors, [], $instrument_titles, 'instruments');

  $member_links = [];
  foreach ($kiss_members as $member_key => $member_data) {
    $member_links[$member_key] = 'artist.php?a=' . $member_key;
  }

  $member_titles = [];
  foreach ($kiss_members as $member_key => $member_data) {
    $member_titles[$member_key] = ucwords(strtolower($member_data['label']));
  }

  $gender_titles = [
    'male' => 'Male',
    'female' => 'Female',
  ];

  $photo_titles = [
    'yes' => 'Yes',
    'no' => 'No',
  ];

  $band_photo_pie_svg = svg_pie($band_photo_counts, $photo_colors, [], $photo_titles, 'band-photos');
  $artist_photo_pie_svg = svg_pie($artist_photo_counts, $photo_colors, [], $photo_titles, 'artist-photos');
  $bands_pie_svg = svg_pie($bands_member_counts, $member_colors, $member_links, $member_titles, 'bands');
  $artists_pie_svg = svg_pie($artists_member_counts, $member_colors, $member_links, $member_titles, 'artists');
  $gender_pie_svg = svg_pie($gender_counts, $gender_colors, [], $gender_titles, 'gender');
?>

<div class="standard">
  <div class="stats-row">
    <h2>BANDS WITH</h2>
    <?php echo $bands_pie_svg; ?>
    <p class="stats-text">
<?php
  echo "<a class='stats-member' data-group='bands' data-member='gene_simmons' href='artist.php?a=gene_simmons' title='" . names_tooltip($lineup_names_by_member['gene_simmons']) . "'>" . $kiss_members['gene_simmons']['label'] . "</a>: " . $kiss_members['gene_simmons']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='paul_stanley' href='artist.php?a=paul_stanley' title='" . names_tooltip($lineup_names_by_member['paul_stanley']) . "'>" . $kiss_members['paul_stanley']['label'] . "</a>: " . $kiss_members['paul_stanley']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='peter_criss' href='artist.php?a=peter_criss' title='" . names_tooltip($lineup_names_by_member['peter_criss']) . "'>" . $kiss_members['peter_criss']['label'] . "</a>: " . $kiss_members['peter_criss']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='ace_frehley' href='artist.php?a=ace_frehley' title='" . names_tooltip($lineup_names_by_member['ace_frehley']) . "'>" . $kiss_members['ace_frehley']['label'] . "</a>: " . $kiss_members['ace_frehley']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='eric_carr' href='artist.php?a=eric_carr' title='" . names_tooltip($lineup_names_by_member['eric_carr']) . "'>" . $kiss_members['eric_carr']['label'] . "</a>: " . $kiss_members['eric_carr']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='vinnie_vincent' href='artist.php?a=vinnie_vincent' title='" . names_tooltip($lineup_names_by_member['vinnie_vincent']) . "'>" . $kiss_members['vinnie_vincent']['label'] . "</a>: " . $kiss_members['vinnie_vincent']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='mark_st_john' href='artist.php?a=mark_st_john' title='" . names_tooltip($lineup_names_by_member['mark_st_john']) . "'>" . $kiss_members['mark_st_john']['label'] . "</a>: " . $kiss_members['mark_st_john']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='bruce_kulick' href='artist.php?a=bruce_kulick' title='" . names_tooltip($lineup_names_by_member['bruce_kulick']) . "'>" . $kiss_members['bruce_kulick']['label'] . "</a>: " . $kiss_members['bruce_kulick']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='eric_singer' href='artist.php?a=eric_singer' title='" . names_tooltip($lineup_names_by_member['eric_singer']) . "'>" . $kiss_members['eric_singer']['label'] . "</a>: " . $kiss_members['eric_singer']['count'] . "<br>";
  echo "<a class='stats-member' data-group='bands' data-member='tommy_thayer' href='artist.php?a=tommy_thayer' title='" . names_tooltip($lineup_names_by_member['tommy_thayer']) . "'>" . $kiss_members['tommy_thayer']['label'] . "</a>: " . $kiss_members['tommy_thayer']['count'];
?>
    </p>
  </div>

  <div class="stats-row">
    <h2>BAND PHOTOS</h2>
    <?php echo $band_photo_pie_svg; ?>
    <p class="stats-text">
<?php
  echo "<span class='stats-band-photo' data-member='yes'>YES</span>: " . $band_photo_counts['yes'] . "<br>";
  echo "<span class='stats-band-photo' data-member='no'>NO</span>: " . $band_photo_counts['no'];
?>
    </p>
  </div>

  <div class="stats-row">
    <h2>ARTISTS WITH</h2>
    <?php echo $artists_pie_svg; ?>
    <p class="stats-text">
<?php
  echo "<a class='stats-member' data-group='artists' data-member='gene_simmons' href='artist.php?a=gene_simmons' title='" . names_tooltip($artist_with_names['gene_simmons']) . "'>" . $kiss_members['gene_simmons']['label'] . "</a>: " . $artist_with_counts['gene_simmons'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='paul_stanley' href='artist.php?a=paul_stanley' title='" . names_tooltip($artist_with_names['paul_stanley']) . "'>" . $kiss_members['paul_stanley']['label'] . "</a>: " . $artist_with_counts['paul_stanley'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='peter_criss' href='artist.php?a=peter_criss' title='" . names_tooltip($artist_with_names['peter_criss']) . "'>" . $kiss_members['peter_criss']['label'] . "</a>: " . $artist_with_counts['peter_criss'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='ace_frehley' href='artist.php?a=ace_frehley' title='" . names_tooltip($artist_with_names['ace_frehley']) . "'>" . $kiss_members['ace_frehley']['label'] . "</a>: " . $artist_with_counts['ace_frehley'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='eric_carr' href='artist.php?a=eric_carr' title='" . names_tooltip($artist_with_names['eric_carr']) . "'>" . $kiss_members['eric_carr']['label'] . "</a>: " . $artist_with_counts['eric_carr'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='vinnie_vincent' href='artist.php?a=vinnie_vincent' title='" . names_tooltip($artist_with_names['vinnie_vincent']) . "'>" . $kiss_members['vinnie_vincent']['label'] . "</a>: " . $artist_with_counts['vinnie_vincent'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='mark_st_john' href='artist.php?a=mark_st_john' title='" . names_tooltip($artist_with_names['mark_st_john']) . "'>" . $kiss_members['mark_st_john']['label'] . "</a>: " . $artist_with_counts['mark_st_john'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='bruce_kulick' href='artist.php?a=bruce_kulick' title='" . names_tooltip($artist_with_names['bruce_kulick']) . "'>" . $kiss_members['bruce_kulick']['label'] . "</a>: " . $artist_with_counts['bruce_kulick'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='eric_singer' href='artist.php?a=eric_singer' title='" . names_tooltip($artist_with_names['eric_singer']) . "'>" . $kiss_members['eric_singer']['label'] . "</a>: " . $artist_with_counts['eric_singer'] . "<br>";
  echo "<a class='stats-member' data-group='artists' data-member='tommy_thayer' href='artist.php?a=tommy_thayer' title='" . names_tooltip($artist_with_names['tommy_thayer']) . "'>" . $kiss_members['tommy_thayer']['label'] . "</a>: " . $artist_with_counts['tommy_thayer'];
?>
    </p>
  </div>

  <div class="stats-row">
    <h2>ARTIST PHOTOS</h2>
    <?php echo $artist_photo_pie_svg; ?>
    <p class="stats-text">
<?php
  echo "<span class='stats-artist-photo' data-member='yes'>YES</span>: " . $artist_photo_counts['yes'] . "<br>";
  echo "<span class='stats-artist-photo' data-member='no'>NO</span>: " . $artist_photo_counts['no'];
?>
    </p>
  </div>

  <div class="stats-row">
    <h2>INSTRUMENTS</h2>
    <?php echo $instrument_pie_svg; ?>
    <p class="stats-text">
<?php
  foreach ($instrument_counts as $instr_key => $count) {
    $tooltip = names_tooltip($instrument_artists[$instr_key] ?? []);
    echo "<span class='stats-instr' data-member='" . htmlspecialchars($instr_key, ENT_QUOTES, 'UTF-8') . "' title='" . $tooltip . "'>" . htmlspecialchars(strtoupper($instr_key), ENT_QUOTES, 'UTF-8') . "</span>: " . $count . "<br>";
  }
?>
    </p>
  </div>

  <div class="stats-row">
    <h2>GENDER</h2>
    <?php echo $gender_pie_svg; ?>
    <p class="stats-text">
<?php
  echo "<span class='stats-gender' data-member='male'>MALE</span>: " . $male_count . "<br>";
  echo "<span class='stats-gender' data-member='female'>FEMALE</span>: " . $female_count;
?>
    </p>
  </div>
</div>

<?php require($_SERVER['DOCUMENT_ROOT'] . "/footer.php"); ?>

<script src="stats.js?v=<?= filemtime($_SERVER['DOCUMENT_ROOT'] . '/stats.js') ?>"></script>

</body>
</html>