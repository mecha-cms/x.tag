<?php

From::plug('tag', function($slug, $fail = false) {
    if (!is_string($slug)) return $fail;
    $f = TAG . DS . $slug . DS . 'id.data';
    if (!file_exists($f)) return $fail;
    return (int) file_get_contents($f);
});