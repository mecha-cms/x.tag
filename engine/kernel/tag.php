<?php

class Tag extends Page {

    public function link(...$lot) {
        if ($v = parent::link()) {
            return $v;
        }
        extract($GLOBALS, EXTR_SKIP);
        $name = $this->_exist() ? pathinfo($this->path, PATHINFO_FILENAME) : null;
        if ($name && $parent = $this->parent) {
            return $parent->url . '/' . trim($state->x->tag->route ?? 'tag', '/') . '/' . $name . '/1';
        }
        return null;
    }

    public function page(array $lot = []) {
        if (!$this->_exist()) {
            return null;
        }
        $path = $this['page'] ?? null;
        if (!is_string($path) || !is_file($path)) {
            return null;
        }
        return new Page($path, $lot);
    }

    public function parent(array $lot = []) {
        if (!$this->_exist()) {
            return null;
        }
        $path = $this['parent'] ?? null;
        if (!is_string($path) || !is_file($path)) {
            return null;
        }
        return new Page($path, $lot);
    }

}