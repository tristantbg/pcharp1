<?php if ($page->isHomepage()): ?>
	<?php
		$intro = $site->index()->filterBy('intendedTemplate', 'loop')->visible()->shuffle()->first();
		if($intro) $introImages = $intro->medias()->toStructure();
	?>
	<?php if (isset($introImages)): ?>
		<div id="intro" class="slider" data-loop="<?= $intro->loop() ?>" data-interval="<?= $intro->timer() ?>">
			<?php foreach ($introImages as $key => $image): ?>
				<?php if ($media = $image->toFile()): ?>
					<div class="slide">
						<?php
						if(!isset($maxWidth)) $maxWidth = 1020;
						$src = $media->width(680)->url();
						$srcset = $media->width(680)->url() . ' 680w,';
						for ($i = 1020; $i <= $maxWidth; $i += 340) $srcset .= $media->width($i)->url() . ' ' . $i . 'w,';
						?>
						<img class="media"
						src="<?= $src ?>"
						srcset="<?= $srcset ?>"
						height="100%"
						width="100%" />
					</div>
				<?php endif ?>
			<?php endforeach ?>
		</div>
	<?php endif ?>
<?php endif ?>