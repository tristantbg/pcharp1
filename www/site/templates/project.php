<?php snippet('header') ?>

<div id="project-description">
	<div id="media-description"></div>
	<div id="default-description"><?= $page->formattedDesc() ?></div>
  <?php if ($page->textEn()->isNotEmpty()): ?>
    <div event-target="text">Text</div>
    <div id="project-texts">
      <div event-target="close-panel">
      	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M100 2.1L97.9 0 50 47.9 2.1 0 0 2.1 47.9 50 0 97.9l2.1 2.1L50 52.1 97.9 100l2.1-2.1L52.1 50z"/></svg>
      </div>
      <div class="text-content" lang="<?php e($page->textFr()->isNotEmpty(), 'en', 'none') ?>">
        <div event-target="lang-switch"></div>
        <div class="text-en"><?= $page->textEn()->kt() ?></div>
        <?php if ($page->textFr()->isNotEmpty()): ?>
          <div class="text-fr"><?= $page->textFr()->kt() ?></div>
        <?php endif ?>
      </div>
    </div>
  <?php endif ?>
</div>

<div id="medias">
	<?php if ($medias && $medias->count() > 0): ?>
		<?php foreach ($medias as $key => $media): ?>
			<?php if ($media->type() == 'image'): ?>
				<a
				href="#img-<?= $key+1 ?>"
				class="media visible"
				data-id="<?= $media->uniqueId() ?>"
				data-project="<?= $media->page()->uid() ?>"
				data-index="<?= $key+1 ?>"
				event-target="lightbox"
				style="width: <?= $media->width() ?>px; height: <?= $media->height() ?>px"
				>
					<div class="inner">
						<?php
						$src = $media->width(50)->dataUri();
						$srcset = $media->width(500)->url() . ' 500w,';
						for ($i = 1000; $i <= 1500; $i += 500) $srcset .= $media->width($i)->url() . ' ' . $i . 'w,';
						?>
						<img class="media-element lazy lazyload"
						src="<?= $src ?>" 
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

	<div id="lightbox-header">
		<div event-target="lightbox">
			<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M100 2.1L97.9 0 50 47.9 2.1 0 0 2.1 47.9 50 0 97.9l2.1 2.1L50 52.1 97.9 100l2.1-2.1L52.1 50z"/></svg>
		</div>
		<?php if ($page->textEn()->isNotEmpty()): ?>
    		<div event-target="text">Text</div>
    	<?php endif ?>
	</div>

	<?php foreach ($medias as $key => $media): ?>

		<?php $isVideo = $media->filemp4()->isNotEmpty() || $media->stream()->isNotEmpty() || $media->mp4()->isNotEmpty() ?>

		<div class="slide"
		id="img-<?= $key+1 ?>"
		data-id="<?= $media->uniqueId() ?>"
		data-media="<?= e($isVideo, 'video', 'image') ?>"
		>


		<?php if($media->stream()->isNotEmpty() || $media->mp4()->isNotEmpty() || $media->filemp4()->isNotEmpty()): ?>
			<div class="content video">
  				<?php
  				$poster = thumb($media, array('width' => 1020))->url();
				$video  = '<video class="js-player"';
				// $video .= ' poster="'.$poster.'"';
				if ($media->stream()->isNotEmpty()) {
					$video .= ' data-stream="'.$media->stream().'"';
				}
				$video .= ' width="auto" height="100%" controls="false" playsinline muted autoplay loop>';
				if ($media->mp4()->isNotEmpty()) {
					$video .= '<source src=' . $media->mp4() . ' type="video/mp4">';
				} else if ($media->filemp4()->isNotEmpty()){
					$video .= '<source src=' . $media->filemp4()->toFile()->url() . ' type="video/mp4">';
				}
				if ($media->webm()->isNotEmpty()) {
					$video .= '<source src=' . $media->webm() . ' type="video/webm">';
				} else if ($media->filewebm()->isNotEmpty()){
					$video .= '<source src=' . $media->filewebm()->toFile()->url() . ' type="video/webm">';
				}
				$video .= '</video>';
				echo $video;
  				?>
			</div>

			<div class="project-description">
        		<?= $media->formattedDesc() ?>
      		</div>

		<?php else: ?>

		      <div class="content image<?= e($media->ratio() > 1.6, ' wide') ?>">
              <?php
              	if(!isset($maxWidth)) $maxWidth = 2720;
                // $placeholder = $media->width(50)->dataUri();
                $src = $media->width(1000)->url();
                $srcset = $media->width(340)->url() . ' 340w,';
                for ($i = 680; $i <= $maxWidth; $i += 340) $srcset .= $media->width($i)->url() . ' ' . $i . 'w,';
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

		      <div class="project-description">
		      	<?= $media->formattedDesc() ?>
		      	<?php if ($page->textEn()->isNotEmpty()): ?>
    				<div event-target="text">Text</div>
				<?php endif ?>
		      </div>

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
	$prevUrl .= '?slide=img-'.$prev->medias()->toStructure()->count()

	?>

	<a id="next-project-link" href="<?= $nextUrl ?>"></a>
	<a id="previous-project-link" href="<?= $prevUrl ?>"></a>

</div>
<?php endif ?>

<div id="page-panel"></div>
<div id="overlay" event-target="close-panel"></div>
<a id="overview-link" href="<?= $site->url() ?>">
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M100 26.1l-50 50-50-50 2.1-2.2L50 71.8l47.9-47.9z"/></svg>
</a>

<?php snippet('footer') ?>
