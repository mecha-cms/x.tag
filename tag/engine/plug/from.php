<?php

From::_('tag', function($slug) {
    if (is_string($slug)) {
        $r = TAG . DS . $slug;
        // Get from external `id` data
        if (is_file($f = $r . DS . 'id.data') && filesize($f) > 0) {
            return (int) file_get_contents($f);
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