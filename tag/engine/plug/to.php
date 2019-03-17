<?php

To::_('tag', function($id) {
    if (is_int($id)) {
        // Search for external `id` data
        foreach (glob(TAG . DS . '*' . DS . 'id.data', GLOB_NOSORT) as $v) {
            if (is_file($v) && $id === (int) file_get_contents($v)) {
                return basename(dirname($v));
            }
        }
        // Search for embedded `id` data
        foreach (glob(TAG . DS . '*.{page,archive}', GLOB_BRACE | GLOB_NOSORT) as $v) {
            if (is_file($v) && $id === Page::apart(file_get_contents($v), 'id', true)) {
                return pathinfo($v, PATHINFO_FILENAME);
            }
        }
    }
    return null;
});