<?php

class Tags extends Pages {

    public function __toString(): string {
        $tags = [];
        foreach ($this->getIterator() as $v) {
            $tags[$v->name] = 1;
        }
        ksort($tags);
        return implode($this->join, array_keys($tags));
    }

    public function page(...$lot) {
        return new Tag(...$lot);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'tag';
        return parent::from(...$lot);
    }

}