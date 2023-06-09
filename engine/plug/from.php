<?php

From::_('tag', static function ($name) {
    if (is_string($name)) {
        $folder = LOT . D . 'tag' . D . $name;
        // Get from external `id` data
        if (is_file($file = $folder . D . 'id.data') && filesize($file) > 0) {
            $id = trim((string) fgets($h = fopen($file, 'r')));
            fclose($h);
            return is_numeric($id) ? (int) $id : $id;
        } else if ($file = exist([
            $folder . '.archive',
            $folder . '.page'
        ], 1)) {
            // Get from internal `id` data
            return From::page(file_get_contents($file), true)['id'] ?? null;
        }
    }
    // Else…
    return null;
});