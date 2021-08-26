<?php

class Tag extends Page {

    public $page = null;
    public $parent = null;

    public function __construct(string $path = null, array $lot = []) {
        $c = c2f(self::class);
        parent::__construct($path, array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot));
        $this->h[] = $c;
    }

    public function link(...$lot) {
        if ($v = parent::get('link')) {
            return $v;
        }
        extract($GLOBALS, EXTR_SKIP);
        $n = $this->exist ? Path::N($this->path) : null;
        $path = $this->parent ? $this->parent->url : null;
        return $n && $path ? $path . ($state->x->tag->path ?? '/tag') . '/' . $n . '/1' : null;
    }

}