<?php

From::_('tag', function($slug, $fail = false) {
    if (!is_string($slug)) {
        return $fail;
    }
    $s = TAG . DS . $slug;
    // Get from external `id` data
    if ($f = File::exist($s . DS . 'id.data')) {
        return is_file($f) ? (int) file_get_contents($f) : $fail;
    } else if ($f = File::exist([
        $s . '.page',
        $s . '.archive'
    ])) {
        // Get from embedded `id` data
        return is_file($f) ? Page::apart($f, $id, $fail, true) : $fail;
    }
    // Else…
    return $fail;
});