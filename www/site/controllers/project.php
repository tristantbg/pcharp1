<?php

return function ($site, $pages, $page) {
	
	// $projects = $page->parent()->children()->visible()->sortBy('date', 'desc');
	$projects = $page->parent()->children()->visible()->flip();
	$medias = new Collection();

	foreach ($page->medias()->toStructure() as $m) {
		if ($f = $m->toFile()) {
			$medias->data[] = $f;
		}
	}

	return array(
	 'medias' => $medias,
	 'projects' => $projects
	);
}

?>
