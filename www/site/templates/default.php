<?php snippet('header') ?>

<div id="page-panel" class="visible">
	<div event-target="close-panel">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><path d="M100 2.1L97.9 0 50 47.9 2.1 0 0 2.1 47.9 50 0 97.9l2.1 2.1L50 52.1 97.9 100l2.1-2.1L52.1 50z"/></svg>
	</div>
	<?= $page->formattedDesc() ?>
</div>
<div id="overlay" event-target="close-panel"></div>

<?php snippet('footer') ?>
