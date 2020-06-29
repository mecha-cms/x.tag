<?php

class Tags extends Pages {

    public $page = null; // The parent page

    public function page(string $path) {
        return new Tag($path, ['page' => $this->page]);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . DS . 'tag';
        return parent::from(...$lot);
    }

}
