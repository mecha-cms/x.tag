<?php

// Check if current page has at least one tag
Hook::set('enter', function() {
    extract($GLOBALS, EXTR_SKIP);
    if (!empty($page)) {
        Config::set('has.tags', $page->tags && count($page->tags) > 0);
    }
});