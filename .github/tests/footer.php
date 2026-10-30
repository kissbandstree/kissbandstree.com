<?php
declare(strict_types=1);

$root = dirname(__DIR__, 2);
require_once $root . '/content.php';
chdir($root);

$lines = array_map(function ($line) {
  return html_entity_decode(strip_tags($line), ENT_QUOTES, 'UTF-8');
}, content_file('footer.txt', FILE_IGNORE_NEW_LINES));
if ($lines !== [
  'Copyright © Kiss Bands Tree. All rights reserved.',
  'Produced at Focus Studios by Ole Peko Sørensen.',
]) {
  throw new RuntimeException('Footer must contain the two requested credit lines.');
}

ob_start();
require $root . '/footer-branding.php';
$branding = ob_get_clean();
if (substr_count($branding, '<br />') !== 1
  || substr_count($branding, 'class="focus"') !== 1
  || strpos($branding, '<svg') === false
  || strpos($branding, 'class="footer-credit"') > strpos($branding, 'class="focus"')) {
  throw new RuntimeException('Footer branding must render two credit lines followed by one Focus logo.');
}

ob_start();
require $root . '/footer.php';
$footer = ob_get_clean();
if (strpos($footer, 'Document updated:') === false
  || strpos($footer, 'footer-socials') === false
  || strpos($footer, 'footer-socials') > strpos($footer, 'footer-branding')
  || substr_count($footer, 'class="focus"') !== 1) {
  throw new RuntimeException('Standard footer must retain metadata and social links above the branding.');
}

$tree = file_get_contents($root . '/tree.php');
if (strpos($tree, "require __DIR__ . '/footer-branding.php'") === false) {
  throw new RuntimeException('Tree must use the shared footer branding.');
}

$styles = file_get_contents($root . '/style.css');
if (preg_match('/\.focus svg\s*\{([^}]+)\}/', $styles, $logoRule) !== 1
  || preg_match('/\bheight:\s*32px\s*;/', $logoRule[1]) !== 1
  || preg_match('/\bwidth:\s*auto\s*;/', $logoRule[1]) !== 1) {
  throw new RuntimeException('Focus logo must match Pekosoft: 32px high with automatic width.');
}

echo "PASS: Footer credit lines, shared branding, standardized Focus logo and preserved metadata.\n";
