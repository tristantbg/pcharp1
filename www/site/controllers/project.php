<?php

return function ($site, $pages, $page) {
	
	$medias = new Collection();

	foreach ($page->medias()->toStructure() as $m) {
		if ($f = $m->toFile()) {
			$medias->data[] = $f;
		}
	}

	return array(
	 'medias' => $medias
	);
}

?>
