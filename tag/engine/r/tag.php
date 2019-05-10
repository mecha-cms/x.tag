<?php

function tag(...$v) {
    return new Tag(...$v);
}

function tags(...$v) {
    return Get::tags(...$v);
}