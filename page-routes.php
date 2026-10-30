<?php

function get_page_routes()
{
  return [
    '' => ['grid' => 'index.php'],
    'bands' => ['grid' => 'bands_grid.php', 'table' => 'bands_table.php', 'list' => 'bands_list.php'],
    'artists' => ['grid' => 'artists_grid.php', 'table' => 'artists_table.php', 'list' => 'artists_list.php'],
    'releases' => ['grid' => 'releases_band_grid.php', 'table' => 'releases_band_table.php', 'list' => 'releases_band_list.php'],
    'related' => ['grid' => 'releases_related_grid.php', 'table' => 'releases_related_table.php', 'list' => 'releases_related_list.php'],
    'calendar' => ['grid' => 'calendar.php'],
    'about' => ['grid' => 'about.php'],
    'thanks' => ['grid' => 'thanks.php'],
    'stats' => ['grid' => 'stats.php'],
    'tree' => ['grid' => 'tree.php'],
  ];
}

function get_page_view_query($query, $view)
{
  $parts = [];
  foreach (explode('&', $query) as $part) {
    $key = urldecode(explode('=', $part, 2)[0]);
    if ($part !== '' && $key !== 'view' && strpos($key, 'view[') !== 0) {
      $parts[] = $part;
    }
  }
  if ($view !== 'grid') {
    $parts[] = 'view=' . $view;
  }
  return implode('&', $parts);
}

function get_page_url($section, $view = 'grid', $query = '')
{
  $routes = get_page_routes();
  if (!isset($routes[$section][$view])) {
    throw new InvalidArgumentException('Unknown page or view.');
  }
  if (count($routes[$section]) > 1) {
    $query = get_page_view_query($query, $view);
  }
  return '/' . $section . ($query !== '' ? '?' . $query : '');
}

function resolve_page_request($path, $query = '')
{
  $routes = get_page_routes();
  $section = trim($path, '/');
  $legacy = false;
  $view = 'grid';
  if (!isset($routes[$section]) || ($section === '' && $path !== '/')) {
    $section = null;
    foreach ($routes as $candidate => $views) {
      foreach ($views as $mode => $template) {
        if ($path === '/' . $template) {
          $section = $candidate;
          $view = $mode;
          $legacy = true;
          break 2;
        }
      }
    }
    if ($section === null) {
      return null;
    }
  }

  $parameters = [];
  parse_str($query, $parameters);
  if (!$legacy && count($routes[$section]) > 1 && isset($parameters['view'])) {
    if (!is_string($parameters['view']) || !isset($routes[$section][$parameters['view']])) {
      throw new InvalidArgumentException('Invalid view. Choose grid, table or list.');
    }
    $view = $parameters['view'];
  }

  $url = get_page_url($section, $view, $query);
  $redirect = $legacy || $path !== '/' . $section
    || (count($routes[$section]) > 1 && isset($parameters['view']) && $parameters['view'] === 'grid');
  return [
    'section' => $section,
    'view' => $view,
    'template' => $routes[$section][$view],
    'url' => $url,
    'canonical' => get_page_url($section, 'grid', $query),
    'redirect' => $redirect ? $url : null,
  ];
}
