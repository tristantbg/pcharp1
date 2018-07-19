<?php snippet('header') ?>

  <?php if ($page->text()->isNotEmpty()): ?>
    <div id="page-text">
      <?= $page->text()->kt() ?>
    </div>
  <?php endif ?>

<?php snippet('footer') ?>
