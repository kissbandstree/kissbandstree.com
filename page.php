<?php
require_once __DIR__ . '/page-routes.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);
$query = parse_url($requestUri, PHP_URL_QUERY) ?? '';
try {
  $route = resolve_page_request($path, $query);
} catch (InvalidArgumentException $error) {
  http_response_code(400);
  header('Content-Type: text/plain; charset=UTF-8');
  echo $error->getMessage();
  exit;
}

if ($route === null) {
  http_response_code(404);
  header('Content-Type: text/plain; charset=UTF-8');
  echo 'Page not found.';
  exit;
}
if ($route['redirect'] !== null) {
  header('Location: ' . $route['redirect'], true, 301);
  exit;
}

$_SERVER['SCRIPT_NAME'] = $_SERVER['PHP_SELF'] = '/' . $route['template'];
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . DIRECTORY_SEPARATOR . $route['template'];
require $_SERVER['SCRIPT_FILENAME'];
