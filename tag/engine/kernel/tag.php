<?php

class Tag extends Page {

    public function __construct($path = null, array $lot = [], $NS = []) {
        global $site, $url;
        $p = $GLOBALS['URL']['path'];
        $n = $path ? Path::N($path) : null;
        if ($site->is('tags')) { // → `blog/tag/tag-slug`
            $p = Path::D($p, 2);
        } else if ($site->is('page')) { // → `blog/page-slug`
            $p = Path::D($p);
        }
        parent::__construct($path, array_replace([
            'url' => $n ? $GLOBALS['URL']['$'] . ($p ? '/' . $p : "") . '/' . Extend::state('tag', 'path') . '/' . $n : null
        ], $lot), $NS);
    }

}