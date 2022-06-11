<?php

class Tag extends Page {

    public $page = null;
    public $parent = null;

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
        if (!$page = $this->page) {
            return null;
        }
        if (!$lot) {
            return $page;
        }
        foreach ($lot as $k => $v) {
            $page->{$k} = $v;
        }
        return $page;
    }

    public function parent(array $lot = []) {
        if (!$this->exist()) {
            return null;
        }
        if (!$parent = $this->parent) {
            return null;
        }
        if (!$lot) {
            return $parent;
        }
        foreach ($lot as $k => $v) {
            $parent->{$k} = $v;
        }
        return $parent;
    }

}