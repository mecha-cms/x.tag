<?php

class Tags extends Pages {

    public $page = null;
    public $parent = null;

    public function page(string $path = null, array $lot = []) {
        $tag = new Tag($path, $lot);
        $tag->page = $this->page;
        $tag->parent = $this->parent;
        return $tag;
    }

    public static function from(...$lot) {
        if (is_array($v = reset($lot))) {
            return parent::from($v);
        }
        $lot[0] = $lot[0] ?? LOT . D . 'tag';
        return parent::from(...$lot);
    }

}