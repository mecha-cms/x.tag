<?php

class Tag extends Page {

    public $page = null;
    public $parent = null;

    public function __construct(string $path = null, array $lot = []) {
        $c = c2f(self::class);
        parent::__construct($path, array_replace_recursive((array) State::get('x.' . $c . '.page', true), $lot));
        $this->hook[] = $c;
    }

    public function link(...$lot) {
        if ($v = parent::get('link')) {
            return $v;
        }
        extract($GLOBALS, EXTR_SKIP);
        $name = $this->exist() ? pathinfo($this->path, PATHINFO_FILENAME) : null;
        $parent = $this->parent ? $this->parent->url : null;
        return $name && $parent ? $parent . '/' . trim($state->x->tag->route ?? 'tag', '/') . '/' . $name . '/1' : null;
    }

}