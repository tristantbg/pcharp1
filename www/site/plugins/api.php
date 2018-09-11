<?php

$prefix = "api/v1";

page::$methods['serialize'] = function($page) {

  $data = [];

  $data['title'] = $page->title()->html();
  $data['entries'] = $page->entries()->toStructure();
  $data['text'] = $page->text()->kt();
    
  $html = new Brick('div');

  if ($page->intendedTemplate() == 'entries') {
    foreach ($data['entries'] as $key => $e) {
      $brick = new Brick('div');
      $brick->attr('class', 'entry');
      appendBrick($e->title()->html(), 'entry-title', $brick);
      appendBrick($e->text()->kt(), 'entry-text', $brick);
      appendBrick($brick, 'entry', $html);
    }
  } else {
    appendBrick($data['text'], 'entry', $html);
  }

  $json[] = array(
    'url'   => (string)$page->url(),
    'title' => (string)$page->title(),
    'template' => (string)$page->intendedTemplate(),
    'text'  => (string)$page->text()->kt()->escape(),
    'formattedText' => esc($html)
  );

  return $json;

};

kirby()->routes([
  [
    'method' => 'GET',
    'pattern' => "{$prefix}/works",
    'action' => function() {
      
      // if(r::ajax() || !site()->user() && !r::ajax()) go(url('error'));

      $projects = page('works')->children()->visible()->sortBy('date', 'desc');

      // Get all medias
      $allMedias = new Collection();

      foreach ($projects as $p) {
        foreach ($p->medias()->toStructure() as $m) {
          if ($f = $m->toFile()) {
            $allMedias->data[] = $f;
          }
        }
      }
      $data = [];

      foreach ($allMedias as $m) {
        $data[$m->uniqueId()] = [];
        $data[$m->uniqueId()]['project'] = $m->page()->uid();
        $data[$m->uniqueId()]['formattedText'] = esc($m->formattedDesc());
        $data[$m->uniqueId()]['overview'] = !$m->notInGrid()->bool();
      }

      return response::json($data);
    }
  ],
  [
    'method' => 'GET',
    'pattern' => "{$prefix}/works/(:any)",
    'action' => function($uid) {

      // if(r::ajax() || !site()->user() && !r::ajax()) go(url('error'));

      $project = page('works/'.$uid);
      $medias = new Collection();

      if ($project) {
        foreach ($project->medias()->toStructure() as $m) {
          if ($f = $m->toFile()) {
            $medias->data[] = $f;
          }
        }
      }

      $data = [];

      foreach ($medias as $m) {
        $data[$m->uniqueId()] = [];
        $data[$m->uniqueId()]['formattedText'] = esc($m->formattedDesc());
        $data[$m->uniqueId()]['overview'] = !$m->notInGrid()->bool();
      }

      return response::json($data);
    }
  ],
  [
    'method' => 'GET',
    'pattern' => "{$prefix}/page/(:any)",
    'action' => function($uid) {

      // if(r::ajax() || !site()->user() && !r::ajax()) go(url('error'));

      $page = page($uid);

      if ($page) {
        $data = $page->serialize();
        return response::json($data);
      }
    }
  ],
]);

?>