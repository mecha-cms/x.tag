<?php

class Tag extends Page {

    public function __construct(string $path = null, array $lot = []) {
        $c = c2f(self::class);
        parent::__construct($path, array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot));
        $this->h[] = $c;
    }

    public function URL(...$lot) {
        extract($GLOBALS, EXTR_SKIP);
        $n = $this->exist ? Path::N($this->path) : null;
        $path = $this->page->url ?? $url . $state->path . '/' . P;
        return $n ? dirname($path) . ($state->x->tag->path ?? '/tag') . '/' . $n . '/1' : null;
    }

}
