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

file::$methods['hiddenImages'] = function($file) {
    $page = $file->page();
    $hiddenImages = 0;

    foreach ($page->medias()->toStructure() as $m) {
        if ($f = $m->toFile()) {
            if($f->notInGrid()->bool()) $hiddenImages++;
        }
    }

    if ($hiddenImages > 0) {
        return '+'.$hiddenImages;
    }
};

function appendBrick($content, $class, $html) {
    $brick = null;
    if ($content) {
        $brick = Brick('div');
        $brick->attr('class', $class);
        $brick->append($content);
    }
    if ($brick) $html->append($brick);
}

file::$methods['formattedDesc'] = function($file) {
    $page = $file->page();
    $data = [];

    $data['title'] = $file->imageTitle()->isNotEmpty() ?  $file->imageTitle()->html() : $page->title()->html();
    $data['subtitle'] = $file->imageSubtitle()->isNotEmpty() ?  $file->imageSubtitle()->html() : $page->subtitle()->html();
    $data['date'] = $page->date('Y');
    if($page->dateEnd() != '') $data['date'] .= ' / '.$page->date('Y', 'dateEnd');
    $data['type'] = $file->imageType()->isNotEmpty() ?  $file->imageType()->html() : $page->type()->html();
    $data['format'] = $file->imageFormat()->isNotEmpty() ?  $file->imageFormat()->kt() : $page->format()->kt();
    $data['materials'] = $file->imageMaterials()->isNotEmpty() ?  $file->imageMaterials()->kt() : $page->materials()->kt();
    $data['text'] = $file->imageText()->isNotEmpty() ?  $file->imageText()->kt() : $page->text()->kt();

    $html = new Brick('div');
    $html->attr('class', 'description');

    if ($data['title']) {
        $brick = Brick('div');
        $brick->attr('class', 'p-title mb');
        $brick->append($data['title']);
        if ($data['subtitle'] != '') $brick->append('<div class="p-subtitle">'.$data['subtitle'].'</div>');
        $html->append($brick);
    }

    appendBrick($data['date'], 'p-date mb', $html);
    appendBrick($data['type'], 'p-type mb', $html);
    appendBrick($data['format'], 'p-format mb', $html);
    appendBrick($data['materials'], 'p-materials mb', $html);
    appendBrick($data['text'], 'p-text mb', $html);

    return $html;
};

page::$methods['formattedDesc'] = function($page) {
    $data = [];
    $html = new Brick('div');
    $html->attr('class', 'description');

    if ($page->intendedTemplate() == 'entries') {

        $data['entries'] = $page->entries()->toStructure();

        foreach ($data['entries'] as $key => $e) {
            $brick = new Brick('div');
            appendBrick($e->title()->html(), 'entry-title', $brick);
            appendBrick($e->text()->kt(), 'entry-text', $brick);
            appendBrick($brick, 'entry', $html);
        }

    } elseif($page->intendedTemplate() == 'project') {

        $data['title'] = $page->title()->html();
        $data['subtitle'] = $page->subtitle()->html();
        $data['date'] = $page->date('Y');
        if($page->dateEnd() != '') $data['date'] .= ' / '.$page->date('Y', 'dateEnd');
        $data['type'] = $page->type()->html();
        $data['format'] = $page->format()->kt();
        $data['materials'] = $page->materials()->kt();
        $data['text'] = $page->text()->kt();

        if ($data['title']) {
            $brick = Brick('div');
            $brick->attr('class', 'p-title mb');
            $brick->append($data['title']);
            if ($data['subtitle'] != '') $brick->append('<div class="p-subtitle">'.$data['subtitle'].'</div>');
            $html->append($brick);
        }

        appendBrick($data['date'], 'p-date mb', $html);
        appendBrick($data['type'], 'p-type mb', $html);
        appendBrick($data['format'], 'p-format mb', $html);
        appendBrick($data['materials'], 'p-materials mb', $html);
        appendBrick($data['text'], 'p-text mb', $html);
    }
    else {

        $data['text'] = $page->text()->kt();

        appendBrick($data['text'], 'entry', $html);
    }

    return $html;
};

kirby()->hook(['panel.page.create', 'panel.page.update'], function($page) {
  if ($page->intendedTemplate() == 'project') {
    $page->update([
      'format' => str_replace(' x ', ' × ', $page->format()->value())
    ]);
    foreach ($page->files() as $key => $f) {
        $f->update([
          'date' => $page->date(),
          'imageFormat' => str_replace(' x ', ' × ', $f->imageFormat()->value())
        ]);
    }
    foreach ($page->medias()->toStructure() as $key => $m) {
        if ($f = $m->toFile()) {
            $f->update(['sliderIndex' => $key+1]);
        }
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
