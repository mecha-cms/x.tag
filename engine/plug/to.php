<?php

To::_('tag', static function ($id) {
    if (!is_int($id)) {
        return null;
    }
    // Search for external `id` data
    foreach (glob(LOT . D . 'tag' . D . '*' . D . 'id.data', GLOB_NOSORT) as $v) {
        if (is_file($v) && $id === (int) trim((string) fgets(fopen($v, 'r')))) {
            return basename(dirname($v));
        }
    }
    // Search for internal `id` data
    foreach (glob(LOT . D . 'tag' . D . '*.{archive,page}', GLOB_BRACE | GLOB_NOSORT) as $v) {
        if (is_file($v) && $id === (From::page(file_get_contents($v))->id ?? null)) {
            return pathinfo($v, PATHINFO_FILENAME);
        }
    }
    return null;
});