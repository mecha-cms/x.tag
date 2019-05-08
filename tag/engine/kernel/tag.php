<?php

class Tag extends Page {

    // Set pre-defined tag property
    public static $data = [];

    public function __construct(string $path = null, array $lot = [], $prefix = []) {
        $p = trim($GLOBALS['URL']['path'], '/');
        if (Config::is('tags')) { // → `blog/tag/tag-slug`
            $p = Path::D($p, 2);
        } else if (Config::is('page')) { // → `blog/page-slug`
            $p = Path::D($p);
        }
        $p = strtr($p, DS, '/');
        $n = $path ? Path::N($path) : null;
        parent::__construct($path, array_replace_recursive([
            'url' => $n ? $GLOBALS['URL']['$'] . ($p ? '/' . $p : "") . '/' . Extend::state('tag', 'path') . '/' . $n : null
        ], $lot), $prefix);
    }

}