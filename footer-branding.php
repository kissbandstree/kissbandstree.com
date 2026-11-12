<?php require_once __DIR__ . '/functions.php'; ?>
<div class="footer-branding">
  <div class="footer-credit">
    <?php
    $footerText = preg_replace_callback('~(<a href="([^"]+)">)([^<]+)(</a>)~', function ($match) {
      return $match[1] . $match[3] . external_link_icon(html_entity_decode($match[2], ENT_QUOTES, 'UTF-8')) . $match[4];
    }, content_text('footer.txt'));
    if ($footerText === null) {
      throw new RuntimeException('Unable to render footer links.');
    }
    echo nl2br($footerText);
    ?>
  </div>
  <a href="https://focusstudios.no" class="focus" aria-label="Focus Studios" title="Focus Studios">
    <?php echo content_text(__DIR__ . '/img/focus_studios.svg'); ?>
  </a>
</div>
