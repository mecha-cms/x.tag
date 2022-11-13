<?php

From::_('tag', static function ($name) {
    if (is_string($name)) {
        $folder = LOT . D . 'tag' . D . $name;
        // Get from external `id` data
        if (is_file($file = $folder . D . 'id.data') && filesize($file) > 0) {
            return (int) trim((string) fgets(fopen($file, 'r')));
        } else if ($file = exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            // Get from embedded `id` data
            return From::page(file_get_contents($file))['id'] ?? null;
        }
    }
    // Else…
    return null;
});

To::_('tag', static function ($id) {
    if (is_int($id)) {
        // Search for external `id` data
        foreach (glob(LOT . D . 'tag' . D . '*' . D . 'id.data', GLOB_NOSORT) as $v) {
            if (is_file($v) && $id === (int) file_get_contents($v)) {
                return basename(dirname($v));
            }
        }
        // Search for embedded `id` data
        foreach (glob(LOT . D . 'tag' . D . '*.{archive,page}', GLOB_BRACE | GLOB_NOSORT) as $v) {
            if (is_file($v) && $id === (From::page(file_get_contents($v))['id'] ?? null)) {
                return pathinfo($v, PATHINFO_FILENAME);
            }
        }
    }
    return null;
});

function tag(...$lot) {
    return Tag::from(...$lot);
}