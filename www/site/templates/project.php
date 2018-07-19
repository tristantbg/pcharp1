<?php snippet('header') ?>


<div id="project-medias">

  <?php foreach($medias as $field): ?>
    <div class="project-image <?= e($field->toFile(), $field->toFile()->orientation()) ?>">
      <?php snippet('responsive-image', ['field' => $field, 'withCaption' => true]) ?>
    </div>
  <?php endforeach ?>

</div>

<?php if ($page->text()->isNotEmpty()): ?>
  <div id="project-description">
    <?= $page->text()->kt() ?>
  </div>
<?php endif ?>

<?php if($page->sound()->isNotEmpty() && $audio = $page->sound()->toFile()): ?>
  <audio controls autoplay>
    <source src="<?= $audio->url() ?>" type="<?= $audio->mime() ?>">
  </audio>
<?php endif ?>

<div id="right-nav">

  <?php if ($page->sound()->isNotEmpty()): ?>
    <div id="audio-control"><a href="">Mute</a></div>
  <?php endif ?>
  <a href="<?= $site->url() ?>" id="close">Close</a>
</div>

<?php snippet('footer') ?>
