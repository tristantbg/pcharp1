<?php snippet('header') ?>

<?php
if ($site->introImages()->isNotEmpty() && $introImage = $site->introImages()->toStructure()->shuffle()->first()->toFile()){
  echo '<div id="intro" class="' . $introImage->orientation() . '">';
  snippet('responsive-image', ['file' => $introImage]);
  echo '</div>';
}
?>

<div id="project-description">
	<div id="media-description"></div>
</div>

<div id="medias">
	<?php if ($results && $results->count() > 0): ?>
    <?php $idx = 0; $lastP = ''; ?>
		<?php foreach ($results as $key => $media): ?>
			<?php
			$project = $media->page();
			if($lastP != $project->uid()) {
			$idx = 1;
			} else {
			$idx++;
			}
			?>
			<?php if ($media->type() == 'image'): ?>
				<a
				class="media visible"
				<?php if ($query): ?>
				href="<?= $project->url().'?slide=img-'.$media->sliderIndex() ?>"
				<?php else: ?>
				href="<?= $project->url() ?>"
				<?php endif ?>
				data-id="<?= $media->uniqueId() ?>"
				data-page-id="<?= $project->uid() ?>"
				data-date="<?= $project->date('Y') ?>"
				style="width: <?= $media->width() ?>px; height: <?= $media->height() ?>px"
				>
					<div class="inner">
						<?php
						// $src = $media->width(50)->dataUri();
						$srcset = $media->width(500)->url() . ' 500w,';
						for ($i = 1000; $i <= 1500; $i += 500) $srcset .= $media->width($i)->url() . ' ' . $i . 'w,';
						?>
						<img class="media-element lazy lazyload"
						data-src="<?= $media->width(500)->url() ?>"
						data-srcset="<?= $srcset ?>"
						data-sizes="auto"
						data-optimumx="1.5"
						width="100%"
						height="100%" />
						<div class="bullet"></div>
						<?php if (!$query): ?>
						<div class="hidden-images"><?= $media->hiddenImages() ?></div>
						<?php endif ?>
					</div>
				</a>
			<?php endif ?>
      <?php
      $lastP = $project->uid();
      ?>
		<?php endforeach ?>
	<?php else: ?>
		<div class="row error">
			<span>Nothing found</span>
		</div>
	<?php endif ?>
</div>

<div id="timeline-date">
	<div class="inner"></div>
</div>

<div id="page-panel"></div>

<?php snippet('footer') ?>
