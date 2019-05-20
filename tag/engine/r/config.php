<?php

// Store comment state to registry…
$state = extend('tag');
if (!empty($state['tag'])) {
    // Prioritize default state
    Config::alt($state);
    User::$data = array_replace_recursive(Page::$data, (array) Config::get('tag', true));
}