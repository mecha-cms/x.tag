<?php

From::_('tag', function($slug, $fail = false) {
    if (!is_string($slug)) return $fail;
    $s = TAG . DS . $slug;
    // Get from external `id` data
    if ($f = File::exist($s . DS . 'id.data')) {
        return (int) file_get_contents($f);
    } else if ($f = File::exist([
        $s . '.page',
        $s . '.archive'
    ])) {
        // Get from embedded `id` data
        return Page::apart($f, $id, $fail, true);
    }
    // Else…
    return $fail;
});