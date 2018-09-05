<?php snippet('header') ?>
	
<div id="project-description">
	<div id="media-description"></div>
	<div id="default-description"><?= $page->formattedDesc() ?></div>
</div>

<div id="medias">
	<?php if ($medias && $medias->count() > 0): ?>
		<?php foreach ($medias as $key => $media): ?>
			<?php if ($media->type() == 'image'): ?>
				<a 
				href="#img-<?= $key+1 ?>" 
				class="media visible" 
				data-id="<?= $media->uniqueId() ?>" 
				data-index="<?= $key+1 ?>" 
				event-target="lightbox" 
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
					</div>
				</a>
			<?php endif ?>
		<?php endforeach ?>
	<?php else: ?>
		<div class="row error">
			<span>No images</span>
		</div>
	<?php endif ?>
</div>

<?php if ($medias->count() > 0): ?>
<div id="lightbox">

	<?php foreach ($medias as $key => $media): ?>

		<?php $isVideo = $media->videofile()->isNotEmpty() || $media->videostream()->isNotEmpty() || $media->videolink()->isNotEmpty() || $media->videoexternal()->isNotEmpty() ?>
	
		<div class="slide" 
		id="img-<?= $key+1 ?>" 
		data-id="<?= $media->uniqueId() ?>" 
		data-media="<?= e($isVideo, 'video', 'image') ?>"
		>

		
		<?php if($isVideo): ?>
			<div class="content video <?= $media->contentSize() ?>">
				<?php 
				$poster = thumb($media, array('width' => 1500))->url();

				if ($media->videostream()->isNotEmpty() || $media->videoexternal()->isNotEmpty() || $media->videofile()->isNotEmpty()) {
					$video  = '<video class="media js-player"';
					$video .= ' poster="'.$poster.'"';
					if ($media->videostream()->isNotEmpty()) {
						$video .= ' data-stream="'.$media->videostream().'"';
					}
					$video .= ' width="auto" height="100%" controls="false" loop>';
					if ($media->videoexternal()->isNotEmpty()) {
						$video .= '<source src=' . $media->videoexternal() . ' type="video/mp4">';
					} else if ($media->videofile()->isNotEmpty()){
						$video .= '<source src=' . $media->videofile()->toFile()->url() . ' type="video/mp4">';
					}
					$video .= '</video>';
					echo $video;
				}
				else {
					$url = $media->videolink();
					if ($media->vendor() == "youtube") {
						echo '<div class="media js-player" data-type="youtube" data-video-id="' . $url  . '"></div>';
					} else {
						echo '<div class="media js-player" data-type="vimeo" data-video-id="' . $url  . '"></div>';
					}
				}
				?>
			</div>
			
			<div class="project-description"><?= $media->formattedDesc() ?></div>
		
		<?php else: ?>

		      <div class="content image contain">
		        <?php
		        if(!isset($maxWidth)) $maxWidth = 3400;
		        if (isset($ratio)) {
		          $src = $media->crop(1000, floor(1000/$ratio))->url();
		          $srcset = $media->crop(340, floor(340/$ratio))->url() . ' 340w,';
		          for ($i = 680; $i <= $maxWidth; $i += 340) $srcset .= $media->crop($i, floor($i/$ratio))->url() . ' ' . $i . 'w,';
		        } else {
		          $src = $media->width(1000)->url();
		          $srcset = $media->width(340)->url() . ' 340w,';
		          for ($i = 680; $i <= $maxWidth; $i += 340) $srcset .= $media->width($i)->url() . ' ' . $i . 'w,';
		        }
		        ?>
		        <img class="media lazy lazyload"
		        data-flickity-lazyload="<?= $src ?>"
		        data-srcset="<?= $srcset ?>"
		        data-sizes="auto"
		        data-optimumx="1.5" 
		        alt="<?= $media->page()->title()->html().' - © '.$site->title()->html() ?>" 
		        height="100%" 
		        width="auto" />
		        <noscript>
		          <img src="<?= $src ?>" alt="<?= $media->page()->title()->html().' - © '.$site->title()->html() ?>" width="100%" height="auto" />
		        </noscript>
		      </div>

		      <div class="project-description"><?= $media->formattedDesc() ?></div>

		<?php endif ?>
	
		</div>
	
	<?php endforeach ?>

	<?php
	
	if ($page->hasNextVisible('date', 'desc')){
		$next = $page->nextVisible('date', 'desc');
	} else {
		$next = $projects->first();
	}
	$nextUrl = $next->url();

	if ($page->hasPrevVisible('date', 'desc')){
		$prev = $page->prevVisible('date', 'desc');
	} else {
		$prev = $projects->last();
	}
	$prevUrl = $prev->url();
	
	?>

	<a id="next-project-link" href="<?= $nextUrl ?>"></a>
	<a id="previous-project-link" href="<?= $prevUrl ?>"></a>
	<a id="overview-link" href="<?= $site->url() ?>">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="-40.8 -32.3 100 100"><path d="M59.2-6.4l-50 50-50-50 1.9-1.9L9.2 39.9 57.3-8.3z"/></svg>
	</a>
	

</div>
<?php endif ?>

<?php snippet('footer') ?>