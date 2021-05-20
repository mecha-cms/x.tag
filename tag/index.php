<?php

require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'from.php';
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'page.php';
require __DIR__ . DS . 'engine' . DS . 'plug' . DS . 'to.php';

require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'tag.php';

$chops = explode('/', $url->path);
$tag = array_pop($chops);
$prefix = array_pop($chops);

$GLOBALS['tag'] = new Tag;

if (
    $tag &&
    '/' . $prefix === ($state->x->tag->path ?? '/tag') &&
    $file = File::exist([
        LOT . DS . 'tag' . DS . $tag . '.archive',
        LOT . DS . 'tag' . DS . $tag . '.page'
    ])
) {
    $tag = new Tag($file);
    $page = LOT . DS . 'page' . implode(DS, $chops);
    if ($page = File::exist([
        $page . '.archive',
        $page . '.page'
    ])) {
        $tag->page = new Page($page);
    }
    $GLOBALS['tag'] = $tag;
    require __DIR__ . DS . 'engine' . DS . 'r' . DS . 'route.php';
}
