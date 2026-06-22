<?php
$defaultOrigin = 'https://kissbandstree.com';
$requestHost = isset($_SERVER['HTTP_HOST']) ? trim((string) $_SERVER['HTTP_HOST']) : '';
$requestUri = isset($_SERVER['REQUEST_URI']) ? trim((string) $_SERVER['REQUEST_URI']) : '/';

if ($requestHost !== '') {
	// Keep localhost/dev on http; use https elsewhere.
	$isLocalHost = stripos($requestHost, 'localhost') === 0 || stripos($requestHost, '127.0.0.1') === 0;
	$origin = ($isLocalHost ? 'http://' : 'https://') . $requestHost;
} else {
	$origin = $defaultOrigin;
}

$ogUrl = $origin . ($requestUri !== '' ? $requestUri : '/');
$canonicalUrl = $defaultOrigin . ($requestUri !== '' ? $requestUri : '/');

$rawImage = isset($thumb) && is_string($thumb) && $thumb !== ''
	? $thumb
	: '/img/icon.svg';
if (preg_match('/^https?:\/\//i', $rawImage) === 1) {
	$ogImage = $rawImage;
} elseif (strpos($rawImage, '/') === 0) {
	$ogImage = $origin . $rawImage;
} else {
	$ogImage = $origin . '/' . $rawImage;
}

$ogTitle = isset($pagetitle) && is_string($pagetitle) && $pagetitle !== ''
	? $pagetitle
	: 'Kiss Bands Tree';
?>
<meta property="og:image" content="<?php echo htmlspecialchars($ogImage, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:title" content="<?php echo htmlspecialchars($ogTitle, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo htmlspecialchars($ogUrl, ENT_QUOTES, 'UTF-8'); ?>">
<meta property="og:description" content="All the bands with members of the American rock band Kiss.">

<title>Kiss Bands Tree</title>
<link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="style.css">
<script src="/sort_table.js"></script>
<script src="/search_table.js"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">