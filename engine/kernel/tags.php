<?php

class Tags extends Pages {

    public function page(string $path = null, array $lot = []) {
        return new Tag($path, $lot);
    }

    public static function from(...$lot) {
        if (is_array($v = reset($lot))) {
            return parent::from($v);
        }
        $lot[0] = $lot[0] ?? LOT . D . 'tag';
        return parent::from(...$lot);
    }

}