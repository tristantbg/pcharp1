<?php snippet('header') ?>

  <div id="about">
    <div id="about-description">
      <?= $page->text()->kt() ?>
    </div>
    <div id="contact">
      <?= $page->contact()->kt() ?>
    </div>
    <div id="credits">
      <?= $page->credits()->kt() ?>
    </div>
  </div>

<?php snippet('footer') ?>
