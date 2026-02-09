<?php

class Tag extends Page {

    public function page(array $lot = []) {
        if (!$this->_exist()) {
            return null;
        }
        if (is_string($v = $this->offsetGet('page')) && is_file($v)) {
            return new Page($v, $lot);
        }
        return null;
    }

    public function route(...$lot) {
        if (0 === strpos($this->path ?? D, LOT . D . 'tag' . D)) {
            extract(lot(), EXTR_SKIP);
            $name = $this->name;
            $parent = $this->parent;
            return ($parent ? $parent->route : "") . '/' . trim($state->x->tag->route ?? 'tag', '/') . '/' . $name . ($parent ? '/1' : "");
        }
        return parent::route(...$lot);
    }

}