<?php

class Tag extends Page {

    public function __construct(string $path = null, array $lot = []) {
        $c = c2f(self::class);
        parent::__construct($path, array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot));
        $this->h[] = $c;
    }

    public function URL(...$lot) {
        extract($GLOBALS, EXTR_SKIP);
        $p = trim($url->path ?? $state->path ?? "", '/');
        if ($state->is('tags')) { // → `./blog/tag/tag-name`
            $p = dirname($p, 2);
        } else if ($state->is('page')) { // → `./blog/page-name`
            $p = dirname($p);
        }
        $p = strtr($p, DS, '/');
        $n = $this->exist ? Path::N($this->path) : null;
        return $n ? $url . ($p ? '/' . $p : "") . ($state->x->tag->path ?? '/tag') . '/' . $n : null;
    }

}
