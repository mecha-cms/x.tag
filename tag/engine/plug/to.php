<?php

To::plug('tag', function($id, $fail = false) {
    if (!is_int($id)) return $fail;
    foreach (glob(TAG . DS . '*' . DS . 'id.data') as $v) {
        if ((int) file_get_contents($v) === $id) {
            return Path::B(Path::D($v));
        }
    }
    return $fail;
});