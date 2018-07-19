<?php

return function ($site, $pages, $page) {

	return array(
	 'medias' => $page->medias()->toStructure(),
	);
}

?>
