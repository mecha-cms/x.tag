<?php

class tags extends Pages {

    public function page(string $path) {
        return new Tag($path);
    }

}