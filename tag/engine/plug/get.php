<?php

// Based on `lot\extend\page\engine\plug\get.php`

function fn_get_tags($folder = TAG, $state = 'page', $sort = [1, 'id'], $key = null) {
    return call_user_func('fn_get_pages', $folder, $state, $sort, $key);
}

Get::_('tags', 'fn_get_tags');