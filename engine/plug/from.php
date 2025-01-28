<?php

From::_('tag', static function ($name) {
    if (!is_string($name)) {
        return null;
    }
    static $cache = [];
    if (isset($cache[$name])) {
        return $cache[$name];
    }
    $folder = LOT . D . 'tag' . D . $name;
    // Get from external `id` data
    if (is_file($file = $folder . D . 'id.data') && filesize($file) > 0) {
        $id = trim((string) fgets(fopen($file, 'r')));
        return ($cache[$name] = is_numeric($id) ? (int) $id : $id);
    }
    if ($file = exist([
        $folder . '.archive',
        $folder . '.page'
    ], 1)) {
        // Get from internal `id` data
        if (null !== ($id = From::page(file_get_contents($file))->id ?? null)) {
            return ($cache[$name] = $id);
        }
    }
    return null;
});