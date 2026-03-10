<?php

class Tags extends Pages {

    public function __toString(): string {
        $tags = [];
        foreach ($this->getIterator() as $v) {
            if (!is_string($n = $v->name)) {
                continue;
            }
            $tags[$n] = 1;
        }
        ksort($tags);
        return implode($this->join, array_keys($tags));
    }

    public function page(...$lot) {
        if (($v = reset($lot)) && $v instanceof Tag) {
            return $v;
        }
        if (is_array($v) && isset($v["\0"])) {
            return $v["\0"];
        }
        return new Tag(...$lot);
    }

    public static function from(...$lot) {
        $lot[0] = $lot[0] ?? LOT . D . 'tag';
        return parent::from(...$lot);
    }

}