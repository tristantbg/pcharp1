<?php

return function ($site, $pages, $page) {
	// $projects = $page->children()->visible()->sortBy('date', 'desc');
	$projects = $page->children()->visible()->flip();
	$query = get('q');

	// Get all medias
	$allMedias = new Collection();
	$allMediasSelection = new Collection();

	foreach ($projects as $p) {
		foreach ($p->medias()->toStructure() as $m) {
			if ($f = $m->toFile()) {
				if(!$f->notInGrid()->bool()) $allMediasSelection->data[] = $f;
				$allMedias->data[] = $f;
			}
		}
	}

	// Search
	if ($query) {

		$resultsInMedias = searchMedias($allMedias, $query);

		// Get all medias in projects fitting query
		$resultsInProjects = $projects->search($query);
		$resultsInProjectsMedias = new Collection();
		foreach ($resultsInProjects as $p) {
			foreach ($p->medias()->toStructure() as $m) {
				if ($f = $m->toFile()) {
					$resultsInProjectsMedias->data[] = $f;
				}
			}
		}

		foreach ($resultsInMedias as $key1 => $m1) {
			foreach ($resultsInProjectsMedias as $key2 => $m2) {
				if($m1 == $m2) $resultsInMedias = $resultsInMedias->without($key1);
			}
		}

		foreach ($resultsInMedias as $m) {
			$resultsInProjectsMedias->data[] = $m;
		}

		$results = $resultsInProjectsMedias;

	} else {
		$results = $allMediasSelection;
	}

	return array(
		'projects' => $projects,
		'allMedias' => $allMedias,
		'results' => $results,
		'query' => $query,
	);
}

?>
