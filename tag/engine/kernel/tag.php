<?php

class Tag extends Page {

    public function URL() {
        $url = $GLOBALS['url'];
        $p = trim($url->path, '/');
        if (Config::is('tags')) { // → `./blog/tag/tag-name`
            $p = dirname($p, 2);
        } else if (Config::is('page')) { // → `./blog/page-name`
            $p = dirname($p);
        }
        $p = strtr($p, DS, '/');
        $n = $this->exist ? Path::N($this->path) : null;
        return $n ? $url . ($p ? '/' . $p : "") . '/' . state('tag')['/'] . '/' . $n : null;
    }

}