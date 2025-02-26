<?php

class Tags extends Pages {

    public function page(...$lot) {
        return new Tag(...$lot);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'tag';
        return parent::from(...$lot);
    }

}