<?php

// Based on `lot\extend\page\engine\plug\get.php`

function fn_get_tag($path, $key = null, $fail = false, $for = null) {
    return call_user_func('fn_get_page', $path, $key, $fail, $for);
}

function fn_get_tags($folder = TAG, $state = 'page', $sort = [1, 'id'], $key = null) {
    return call_user_func('fn_get_pages', $folder, $state, $sort, $key);
}

Get::plug('tag', 'fn_get_tag');
Get::plug('tags', 'fn_get_tags');