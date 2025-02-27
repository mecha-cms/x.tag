<?php

class Tag extends Page {

    public function link(...$lot) {
        if ($v = parent::link(...$lot)) {
            return $v;
        }
        extract(lot(), EXTR_SKIP);
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
        if ($v = $this->offsetGet('page')) {
            if (is_string($v) && is_file($v)) {
                return new Page($v, $lot);
            }
        }
        return null;
    }

    public function route(...$lot) {
        if ($path = $this->_exist()) {
            if (0 === strpos($path, $r = LOT . D . 'tag' . D)) {
                $folder = dirname($path) . D . pathinfo($path, PATHINFO_FILENAME);
                return '/' . trim(strtr($folder, [$r => '/', D => '/']), '/');
            }
        }
        return parent::route(...$lot);
    }

}