<?php snippet('header') ?>

<div id="page-panel" class="visible">
  <div class="text-content" lang="<?php e($page->textFr()->isNotEmpty(), 'en', 'none') ?>">
    <div event-target="lang-switch"></div>
    <div class="text-en"><?= $page->text()->kt() ?></div>
    <?php if ($page->textFr()->isNotEmpty()): ?>
      <div class="text-fr"><?= $page->textFr()->kt() ?></div>
    <?php endif ?>
  </div>
</div>

<?php snippet('footer') ?>
