<?php

From::_('tag', function($slug) {
    if (is_string($slug)) {
        $r = TAG . DS . $slug;
        // Get from external `id` data
        if ($f = File::exist($r . DS . 'id.data')) {
            return is_file($f) ? (int) file_get_contents($f) : null;
        } else if ($f = File::exist([
            $r . '.page',
            $r . '.archive'
        ])) {
            // Get from embedded `id` data
            return is_file($f) ? Page::apart(file_get_contents($f), 'id', true) : null;
        }
    }
    // Else…
    return null;
});