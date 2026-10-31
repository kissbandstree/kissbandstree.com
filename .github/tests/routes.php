<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/page-routes.php';

$checks = 0;
function check_route($condition, $message)
{
  global $checks;
  if (!$condition) {
    throw new RuntimeException($message);
  }
  $checks++;
}

$apache = file_get_contents(dirname(__DIR__, 2) . '/.htaccess');
check_route(preg_match('/^DirectorySlash Off$/m', $apache) === 1, 'Apache must not force directory slashes on clean page routes.');
preg_match_all('/^RewriteRule (\S+) page\.php \[L\]$/m', $apache, $matches);
$patterns = $matches[1];
check_route(count($patterns) === 3, 'Missing Apache page dispatch rules.');
function apache_routes_path($path)
{
  global $patterns;
  foreach ($patterns as $pattern) {
    if (preg_match('~' . $pattern . '~', ltrim($path, '/')) === 1) {
      return true;
    }
  }
  return false;
}

$query = 'filter=Tommy%20Thayer&tag=one&tag=two';
foreach (get_page_routes() as $section => $views) {
  foreach ($views as $view => $template) {
    $clean = '/' . $section;
    $viewQuery = count($views) > 1 && $view !== 'grid' ? '&view=' . $view : '';
    $route = resolve_page_request($clean, $query . $viewQuery);
    check_route($route['template'] === $template && $route['redirect'] === null, 'Wrong clean route: ' . $template);
    check_route($route['url'] === $clean . '?' . $query . $viewQuery, 'Changed query values: ' . $template);
    check_route($route['canonical'] === $clean . '?' . $query, 'Wrong canonical route: ' . $template);
    $legacy = resolve_page_request('/' . $template, $query);
    check_route($legacy['redirect'] === $route['url'], 'Wrong legacy redirect: ' . $template);
    check_route(apache_routes_path($clean) && apache_routes_path('/' . $template), 'Apache misses route: ' . $template);
    if (count($views) > 1) {
      $conflict = resolve_page_request('/' . $template, 'view=list&' . $query . '&view[]=table');
      check_route($conflict['redirect'] === $route['url'], 'Legacy query overrode original view: ' . $template);
    }
  }
  if ($section !== '') {
    check_route(resolve_page_request('/' . $section . '/')['redirect'] === '/' . $section, 'Trailing slash did not redirect.');
  }
}

foreach (['bands', 'artists', 'releases'] as $section) {
  $sectionDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $section;
  $createdDirectory = !is_dir($sectionDirectory);
  if ($createdDirectory && !mkdir($sectionDirectory)) {
    throw new RuntimeException('Could not create route directory fixture: ' . $section);
  }
  try {
    $slashRoute = resolve_page_request('/' . $section . '/');
    check_route($slashRoute['redirect'] === '/' . $section, 'Physical section directory changed canonical routing: ' . $section);
    check_route(resolve_page_request('/' . $section)['redirect'] === null, 'Physical section directory redirected the clean page: ' . $section);
  } finally {
    if ($createdDirectory) {
      rmdir($sectionDirectory);
    }
  }
}
foreach (['bands', 'artists', 'releases', 'related'] as $section) {
  check_route(resolve_page_request('/' . $section, 'view=grid&x=1')['redirect'] === '/' . $section . '?x=1', 'Explicit Grid did not normalize.');
  foreach (['view=bad', 'view=', 'view[]=table', 'view[mode]=table'] as $invalid) {
    try {
      resolve_page_request('/' . $section, $invalid);
      throw new RuntimeException('Invalid view accepted: ' . $invalid);
    } catch (InvalidArgumentException $expected) {
      $checks++;
    }
  }
}
foreach (['/missing', '/missing.php', '/band.php', '/artist.php', '/release_band.php', '/page.php', '/page-routes.php', '/icons.svg'] as $path) {
  check_route(resolve_page_request($path) === null && !apache_routes_path($path), 'Page router captured unrelated path: ' . $path);
}

