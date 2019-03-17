<?php

// Check if current page has at least one tag
Hook::set('enter', function() {
    if ($page = Lot::get('page')) {
        Config::set('has.tags', $page->tags && count($page->tags) > 0);
    }
});