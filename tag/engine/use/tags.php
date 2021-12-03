<?php

class Tags extends Pages {

    public $page = null;
    public $parent = null;

    public function page(string $path) {
        $tag = new Tag($path);
        $tag->page = $this->page;
        $tag->parent = $this->parent;
        return $tag;
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'tag';
        return parent::from(...$lot);
    }

}

function tags(...$v) {
    return Tags::from(...$v);
}