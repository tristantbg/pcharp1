<?php

return function ($site, $pages, $page) {
	$projects = $site->index()->filterBy('intendedTemplate', 'project')->visible();

	return array(
	 'medias' => $page->medias()->toStructure(),
	 'projects' => $projects,
	);
}

?>
