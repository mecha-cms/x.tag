<?php

class Tag extends Page {

    public function __construct($input = null, $lot = [], $NS = ['*', 'tag']) {
        parent::__construct($input, $lot, $NS);
    }

    public static function open($path, $lot = [], $NS = ['*', 'tag']) {
        return parent::open($path, $lot, $NS);
    }

}