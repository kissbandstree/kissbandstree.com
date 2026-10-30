<?php require_once __DIR__ . '/content.php'; ?>
<div class="footer-branding">
  <div class="footer-credit">
    <?php echo nl2br(content_text('footer.txt')); ?>
  </div>
  <a href="https://focusstudios.no" class="focus" aria-label="Focus Studios">
    <?php echo content_text(__DIR__ . '/img/focus_studios.svg'); ?>
  </a>
</div>
