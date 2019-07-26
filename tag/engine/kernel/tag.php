<?php

class Tag extends Page {

    public function __construct(string $path = null, array $lot = [], $prefix = []) {
        $url = $GLOBALS['url'];
        $p = trim($url->path, '/');
        if (Config::is('tags')) { // → `./blog/tag/tag-slug`
            $p = dirname($p, 2);
        } else if (Config::is('page')) { // → `./blog/page-slug`
            $p = dirname($p);
        }
        $p = strtr($p, DS, '/');
        $n = $path ? Path::N($path) : null;
        parent::__construct($path, array_replace_recursive([
            'url' => $n ? $url . ($p ? '/' . $p : "") . '/' . state('tag')['/'] . '/' . $n : null
        ], $lot), $prefix);
    }

}