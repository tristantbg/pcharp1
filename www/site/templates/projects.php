<?php snippet('header') ?>

<?php
	$galleryPage = $page;
	$galleryChilds = $galleryPage->children()->visible()->filterBy('inIntro','true');
	$galleryImages = new Collection();
	foreach ($galleryChilds as $c) {
		 foreach ($c->medias()->toStructure() as $i) {
				 if($image = $i->toFile()) {
            if (!$image->notinintro()->bool()) {
              $galleryImages->data[] = $i->toFile();
            }
         }
		 }
	}

  $introImage = $galleryImages->shuffle()->first();

  echo '<div id="intro" class="' . $introImage->orientation() . '">';
  snippet('responsive-image', ['file' => $introImage]);
  echo '</div>';
?>

<?php snippet('footer') ?>
