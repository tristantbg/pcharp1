<?php

file::$methods['formattedName'] = function($file, $key) {
    $page = $file->page();

    return esc(strtolower(site()->title().'_'.$page->uid().'_'.$key.'.'.$file->extension()));
};

file::$methods['uniqueId'] = function($file) {
  return $file->page()->uid().'_'.$file->filename();
};

file::$methods['getTags'] = function($file) {
  $page = $file->page();
  $tags = array_merge($page->tags()->split(','), $page->hiddentags()->split(','));
  return implode(',', $tags);
};

file::$methods['displayCredits'] = function($file) {
    $page = $file->page();

    $credits = '';

    foreach ($page->credits()->toStructure() as $key => $c){
        $credit = new Brick('div');
        $credit->attr('class', 'credit');
        $text = '';
        if($c->title()->isNotEmpty()) $text .= $c->title().': ';
        if($c->text()->isNotEmpty()) $text .= $c->text();
        if($c->link()->isNotEmpty()) {
          $link = new Brick('a');
          $link->attr('href', $c->link());
          $link->append($text);
          $credit->append($link);
        } else {
          $credit->append($text);
        }
        $credits .= html($credit);
    }

    return esc($credits);
};

page::$methods['displayCredits'] = function($page) {

    $credits = '';

    foreach ($page->credits()->toStructure() as $key => $c){
        $credit = new Brick('div');
        $credit->attr('class', 'credit');
        $text = '';
        if($c->title()->isNotEmpty()) $text .= $c->title().': ';
        if($c->text()->isNotEmpty()) $text .= $c->text();
        if($c->link()->isNotEmpty()) {
          $link = new Brick('a');
          $link->attr('href', $c->link());
          $link->append($text);
          $credit->append($link);
        } else {
          $credit->append($text);
        }
        $credits .= html($credit);
    }

    return $credits;
};

file::$methods['imageLink'] = function($file) {
    $page = $file->page();

    if ($page->link()->isNotEmpty()) {
      return '<a href="'.$page->link().'" target="_blank">Link</a>';
    } else {
      return '<a href="'.$file->url().'" target="_blank">Image link</a>';
    }
};

file::$methods['relatedImages'] = function($file) {
    $page = $file->page();

    $relatedImages = '';
    $idx = 1;

    foreach ($page->medias()->toStructure()->shuffle() as $key => $f){
        if($idx <= 4 && $f = $f->toFile()) {
          if($f->filename() != $file->filename()) {  
            $related = new Brick('div');
            $related->attr('class', 'related lazy lazyload');
            $related->attr('data-target', $f->uniqueId());
            $related->attr('data-bg', $f->crop(80, 80)->url());
            $relatedImages .= html($related);
            $idx++;
          }
        } else {
          break;
        }
    }

    return esc($relatedImages);
};

kirby()->hook(['panel.page.create', 'panel.page.update'], function($page) {
  if ($page->intendedTemplate() == 'project') {
  	foreach ($page->files() as $key => $f) {
  		$f->update(['date' => $page->date()]);
  	}
  	try {
		if ($page->featured()->empty() && $page->medias()->isNotEmpty()) {
			$page->update(['featured' => $page->medias()->toStructure()->first()->value()]);
		}
	} catch(Exception $e) {
		return response::error($e->getMessage());
	}
  }
});