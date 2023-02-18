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