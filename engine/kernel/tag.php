<?php

class Tag extends Page {

    public function link(...$lot) {
        if ($v = parent::link(...$lot)) {
            return $v;
        }
        extract($GLOBALS, EXTR_SKIP);
        if ($name = $this->_exist() ? pathinfo($this->path, PATHINFO_FILENAME) : null) {
            $route = trim($state->x->tag->route ?? 'tag', '/');
            if ($parent = $this->parent) {
                return $parent->url . '/' . $route . '/' . $name . '/1';
            }
            return $url . '/' . $route . '/' . $name . '/1';
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

}