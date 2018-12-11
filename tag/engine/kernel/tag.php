<?php

class Tag extends Page {

    public function __construct(string $path = null, array $lot = [], $NS = []) {
        global $config, $url;
        $p = $GLOBALS['URL']['path'];
        $n = $path ? Path::N($path) : null;
        if ($config->is('tags')) { // → `blog/tag/tag-slug`
            $p = Path::D($p, 2);
        } else if ($config->is('page')) { // → `blog/page-slug`
            $p = Path::D($p);
        }
        parent::__construct($path, extend([
            'url' => $n ? $GLOBALS['URL']['$'] . ($p ? '/' . $p : "") . '/' . Extend::state('tag', 'path') . '/' . $n : null
        ], $lot, false), $NS);
    }

}