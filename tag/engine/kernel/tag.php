<?php

class Tag extends Page {

    public $page = null;

    public function __construct(string $path = null, array $lot = []) {
        $c = c2f(self::class);
        parent::__construct($path, array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot));
        $this->h[] = $c;
    }

    public function link(...$lot) {
        if ($link = $this->_get('link', $lot)) {
            return $link;
        }
        extract($GLOBALS, EXTR_SKIP);
        $n = $this->exist ? Path::N($this->path) : null;
        $path = $this->page ? $this->page->url : null;
        return $n && $path ? $path . ($state->x->tag->path ?? '/tag') . '/' . $n . '/1' : null;
    }

}
