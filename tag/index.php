<?php

// Create a `tag` folder in `lot` if it is not there
$f = LOT . DS . 'tag';
if (!Folder::exist($f)) {
    Folder::set($f, 0600);
    File::write('deny from all')->saveTo($f . DS . '.htaccess', 0600);
    Guardian::kick($url->current);
}

require __DIR__ . DS . 'engine' . DS . 'fire.php';