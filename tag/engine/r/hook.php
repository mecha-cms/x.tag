<?php

// Check if current page has at least one tag
Hook::set('set', function() {
    extract($GLOBALS);
    if (!empty($page)) {
        Config::set('has.tags', $page->tags && count($page->tags) > 0);
    }
});