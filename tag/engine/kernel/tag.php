<?php

class Tag extends Page {

    public function __construct($input = [], $lot = [], $NS = []) {
        global $site, $url;
        $p = $url->path;
        if (is_array($input)) {
            $s = Path::N(isset($input['path']) ? $input['path'] : "");
        } else {
            $s = Path::N($input);
        }
        if ($site->is('tags')) { // → `blog/tag/tag-slug`
            $p = Path::D($p, 2);
        } else if ($site->is('page')) { // → `blog/page-slug`
            $p = Path::D($p);
        }
        parent::__construct($input, array_replace([
            'url' => $s ? $url . ($p ? '/' . $p : "") . '/' . Extend::state('tag', 'path') . '/' . $s : null
        ], $lot), $NS);
    }

}