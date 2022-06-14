<?php

class Tag extends Page {

    public function link(...$lot) {
        if ($v = parent::link()) {
            return $v;
        }
        extract($GLOBALS, EXTR_SKIP);
        $name = $this->exist() ? pathinfo($this->path, PATHINFO_FILENAME) : null;
        if ($name && $parent = $this->parent) {
            return $parent->url . '/' . trim($state->x->tag->route ?? 'tag', '/') . '/' . $name . '/1';
        }
        return null;
    }

    public function page(array $lot = []) {
        if (!$this->exist()) {
            return null;
        }
        $path = $this->lot['page'] ?? null;
        if (!is_string($path) || !is_file($path)) {
            return null;
        }
        return new Page($path, $lot);
    }

    public function parent(array $lot = []) {
        if (!$this->exist()) {
            return null;
        }
        $path = $this->lot['parent'] ?? null;
        if (!is_string($path) || !is_file($path)) {
            return null;
        }
        return new Page($path, $lot);
    }

}