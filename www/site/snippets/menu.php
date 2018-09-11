<header>
	<a href="<?= $site->url() ?>" id="site-title">
		<?php if ($page->isHomepage()): ?>
			<h1><?= $site->title()->html() ?></h1>
		<?php else: ?>
		<h1 class="hidden"><?= $page->title()->html() ?></h1>
		<span><?= $site->title()->html() ?></span>
		<?php endif ?>
	</a>

	<nav id="menu">
    <a id="homepage" href="<?= $site->url() ?>"><?= $site->homepage()->title()->html() ?></a>
		<?php foreach ($pages->visible()->not($site->homepage()) as $key => $item): ?>
      <?php if ($item->intendedTemplate() == 'search'): ?>
        <form id="search" action="<?= $site->url() ?>">
          <input id="query" type="text" name="q">
          <label for="query"><?= $item->title()->html() ?></label>
        </form>
      <?php else: ?>
        <a class="no-barba" href="<?= $item->url() ?>" event-target="page"><?= $item->title()->html() ?></a>
      <?php endif ?>
		<?php endforeach ?>
	</nav>
</header>
