<?php namespace _\page;

function query() {
    $query = [];
    foreach ((array) $this->kind as $v) {
        if ($slug = \To::tag($v)) {
            $query[] = \strtr($slug, '-', ' ');
        }
    }
    return $query;
}

function tags() {
    $tags = [];
    foreach ($this->query as $v) {
        $v = \strtr($v, ' ', '-');
        $tags[$v] = new \Tag(TAG . DS . $v . '.page');
    }
    return new \Anemon($tags);
}

\Page::_('query', __NAMESPACE__ . "\\query");
\Page::_('tags', __NAMESPACE__ . "\\tags");