if (($argv[1] ?? '') === '--http') {
  $base = rtrim($argv[2] ?? 'http://localhost:8003', '/');
  function request_route($path)
  {
    global $base;
    $context = stream_context_create(['http' => ['ignore_errors' => true, 'follow_location' => 0, 'timeout' => 60]]);
    $body = file_get_contents($base . $path, false, $context);
    if ($body === false) {
      throw new RuntimeException('HTTP request failed: ' . $path);
    }
    $headers = http_get_last_response_headers();
    preg_match('/HTTP\/\S+ (\d+)/', $headers[0], $status);
    $response = ['status' => (int) $status[1], 'body' => $body];
    foreach (array_slice($headers, 1) as $header) {
      $pair = explode(':', $header, 2);
      if (count($pair) === 2) {
        $response['headers'][strtolower($pair[0])] = trim($pair[1]);
      }
    }
    check_route(preg_match('/(?:Warning|Fatal error|Parse error|Notice|Deprecated):/', strip_tags($body)) !== 1, 'PHP diagnostics: ' . $path);
    return $response;
  }
  foreach (['bands', 'artists', 'releases'] as $section) {
    $sectionDirectory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . $section;
    $createdDirectory = !is_dir($sectionDirectory);
    if ($createdDirectory && !mkdir($sectionDirectory)) {
      throw new RuntimeException('Could not create HTTP route directory fixture: ' . $section);
    }
    try {
      $slash = request_route('/' . $section . '/?view=list&' . $query);
      check_route($slash['status'] === 301 && ($slash['headers']['location'] ?? '') === '/' . $section . '?' . $query . '&view=list', 'Physical section slash did not normalize: ' . $section);
      $response = request_route('/' . $section . '?view=list&' . $query);
      check_route($response['status'] === 200, 'Physical section route failed: ' . $section);
      $canonical = htmlspecialchars('https://kissbandstree.com/' . $section . '?' . $query, ENT_QUOTES, 'UTF-8');
      check_route(strpos($response['body'], 'rel="canonical" href="' . $canonical . '"') !== false, 'Wrong physical section canonical URL: ' . $section);
      check_route(strpos($response['body'], 'icons.svg#view_list') !== false, 'Wrong physical section view: ' . $section);
    } finally {
      if ($createdDirectory) {
        rmdir($sectionDirectory);
      }
    }
  }
  function view_switch_url($body)
  {
    $document = new DOMDocument();
    $document->loadHTML($body, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
    $links = (new DOMXPath($document))->query('//a[@title="View"]');
    return $links->length === 1 ? $links->item(0)->getAttribute('href') : null;
  }
  foreach (get_page_routes() as $section => $views) {
    foreach ($views as $view => $template) {
      $url = '/' . $section . ($view !== 'grid' ? '?view=' . $view : '');
      $response = request_route($url);
      check_route($response['status'] === 200, 'Clean page failed: ' . $url);
      check_route(strpos($response['body'], 'rel="canonical" href="https://kissbandstree.com/' . $section . '"') !== false, 'Wrong HTTP canonical: ' . $url);
      check_route(strpos($response['body'], 'property="og:url" content="' . htmlspecialchars($base . $url, ENT_QUOTES, 'UTF-8') . '"') !== false, 'Wrong sharing URL: ' . $url);
      if (count($views) > 1) {
        check_route(strpos($response['body'], 'icons.svg#view_' . $view) !== false, 'Wrong rendered view: ' . $url);
        $next = ['grid' => 'table', 'table' => 'list', 'list' => 'grid'][$view];
        $nextUrl = '/' . $section . ($next !== 'grid' ? '?view=' . $next : '');
        check_route(view_switch_url($response['body']) === $nextUrl, 'Wrong view switch: ' . $url);
      }
      $legacy = request_route('/' . $template . '?' . $query);
      $target = '/' . $section . '?' . $query . ($view !== 'grid' ? '&view=' . $view : '');
      check_route($legacy['status'] === 301 && ($legacy['headers']['location'] ?? '') === $target, 'HTTP legacy redirect failed: ' . $template);
    }
    if ($section !== '') {
      $slash = request_route('/' . $section . '/?x=1');
      check_route($slash['status'] === 301 && ($slash['headers']['location'] ?? '') === '/' . $section . '?x=1', 'HTTP slash redirect failed.');
    }
  }
  foreach (['bands', 'artists', 'releases', 'related'] as $section) {
    $filtered = request_route('/' . $section . '?view=table&' . $query);
    check_route(view_switch_url($filtered['body']) === '/' . $section . '?' . $query . '&view=list', 'View switch lost query parameters.');
    foreach (['view=bad', 'view=', 'view[]=table'] as $invalid) {
      check_route(request_route('/' . $section . '?' . $invalid)['status'] === 400, 'HTTP invalid view accepted.');
    }
    $grid = request_route('/' . $section . '?view=grid&x=1');
    check_route($grid['status'] === 301 && ($grid['headers']['location'] ?? '') === '/' . $section . '?x=1', 'HTTP Grid normalization failed.');
    $conflict = request_route('/' . get_page_routes()[$section]['table'] . '?view=list&x=1');
    check_route($conflict['status'] === 301 && ($conflict['headers']['location'] ?? '') === '/' . $section . '?x=1&view=table', 'HTTP conflicting legacy view failed.');
  }
  foreach (['/missing-clean-route', '/missing-clean-route.php', '/page.php'] as $path) {
    check_route(request_route($path)['status'] === 404, 'Missing page did not return 404.');
  }
  foreach (['/style.css', '/icons.svg', '/png/og/calendar.png', '/band.php?a=kiss_1', '/artist.php?a=paul_stanley'] as $path) {
    check_route(request_route($path)['status'] === 200, 'Existing resource or detail page failed: ' . $path);
  }
  $band = request_route('/band.php?a=kiss_1');
  check_route(view_switch_url($band['body']) === '/bands', 'Band identifier leaked into section navigation.');
}
echo "Passed: $checks routing checks.\n";
