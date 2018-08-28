<?php snippet('header') ?>
	
<div class="slider">

	<?php foreach ($medias as $key => $image): ?>

		<?php if($image = $image->toFile()): ?>
		<?php $isVideo = $image->videofile()->isNotEmpty() || $image->videostream()->isNotEmpty() || $image->videolink()->isNotEmpty() || $image->videoexternal()->isNotEmpty() ?>
	
		<div class="slide" 
		<?php if($image->caption()->isNotEmpty()): ?>
		data-caption="<?= $image->caption()->kt()->escape() ?>"
		<?php endif ?>
		data-media="<?= e($isVideo, 'video', 'image') ?>"
		>
		
		<?php if($isVideo): ?>
			<div class="content video <?= $image->contentSize() ?>">
				<?php 
				$poster = thumb($image, array('width' => 1500))->url();

				if ($image->videostream()->isNotEmpty() || $image->videoexternal()->isNotEmpty() || $image->videofile()->isNotEmpty()) {
					$video  = '<video class="media js-player"';
					$video .= ' poster="'.$poster.'"';
					if ($image->videostream()->isNotEmpty()) {
						$video .= ' data-stream="'.$image->videostream().'"';
					}
					$video .= ' width="100%" height="100%" controls="false" loop>';
					if ($image->videoexternal()->isNotEmpty()) {
						$video .= '<source src=' . $image->videoexternal() . ' type="video/mp4">';
					} else if ($image->videofile()->isNotEmpty()){
						$video .= '<source src=' . $image->videofile()->toFile()->url() . ' type="video/mp4">';
					}
					$video .= '</video>';
					echo $video;
				}
				else {
					$url = $image->videolink();
					if ($image->vendor() == "youtube") {
						echo '<div class="media js-player" data-type="youtube" data-video-id="' . $url  . '"></div>';
					} else {
						echo '<div class="media js-player" data-type="vimeo" data-video-id="' . $url  . '"></div>';
					}
				}
				?>
			</div>
		<?php else: ?>
			<div class="content image"
		    data-caption="<?= $image->caption()->kt()->escape() ?>"
		    >

		      <div class="content image contain">
		        <?php
		        if(!isset($maxWidth)) $maxWidth = 3400;
		        if (isset($ratio)) {
		          $src = $image->crop(1000, floor(1000/$ratio))->url();
		          $srcset = $image->crop(340, floor(340/$ratio))->url() . ' 340w,';
		          for ($i = 680; $i <= $maxWidth; $i += 340) $srcset .= $image->crop($i, floor($i/$ratio))->url() . ' ' . $i . 'w,';
		        } else {
		          $src = $image->width(1000)->url();
		          $srcset = $image->width(340)->url() . ' 340w,';
		          for ($i = 680; $i <= $maxWidth; $i += 340) $srcset .= $image->width($i)->url() . ' ' . $i . 'w,';
		        }
		        ?>
		        <img class="media lazy lazyload"
		        data-flickity-lazyload="<?= $src ?>"
		        data-srcset="<?= $srcset ?>"
		        data-sizes="auto"
		        data-optimumx="1.5"
		        alt="<?= $image->page()->title()->html().' - © '.$site->title()->html() ?>" height="100%" width="auto" />
		        <noscript>
		          <img src="<?= $src ?>" alt="<?= $image->page()->title()->html().' - © '.$site->title()->html() ?>" width="100%" height="auto" />
		        </noscript>
		      </div>

		    </div>
		<?php endif ?>
	
		</div>
	
		<?php endif ?>

	<?php endforeach ?>

	<?php
	
	if ($page->hasNextVisible('date', 'desc')){
		$next = $page->nextVisible('date', 'desc');
	} else {
		$next = $projects->first();
	}
	$nextUrl = $next->url();
	
	?>

	<a id="next-project-link" href="<?= $nextUrl ?>"></a>
	

</div>

<div id="project-description">
	<h1><?= $page->title()->html() ?></h1>
	<?php if($page->text()->isNotEmpty()): ?>
		<div class="main-text">
			<?= $page->text()->kt() ?>
		</div>
	<?php endif ?>
	<?php if($page->additionalText()->isNotEmpty()): ?>
		<div class="additional-text">
			<?= $page->additionalText()->kt() ?>
		</div>
	<?php endif ?>
	<div id="bottom-bar">
		<?php snippet('palette', array('p' => $page)) ?>
		<div id="slide-number"></div>
	</div>
</div>

<?php snippet('footer') ?>