<?php

To::_('tag', function($id, $fail = false) {
    if (!is_int($id)) return $fail;
    // Search for external `id` data
    foreach (glob(TAG . DS . '*' . DS . 'id.data') as $v) {
        if ((int) file_get_contents($v) === $id) {
            return Path::B(Path::D($v));
        }
    }
    // Search for embedded `id` data
    foreach (glob(TAG . DS . '*.{page,archive}', GLOB_BRACE) as $v) {
        if (Page::apart($v, 'id', null, true) === $id) {
            return Path::N($v);
        }
    }
    return $fail;
});